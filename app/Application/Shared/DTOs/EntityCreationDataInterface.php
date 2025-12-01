<?php
declare(strict_types=1);

namespace App\Application\Shared\DTOs;

/**
 * Marker interface for all entity creation DTOs.
 * Specific DTOs like UserRegistrationRequest or others must implement this.
 */
interface EntityCreationDataInterface
{
    // Intentionally no methods as this serves as a marker
    // Optionally, common methods for DTOs can be declared here
}
