<?php
declare(strict_types=1);

namespace App\Application\DI\Providers;

use App\Application\DI\Interfaces\DIContainerInterface;
use App\Application\DI\Interfaces\ServiceProviderInterface;
use App\Common\Enums\ServiceLifetime;
use Psr\Log\LoggerInterface;
use App\Infrastructure\Logging\FileLogger;
use App\SomeModule\SomeService;

/**
 * Registers Common services in the DI container.
 */
final class CommonServiceProvider implements ServiceProviderInterface
{
    public function register(DIContainerInterface $container): void
    {
        $container->bind(
            LoggerInterface::class,
            FileLogger::class,
            ServiceLifetime::SINGLETON
        );

        $container->bind(
            SomeService::class,
            SomeService::class,
            ServiceLifetime::TRANSIENT
        );

        // More service registrations for this module...
    }
}
