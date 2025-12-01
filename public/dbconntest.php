<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\EnvironmentLoader;
use App\Infrastructure\Core\DI\DIContainer;
use App\Infrastructure\Persistence\Core\Database\DatabaseConnection;


require_once __DIR__ . '/index.php';

try {
    $envLoader = $container->get(EnvironmentLoader::class);
    $envLoader->load();

    $dbConnection = DatabaseConnection::getInstance($envLoader);

    if ($dbConnection->isConnected()) {
        echo "DatabaseConnection: Connection successful.\n";
    } else {
        echo "DatabaseConnection: Connection failed.\n";
    }
} catch (Exception $e) {
    echo "DatabaseConnection error: " . $e->getMessage() . "\n";
}
