<?php

declare(strict_types=1);

namespace CoolMS\Entity\Tests\Traits;

use CoolMS\Entity\Tests\Fixture\TranslatableEntity;
use CoolMS\Entity\ValueObject\LocaleFallback;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * TranslatableProviderTrait stores per-locale values of an entity's fields in
 * extras['translations'], keyed locale first and field second.
 *
 * translate() falls back for each field on its own, along a chain the site
 * configures (LocaleFallback): the exact locale, its language (de-AT to de),
 * the site's default locale, then the site's other locales in the configured
 * order, never in the order values were stored. Without a configuration the
 * default is English and nothing else is consulted. The fixture is the shape a
 * consumer writes: the extras trait plus this one.
 */
final class TranslatableProviderTraitTest extends TestCase
{
    #[Test]
    public function anEntityWithoutTranslationsTranslatesToNull(): void
    {
        $entity = new TranslatableEntity();

        self::assertSame([], $entity->getTranslations());
        self::assertNull($entity->translate('en', 'alt'));
    }

    #[Test]
    public function theExactLocaleWins(): void
    {
        $entity = new TranslatableEntity()
            ->setTranslation('en', 'alt', 'Company logo')
            ->setTranslation('de', 'alt', 'Firmenlogo')
            ->setTranslation('de-AT', 'alt', 'Firmenlogo (AT)');

        self::assertSame('Firmenlogo (AT)', $entity->translate('de-AT', 'alt'));
    }

    #[Test]
    public function aRegionalLocaleFallsBackToItsLanguage(): void
    {
        $entity = new TranslatableEntity()
            ->setTranslation('en', 'alt', 'Company logo')
            ->setTranslation('de', 'alt', 'Firmenlogo');

        self::assertSame('Firmenlogo', $entity->translate('de-AT', 'alt'));
    }

    #[Test]
    public function aLocaleWithNothingOfItsOwnFallsBackToEnglish(): void
    {
        // French is stored first, so a fallback that skipped English and
        // took the first locale would answer in French.
        $entity = new TranslatableEntity()
            ->setTranslation('fr', 'alt', 'Logo de la societe')
            ->setTranslation('en', 'alt', 'Company logo');

        self::assertSame('Company logo', $entity->translate('es', 'alt'));
    }

    #[Test]
    public function withoutAConfiguredOrderNoOtherStoredLocaleIsConsulted(): void
    {
        $entity = new TranslatableEntity()
            ->setTranslation('fr', 'alt', 'Logo de la societe')
            ->setTranslation('de', 'alt', 'Firmenlogo');

        self::assertNull($entity->translate('es', 'alt'));
    }

    /**
     * French holds the caption and German the alt text, French first in the
     * site's order: Spanish takes each field from the first locale that has it.
     */
    #[Test]
    public function eachFieldIsTakenFromTheFirstConfiguredLocaleThatHoldsIt(): void
    {
        $entity = new TranslatableEntity()
            ->setTranslation('fr', 'caption', 'Image de marque')
            ->setTranslation('de', 'alt', 'Firmenlogo');
        $site = new LocaleFallback(defaultLocale: 'en', order: ['fr', 'de']);

        self::assertSame('Firmenlogo', $entity->translate('es', 'alt', $site));
        self::assertSame('Image de marque', $entity->translate('es', 'caption', $site));
    }

    #[Test]
    public function theConfiguredOrderDecidesNotTheOrderValuesWereStored(): void
    {
        $entity = new TranslatableEntity()
            ->setTranslation('de', 'alt', 'Firmenlogo')
            ->setTranslation('fr', 'alt', 'Logo de la societe');

        self::assertSame('Logo de la societe', $entity->translate('es', 'alt', new LocaleFallback('en', ['fr', 'de'])));
        self::assertSame('Firmenlogo', $entity->translate('es', 'alt', new LocaleFallback('en', ['de', 'fr'])));
    }

    #[Test]
    public function theSitesDefaultLocaleComesBeforeItsOtherLocales(): void
    {
        $entity = new TranslatableEntity()
            ->setTranslation('fr', 'alt', 'Logo de la societe')
            ->setTranslation('de', 'alt', 'Firmenlogo');

        self::assertSame('Firmenlogo', $entity->translate('es', 'alt', new LocaleFallback('de', ['fr'])));
    }

    #[Test]
    public function aStoredLocaleTheSiteDoesNotListIsNeverConsulted(): void
    {
        $entity = new TranslatableEntity()->setTranslation('it', 'alt', 'Logo aziendale');

        self::assertNull($entity->translate('es', 'alt', new LocaleFallback('en', ['fr', 'de'])));
    }

    #[Test]
    public function aRegionalLocaleWrittenWithAnUnderscoreFallsBackToItsLanguageToo(): void
    {
        $entity = new TranslatableEntity()->setTranslation('es', 'alt', 'Logotipo');

        self::assertSame('Logotipo', $entity->translate('es_MX', 'alt'));
    }

    #[Test]
    public function aFieldNoLocaleHoldsTranslatesToNull(): void
    {
        $entity = new TranslatableEntity()->setTranslation('en', 'alt', 'Company logo');

        self::assertNull($entity->translate('en', 'caption'));
    }

    #[Test]
    public function theFallbackIsDecidedForEachFieldOnItsOwn(): void
    {
        $entity = new TranslatableEntity()
            ->setTranslation('en', 'caption', 'Brand image')
            ->setTranslation('de', 'caption', 'Markenbild')
            ->setTranslation('de-AT', 'alt', 'Firmenlogo (AT)');

        self::assertSame('Firmenlogo (AT)', $entity->translate('de-AT', 'alt'));
        self::assertSame('Markenbild', $entity->translate('de-AT', 'caption'));
    }

    #[Test]
    public function setTranslationWritesOneFieldOfOneLocaleIntoTheExtrasBag(): void
    {
        $entity = new TranslatableEntity();

        $returned = $entity
            ->setTranslation('en', 'alt', 'Logo')
            ->setTranslation('en', 'caption', 'Brand image')
            ->setTranslation('de', 'alt', 'Firmenlogo')
            ->setTranslation('en', 'alt', 'Company logo');

        $expected = [
            'en' => ['alt' => 'Company logo', 'caption' => 'Brand image'],
            'de' => ['alt' => 'Firmenlogo'],
        ];
        self::assertSame($entity, $returned);
        self::assertSame($expected, $entity->getTranslations());
        self::assertSame(['translations' => $expected], $entity->extras);
    }
}
