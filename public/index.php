<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Core\DI\DIContainer;
use App\Infrastructure\Core\Routing\MiddlewareDispatcher;
use App\Infrastructure\Core\Routing\MiddlewarePipeline;
use App\Infrastructure\Core\Routing\RouteCollectionInterface;
use App\Infrastructure\Core\Routing\RouterInterface;
use App\Infrastructure\Core\Routing\RouteMatcherInterface;
use App\Infrastructure\Core\DI\ControllerInvoker;
use App\Infrastructure\Core\DI\MethodInvoker;
use App\Application\Session\Services\SessionService;
use Psr\Log\LoggerInterface;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Monolog\ErrorHandler;

// Enable comprehensive error reporting
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../runtime/logs/error.log');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);


// Instantiate the DI container
$container = new DIContainer();

// Register services from config
(require __DIR__ . '/../config/DI/container.php')($container);

// Get essential services
$basePath = $container->getRaw('BASE_PATH');
$logger = $container->get(LoggerInterface::class);
$sessionService = $container->get(SessionService::class);
$responseFactory = new Psr17Factory(); // Replace Laminas ResponseFactory with Nyholm implementation

// Get routing and middleware components
$routeCollection = $container->get(RouteCollectionInterface::class);
$routeMatcher = $container->get(RouteMatcherInterface::class);
$router = $container->get(RouterInterface::class);
$pipeline = $container->get(MiddlewarePipeline::class);
$controllerInvoker = $container->get(ControllerInvoker::class);
$methodInvoker = $container->get(MethodInvoker::class);
$dispatcher = $container->get(MiddlewareDispatcher::class);

require_once __DIR__ . '/../config/middleware_global.php';
registerGlobalMiddlewares($pipeline, $container);

// Setup error handler for logging with Monolog
ErrorHandler::register($logger);

// Create server request using Nyholm ServerRequestCreator
$psr17Factory = new Psr17Factory();
$serverRequestCreator = new ServerRequestCreator(
    $psr17Factory, // ServerRequestFactory
    $psr17Factory, // UriFactory
    $psr17Factory, // UploadedFileFactory
    $psr17Factory  // StreamFactory
);

$request = $serverRequestCreator->fromGlobals();

try {
    $response = $dispatcher->handle($request);
} catch (\Throwable $e) {
    $logger->critical('Exception during app startup: ' . $e->getMessage());
    
    $jsonString = json_encode(['error' => 'Internal Server Error']);
    $response = $psr17Factory->createResponse(500)
        ->withHeader('Content-Type', 'application/json')
        ->withBody($psr17Factory->createStream($jsonString));
}


// Emit response using Laminas SapiEmitter
$emitter = new SapiEmitter();
$emitter->emit($response);
