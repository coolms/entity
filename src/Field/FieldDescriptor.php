<?php

declare(strict_types=1);

namespace CoolMS\Entity\Field;

/**
 * Phase X-2.5 — resolved field-capability descriptor.
 *
 * Output of `EntityFieldDescriptorInterface::describe()`. Carries
 * the post-resolution view of one entity property (label resolved,
 * type stringified, operator defaults filled in, enum values
 * materialised) so consumers — the wizard frontend, the audience
 * materialiser, future generic search UIs — never need to touch
 * Reflection or the underlying `#[FieldMeta]` attribute.
 */
final readonly class FieldDescriptor
{
    /**
     * @param string                     $field           property name (matches RQL field identifier)
     * @param string                     $label           human-readable label (resolved from FieldMeta or auto-derived)
     * @param string                     $type            wire type: 'string', 'int', 'float', 'bool', 'date', 'enum', or 'unknown'
     * @param bool                       $filterable      field may appear in RQL filter clauses
     * @param list<string>               $filterOperators whitelist of CoolMS\Rql operator codes (empty list when !filterable)
     * @param bool                       $sortable        field may appear in RQL sort clauses
     * @param bool                       $searchable      field participates in free-text search
     * @param array<string, string>|null $enumValues      case-value → display-label map when $type is 'enum', null otherwise
     */
    public function __construct(
        public string $field,
        public string $label,
        public string $type,
        public bool $filterable,
        public array $filterOperators,
        public bool $sortable,
        public bool $searchable,
        public ?array $enumValues = null,
    ) {
    }
}
