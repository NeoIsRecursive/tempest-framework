<?php

declare(strict_types=1);

namespace Tempest\Support\Reflection;

use ReflectionNamedType;

final readonly class Type
{
    public string $name;

    public function __construct(string|ReflectionNamedType $type)
    {
        $this->name = match(true) {
            is_string($type) => $type,
            default => $type->getName(),
        };
    }
}
