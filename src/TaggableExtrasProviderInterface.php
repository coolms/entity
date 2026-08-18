<?php

declare(strict_types=1);

namespace CoolMS\Entity;

interface TaggableExtrasProviderInterface extends ExtrasProviderInterface
{
    public function addTaxonomyTag(string $taxonomyNodeId): static;

    public function removeTaxonomyTag(string $taxonomyNodeId): static;

    /** @return string[] */
    public function getTaxonomyTagIds(): array;

    public function addTag(string $tag): static;

    public function removeTag(string $tag): static;

    /** @return string[] */
    public function getTags(): array;
}
