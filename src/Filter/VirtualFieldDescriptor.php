<?php

declare(strict_types=1);

namespace CoolMS\Entity\Filter;

use Closure;
use InvalidArgumentException;

/**
 * Phase X-2.5b -- ORM-agnostic descriptor for a computed entity
 * field exposed to RQL filtering.
 *
 * Carries either a SQL expression (X strategy) or a translator
 * callback (Y strategy); never both, never neither. The strategy
 * choice is enforced at construction time so consumers never see
 * an invalid descriptor.
 *
 * Contract layer: no persistence or framework imports. The translator
 * callback is typed as a plain `Closure`; its concrete signature
 * is documented on `VirtualFieldFilterApplier` -- the
 * Application/Infrastructure-layer service that interprets it.
 */
final readonly class VirtualFieldDescriptor
{
    /**
     * @param string       $name          Canonical identifier used in RQL filter clauses (e.g. 'daysSinceLastLogin').
     * @param string       $label         human-readable label for UI surfaces
     * @param string       $filterType    wire type: 'string' | 'int' | 'float' | 'bool' | 'date' | 'datetime' | 'enum'
     * @param string|null  $sqlExpression DQL/SQL fragment that yields the computed value (X strategy). Mutually exclusive with $translator.
     * @param Closure|null $translator    Callback that mutates the QueryBuilder for this field (Y strategy). Mutually exclusive with $sqlExpression.
     * @param list<string> $allowedOps    whitelist of RQL operator codes accepted for this field
     * @param string|null  $description   optional long-form documentation surfaced in admin UI
     */
    public function __construct(
        public string $name,
        public string $label,
        public string $filterType,
        public ?string $sqlExpression = null,
        public ?Closure $translator = null,
        public array $allowedOps = [],
        public ?string $description = null,
    ) {
        $hasExpression = null !== $this->sqlExpression && '' !== $this->sqlExpression;
        $hasTranslator = null !== $this->translator;

        if ($hasExpression === $hasTranslator) {
            throw new InvalidArgumentException(sprintf("VirtualFieldDescriptor '%s' must declare exactly one of sqlExpression or translator (got %s).", $this->name, $hasExpression ? 'both' : 'neither'));
        }
    }

    public function hasSqlExpression(): bool
    {
        return null !== $this->sqlExpression && '' !== $this->sqlExpression;
    }

    public function hasTranslator(): bool
    {
        return null !== $this->translator;
    }
}
