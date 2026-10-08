<?php

declare(strict_types=1);

namespace CoolMS\Entity\ValueObject;

use function array_unique;
use function array_values;
use function preg_split;

/**
 * Where a translated field is looked for when the requested locale does not
 * hold it, as a site configures it.
 *
 * The chain for a requested locale is: the locale itself, its language
 * (es-MX or es_MX to es), the site's default locale, then the site's other
 * locales in the configured order. Each locale appears once, at its first
 * place. A locale the site does not list is never in the chain, so the order
 * in which values happen to be stored never decides which one is shown.
 */
final readonly class LocaleFallback
{
    /**
     * @param list<string> $order the site's locales, in the order a missing value is looked for after the default
     */
    public function __construct(
        public string $defaultLocale = 'en',
        public array $order = [],
    ) {
    }

    public static function languageOf(string $locale): string
    {
        $parts = preg_split('/[-_]/', $locale, 2);

        return false === $parts ? $locale : $parts[0];
    }

    /**
     * @return list<string>
     */
    public function chainFor(string $locale): array
    {
        return array_values(array_unique([$locale, self::languageOf($locale), $this->defaultLocale, ...$this->order]));
    }
}
