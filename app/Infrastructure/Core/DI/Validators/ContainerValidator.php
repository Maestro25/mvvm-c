<?php
declare(strict_types=1);

namespace App\Infrastructure\Core\DI\Validators;

use App\Application\DI\Interfaces\DIContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class ContainerValidator
 *
 * Validates service bindings and dependencies in the DI container.
 */
final class ContainerValidator
{
    private DIContainerInterface $container;

    public function __construct(DIContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Validate all configured services to ensure they are resolvable.
     *
     * @param array<class-string> $serviceIds List of service identifiers to validate.
     * @throws \RuntimeException on validation failure.
     */
    public function validate(array $serviceIds): void
    {
        foreach ($serviceIds as $id) {
            try {
                $this->container->get($id);
            } catch (NotFoundExceptionInterface $e) {
                throw new \RuntimeException("Service {$id} is not found or its dependencies cannot be resolved.");
            } catch (\Throwable $e) {
                throw new \RuntimeException("Error resolving service {$id}: " . $e->getMessage(), 0, $e);
            }
        }
    }
}
