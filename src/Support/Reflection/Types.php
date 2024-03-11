<?php

declare(strict_types=1);

namespace Tempest\Support\Reflection;

use ArrayIterator;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;
use Reflector;

/**
 * @extends ArrayIterator<array-key, \Tempest\Support\Reflection\Type>
 */
final class Types extends ArrayIterator
{
    /** @var \Tempest\Support\Reflection\Type[] */
    private array $types;

    private function __construct(Reflector|ReflectionType $reflector)
    {
        $this->types = match($reflector::class) {
            ReflectionClass::class => [new Type($reflector->getName())],
            ReflectionMethod::class => $this->normalize($reflector->getReturnType()),
            ReflectionParameter::class => $this->normalize($reflector->getType()),
            ReflectionNamedType::class, ReflectionIntersectionType::class, ReflectionUnionType::class => $this->normalize($reflector),
            default => throw new CannotCreateTypeError($reflector),
        };

        parent::__construct($this->types);
    }

    public static function from(string|Reflector|ReflectionType $reflector): self
    {
        if (is_string($reflector)) {
            $reflector = new ReflectionClass($reflector);
        }

        return new self($reflector);
    }

    /**
     * @param ReflectionType $type
     * @return \Tempest\Support\Reflection\Type[]
     */
    private function normalize(ReflectionType $type): array
    {
        return match($type::class) {
            ReflectionNamedType::class => [new Type($type->getName())],
            ReflectionUnionType::class => array_map(fn (ReflectionNamedType $type) => $type->getName(), $type->getTypes()),
            ReflectionIntersectionType::class => array_map(fn (ReflectionNamedType $type) => $type->getName(), $type->getTypes()),
            default => throw new CannotCreateTypeError($type),
        };
    }
}
