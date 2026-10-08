<?php

declare(strict_types=1);

namespace CoolMS\Entity\Traits;

use CoolMS\Entity\ValueObject\LocaleFallback;

use function is_string;

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
     * The field's value in the requested locale, or else in the first locale of
     * the fallback chain that holds that field: each field is looked for on
     * its own, so one locale can answer the caption and another the alt text.
     * The chain is the locale, its language (es-MX to es), the site's default
     * locale, then the site's other locales in the configured order; see
     * LocaleFallback. Without one, the default locale is `en` and no other
     * locale is consulted. Null when no locale in the chain holds the field.
     */
    public function translate(string $locale, string $field, ?LocaleFallback $fallback = null): ?string
    {
        $all = $this->getExtra('translations') ?? [];

        foreach (($fallback ?? new LocaleFallback())->chainFor($locale) as $candidate) {
            $value = $all[$candidate][$field] ?? null;
            if (is_string($value)) {
                return $value;
            }
        }

        return null;
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
}
