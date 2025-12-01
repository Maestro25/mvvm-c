<?php
declare(strict_types=1);

namespace App\Common\Enums;

/**
 * Enum ServiceLifetime
 *
 * Defines possible lifetimes for services in the DI container.
 */
enum ServiceLifetime: string
{
    case SINGLETON = 'singleton'; // Single instance per container
    case TRANSIENT = 'transient'; // New instance each injection request
    case SCOPED = 'scoped';       // Instance per scope (e.g. per request), extendable for advanced usage
}
