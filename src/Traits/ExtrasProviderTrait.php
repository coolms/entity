<?php

declare(strict_types=1);

namespace CoolMS\Entity\Traits;

use CoolMS\Core\Attribute\FieldMeta;
use CoolMS\Core\Mapping\Column;
use CoolMS\Entity\Exception\ReservedPropertyException;

/**
 * Provides a JSON `extras` bag for storing arbitrary dynamic fields.
 *
 * Magic access notes (important for contributors):
 *  - __get / __set are invoked ONLY for names that do NOT correspond to a declared property.
 *    If a subclass declares `public string $bio`, PHP routes $entity->bio directly to that
 *    property and never calls __get/__set for 'bio'. This is standard PHP 8 behavior.
 *  - offsetGet / offsetSet always go through this trait (required by Symfony PropertyAccessor
 *    for `[field_name]` property_path notation in dynamic forms).
 *  - The only name guarded at runtime is 'extras' -- the bag itself. All other reserved system
 *    properties (id, createdAt, etc.) protect themselves via their own property declarations.
 *  - Design-time protection against reserved names is enforced by Scaffolding\ReservedFieldNames
 *    before any field reaches the database.
 */
trait ExtrasProviderTrait
{
    /** @var array<string, mixed> */
    #[FieldMeta(private: true)]
    #[Column(name: 'extras', type: 'json', nullable: false, options: ['default' => '{}'])]
    public array $extras = [];

    public function __get(string $name): mixed
    {
        return $this->extras[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->assertNotReserved($name);
        // Explicit copy+reassign ensures an ORM's deferred change-tracking
        // sees a property write (array element mutation alone may not trigger
        // copy-on-write write-back under PHP 8.5 `private(set)` semantics).
        $extras = $this->extras;
        $extras[$name] = $value;
        $this->extras = $extras;
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->extras);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->extras[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        // Must share the same guard as __set.
        // Without this, $entity['extras'] = [] would bypass the check.
        $this->assertNotReserved((string) $offset);
        // Explicit copy+reassign -- see __set() comment above.
        $extras = $this->extras;
        $extras[(string) $offset] = $value;
        $this->extras = $extras;
    }

    public function offsetUnset(mixed $offset): void
    {
        $extras = $this->extras;
        unset($extras[$offset]);
        $this->extras = $extras;
    }

    /**
     * Retrieve a single key from the extras bag.
     * Prefer this over magic __get for explicit, IDE-friendly access in traits.
     */
    public function getExtra(string $key): mixed
    {
        return $this->extras[$key] ?? null;
    }

    /**
     * Store a single key in the extras bag.
     * Prefer this over magic __set so traits can read/write extras cleanly
     * without triggering reserved-name guards on unrelated keys.
     */
    public function setExtra(string $key, mixed $value): void
    {
        $extras = $this->extras;
        $extras[$key] = $value;
        $this->extras = $extras;
    }

    private function assertNotReserved(string $name): void
    {
        if ('extras' === $name) {
            throw new ReservedPropertyException($name, static::class);
        }
    }
}
