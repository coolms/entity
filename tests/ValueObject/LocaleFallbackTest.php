<?php

declare(strict_types=1);

namespace CoolMS\Entity\Tests\ValueObject;

use CoolMS\Entity\ValueObject\LocaleFallback;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * The chain a translated field is looked for along: the requested locale, its
 * language, the site's default locale, then the site's other locales in the
 * configured order, each once, at its first place.
 */
final class LocaleFallbackTest extends TestCase
{
    #[Test]
    public function theChainIsTheLocaleItsLanguageTheDefaultThenTheConfiguredOrder(): void
    {
        $site = new LocaleFallback(defaultLocale: 'en', order: ['fr', 'de', 'en']);

        self::assertSame(['es-MX', 'es', 'en', 'fr', 'de'], $site->chainFor('es-MX'));
    }

    #[Test]
    public function aLocaleWithoutARegionAppearsOnce(): void
    {
        self::assertSame(['es', 'en'], new LocaleFallback()->chainFor('es'));
    }

    #[Test]
    public function theLanguageIsWhatPrecedesTheRegionInEitherSpelling(): void
    {
        self::assertSame('es', LocaleFallback::languageOf('es-MX'));
        self::assertSame('es', LocaleFallback::languageOf('es_MX'));
        self::assertSame('zh', LocaleFallback::languageOf('zh-Hant-TW'));
        self::assertSame('es', LocaleFallback::languageOf('es'));
    }
}
