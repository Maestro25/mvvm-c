<?php
declare(strict_types=1);

namespace App\Infrastructure\Logging;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

final class LoggerFactory
{
    public static function createLogger(string $name = 'app_logger'): Logger
    {
        $logger = new Logger($name);

        $lineFormat = "[%datetime%] %level_name%: %message%\n";
        $dateFormat = "Y-m-d H:i:s";
        $formatter = new LineFormatter($lineFormat, $dateFormat, true, true);

        $fileHandler = new StreamHandler(__DIR__ . '/../../logs/app.log', Logger::DEBUG);
        $fileHandler->setFormatter($formatter);
        $logger->pushHandler($fileHandler);

        $stdoutHandler = new StreamHandler('php://stdout', Logger::DEBUG);
        $stdoutHandler->setFormatter($formatter);
        $logger->pushHandler($stdoutHandler);

        return $logger;
    }
}
