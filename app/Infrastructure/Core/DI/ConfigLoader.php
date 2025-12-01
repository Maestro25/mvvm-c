<?php
declare(strict_types=1);

namespace App\Infrastructure\Core\DI;

use App\Application\DI\Interfaces\DIContainerInterface;
use App\Common\Enums\ServiceLifetime;
use RuntimeException;

/**
 * Loads array-based DI service definitions from PHP config files.
 * 
 * Responsibilities:
 * - Validate file existence and readability.
 * - Validate returned config structure.
 * - Normalize and bind services with explicit lifetime handling.
 */
final class ConfigLoader
{
    /**
     * Load service definitions from a PHP config file.
     *
     * @param DIContainerInterface $container DI container instance.
     * @param string $filePath Absolute path to PHP config file.
     * @throws RuntimeException if file not found, unreadable or returns invalid data.
     */
    public function loadFromFile(DIContainerInterface $container, string $filePath): void
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new RuntimeException("Configuration file {$filePath} not found or unreadable.");
        }

        /** @var array<string, mixed> $definitions */
        $definitions = require $filePath;

        if (!is_array($definitions)) {
            throw new RuntimeException("Configuration file {$filePath} must return an array.");
        }

        foreach ($definitions as $abstract => $concrete) {
            if (is_array($concrete) && isset($concrete['class'])) {
                // Validate service lifetime explicitly, fallback to SINGLETON
                $lifetimeValue = $concrete['lifetime'] ?? ServiceLifetime::SINGLETON;
                $lifetime = $lifetimeValue instanceof ServiceLifetime
                    ? $lifetimeValue
                    : ServiceLifetime::tryFrom($lifetimeValue) ?? ServiceLifetime::SINGLETON;

                $container->bind($abstract, $concrete['class'], $lifetime);
            } elseif (
                is_string($concrete)
                || is_callable($concrete)
                || is_object($concrete)
            ) {
                // Default to SINGLETON for typical bindings
                $container->bind($abstract, $concrete, ServiceLifetime::SINGLETON);
            } else {
                throw new RuntimeException("Invalid binding format for service {$abstract}.");
            }
        }
    }
}
