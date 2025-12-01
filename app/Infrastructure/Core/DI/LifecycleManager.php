<?php
declare(strict_types=1);

namespace App\Infrastructure\Core\DI;

use App\Application\DI\Interfaces\LifecycleManagerInterface;
use App\Common\Enums\ServiceLifetime;

/**
 * Manages service lifecycles: singleton, scoped, transient.
 * Keeps lifecycle state isolated for clean separation of concerns.
 */
final class LifecycleManager implements LifecycleManagerInterface
{
    /**
     * Singleton instances container.
     * @var array<string, object>
     */
    private array $singletons = [];

    /**
     * Scoped instances container.
     * @var array<string, object>
     */
    private array $scoped = [];

    /**
     * Provides instance according to specified lifetime.
     *
     * @param string        $serviceId
     * @param callable      $factory   Factory to create instance
     * @param ServiceLifetime $lifetime Lifecycle type
     * @return object       Created or cached instance
     */
    public function getOrCreate(string $serviceId, callable $factory, ServiceLifetime $lifetime): object
    {
        return match ($lifetime) {
            ServiceLifetime::SINGLETON => $this->getSingleton($serviceId, $factory),
            ServiceLifetime::SCOPED => $this->getScoped($serviceId, $factory),
            ServiceLifetime::TRANSIENT => $factory(),
        };
    }

    /**
     * Get or create singleton instance.
     */
    private function getSingleton(string $serviceId, callable $factory): object
    {
        return $this->singletons[$serviceId] ??= $factory();
    }

    /**
     * Get or create scoped instance.
     */
    private function getScoped(string $serviceId, callable $factory): object
    {
        return $this->scoped[$serviceId] ??= $factory();
    }

    /**
     * Clear all scoped instances.
     */
    public function clearScoped(): void
    {
        $this->scoped = [];
    }

    /**
     * Clear all lifecycle instances: singleton and scoped.
     */
    public function clearAll(): void
    {
        $this->singletons = [];
        $this->scoped = [];
    }
}
