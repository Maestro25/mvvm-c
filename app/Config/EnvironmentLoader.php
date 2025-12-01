<?php
declare(strict_types=1);

namespace App\Config;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use RuntimeException;

/**
 * EnvironmentLoader
 *
 * Loads environment variables from `.env` files in non-production environments,
 * and falls back to system environment variables in production.
 */
final class EnvironmentLoader
{
    private Dotenv $dotenv;
    private array $cachedEnv = [];

    /**
     * @param string $basePath Absolute path to directory containing .env files.
     */
    public function __construct(private string $basePath)
    {
        $this->dotenv = Dotenv::createImmutable($this->basePath);
    }

    /**
     * Load environment variables:
     * - Use .env files only in non-production environments
     * - Validate required environment variables are present/not empty
     * - Throws RuntimeException on error or missing required variables
     */
    // public function load(): void
    // {
    //     $env = getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? 'production');
    //     $isProd = strcasecmp($env, 'production') === 0;

    //     // Required environment variables, add your session cookie config here
    //     $required = [
    //         'DB_HOST',
    //         'DB_NAME',
    //         'DB_USER',
    //         'DB_PASS',
    //         'APP_ENV',
    //         'APP_DEBUG',
    //         'JWT_SECRET',

    //         // Session cookie configuration required keys
    //         'SESSION_COOKIE_NAME',
    //         'SESSION_COOKIE_LIFETIME',
    //         'SESSION_COOKIE_PATH',
    //         'SESSION_COOKIE_DOMAIN',
    //         'SESSION_COOKIE_SECURE',
    //         'SESSION_COOKIE_HTTPONLY',
    //         'SESSION_COOKIE_SAMESITE',
    //     ];

    //     if (!$isProd) {
    //         try {
    //             $this->dotenv->safeLoad();
    //             $this->dotenv->required($required)->notEmpty();
    //         } catch (InvalidPathException $e) {
    //             throw new RuntimeException("Environment file not found or unreadable: " . $e->getMessage(), 0, $e);
    //         } catch (\Exception $e) {
    //             throw new RuntimeException("Environment validation error: " . $e->getMessage(), 0, $e);
    //         }
    //     } else {
    //         // In production, check required env vars in system environment only
    //         foreach ($required as $req) {
    //             $val = getenv($req) ?: ($_ENV[$req] ?? null);
    //             if ($val === null || trim($val) === '') {
    //                 throw new RuntimeException("Required environment variable '{$req}' is missing or empty in production environment");
    //             }
    //         }
    //     }

    //     // Cache environment variables trimmed for optimized access
    //     $envSources = $_ENV + $_SERVER;
    //     foreach ($envSources as $key => $value) {
    //         if (is_string($value)) {
    //             $this->cachedEnv[$key] = trim($value);
    //         }
    //     }

    //     foreach (getenv() ?: [] as $key => $value) {
    //         if (is_string($value)) {
    //             $this->cachedEnv[$key] = trim($value);
    //         }
    //     }
    // }
    public function load(): void
    {
        $required = [
            'DB_HOST',
            'DB_NAME',
            'DB_USER',
            'DB_PASS',
            'APP_ENV',
            'APP_DEBUG',
            'JWT_SECRET',

            // Session cookie config keys
            'SESSION_COOKIE_NAME',
            'SESSION_COOKIE_LIFETIME',
            'SESSION_COOKIE_PATH',
            'SESSION_COOKIE_DOMAIN',
            'SESSION_COOKIE_SECURE',
            'SESSION_COOKIE_HTTPONLY',
            'SESSION_COOKIE_SAMESITE',
        ];


        try {
            // Always load the .env file regardless of environment
            $this->dotenv->safeLoad();
            $this->dotenv->required($required)->notEmpty();
        } catch (InvalidPathException $e) {
            throw new RuntimeException("Environment file not found or unreadable: " . $e->getMessage(), 0, $e);
        } catch (\Exception $e) {
            throw new RuntimeException("Environment validation error: " . $e->getMessage(), 0, $e);
        }

        // Cache environment variables trimmed for optimized access
        $envSources = $_ENV + $_SERVER;
        foreach ($envSources as $key => $value) {
            if (is_string($value)) {
                $this->cachedEnv[$key] = trim($value);
            }
        }
        foreach (getenv() ?: [] as $key => $value) {
            if (is_string($value)) {
                $this->cachedEnv[$key] = trim($value);
            }
        }
    }

    /**
     * Retrieve an environment variable by key.
     * Falls back to specified default if not found.
     *
     * @param string $key Environment variable key
     * @param string|null $default Default value if variable not set or empty
     * @return string|null
     */
    public function get(string $key, ?string $default = null): ?string
    {
        return isset($this->cachedEnv[$key]) && $this->cachedEnv[$key] !== '' ? $this->cachedEnv[$key] : $default;
    }

    /**
     * Check if an environment variable is set and not empty.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->cachedEnv[$key]) && trim((string) $this->cachedEnv[$key]) !== '';
    }
}
