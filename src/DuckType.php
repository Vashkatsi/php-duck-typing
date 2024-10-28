<?php
/**
 * DuckType Library
 *
 * @license BSD-3-Clause
 * @link https://github.com/vashkatsi/ducktype
 */

namespace DuckType;

use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionType;
use InvalidArgumentException;
use DuckType\Exceptions\DuckTypeException;

/**
 * @throws ReflectionException
 */
function assertDuckType(object $instance, object|string $type): bool
{
    static $reflectionCache = [];

    $typeName = is_object($type) ? get_class($type) : $type;

    if (!interface_exists($typeName) && !class_exists($typeName)) {
        throw new InvalidArgumentException("Type $typeName does not exist.");
    }

    $typeReflection = $reflectionCache[$typeName] ?? new ReflectionClass($typeName);
    $reflectionCache[$typeName] = $typeReflection;

    $instanceClass = get_class($instance);

    $instanceReflection = $reflectionCache[$instanceClass] ?? new ReflectionClass($instanceClass);
    $reflectionCache[$instanceClass] = $instanceReflection;

    $errors = [];

    foreach ($typeReflection->getMethods() as $typeMethod) {
        if (!$instanceReflection->hasMethod($typeMethod->getName())) {
            $errors[] = "Method {$typeMethod->getName()} not found in instance.";
            continue;
        }

        $instanceMethod = $instanceReflection->getMethod($typeMethod->getName());

        if ($typeMethod->isPublic() && !$instanceMethod->isPublic()) {
            $errors[] = "Method {$typeMethod->getName()} should be public.";
        } elseif ($typeMethod->isProtected() && !$instanceMethod->isProtected()) {
            $errors[] = "Method {$typeMethod->getName()} should be protected.";
        } elseif ($typeMethod->isPrivate() && !$instanceMethod->isPrivate()) {
            $errors[] = "Method {$typeMethod->getName()} should be private.";
        }

        if ($instanceMethod->getNumberOfParameters() > $typeMethod->getNumberOfParameters()) {
            $errors[] = "Method {$typeMethod->getName()} has more parameters than expected.";
            continue;
        }

        $typeParams = $typeMethod->getParameters();
        $instanceParams = $instanceMethod->getParameters();

        foreach ($typeParams as $i => $typeParam) {
            if (!isset($instanceParams[$i])) {
                $errors[] = "Parameter {$typeParam->getName()} in method {$typeMethod->getName()} is missing.";
                continue;
            }

            $instanceParam = $instanceParams[$i];

            if ($typeParam->hasType()) {
                $typeParamType = $typeParam->getType();
                $instanceParamType = $instanceParam->getType();

                if (!$instanceParamType) {
                    $errors[] = "Parameter {$typeParam->getName()} in method {$typeMethod->getName()} is missing type declaration.";
                    continue;
                }

                if (!isTypeContravariant($typeParamType, $instanceParamType)) {
                    $errors[] = "Parameter {$typeParam->getName()} in method {$typeMethod->getName()} has type mismatch.";
                }
            }
        }

        if ($typeMethod->hasReturnType()) {
            $typeReturnType = $typeMethod->getReturnType();
            $instanceReturnType = $instanceMethod->getReturnType();

            if (!$instanceReturnType) {
                $errors[] = "Method {$typeMethod->getName()} is missing return type.";
                continue;
            }

            if (!isTypeCovariant($typeReturnType, $instanceReturnType)) {
                $errors[] = "Return type of method {$typeMethod->getName()} does not match.";
            }
        }
    }

    foreach ($typeReflection->getProperties() as $typeProperty) {
        if (!$instanceReflection->hasProperty($typeProperty->getName())) {
            $errors[] = "Property {$typeProperty->getName()} not found in instance.";
            continue;
        }

        $instanceProperty = $instanceReflection->getProperty($typeProperty->getName());

        if ($typeProperty->isPublic() && !$instanceProperty->isPublic()) {
            $errors[] = "Property {$typeProperty->getName()} should be public.";
        } elseif ($typeProperty->isProtected() && !$instanceProperty->isProtected()) {
            $errors[] = "Property {$typeProperty->getName()} should be protected.";
        } elseif ($typeProperty->isPrivate() && !$instanceProperty->isPrivate()) {
            $errors[] = "Property {$typeProperty->getName()} should be private.";
        }

        if ($typeProperty->hasType()) {
            $typePropType = $typeProperty->getType();
            $instancePropType = $instanceProperty->getType();

            if (!$instancePropType) {
                $errors[] = "Property {$typeProperty->getName()} is missing type declaration.";
                continue;
            }

            if ($typePropType->getName() !== $instancePropType->getName() ||
                $typePropType->allowsNull() !== $instancePropType->allowsNull()) {
                $errors[] = "Property {$typeProperty->getName()} has a type mismatch.";
            }
        }
    }

    if (!empty($errors)) {
        throw new DuckTypeException($errors);
    }

    return true;
}

function isTypeContravariant(ReflectionType $expectedType, ReflectionType $actualType): bool
{
    if ($expectedType instanceof ReflectionNamedType && $actualType instanceof ReflectionNamedType) {
        if ($expectedType->allowsNull() !== $actualType->allowsNull()) {
            return false;
        }

        $expectedTypeName = $expectedType->getName();
        $actualTypeName = $actualType->getName();

        if ($expectedTypeName === $actualTypeName) {
            return true;
        }

        if (class_exists($expectedTypeName) && class_exists($actualTypeName)) {
            if (is_a($expectedTypeName, $actualTypeName, true)) {
                return true;
            }
        }

        return false;
    }

    return false;
}

function isTypeCovariant(ReflectionType $expectedType, ReflectionType $actualType): bool
{
    if ($expectedType instanceof ReflectionNamedType && $actualType instanceof ReflectionNamedType) {
        if ($expectedType->allowsNull() !== $actualType->allowsNull()) {
            return false;
        }

        $expectedTypeName = $expectedType->getName();
        $actualTypeName = $actualType->getName();

        if ($expectedTypeName === $actualTypeName) {
            return true;
        }

        if (class_exists($actualTypeName) && class_exists($expectedTypeName)) {
            if (is_a($actualTypeName, $expectedTypeName, true)) {
                return true;
            }
        }

        return false;
    }

    return false;
}
