<?php

declare(strict_types=1);

namespace CoolMS\Entity\Attribute;

use Attribute;

/**
 * Phase X-2.5b -- declares a computed (non-column) field for RQL
 * filtering on the carrying entity. Applied to a method whose
 * name is used to derive the RQL field name
 * (`getDaysSinceLastLogin` -> `daysSinceLastLogin`).
 *
 * Strategy selection: provide exactly one of $sqlExpression or
 * $translatorMethod. The X strategy renders the SQL fragment
 * inline in the filter clause; the Y strategy invokes a static
 * method on the same class to mutate the QueryBuilder for complex
 * cases (joins, sub-queries, multi-parameter expressions).
 *
 * Cross-module contributions that cannot edit the target entity
 * implement `VirtualFieldProviderInterface` instead.
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class VirtualField
{
    /**
     * @param string       $label            human-readable label for UI surfaces
     * @param string       $filterType       wire type: 'string' | 'int' | 'float' | 'bool' | 'date' | 'datetime' | 'enum'
     * @param string|null  $sqlExpression    DQL/SQL fragment that yields the computed value (X strategy)
     * @param string|null  $translatorMethod static method on the same class that mutates the QueryBuilder (Y strategy)
     * @param list<string> $allowedOps       whitelist of RQL operator codes accepted for this field
     * @param string|null  $description      optional long-form documentation surfaced in admin UI
     */
    public function __construct(
        public string $label,
        public string $filterType,
        public ?string $sqlExpression = null,
        public ?string $translatorMethod = null,
        public array $allowedOps = [],
        public ?string $description = null,
    ) {
    }
}
