<?php

declare(strict_types=1);

namespace Tempest\Support\Reflection;

use ReflectionType;
use Reflector;
use TypeError;

final class CannotCreateTypeError extends TypeError
{
    public function __construct(string|Reflector|ReflectionType $reflector)
    {
        if (! is_string($reflector)) {
            $reflector = $reflector::class;
        }

        parent::__construct("Could not create a Type from {$reflector}");
    }
}
