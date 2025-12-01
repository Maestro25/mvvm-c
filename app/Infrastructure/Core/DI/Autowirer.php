<?php
declare(strict_types=1);

namespace App\Infrastructure\Core\DI;

use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;
use Psr\Container\ContainerExceptionInterface;

/**
 * Automatically resolves and instantiates classes using reflection.
 * 
 * - Supports explicit parameter overrides for scalars and non-class params.
 * - Handles nullable types and default values gracefully.
 * - Caches ReflectionClass instances for performance.
 */
final class Autowirer
{
    /**
     * Cache for ReflectionClass instances to reduce reflection overhead.
     * @var array<string, ReflectionClass<object>>
     */
    private array $reflectionCache = [];

    public function __construct(private readonly DIContainer $container)
    {
    }

    /**
     * Automatically instantiate the given class name with resolved dependencies.
     * Supports explicit parameter overrides keyed by parameter names.
     *
     * @param class-string $className
     * @param array<string, mixed> $parameters Explicit constructor parameter overrides.
     * @return object
     * @throws ContainerExceptionInterface
     * @throws RuntimeException
     */
    public function create(string $className, array $parameters = []): object
    {
        // Use cached reflection for performance
        $reflection = $this->reflectionCache[$className] ??= new ReflectionClass($className);

        if (!$reflection->isInstantiable()) {
            throw new RuntimeException("Class {$className} is not instantiable.");
        }

        $constructor = $reflection->getConstructor();
        if ($constructor === null || $constructor->getNumberOfParameters() === 0) {
            return $reflection->newInstance();
        }

        $dependencies = [];

        foreach ($constructor->getParameters() as $param) {
            $paramName = $param->getName();
            $paramType = $param->getType();

            // If explicit parameter override provided, use it directly
            if (array_key_exists($paramName, $parameters)) {
                $dependencies[] = $parameters[$paramName];
                continue;
            }

            if ($paramType === null) {
                // Untyped parameter: use default value if available, else fail
                if ($param->isDefaultValueAvailable()) {
                    $dependencies[] = $param->getDefaultValue();
                    continue;
                }
                throw new RuntimeException(
                    "Cannot resolve untyped parameter \${$paramName} in {$className} constructor."
                );
            }

            if ($paramType instanceof ReflectionNamedType) {
                $typeName = $paramType->getName();
                if (!$paramType->isBuiltin()) {
                    // Class or interface: resolve from container
                    $dependencies[] = $this->container->get($typeName);
                } else {
                    // Builtin type (scalar)
                    if ($param->isDefaultValueAvailable()) {
                        $dependencies[] = $param->getDefaultValue();
                    } elseif ($paramType->allowsNull()) {
                        // Nullable built-in without default, pass null
                        $dependencies[] = null;
                    } else {
                        throw new RuntimeException(
                            "Cannot resolve builtin parameter \${$paramName} in {$className} constructor."
                        );
                    }
                }
            }
        }

        return $reflection->newInstanceArgs($dependencies);
    }
}
