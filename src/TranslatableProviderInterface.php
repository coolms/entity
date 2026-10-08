<?php

declare(strict_types=1);

namespace CoolMS\Entity;

use CoolMS\Entity\ValueObject\LocaleFallback;

interface TranslatableProviderInterface extends ExtrasProviderInterface
{
    public function translate(string $locale, string $field, ?LocaleFallback $fallback = null): ?string;

    public function setTranslation(string $locale, string $field, string $value): static;

    /** @return array<string, array<string, string>> */
    public function getTranslations(): array;
}
