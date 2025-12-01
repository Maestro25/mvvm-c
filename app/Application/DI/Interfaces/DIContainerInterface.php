<?php
declare(strict_types=1);

namespace App\Application\DI\Interfaces;

use App\Common\Enums\ServiceLifetime;
use Closure;
use Psr\Container\ContainerInterface;

/**
 * Interface defining a Dependency Injection Container.
 *
 * Combines PSR-11 container resolution with explicit binding registration.
 */
interface DIContainerInterface extends ContainerInterface
{
    /**
     * Bind an abstraction (interface or class) to a concrete implementation.
     *
     * @param class-string $abstract Interface or abstract class.
     * @param class-string|callable|object $concrete Concrete class name, factory callable, or instance.
     * @param ServiceLifetime $lifetime Service lifetime (singleton, transient, scoped).
     * @return void
     */
    public function bind(string $abstract, object|string|null $concrete, ServiceLifetime $lifetime = ServiceLifetime::SINGLETON): void;


    /**
     * Register a pre-made singleton instance in the container.
     *
     * @param class-string $abstract Interface or class name.
     * @param object $instance Instance to register.
     */
    public function singleton(string $abstract, object $instance): void;

    /**
     * Check if a service is registered or available for resolution.
     *
     * @param class-string $id Service identifier.
     * @return bool
     */
    public function has(string $id): bool;
}
