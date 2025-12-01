<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Core\Database;

use SafeMySQL;
use App\Config\EnvironmentLoader;
use RuntimeException;

/**
 * DBConnection
 *
 * Singleton managing a shared SafeMySQL connection instance
 * for secure and performant DB access throughout the app lifecycle.
 *
 * Usage:
 *   $db = DBConnection::getInstance($envLoader)->getConnection();
 *   $row = $db->getRow("SELECT * FROM users WHERE id = ?i", $id);
 */
final class DatabaseConnection
{
    private static ?self $instance = null;
    private SafeMySQL $connection;
    private EnvironmentLoader $envLoader;

    /**
     * Private constructor to prevent multiple instances.
     * Reads DB config via EnvironmentLoader.
     *
     * @param EnvironmentLoader $envLoader
     * @throws RuntimeException on invalid config or connection failure.
     */
    private function __construct(EnvironmentLoader $envLoader)
    {
        $this->envLoader = $envLoader;

        $host = $this->envLoader->get('DB_HOST', 'localhost');
        $user = $this->envLoader->get('DB_USER', '');
        $pass = $this->envLoader->get('DB_PASS', '');
        $name = $this->envLoader->get('DB_NAME');
        $port = (int) $this->envLoader->get('DB_PORT', '3306');
        $charset = $this->envLoader->get('DB_CHARSET', 'utf8mb4');

        if (empty($name)) {
            throw new RuntimeException('Database name (DB_NAME) not set in environment configuration.');
        }
        if (empty($user)) {
            throw new RuntimeException('Database user (DB_USER) not set in environment configuration.');
        }
        if (empty($host)) {
            throw new RuntimeException('Database host (DB_HOST) not set in environment configuration.');
        }
        if ($port <= 0) {
            throw new RuntimeException('Database port (DB_PORT) is invalid.');
        }

        $config = [
            'user' => $user,
            'pass' => $pass,
            'db' => $name,
            'host' => $host,
            'port' => $port,
            'charset' => $charset,
            'throw_exception' => true,  // Enable exceptions in SafeMySQL
        ];

        try {
            $this->connection = new SafeMySQL($config);
        } catch (\Throwable $e) {
            throw new RuntimeException("Database connection failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Returns singleton instance, lazily initialized.
     * 
     * @param EnvironmentLoader $envLoader
     * @return self
     */
    public static function getInstance(EnvironmentLoader $envLoader): self
    {
        if (self::$instance === null) {
            self::$instance = new self($envLoader);
        }
        return self::$instance;
    }

    /**
     * Get the SafeMySQL connection instance.
     *
     * @return SafeMySQL
     */
    public function getConnection(): SafeMySQL
    {
        return $this->connection;
    }

    /**
     * Prevent cloning.
     */
    private function __clone()
    {
    }

    /**
     * Prevent unserialization.
     * 
     * @throws \Exception
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }

    /**
     * Check DB connection health.
     * 
     * @return bool True if connection alive, false otherwise.
     */
    public function isConnected(): bool
    {
        try {
            $this->connection->query("SELECT 1");
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
    // devonly
    public function getConfig(): array
    {
        return [
            'host' => $this->envLoader->get('DB_HOST'),
            'user' => $this->envLoader->get('DB_USER'),
            'name' => $this->envLoader->get('DB_NAME'),
            'port' => (int) $this->envLoader->get('DB_PORT', '3306'),
            'charset' => $this->envLoader->get('DB_CHARSET', 'utf8mb4')
        ];
    }

}
