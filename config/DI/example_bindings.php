
//--- Registration functions ---

// This file demonstrates various examples of refactored service bindings in a DI container
// Each example is commented out with explanation for when and why to use it.


/*
// 1. Simple Class Binding (Autowiring)
$container->bind(UserRepositoryInterface::class, UserRepository::class, ServiceLifetime::SINGLETON);
// Automatically wires UserRepository constructor dependencies.
// Use when service has type-hinted constructor deps.
*/


/*
// 2. Interface to Implementation Binding
$container->bind(AuthServiceInterface::class, AuthService::class, ServiceLifetime::SINGLETON);
// Allows depending on interfaces rather than concrete classes.
// Best practice for loose coupling.
*/


/*
// 3. Factory Closure Binding
$container->bind(DatabaseConnection::class, function(DIContainer $c) {
    $config = $c->get(EnvironmentLoader::class);
    return new DatabaseConnection($config->getDsn());
}, ServiceLifetime::SINGLETON);
// Use when you need custom instantiation logic or config values injected.
*/


/*
// 4. Scalar/Value Binding
$container->bind('jwtSecret', function() {
    $secret = getenv('JWT_SECRET');
    if (!$secret) {
        throw new RuntimeException('JWT_SECRET environment variable not set');
    }
    return $secret;
}, ServiceLifetime::SINGLETON);
// For injecting config constants or simple values.
*/


/*
// 5. Composite Service with Multiple Dependencies
$container->bind(SessionStorageInterface::class, function(DIContainer $c) {
    $dbStorage = $c->get(DatabaseSessionStorage::class);
    $fileStorage = $c->get(FileSessionStorage::class);
    return new CompositeSessionStorage($dbStorage, $fileStorage);
}, ServiceLifetime::SINGLETON);
// Useful when service aggregates multiple sub-services.
*/


/*
// 6. Named/Aliased Services
$container->bind('http.adapter.auth', AuthHttpAdapter::class, ServiceLifetime::SINGLETON);
$container->bind('http.adapter.main', MainHttpAdapter::class, ServiceLifetime::SINGLETON);
// Use named keys for different implementations of the same interface or class.
*/


/*
// 7. Binding Using Constructor Promotion (PHP 8+ style)
// In service class:
class AuthService implements AuthServiceInterface {
    public function __construct(
        private RegisterUserUseCaseInterface $registerUser,
        private LoginUserUseCaseInterface $loginUser,
        private LoggerInterface $logger
    ) {}
}
// Container binding stays like simple autowiring:
$container->bind(AuthServiceInterface::class, AuthService::class, ServiceLifetime::SINGLETON);
// Prefer this style for readability and less boilerplate.
*/
