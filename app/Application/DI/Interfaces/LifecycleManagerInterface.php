<?php
declare(strict_types=1);

namespace App\Application\DI\Interfaces;

use App\Common\Enums\ServiceLifetime;

/**
 * Interface LifecycleManagerInterface
 *
 * Manages service lifetimes in DI container.
 */
interface LifecycleManagerInterface
{
    /**
     * Retrieve an existing instance for given service ID or create new.
     *
     * @param string $serviceId
     * @param callable $factory Factory callable to create instance if needed
     * @param ServiceLifetime $lifetime Type of service lifetime (singleton, transient, scoped)
     * @return object
     */
    public function getOrCreate(string $serviceId, callable $factory, ServiceLifetime $lifetime): object;

    /**
     * Clear scoped instances (e.g., at end of request).
     */
    public function clearScoped(): void;

    /**
     * Clear all managed instances.
     */
    public function clearAll(): void;
}
