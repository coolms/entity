<?php

declare(strict_types=1);

namespace CoolMS\Entity\ValueObject;

final readonly class EntityClassMetadata
{
    public function __construct(
        public string $className,    // e.g. Acme\Blog\Entity\Article
        public string $module,       // e.g. "Blog"
        public string $shortName,    // e.g. "Article"
        public bool $isAggregate,  // implements AggregateRootInterface
        public bool $isEmbeddable, // ORM Embeddable / Value Object
        public bool $isDynamic,    // in EntityAliasRegistry
        public ?string $dynamicAlias, // "page_variant" if dynamic, null otherwise
        public string $label = '',   // from #[ClassMeta] attribute
        /** @var EntityFieldMetadata[] */
        public array $fields = [],
    ) {
    }
}
