<?php
declare(strict_types=1);

namespace App\Infrastructure\Hydrators;

use App\Domain\ValueObjects\Factories\Interfaces\ValueObjectFactoryInterface;
use App\Presentation\ViewModels\Interfaces\ViewModelInterface;

use App\Presentation\ViewModels\ViewModel;
use ReflectionProperty;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionUnionType;

class Hydrator implements HydratorInterface
{
    private ValueObjectFactoryInterface $voFactory;

    public function __construct(ValueObjectFactoryInterface $voFactory)
    {
        $this->voFactory = $voFactory;
    }

    /**
     * Hydrate properties and setters recursively.
     *
     * @param object $viewModel
     * @param array<string, mixed> $data
     */
    public function hydrate(object $viewModel, array $data): void
    {
        foreach ($data as $property => $value) {
            $setter = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $property)));

            if (method_exists($viewModel, $setter)) {
                if (PHP_VERSION_ID >= 80400) {
                    $className = get_class($viewModel);
                    $methodNameStr = $className . '::' . $setter;
                    $refMethod = ReflectionMethod::createFromMethodName($methodNameStr);
                } else {
                    $refMethod = new ReflectionMethod($viewModel, $setter);
                }

                $params = $refMethod->getParameters();

                if (isset($params[0])) {
                    $paramType = $params[0]->getType();

                    if ($paramType instanceof ReflectionNamedType) {
                        $typeName = $paramType->getName();
                        $value = $this->voFactory->hydrate($typeName, $value);
                    } elseif ($paramType instanceof ReflectionUnionType) {
                        foreach ($paramType->getTypes() as $singleType) {
                            if ($singleType instanceof ReflectionNamedType) {
                                $typeName = $singleType->getName();
                                $hydrated = $this->voFactory->hydrate($typeName, $value);
                                if ($hydrated !== $value) {
                                    $value = $hydrated;
                                    break;
                                }
                            }
                        }
                    }
                }

                $viewModel->$setter($value);
                continue;
            }

            if (!property_exists($viewModel, $property)) {
                throw new \RuntimeException("Property {$property} does not exist on " . get_class($viewModel));
            }

            $refProperty = new ReflectionProperty($viewModel, $property);

            if ($refProperty->isReadOnly() && $refProperty->isInitialized($viewModel)) {
                continue;
            }

            $refProperty->setAccessible(true);

            try {
                $refType = $refProperty->getType();
                $propType = null;

                if ($refType instanceof ReflectionNamedType) {
                    $propType = $refType->getName();
                } elseif ($refType instanceof ReflectionUnionType) {
                    // Optionally, handle union property types if necessary
                }

                if (is_array($value) && $propType !== null && $this->isComplexPropertyType($propType)) {
                    $value = $this->hydrateNestedObject($propType, $value);
                }

                $value = $this->voFactory->hydrate($propType, $value);

                $refProperty->setValue($viewModel, $value);
            } catch (\TypeError $e) {
                throw new \RuntimeException("Type error on property {$property}: " . $e->getMessage());
            }
        }
    }


    private function isComplexPropertyType(?string $type): bool
    {
        return !empty($type) && class_exists($type);
    }

    private function hydrateNestedObject(string $className, array $data): object
    {
        if (is_subclass_of($className, ViewModelInterface::class)) {
            return $className::fromArray($data);
        }
        if (method_exists($className, 'fromArray')) {
            return $className::fromArray($data);
        }
        return new $className(...$data);
    }
}
