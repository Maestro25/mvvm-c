<?php
declare(strict_types=1);

namespace App\Application\DI\Interfaces;

use App\Application\DI\Interfaces\DIContainerInterface;

/**
 * Interface ServiceProviderInterface
 *
 * Defines a contract for modular service registration.
 */
interface ServiceProviderInterface
{
    /**
     * Register services into the container.
     *
     * @param DIContainerInterface $container
     */
    public function register(DIContainerInterface $container): void;
}
