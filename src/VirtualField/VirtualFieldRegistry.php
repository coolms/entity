<?php

declare(strict_types=1);

namespace CoolMS\Entity\VirtualField;

use Closure;
use CoolMS\Entity\Attribute\VirtualField;
use CoolMS\Entity\Filter\VirtualFieldDescriptor;
use CoolMS\Entity\Registry\EntityAliasRegistryInterface;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;

/**
 * Phase X-2.5b -- default `VirtualFieldRegistryInterface`
 * implementation.
 *
 * Resolves the entity alias to its FQCN via
 * `EntityAliasRegistryInterface`, walks the class's public methods
 * for `#[VirtualField]` attributes, then appends descriptors from
 * every tagged `VirtualFieldProviderInterface` whose
 * `getTargetEntity()` matches the alias. Results are cached per
 * alias.
 */
final class VirtualFieldRegistry implements VirtualFieldRegistryInterface
{
    /**
     * @var array<string, list<VirtualFieldDescriptor>>
     */
    private array $cache = [];

    /**
     * @param iterable<VirtualFieldProviderInterface> $providers
     */
    public function __construct(
        private readonly iterable $providers,
        private readonly EntityAliasRegistryInterface $aliasRegistry,
    ) {
    }

    public function getForEntity(string $entityAlias): array
    {
        if (isset($this->cache[$entityAlias])) {
            return $this->cache[$entityAlias];
        }

        $descriptors = $this->discoverFromAttributes($entityAlias);

        foreach ($this->providers as $provider) {
            if ($provider->getTargetEntity() === $entityAlias) {
                foreach ($provider->getDescriptors() as $descriptor) {
                    $descriptors[] = $descriptor;
                }
            }
        }

        return $this->cache[$entityAlias] = $descriptors;
    }

    public function findByName(string $entityAlias, string $name): ?VirtualFieldDescriptor
    {
        foreach ($this->getForEntity($entityAlias) as $descriptor) {
            if ($descriptor->name === $name) {
                return $descriptor;
            }
        }

        return null;
    }

    /**
     * @return list<VirtualFieldDescriptor>
     */
    private function discoverFromAttributes(string $entityAlias): array
    {
        $fqcn = $this->aliasRegistry->resolve($entityAlias);
        if (null === $fqcn) {
            return [];
        }

        $reflection = new ReflectionClass($fqcn);
        $descriptors = [];

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $attrs = $method->getAttributes(VirtualField::class);
            if ([] === $attrs) {
                continue;
            }
            /** @var VirtualField $meta */
            $meta = $attrs[0]->newInstance();

            $descriptors[] = new VirtualFieldDescriptor(
                name: $this->deriveName($method->getName()),
                label: $meta->label,
                filterType: $meta->filterType,
                sqlExpression: $meta->sqlExpression,
                translator: null !== $meta->translatorMethod
                    ? $this->resolveTranslatorClosure($fqcn, $meta->translatorMethod)
                    : null,
                allowedOps: $meta->allowedOps,
                description: $meta->description,
            );
        }

        return $descriptors;
    }

    /**
     * Strips a leading `get`/`is`/`has` accessor prefix and lowercases the first
     * letter; otherwise returns the method name unchanged.
     */
    private function deriveName(string $methodName): string
    {
        foreach (['get', 'is', 'has'] as $prefix) {
            $prefixLen = strlen($prefix);
            if (strlen($methodName) > $prefixLen
                && str_starts_with($methodName, $prefix)
                && ctype_upper($methodName[$prefixLen])
            ) {
                return lcfirst(substr($methodName, $prefixLen));
            }
        }

        return $methodName;
    }

    /**
     * @param class-string $fqcn
     */
    private function resolveTranslatorClosure(string $fqcn, string $methodName): Closure
    {
        if (!method_exists($fqcn, $methodName)) {
            throw new InvalidArgumentException(sprintf("VirtualField translatorMethod '%s::%s' does not exist.", $fqcn, $methodName));
        }
        $reflection = new ReflectionMethod($fqcn, $methodName);
        if (!$reflection->isStatic()) {
            throw new InvalidArgumentException(sprintf("VirtualField translatorMethod '%s::%s' must be static.", $fqcn, $methodName));
        }

        return $reflection->getClosure();
    }
}
