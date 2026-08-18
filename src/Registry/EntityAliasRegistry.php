<?php

declare(strict_types=1);

namespace CoolMS\Entity\Registry;

use InvalidArgumentException;

final class EntityAliasRegistry
{
    /**
     * @param array<class-string, string> $aliasMap
     *
     * public private(set): readable by anyone, writable only here.
     * Use $registry->aliasMap directly instead of a separate all() getter.
     *
     * Defaults to empty because that is the only value the container ever
     * passes -- every entry arrives later through `register()`, added by each
     * module's compiler pass. Without the default the class cannot be
     * autowired, which forced an exclusion from the service scan, which left an
     * ABSTRACT definition under this FQCN that nothing could reference.
     */
    public function __construct(
        public private(set) array $aliasMap = [],
    ) {
    }

    /**
     * Register (or overwrite) the alias for an ExtrasProvider entity class.
     *
     * Called from each module's Extension::load() via addMethodCall('register', [...])
     * so that modules can self-register via their own RegistrationPass.
     *
     * @param class-string $class
     */
    public function register(string $class, string $alias): void
    {
        $this->aliasMap[$class] = $alias;
    }

    public function getAlias(string $class): string
    {
        return $this->aliasMap[$class]
            ?? throw new InvalidArgumentException("No entity alias registered for \"$class\".");
    }

    /**
     * Null-returning variant of getAlias(). Use when a missing alias is a normal
     * branch (e.g., instance-aware schema resolution falls back to empty schema)
     * rather than a programmer error.
     */
    public function aliasOf(string $class): ?string
    {
        return $this->aliasMap[$class] ?? null;
    }

    public function hasAlias(string $class): bool
    {
        return isset($this->aliasMap[$class]);
    }

    /**
     * Check whether $slug is registered as an alias for any PHP-registered entity.
     * This is the complement of hasAlias() -- that method accepts a class name (key),
     * this one accepts an alias string (value).
     */
    public function hasSlug(string $slug): bool
    {
        return in_array($slug, $this->aliasMap, true);
    }

    /**
     * Return the fully qualified class name whose alias matches $alias, or null
     * if the alias is not registered as a PHP entity alias.
     *
     * @return class-string|null
     */
    public function getClassForAlias(string $alias): ?string
    {
        /** @var class-string|false $class */
        $class = array_search($alias, $this->aliasMap, true);

        return false !== $class ? $class : null;
    }
}
