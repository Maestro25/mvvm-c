<?php
declare(strict_types=1);

use App\Common\Enums\ServiceLifetime;
use App\Infrastructure\Core\DI\DIContainer;
use App\Presentation\Shared\Coordinators\TestPageCoordinator;
use App\Presentation\Shared\ViewModels\TestPageStateViewModel;
use App\Presentation\Shared\Views\ViewRenderer;
use App\Presentation\Shared\Views\ViewRendererInterface;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Psr\EventDispatcher\EventDispatcherInterface;

function registerTestSetup(DIContainer $container): void
{
    $container->bind(ViewRendererInterface::class, function (DIContainer $c) {
        // Get base path string from container raw values
        $basePath = $c->getRaw('BASE_PATH');

        // Define the template base path based on the injected base path
        $baseTemplatePath = $basePath . '/app/Presentation/Shared/Views/templates';

        // Return the ViewRenderer instance with injected dependencies
        return new ViewRenderer(
            $baseTemplatePath,
            $c->get(LoggerInterface::class),
        );
    }, ServiceLifetime::SINGLETON);

    $container->bind(TestPageCoordinator::class, function (DIContainer $c) {
        return new TestPageCoordinator(
            $c->get(TestPageStateViewModel::class),
            $c->get(ViewRendererInterface::class),
            $c->get(LoggerInterface::class),
            $c->get(EventDispatcherInterface::class),

        );
    }, ServiceLifetime::SINGLETON);


}
;