<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Core\DI\DIContainer;
use App\Config\EnvironmentLoader;

use App\Infrastructure\Persistence\Core\Database\DatabaseConnection;
use Psr\Log\LoggerInterface;
use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Level;

// Instantiate the DI container and register services
$container = new DIContainer();
(require __DIR__ . '/../config/DI/container.php')($container);

// Set up a simple logger to stdout
$logger = new MonologLogger('connection_test');
$formatter = new LineFormatter("[%datetime%] [%level_name%] %message%\n", null, true, true);
$stdoutHandler = new StreamHandler('php://stdout', Level::Debug);
$stdoutHandler->setFormatter($formatter);
$logger->pushHandler($stdoutHandler);

try {
    // Load environment
    $envLoader = $container->get(EnvironmentLoader::class);

    // Test DatabaseConnection singleton
    $dbConnection = $container->get(DatabaseConnection::class);

    if ($dbConnection->isConnected()) {
        $logger->info("DatabaseConnection is connected.");
        $config = $dbConnection->getConfig(); // Assume you have a method to get config safely
        $logger->info("DB connection config: " . json_encode($config));
    } else {
        $logger->error("DatabaseConnection failed to connect.");
    }

    // Test SafeMySQL wrapper singleton
    $safeMySQL = $container->get(SafeMySQL::class);

    // Test basic query to confirm connection works
    $testResult = $safeMySQL->getOne("SELECT DATABASE() AS dbname");
    $logger->info("SafeMySQL connected to database: " . $testResult);

} catch (Throwable $ex) {
    $logger->error("Error during DB connection test: " . $ex->getMessage());
}
