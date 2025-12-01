<?php
declare(strict_types=1);

namespace App\Infrastructure\Core\DI;

use App\Application\DI\Interfaces\DIContainerInterface;
use App\Common\Enums\ServiceLifetime;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use App\Infrastructure\Core\DI\Exceptions\CircularDependencyException;
use RuntimeException;

final class DIContainer implements DIContainerInterface, ContainerInterface
{
    /** @var array<string, object|string|\Closure|scalar|null> */
    private array $bindings = [];

    /** @var array<string, ServiceLifetime> */
    private array $lifetimes = [];

    private readonly LifecycleManager $lifecycleManager;
    private readonly Autowirer $autowirer;

    /** Tracks currently resolving services to detect cycles */
    private array $resolving = [];

    private ?LoggerInterface $logger = null;

    public function __construct()
    {
        $this->lifecycleManager = new LifecycleManager();
        $this->autowirer = new Autowirer($this);
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Bind a service with concrete implementation and lifetime.
     */
    public function bind(string $abstract, object|string|null $concrete, ServiceLifetime $lifetime = ServiceLifetime::SINGLETON): void
    {
        $this->bindings[$abstract] = $concrete;
        $this->lifetimes[$abstract] = $lifetime;
    }

    /**
     * Shortcut to bind singleton services.
     */
    public function singleton(string $abstract, object|string|null $concrete): void
    {
        $this->bind($abstract, $concrete, ServiceLifetime::SINGLETON);
    }

    /**
     * Check if container has binding or class.
     */
    public function has(string $id): bool
    {
        return isset($this->bindings[$id]) || class_exists($id);
    }

    /**
     * Get service instance by ID.
     * Throws CircularDependencyException if circular reference detected.
     *
     * @return object
     */
    public function get(string $id): object
    {
        if (isset($this->resolving[$id])) {
            $stackTrace = implode(' -> ', array_keys($this->resolving)) . " -> {$id}";
            $errorMessage = "Circular dependency detected resolving '{$id}'. Stack: {$stackTrace}";
            if ($this->logger) {
                $this->logger->critical($errorMessage);
            } else {
                error_log($errorMessage);
            }
            throw new CircularDependencyException($errorMessage);
        }

        $this->resolving[$id] = true;

        try {
            $service = $this->resolve($id);

            if (!is_object($service)) {
                throw new RuntimeException("Service '{$id}' did not resolve to an object, got " . gettype($service));
            }

            return $service;
        } catch (\Throwable $e) {
            $errorMessage = "Error resolving service '{$id}': " . $e->getMessage();
            if ($this->logger) {
                $this->logger->error($errorMessage);
            } else {
                error_log($errorMessage);
            }
            throw $e;
        } finally {
            unset($this->resolving[$id]);
        }
    }

    /**
     * Retrieve raw or scalar service without autowiring.
     * Throws if service is a class binding (use get() instead).
     *
     * @return mixed
     */
    public function getRaw(string $id): mixed
    {
        if (!isset($this->bindings[$id])) {
            throw new RuntimeException("No binding found for '{$id}'.");
        }

        $concrete = $this->bindings[$id];

        if (is_string($concrete) && class_exists($concrete)) {
            throw new RuntimeException("Service '{$id}' is a class. Use get() instead.");
        }

        return $concrete instanceof \Closure ? ($concrete)($this) : $concrete;
    }

    /**
     * Resolves the service by ID considering lifetime, autowiring, and binding type.
     *
     * @return object|string|int|float|bool|null
     */
    private function resolve(string $id): object|string|int|float|bool|null
    {
        $lifetime = $this->lifetimes[$id] ?? ServiceLifetime::SINGLETON;
        $concrete = $this->bindings[$id] ?? $id;

        return $this->lifecycleManager->getOrCreate(
            $id,
            function () use ($concrete, $id) {
                try {
                    if ($concrete instanceof \Closure) {
                        return $concrete($this);
                    }
                    if (is_object($concrete)) {
                        return $concrete;
                    }
                    if (is_string($concrete) && class_exists($concrete)) {
                        return $this->autowirer->create($concrete);
                    }
                    throw new RuntimeException("Invalid service definition for " . (is_string($concrete) ? $concrete : gettype($concrete)));
                } catch (\Throwable $e) {
                    $errorMessage = "Error in factory for service '{$id}': " . $e->getMessage();
                    if ($this->logger) {
                        $this->logger->error($errorMessage);
                    } else {
                        error_log($errorMessage);
                    }
                    throw $e;
                }
            },
            $lifetime
        );
    }
}
