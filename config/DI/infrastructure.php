<?php
declare(strict_types=1);

use App\Application\Shared\Services\UserContext;
use App\Application\Shared\Services\UserContextExtractor;
use App\Application\Shared\Services\UserContextInterface;
use App\Config\EnvironmentLoader;
use App\Infrastructure\Core\DI\DIContainer;

use App\Infrastructure\Persistence\Core\Database\DatabaseConnection;
use Monolog\Logger;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Level;
use App\Common\Enums\ServiceLifetime;
use App\Domain\Shared\Factories\AuditInfoFactory;
use App\Domain\Shared\Factories\AuditInfoFactoryInterface;
use Laminas\Diactoros\ServerRequestFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Psr\EventDispatcher\EventDispatcherInterface;
use SafeMySQL;

/*
Example bindings:

// Singleton with custom factory closure for environment loader
$container->singleton(EnvironmentLoader::class, function () {
    $loader = new EnvironmentLoader(BASE_PATH);
    $loader->load();
    return $loader;
});

// Scalar binding for config value retrieval
$container->bind('jwtSecret', function (DIContainer $c) {
    $secret = $c->get(EnvironmentLoader::class)->get('JWT_SECRET');
    if (!$secret) {
        throw new \RuntimeException('JWT_SECRET environment variable not set');
    }
    return $secret;
}, ServiceLifetime::SINGLETON);

// Singleton DB connection using EnvironmentLoader
$container->singleton(DatabaseConnection::class, function (DIContainer $c) {
    return DatabaseConnection::getInstance($c->get(EnvironmentLoader::class));
});

// Singleton Logger with Monolog and multiple handlers
$container->bind(LoggerInterface::class, function () {
    $logger = new MonologLogger('app');
    $formatter = new LineFormatter("[%datetime%] [%level_name%] %message%\n", 'D M d H:i:s Y', true, true);

    $stdoutHandler = new StreamHandler('php://stdout', Level::Debug);
    $stdoutHandler->setFormatter($formatter);
    $logger->pushHandler($stdoutHandler);

    $fileHandler = new StreamHandler(BASE_PATH . '/runtime/logs/app.log', Level::Debug);
    $fileHandler->setFormatter($formatter);
    $logger->pushHandler($fileHandler);

    return $logger;
}, ServiceLifetime::SINGLETON);
*/

function registerInfrastructure(DIContainer $container): void
{
    // ✅ Container self-reference
    $container->singleton(ContainerInterface::class, function (DIContainer $c) {
        return $c;
    });

    // ✅ Scalar bindings
    $container->singleton('BASE_PATH', function () {
        return rtrim(realpath(__DIR__ . '/../../'), DIRECTORY_SEPARATOR);
    });
    // Add to your container registration
    $container->singleton('assets.path', function () {
        return rtrim(realpath(__DIR__ . '/../../public/assets'), DIRECTORY_SEPARATOR);
    });


    // ✅ FIXED: Proper function() syntax
    $container->singleton(EnvironmentLoader::class, function (DIContainer $c) {
        $basePath = $c->getRaw('BASE_PATH');
        $loader = new EnvironmentLoader($basePath);
        $loader->load();
        return $loader;
    });

    $container->bind('jwtSecret', function (DIContainer $c) {
        $secret = $c->get(EnvironmentLoader::class)->get('JWT_SECRET');
        if (!$secret) {
            throw new \RuntimeException('JWT_SECRET environment variable not set');
        }
        return $secret;
    }, ServiceLifetime::SINGLETON);

    // ✅ All other bindings (unchanged syntax)
    $container->singleton(DatabaseConnection::class, function (DIContainer $c) {
        $env = $c->get(EnvironmentLoader::class);
        return DatabaseConnection::getInstance($env);
    });

    $container->singleton(SafeMySQL::class, function (DIContainer $c) {
        $dbConnection = $c->get(DatabaseConnection::class);
        return $dbConnection->getConnection();
    });

    $container->singleton(LoggerInterface::class, function (DIContainer $c) {
        $basePath = $c->getRaw('BASE_PATH');
        $logger = new Logger('app');
        $formatter = new LineFormatter(
            "[%datetime%] [%level_name%] %message% %context%\n",
            'D M d H:i:s Y',
            true,
            true
        );

        $stdoutHandler = new StreamHandler('php://stdout', Level::Debug);
        $stdoutHandler->setFormatter($formatter);
        $logger->pushHandler($stdoutHandler);

        $fileHandler = new StreamHandler($basePath . '/runtime/logs/app.log', Level::Debug);
        $fileHandler->setFormatter($formatter);
        $logger->pushHandler($fileHandler);

        return $logger;
    });

    // ✅ PSR-7 Factory (covers all middleware needs)
    $container->singleton(ResponseFactoryInterface::class, function () {
        return new Psr17Factory();
    });

    // ✅ Rest of bindings...
    $container->bind(EventDispatcherInterface::class, function () {
        return new EventDispatcher();
    }, ServiceLifetime::SINGLETON);

    $container->bind(ServerRequestInterface::class, function (DIContainer $c) {
        $psr17Factory = $c->get(ResponseFactoryInterface::class);
        $creator = new ServerRequestCreator(
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
            $psr17Factory
        );
        return $creator->fromGlobals();
    }, ServiceLifetime::SINGLETON);

    $container->bind(AuditInfoFactoryInterface::class, AuditInfoFactory::class, ServiceLifetime::SINGLETON);
    $container->bind(UserContextExtractor::class, function ($c) {
        return new UserContextExtractor(
            $c->get(ServerRequestInterface::class),
            null // Or inject currently authenticated UserId if available here
        );
    }, ServiceLifetime::SINGLETON);

    // Bind UserContextInterface to UserContext with UserContextExtractor injected
    $container->bind(UserContextInterface::class, function ($c) {
        return new UserContext($c->get(LoggerInterface::class));
    }, ServiceLifetime::SINGLETON);
}

