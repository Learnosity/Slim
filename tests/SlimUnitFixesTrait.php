<?php

use PHPUnit\Framework\Exception;
use PHPUnit\Framework\InvalidArgumentException;

trait SlimUnitFixesTrait
{
    public static function assertAttributeEquals($expected, string $actualAttributeName, $actualClassOrObject, string $message = '', float $delta = 0.0, int $maxDepth = 10, bool $canonicalize = false, bool $ignoreCase = false): void
    {
        static::assertEquals(
            $expected,
            static::readAttribute($actualClassOrObject, $actualAttributeName),
            $message,
            $delta,
            $maxDepth,
            $canonicalize,
            $ignoreCase
        );
    }

    public static function assertAttributeSame($expected, string $actualAttributeName, $actualClassOrObject, string $message = ''): void
    {
        static::assertSame(
            $expected,
            static::readAttribute($actualClassOrObject, $actualAttributeName),
            $message
        );
    }

    public static function assertAttributeContains($needle, string $haystackAttributeName, $haystackClassOrObject, string $message = '', bool $ignoreCase = false, bool $checkForObjectIdentity = true, bool $checkForNonObjectIdentity = false): void
    {
        static::assertContains(
            $needle,
            static::readAttribute($haystackClassOrObject, $haystackAttributeName),
            $message,
            $ignoreCase,
            $checkForObjectIdentity,
            $checkForNonObjectIdentity
        );
    }

    public static function readAttribute($classOrObject, string $attributeName)
    {
        if (!self::slimIsValidClassAttributeName($attributeName)) {
            throw InvalidArgumentException::create(2, 'valid attribute name');
        }

        if (is_string($classOrObject)) {
            if (!class_exists($classOrObject)) {
                throw InvalidArgumentException::create(
                    1,
                    'class name'
                );
            }

            return static::getStaticAttribute(
                $classOrObject,
                $attributeName
            );
        }

        if (is_object($classOrObject)) {
            return static::getObjectAttribute(
                $classOrObject,
                $attributeName
            );
        }

        throw InvalidArgumentException::create(
            1,
            'class name or object'
        );
    }

    public static function getObjectAttribute($object, string $attributeName)
    {
        if (!is_object($object)) {
            throw InvalidArgumentException::create(1, 'object');
        }

        if (!self::slimIsValidClassAttributeName($attributeName)) {
            throw InvalidArgumentException::create(2, 'valid attribute name');
        }

        $reflector = new ReflectionObject($object);

        do {
            try {
                $attribute = $reflector->getProperty($attributeName);

                if (!$attribute || $attribute->isPublic()) {
                    return $object->{$attributeName};
                }

                $attribute->setAccessible(true);
                $value = $attribute->getValue($object);
                $attribute->setAccessible(false);

                return $value;
            } catch (ReflectionException $e) {
            }
        } while ($reflector = $reflector->getParentClass());

        throw new Exception(
            sprintf(
                'Attribute "%s" not found in object.',
                $attributeName
            )
        );
    }

    public static function getStaticAttribute(string $className, string $attributeName)
    {
        if (!class_exists($className)) {
            throw InvalidArgumentException::create(1, 'class name');
        }

        if (!self::slimIsValidClassAttributeName($attributeName)) {
            throw InvalidArgumentException::create(2, 'valid attribute name');
        }

        try {
            $class = new ReflectionClass($className);
            // @codeCoverageIgnoreStart
        } catch (ReflectionException $e) {
            throw new Exception(
                $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }
        // @codeCoverageIgnoreEnd

        while ($class) {
            $attributes = $class->getStaticProperties();

            if (array_key_exists($attributeName, $attributes)) {
                return $attributes[$attributeName];
            }

            $class = $class->getParentClass();
        }

        throw new Exception(
            sprintf(
                'Attribute "%s" not found in class.',
                $attributeName
            )
        );
    }

    private static function slimIsValidClassAttributeName(string $attributeName): bool
    {
        return (bool) preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $attributeName);
    }
}
