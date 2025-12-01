<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Core\DI\DIContainer;
use App\Application\Session\Services\SessionService;
use Psr\Log\LoggerInterface;
use App\Domain\Shared\ValueObjects\UserId;
use App\Infrastructure\Persistence\Session\Repositories\DbSessionStorage;
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Setup DI container
$container = new DIContainer();
(require __DIR__ . '/../config/DI/container.php')($container);

$logger = $container->get(LoggerInterface::class);
$sessionService = $container->get(SessionService::class);
$sessionHandler = $container->get(\SessionHandlerInterface::class);
$dbStorage = $container->get(DbSessionStorage::class);

// Register session handler
session_set_save_handler($sessionHandler, true);

$logger->info("Starting session");
$sessionService->startSession();

$userId = UserId::generate();
$sessionService->setUserId($userId);
$_SESSION['test_key'] = 'test_value';

$sessionId = session_id();
$logger->info("Current session ID before closing write: $sessionId");

// Log explicitly before write
$logger->info("About to call session_write_close() - triggering write()");

session_write_close();

$logger->info("session_write_close() completed");

// Confirm persistence
$sessionData = $dbStorage->read($sessionId);
if ($sessionData !== '') {
    $logger->info("Session data persisted for session ID: $sessionId");
    $logger->info("Session raw data: $sessionData");
} else {
    $logger->warning("Session data NOT found for session ID: $sessionId");
}

// Check active sessions for user
$sessions = $dbStorage->findActiveSessionsByUser((string) $userId);
$logger->info('Active sessions for user: ' . print_r($sessions, true));
