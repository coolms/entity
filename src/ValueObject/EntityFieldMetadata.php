<?php

declare(strict_types=1);

namespace CoolMS\Entity\ValueObject;

final readonly class EntityFieldMetadata
{
    public function __construct(
        public string $name,
        public string $type,         // mapped to: text|number|boolean|date
        public bool $nullable,
        public bool $isIdentifier,
        public bool $isEmbedded,     // part of an @Embeddable VO
        public ?string $label = null, // from #[FieldMeta]; null = no explicit label set
        public ?string $formType = null,  // from #[FieldMeta]
        public bool $showInForm = true,   // from #[FieldMeta]
        public ?int $sortOrder = null,    // from #[FieldMeta]
        public string $source = 'entity', // 'entity'|'module'|'runtime'
        /** null | 'db' | 'file' | 'module' | 'runtime' */
        public ?string $overrideKind = null,
        /** Fully-qualified class name of a backed enum when the column declares enumType, otherwise null. */
        public ?string $enumClass = null,
    ) {
    }
}
