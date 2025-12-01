<?php
declare(strict_types=1);

namespace App\Infrastructure\Core\DI;

use App\Application\DI\Interfaces\DIContainerInterface;
use App\Application\DI\Interfaces\ServiceRegistryInterface;
use App\Common\Enums\ServiceLifetime;

use Psr\Log\LoggerInterface;
use App\Infrastructure\Logging\FileLogger;

/**
 * ServiceRegistry registers all application services in the DI container.
 * Uses DIContainerInterface instead of PSR-11 ContainerInterface to
 * leverage service registration methods such as bind().
 */
final class ServiceRegistry implements ServiceRegistryInterface
{
    /**
     * Register all services here.
     *
     * @param DIContainerInterface $container Fully featured DI container.
     */
    public function registerServices(DIContainerInterface $container): void
    {
        // Register logger as singleton
        // $container->bind(
        //     LoggerInterface::class,
        //     FileLogger::class,
        //     ServiceLifetime::SINGLETON
        // );

        // Register additional services here...
    }
}
