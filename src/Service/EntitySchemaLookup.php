<?php

declare(strict_types=1);

namespace CoolMS\Entity\Service;

use BackedEnum;
use CoolMS\Entity\Contract\EntityTypeSchemaContributorInterface;
use CoolMS\Entity\Contract\FieldSchemaSourceInterface;
use CoolMS\Entity\Registry\EntityAliasRegistry;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Resolves the extras schema for an entity alias.
 *
 * Owned by this package because extras is an entity capability: many modules
 * declare it on their own entities, and none of them is a runtime-type module.
 * What the engine needs from higher modules arrives through ports:
 *
 *   FieldSchemaSourceInterface            record-defined fields
 *   EntityTypeSchemaContributorInterface  type inheritance, OPTIONAL
 */
class EntitySchemaLookup
{
    /**
     * @var array<string, array<string, mixed>> In-memory request-scoped cache
     */
    private array $cache = [];

    private readonly PropertyAccessorInterface $propertyAccessor;

    public function __construct(
        private readonly FieldSchemaSourceInterface $fieldSchemaSource,
        private readonly string $configDir = '',
        private readonly string $modulesDir = '',
        private readonly ?EntityTypeSchemaContributorInterface $typeContributor = null,
        private readonly ?EntityAliasRegistry $aliasRegistry = null,
        ?PropertyAccessorInterface $propertyAccessor = null,
    ) {
        $this->propertyAccessor = $propertyAccessor ?? PropertyAccess::createPropertyAccessor();
    }

    /**
     * Returns a merged schema for the given entity alias.
     *
     * When the alias names a runtime type with a parent chain, schemas are
     * merged root-to-leaf so that child fields override parent fields.
     *
     * The result is a map of fieldName -- field config array, with
     * record-defined fields overriding any static YAML definitions of the same
     * name.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getSchemaForEntity(string $entityAlias): array
    {
        if (isset($this->cache[$entityAlias])) {
            return $this->cache[$entityAlias];
        }

        $contribution = $this->typeContributor?->getSchemaContribution($entityAlias);
        if (null !== $contribution) {
            // With a parent, always resolve via the full inheritance chain
            // rather than using the potentially incomplete single-type cache.
            if ([] !== $contribution->inheritanceChain) {
                return $this->cache[$entityAlias] = $this->mergeChain($contribution->inheritanceChain);
            }
            // Fast path: pre-built schema cache on the type (no inheritance needed).
            if (null !== $contribution->prebuiltSchema) {
                return $this->cache[$entityAlias] = $contribution->prebuiltSchema;
            }
        }

        // Record-defined fields take precedence over static YAML definitions
        return $this->cache[$entityAlias] = array_merge(
            $this->loadStaticFields($entityAlias),
            $this->fieldSchemaSource->getRuntimeFields($entityAlias),
        );
    }

    /**
     * Returns a freshly built schema for the given alias, always going through
     * the YAML + record merge path -- bypasses the type's pre-built cache.
     *
     * For inherited types the full parent chain is walked.
     *
     * Use this when you need accurate `source` and `locked` metadata
     * (e.g., the Schema Editor endpoint) rather than the potentially stale
     * pre-built schema cached on the type.
     *
     * @return array<string, array<string, mixed>>
     */
    public function buildFreshSchema(string $entityAlias): array
    {
        $contribution = $this->typeContributor?->getSchemaContribution($entityAlias);
        if (null !== $contribution && [] !== $contribution->inheritanceChain) {
            return $this->mergeChain($contribution->inheritanceChain);
        }

        return array_merge(
            $this->loadStaticFields($entityAlias),
            $this->fieldSchemaSource->getRuntimeFields($entityAlias),
        );
    }

    /**
     * Returns the schema filtered to fields that apply to this specific entity instance.
     *
     * Each schema field config may carry an `appliesTo` map of property-name => expected-value
     * pairs (AND semantics, exact match). A field is included only when every key in
     * `appliesTo` matches the instance's property value. Backed enums are unwrapped to their
     * scalar `value` for comparison. Missing properties yield false (field omitted).
     *
     * When the instance class has no registered alias, returns an empty schema.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getSchemaForInstance(object $entity): array
    {
        if (null === $this->aliasRegistry) {
            return [];
        }
        $alias = $this->aliasRegistry->aliasOf($entity::class);
        if (null === $alias) {
            return [];
        }

        return array_filter(
            $this->getSchemaForEntity($alias),
            fn (array $fieldConfig): bool => $this->matchesAppliesTo($fieldConfig['appliesTo'] ?? null, $entity),
        );
    }

    /**
     * Invalidate the in-memory cache entry for one or all entity aliases.
     * Call after creating/updating field definitions at runtime.
     */
    public function invalidate(?string $entityAlias = null): void
    {
        if (null === $entityAlias) {
            $this->cache = [];
        } else {
            unset($this->cache[$entityAlias]);
        }
    }

    /**
     * Merge the schemas of an inheritance chain, root first, so that a child
     * overrides a parent field of the same name (array_merge -- later wins).
     *
     * @param string[] $aliases root-to-leaf
     *
     * @return array<string, array<string, mixed>>
     */
    private function mergeChain(array $aliases): array
    {
        return array_reduce(
            $aliases,
            fn (array $acc, string $alias): array => array_merge(
                $acc,
                $this->loadStaticFields($alias),
                $this->fieldSchemaSource->getRuntimeFields($alias),
            ),
            [],
        );
    }

    /**
     * Load static field definitions for the given alias.
     *
     * Scans two locations (results are merged; per-field files win over per-alias file):
     *
     * 1. Legacy: {configDir}/{alias}.yaml  (single file per alias)
     *    ```yaml
     *    brand:
     *      type: string
     *      label: Brand
     *    ```
     *
     * 2. Per-field: {modulesDir}/ * /fields/{alias}/{fieldName}.yaml
     *    ```yaml
     *    type: string
     *    label: Brand
     *    ```
     *
     * @return array<string, array<string, mixed>>
     */
    private function loadStaticFields(string $alias): array
    {
        $result = [];

        // -- 1. Legacy per-alias file --------------------------------------
        if ('' !== $this->configDir) {
            $file = rtrim($this->configDir, '/') . '/' . $alias . '.yaml';
            if (is_file($file)) {
                /** @var array<string, array<string, mixed>> $fields */
                $fields = Yaml::parseFile($file) ?? [];
                foreach ($fields as $name => &$config) {
                    $config['source'] = 'module';
                    $config['locked'] = (bool) ($config['locked'] ?? true);
                    $config['security'] = [
                        'read' => (array) ($config['security']['read'] ?? []),
                        'write' => (array) ($config['security']['write'] ?? []),
                    ];
                }
                unset($config);
                $result = $fields;
            }
        }

        // -- 2. Per-field files --------------------------------------------
        if ('' !== $this->modulesDir) {
            $pattern = sprintf(
                '%s/*/fields/%s/*.yaml',
                rtrim($this->modulesDir, '/'),
                $alias,
            );
            foreach (glob($pattern) ?: [] as $file) {
                $fieldName = pathinfo($file, PATHINFO_FILENAME);
                /** @var array<string, mixed> $config */
                $config = Yaml::parseFile($file) ?? [];
                if ([] === $config) {
                    continue;
                }
                $config['source'] = 'module';
                $config['locked'] = (bool) ($config['locked'] ?? true);
                $config['security'] = [
                    'read' => (array) ($config['security']['read'] ?? []),
                    'write' => (array) ($config['security']['write'] ?? []),
                ];
                // Per-field files win over the per-alias file for the same field name
                $result[$fieldName] = $config;
            }
        }

        return $result;
    }

    /**
     * AND-semantics across `appliesTo` keys. Each value is either a scalar (exact match)
     * or a list of scalars (OR-any-of via in_array strict).
     *
     * Empty / null map matches every instance. Missing properties yield false.
     * Backed-enum values are compared via `->value`. An empty list value matches
     * nothing -- no value can satisfy in_array against an empty haystack.
     *
     * @param array<string, mixed>|null $appliesTo
     */
    private function matchesAppliesTo(?array $appliesTo, object $entity): bool
    {
        if (null === $appliesTo || [] === $appliesTo) {
            return true;
        }
        foreach ($appliesTo as $property => $expected) {
            try {
                $actual = $this->propertyAccessor->getValue($entity, $property);
            } catch (NoSuchPropertyException) {
                return false;
            }
            if ($actual instanceof BackedEnum) {
                $actual = $actual->value;
            }
            if (is_array($expected)) {
                if (!in_array($actual, $expected, true)) {
                    return false;
                }
            } elseif ($actual !== $expected) {
                return false;
            }
        }

        return true;
    }
}
