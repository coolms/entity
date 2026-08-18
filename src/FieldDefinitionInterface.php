<?php

declare(strict_types=1);

namespace CoolMS\Entity;

use CoolMS\Core\Identifier\IdentifierProviderInterface;

interface FieldDefinitionInterface extends IdentifierProviderInterface, NameProviderInterface
{
    /**
     * Data type identifier (string, int, boolean, datetime, etc.).
     */
    public string $type {
        get;
    }

    /**
     * Validation rules keyed by Symfony Constraint short name.
     * Example: ['NotBlank' => null, 'Length' => ['min' => 3, 'max' => 100]].
     *
     * @var array<string, mixed>
     */
    public array $validationRules {
        get;
    }

    /**
     * Human-readable label for this field.
     */
    public string $label {
        get;
    }

    /**
     * Whether this field is required (has a NotBlank validation rule).
     */
    public bool $isRequired {
        get;
    }

    /** @var string[] Roles allowed to read this field. Empty means all roles. */
    public array $securityRead {
        get;
    }

    /** @var string[] Roles allowed to write this field. Empty means all roles. */
    public array $securityWrite {
        get;
    }

    /**
     * Serializer groups used when normalizing this field.
     *
     * @var array<string, mixed>
     */
    public array $normalizationGroups {
        get;
    }

    /**
     * Serializer groups used when denormalizing this field.
     *
     * @var array<string, mixed>
     */
    public array $denormalizationGroups {
        get;
    }
}
