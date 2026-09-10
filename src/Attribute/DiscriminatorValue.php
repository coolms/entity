<?php

declare(strict_types=1);

namespace CoolMS\Entity\Attribute;

use Attribute;

/**
 * Declare the discriminator value for a single-table inheritance entity.
 *
 * Place this attribute on the root entity and on every subclass. The reader
 * that acts on them lives in the persistence adapter: it collects them at boot
 * and builds the inheritance map dynamically, so the root entity never has to
 * enumerate its subclasses and no dependency on the parent mapping class is
 * created.
 *
 * WHY THIS LIVES IN THE DOMAIN PACKAGE AND NOT IN THE ADAPTER:
 * an attribute placed on an entity is INHERITED BY EVERY SUBCLASS, so a
 * subclass declared in another package acquires whatever package declares the
 * attribute. While this class sat in the adapter, any package declaring a
 * subclass required the adapter merely to state its own discriminator value,
 * and so did every third-party module extending such an entity. This class
 * imports nothing but `Attribute`; everything persistence-specific is in the
 * reader, which stays behind the seam.
 *
 * The word is borrowed from mapping vocabulary, but the statement is not about
 * storage: it is a subclass declaring its own identity, which is a property of
 * the model rather than of where the model is kept.
 *
 * Usage:
 *   #[DiscriminatorValue('user')]
 *   class User implements UserInterface { ... }
 *
 *   #[DiscriminatorValue('oauth2_user')]
 *   class OAuth2User extends User { ... }
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class DiscriminatorValue
{
    public function __construct(public string $value)
    {
    }
}
