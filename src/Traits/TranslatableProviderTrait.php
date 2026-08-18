<?php

declare(strict_types=1);

namespace CoolMS\Entity\Traits;

/**
 * ORM-agnostic field-level translations stored in the extras JSON bag.
 *
 * Requires: ExtrasProviderTrait (for getExtra/setExtra).
 *
 * Storage format in extras['translations']:
 * {
 *   "en": { "alt": "Company logo", "caption": "Brand image" },
 *   "ru": { "alt": "Логотип компании", "caption": "Фирменное изображение" }
 * }
 *
 * NOT for Page content -- PageVariant is the richer pattern for that.
 * USE FOR: alt, caption, title, description on MediaAsset, Product, Category etc.
 */
trait TranslatableProviderTrait
{
    // Requires ExtrasProviderTrait

    /**
     * Get translated field value with BCP 47 fallback chain.
     * ru-RU, then ru, then en, then first available locale, then null.
     */
    public function translate(string $locale, string $field): ?string
    {
        $all = $this->getExtra('translations') ?? [];

        return $all[$locale][$field]
            ?? $all[$this->extractLanguage($locale)][$field]
            ?? $all['en'][$field]
            ?? array_first($all)[$field]
            ?? null;
    }

    public function setTranslation(string $locale, string $field, string $value): static
    {
        $all = $this->getExtra('translations') ?? [];
        $all[$locale][$field] = $value;
        $this->setExtra('translations', $all);

        return $this;
    }

    /** @return array<string, array<string, string>> */
    public function getTranslations(): array
    {
        return $this->getExtra('translations') ?? [];
    }

    private function extractLanguage(string $locale): string
    {
        return explode('-', $locale)[0];
    }
}
