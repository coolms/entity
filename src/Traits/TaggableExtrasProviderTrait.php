<?php

declare(strict_types=1);

namespace CoolMS\Entity\Traits;

/**
 * Soft-reference tagging via the extras JSON bag.
 *
 * Requires: ExtrasProviderTrait.
 *
 * Two tag types:
 *   extras['taxonomy'] = ['uuid1', 'uuid2'] -- TaxonomyNode UUID soft refs (no FK)
 *   extras['tags'] = ['featured', 'new'] -- plain string tags
 *
 * No FK between modules -- referential integrity enforced at application layer.
 * Reverse lookup via coolms_tag_index (event-driven, see backlog).
 */
trait TaggableExtrasProviderTrait
{
    // Requires ExtrasProviderTrait

    public function addTaxonomyTag(string $taxonomyNodeId): static
    {
        $tags = $this->getExtra('taxonomy') ?? [];
        $tags[] = $taxonomyNodeId;
        $this->setExtra('taxonomy', array_values(array_unique($tags)));

        return $this;
    }

    public function removeTaxonomyTag(string $taxonomyNodeId): static
    {
        $this->setExtra('taxonomy', array_values(array_filter(
            $this->getExtra('taxonomy') ?? [],
            fn (string $id) => $id !== $taxonomyNodeId,
        )));

        return $this;
    }

    /** @return string[] */
    public function getTaxonomyTagIds(): array
    {
        return $this->getExtra('taxonomy') ?? [];
    }

    public function addTag(string $tag): static
    {
        $tags = $this->getExtra('tags') ?? [];
        $tags[] = $tag;
        $this->setExtra('tags', array_values(array_unique($tags)));

        return $this;
    }

    public function removeTag(string $tag): static
    {
        $this->setExtra('tags', array_values(array_filter(
            $this->getExtra('tags') ?? [],
            fn (string $t) => $t !== $tag,
        )));

        return $this;
    }

    /** @return string[] */
    public function getTags(): array
    {
        return $this->getExtra('tags') ?? [];
    }
}
