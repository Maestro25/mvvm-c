<?php
declare(strict_types=1);

use App\Application\Session\Translators\SessionArrayToDtoTranslator;
use App\Application\Shared\Services\AuditService;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Infrastructure\Core\DI\DIContainer;

use App\Infrastructure\Persistence\Session\Repositories\DbSessionStorage;
use App\Infrastructure\Persistence\Session\Repositories\FileSessionStorage;
use App\Infrastructure\Persistence\Session\Mappers\SessionDbMapper;
use App\Infrastructure\Persistence\Session\Mappers\SessionDbMapperInterface;
use App\Domain\Session\Factories\SessionFactory;
use App\Domain\Session\Factories\SessionFactoryInterface;
use App\Application\Session\Mappers\SessionCreationDataMapper;
use App\Application\Session\Mappers\SessionCreationDataMapperInterface;
use App\Application\Session\Services\CompositeSessionHandler;
use App\Application\Session\Services\SessionService;
use App\Application\Session\Services\SessionServiceInterface;
use App\Application\Session\Services\SessionGarbageCollector;
use App\Application\Session\Services\SessionGarbageCollectorInterface;
use App\Application\Session\Services\SessionTokenManager;
use App\Application\Session\Services\SessionTokenManagerInterface;
use App\Application\Session\Services\SessionCookieManager;
use App\Application\Session\Services\SessionCookieManagerInterface;
use App\Common\Enums\ServiceLifetime;
use App\Config\SessionConfig;
use App\Domain\Session\Repositories\DbSessionStorageInterface;
use App\Domain\Session\Repositories\FileSessionStorageInterface;
use App\Domain\Session\Repositories\SessionRepositoryInterface;
use App\Infrastructure\Persistence\Session\Repositories\SessionRepository;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use App\Application\Session\Services\TokenGenerator;
use App\Application\Session\Services\TokenGeneratorInterface;
use App\Application\Shared\Services\UserContextInterface;
use App\Config\EnvironmentLoader;
use App\Domain\Session\Validation\SessionValidator;
use App\Domain\Session\Validation\SessionValidatorInterface;
use App\Domain\Shared\Factories\AuditInfoFactoryInterface;


function registerSession(DIContainer $container): void
{
// Bind session mappers
$container->bind(SessionDbMapperInterface::class, SessionDbMapper::class, ServiceLifetime::SINGLETON);
$container->bind(SessionCreationDataMapperInterface::class, SessionCreationDataMapper::class, ServiceLifetime::SINGLETON);

// Bind session factory
$container->bind(SessionFactoryInterface::class, SessionFactory::class, ServiceLifetime::SINGLETON);

// Bind SafeMysql instance required by DbSessionStorage
// Bind TokenGenerator and its dependencies
$container->bind(TokenGeneratorInterface::class, function(DIContainer $c) {
    return new TokenGenerator($c->get(LoggerInterface::class));
}, ServiceLifetime::SINGLETON);

// Bind DbSessionStorage with all dependencies
$container->bind(DbSessionStorageInterface::class, function(DIContainer $c) {
    return new DbSessionStorage(
        $c->get(SafeMySQL::class),
        $c->get(LoggerInterface::class),
        $c->get(SessionDbMapperInterface::class),
        $c->get(SessionCreationDataMapperInterface::class),
        $c->get(SessionFactoryInterface::class),
        $c->get(AuditInfoFactoryInterface::class),
        $c->get(TokenGenerator::class),
        // Optionally set table name if needed, or use default
    );
}, ServiceLifetime::SINGLETON);

// Bind FileSessionStorage (assumes no constructor params)
$container->bind(FileSessionStorageInterface::class, function(DIContainer $c) {
    return new FileSessionStorage();
}, ServiceLifetime::SINGLETON);

// Bind CompositeSessionHandler implementing SessionHandlerInterface
$container->bind(SessionHandlerInterface::class, function (DIContainer $c) {
    $dbStorage = $c->get(DbSessionStorageInterface::class);
    $fileStorage = $c->get(FileSessionStorageInterface::class);

    return new CompositeSessionHandler($dbStorage, $fileStorage);
}, ServiceLifetime::SINGLETON);

// Bind SessionRepository
$container->bind(SessionRepositoryInterface::class, function(DIContainer $c) {
    return new SessionRepository(
        $c->get(SafeMySQL::class),
        $c->get(SessionDbMapperInterface::class)
    );
}, ServiceLifetime::SINGLETON);

$container->bind(SessionValidatorInterface::class, function () {
    return new SessionValidator();
}, ServiceLifetime::SINGLETON);

// Bind SessionService with correct injection of SessionHandlerInterface
$container->bind(SessionServiceInterface::class, function(DIContainer $c) {
    return new SessionService(
        $c->get(SessionHandlerInterface::class),
        $c->get(SessionTokenManagerInterface::class),
        $c->get(SessionRepositoryInterface::class),
        $c->get(UserRepositoryInterface::class),
        $c->get(SessionCookieManagerInterface::class),
        $c->get(SessionValidatorInterface::class),
        $c->get(SessionFactoryInterface::class),
        $c->get(SessionArrayToDtoTranslator::class),
        $c->get(AuditService::class),
        $c->get(UserContextInterface::class),
        $c->get(LoggerInterface::class),
        $c->get(EventDispatcherInterface::class),
        $c->get(ServerRequestInterface::class),
    );
}, ServiceLifetime::SINGLETON);

// Bind other session services as singletons
$container->bind(SessionGarbageCollectorInterface::class, SessionGarbageCollector::class, ServiceLifetime::SINGLETON);
$container->bind(SessionTokenManagerInterface::class, SessionTokenManager::class, ServiceLifetime::SINGLETON);

$container->bind(SessionCookieManagerInterface::class, function (DIContainer $c) {
    $logger = $c->get(LoggerInterface::class);
    $envLoader = $c->get(EnvironmentLoader::class);

    $cookieName = $envLoader->get('SESSION_COOKIE_NAME', 'MY_SESSION_COOKIE');
    $cookieLifetime = (int) $envLoader->get('SESSION_COOKIE_LIFETIME', '604800'); // 7 days
    $cookiePath = $envLoader->get('SESSION_COOKIE_PATH', '/');
    $cookieDomain = $envLoader->get('SESSION_COOKIE_DOMAIN');
    if ($cookieDomain === 'null') {
        $cookieDomain = null; // Support nullable domain via string 'null'
    }
    $cookieSecure = filter_var($envLoader->get('SESSION_COOKIE_SECURE', 'true'), FILTER_VALIDATE_BOOLEAN);
    $cookieHttpOnly = filter_var($envLoader->get('SESSION_COOKIE_HTTPONLY', 'true'), FILTER_VALIDATE_BOOLEAN);
    $cookieSameSite = $envLoader->get('SESSION_COOKIE_SAMESITE', 'Lax');

    return new SessionCookieManager(
        $cookieName,
        $cookieLifetime,
        $cookiePath,
        $cookieDomain,
        $cookieSecure,
        $cookieHttpOnly,
        $cookieSameSite,
        $logger
    );
}, ServiceLifetime::SINGLETON);

};
