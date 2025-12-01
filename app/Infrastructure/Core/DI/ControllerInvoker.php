<?php
declare(strict_types=1);

namespace App\Infrastructure\Core\DI;

use Psr\Container\ContainerInterface;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use RuntimeException;

/**
 * Invokes callables with automatic DI and parameter injection.
 * Supports closures, callable arrays, controller class strings with __invoke,
 * and explicit override parameters.
 * Delegates parameter resolution to reusable private method.
 */
final class ControllerInvoker
{
    private readonly ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Invoke the callable with dependencies injected from container and optional parameters.
     *
     * @param callable|string|array $callable Controller callable (['Class', 'method'], closure, or class name with __invoke)
     * @param array<string, mixed> $parameters Explicit parameters to pass (e.g., query params)
     * @return mixed The result of callable execution
     */
    public function invoke(callable|string|array $callable, array $parameters = []): mixed
    {
        // Support string class name with __invoke method
        if (is_string($callable) && class_exists($callable)) {
            $callable = [$this->container->get($callable), '__invoke'];
        }

        $reflection = $this->getReflectionFunction($callable);
        $args = $this->resolveParameters($reflection->getParameters(), $parameters);

        return $reflection->invokeArgs($args);
    }

    /**
     * Create a ReflectionFunction or ReflectionMethod depending on callable type.
     */
    private function getReflectionFunction(callable|array $callable): ReflectionFunction|ReflectionMethod
    {
        if (is_string($callable) && str_contains($callable, '::')) {
            return ReflectionMethod::createFromMethodName($callable);
        }

        return new ReflectionFunction($callable);
    }

    /**
     * Resolves parameters from container or explicit overrides, manages defaults and exceptions.
     *
     * @param \ReflectionParameter[] $params
     * @param array<string, mixed> $overrides
     * @return array<int, mixed>
     * @throws RuntimeException on unresolvable required parameters
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
