<?php

declare(strict_types=1);

namespace CoolMS\Entity\VirtualField;

use CoolMS\Entity\Filter\VirtualFieldDescriptor;

/**
 * Phase X-2.5b -- contributes virtual field descriptors for a
 * target entity from outside the entity class itself. Used when
 * the contributing module cannot edit the target entity (e.g.,
 * Document module adding `totalGeneratedDocuments` to Identity's
 * `User`).
 *
 * Implementations are tagged by `VirtualFieldServicesPass` and
 * aggregated by `VirtualFieldRegistry` alongside descriptors
 * discovered from `#[VirtualField]` attributes on the entity's
 * own methods. Tagging happens in the compiler pass (not via
 * `#[AutoconfigureTag]`) because the `App\:` prototype scan in
 * `config/services.yaml` runs after bundle extensions and replaces
 * the autoconfigured definitions with fresh ones, losing the tag.
 *
 * Implementations MUST be deterministic -- repeated calls for the
 * same entity alias produce identical descriptor lists.
 */
interface VirtualFieldProviderInterface
{
    public const string TAG_NAME = 'coolms.entity_virtual_field_provider';

    /**
     * Entity alias this provider contributes to (e.g. 'user').
     */
    public function getTargetEntity(): string;

    /**
     * @return list<VirtualFieldDescriptor>
     */
    public function getDescriptors(): array;
}
