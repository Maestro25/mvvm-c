<?php
declare(strict_types=1);

namespace App\Infrastructure\Core\DI;

use Psr\Container\ContainerInterface;
use ReflectionMethod;
use ReflectionNamedType;
use RuntimeException;

/**
 * Invokes specified method on object or class with automatic DI parameter resolution.
 * Supports explicit parameter overrides, static and instance methods.
 * Shares parameter resolution logic identical to ControllerInvoker for DRY.
 */
final class MethodInvoker
{
    private readonly ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Invoke a method with resolved dependencies, supporting explicit parameter overrides.
     * 
     * @param object|string $objectOrClass Object instance or class name
     * @param string $method Method name to invoke
     * @param array<string, mixed> $parameters Optional explicit parameter overrides
     * @return mixed Result of method call
     * @throws RuntimeException If required parameters cannot be resolved
     */
    public function invokeMethod(object|string $objectOrClass, string $method, array $parameters = []): mixed
    {
        // Handle "ClassName::methodName" string callable safely
        if (is_string($objectOrClass) && str_contains($objectOrClass, '::')) {
            $reflection = ReflectionMethod::createFromMethodName($objectOrClass);
            $object = null;
        } else {
            $object = is_object($objectOrClass) ? $objectOrClass : $this->container->get($objectOrClass);
            $reflection = new ReflectionMethod($object, $method);
        }

        $args = $this->resolveParameters($reflection->getParameters(), $parameters);

        if ($object === null) {
            return $reflection->invokeArgs(null, $args);
        }

        return $reflection->invokeArgs($object, $args);
    }

    /**
     * Resolves parameters for method calls via container or overrides.
     * Shared with ControllerInvoker for minimal duplication.
     * 
     * @param \ReflectionParameter[] $params
     * @param array<string, mixed> $overrides
     * @return array<int, mixed>
     * @throws RuntimeException on unresolved required parameters
     */
    private function resolveParameters(array $params, array $overrides): array
    {
        $args = [];

        foreach ($params as $param) {
            $name = $param->getName();
            $type = $param->getType();

            if (array_key_exists($name, $overrides)) {
                $args[] = $overrides[$name];
                continue;
            }

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $className = $type->getName();
                if ($this->container->has($className)) {
                    $args[] = $this->container->get($className);
                } elseif ($param->isDefaultValueAvailable()) {
                    $args[] = $param->getDefaultValue();
                } else {
                    throw new RuntimeException("Unable to resolve parameter \${$name}");
                }
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw new RuntimeException("Missing required parameter \${$name}");
            }
        }

        return $args;
    }
}
