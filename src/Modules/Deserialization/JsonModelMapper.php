<?php

declare(strict_types=1);

namespace Crazynoob01\Common\Modules\Deserialization;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Closure;
use ErrorException;
use Exception;
use Illuminate\Support\Str;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;

/**
 * @template  TValue
 *
 * Notes:
 * Objects need to implement the \Crazynoob01\Common\Modules\Deserialization\CanBeMappedToJsonModel interface
 * Objects should use types where possible, and for properties that use document blocks, they must be fully qualified.
 */
class JsonModelMapper {
    public const TYPE_STRING = 'string';

    public const TYPE_FLOAT = 'float';

    public const TYPE_INTEGER = 'int';

    public const TYPE_BOOLEAN = 'bool';

    public const TYPE_ARRAY = 'array';

    private const TYPE_MIXED = 'mixed';

    private const TYPE_OBJECT = 'object';

    public function __construct(private bool $deserializePublicProperties = false, private bool $isPropertySnakeCase = true) {
    }

    /**
     * @phpstan-param null|Closure():class-string<TValue>                             $returnsClass
     * @phpstan-param null|Closure(class-string<TValue>, TValue):class-string<TValue> $decoratesFullyQualifiedClassPath
     *
     * @throws ErrorException
     *
     * @return array<TValue>|PaginatedModel|TValue
     */
    public function deserialise(
        string $json,
        ?Closure $returnsClass = null,
        ?Closure $decoratesFullyQualifiedClassPath = null,
        ?Closure $paginatedModelFactory = null) {
        try {
            $parentArray = json_decode($json, true);

            // if the data parameter exists in the array, then use that as the array, otherwise, use the parent array
            $dataArray = $this->extractDataFromArray($parentArray);

            if ($returnsClass === null) {
                return $dataArray;
            }

            // if the resulting set is a paginated set, then treat it differently
            if (array_key_exists('meta', $parentArray) === true && array_key_exists('pagination', $parentArray['meta']) === true) {
                $models = array_map(fn (array $item) => $this->map($item, $returnsClass, null), $dataArray);

                $meta = $this->map($parentArray['meta'], fn () => Meta::class, null);

                // if the factory method to create the paginated model is provided, then use it
                if ($paginatedModelFactory !== null) {
                    return $paginatedModelFactory($meta, ...$models);
                }

                return new PaginatedModel($meta, ...$models);
            }

            // determine if the array has numeric indices
            // if it's not an associative array, it's a collection of items
            if ($this->isAssociative($dataArray) === true) {
                return $this->map($dataArray, $returnsClass, $decoratesFullyQualifiedClassPath);
            }

            return array_map(fn (array $item) => $this->map($item, $returnsClass, $decoratesFullyQualifiedClassPath), $dataArray);
        } catch (ReflectionException | DeserializationException $exception) {
            throw new ErrorException($exception->getMessage(), $exception->getCode(), 1, $exception->getFile(), $exception->getLine(), $exception);
        }
    }

    /**
     * @phpstan-param null|Closure():class-string<TValue>                             $returnsClass
     * @phpstan-param null|Closure(class-string<TValue>, TValue):class-string<TValue> $decoratesFullyQualifiedClassPath
     *
     * @throws ErrorException
     *
     * @return array<TValue>|PaginatedModel|TValue
     */
    public static function mapFromJson(
        string $json,
        ?Closure $returnsClass = null,
        ?Closure $decoratesFullyQualifiedClassPath = null,
        ?Closure $paginatedModelFactory = null,
        bool $isPropertySnakeCase = true,
        bool $deserialisePublicProperties = false,
    ) {
        return (new self($deserialisePublicProperties, $isPropertySnakeCase))->deserialise($json, $returnsClass, $decoratesFullyQualifiedClassPath, $paginatedModelFactory);
    }

    private function isAssociative(array $array): bool {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }

    /**
     * @throws DeserializationException
     * @throws ReflectionException
     * @phpstan-param array                  $array
     * @phpstan-param Closure():class-string $returnsClass
     */
    private function map(array $array, Closure $returnsClass, ?Closure $decorator): mixed {
        // if the array contains a key of data, raise the elements within one level higher
        $array = $this->extractDataFromArray($array);

        $arguments  = [];
        $reflection = new ReflectionClass($returnsClass());
        $properties = $reflection->getProperties();

        // iterate over the properties and attempt to retrieve it from the array
        foreach ($properties as $property) {
            // only handle private or protected properties if required to
            if ($this->deserializePublicProperties === false && $property->isPublic() === true) {
                continue;
            }

            // convert the property name to snake case because the api returns in snake case
            $propertyNameForJson = $this->isPropertySnakeCase === true ? Str::snake($property->getName()) : $property->getName();

            // determine the type of the value
            $value = $array[$propertyNameForJson] ?? null;

            // ensure that the value being passed is converted to the type of the class property
            try {
                $arguments[$property->getName()] = $this->convertToPropertyTypeFromTypehint($property, $value, $decorator);
            } catch (ErrorException) {
                // no more support for doc block typehints
                throw new DeserializationException($property->getName(), $returnsClass());
            }
        }

        return new ($returnsClass())(...$arguments);
    }

    /**
     * @throws ErrorException
     * @phpstan-param null|Closure(class-string<TValue>, TValue): class-string<TValue> $decorator
     */
    private function convertToPropertyTypeFromTypehint(
        ReflectionProperty $reflectionProperty,
        mixed $value,
        ?Closure $decorator): mixed {
        /** @phpstan-var ReflectionNamedType|ReflectionUnionType|null $reflectionType */
        $reflectionType = $reflectionProperty->getType();

        // if the reflection type is a union type
        if ($reflectionType instanceof ReflectionUnionType) {
            // check if there is an associated attribute on the property
            $function = $this->getFunctionForAttribute($reflectionProperty, $decorator);

            if ($function === null) {
                // if the type is a union type, try each of the valid types until the object can be deserialized
                foreach ($reflectionType->getTypes() as $namedType) {
                    try {
                        return $this->resolveNestedClass($namedType->getName(), $value, $decorator);
                    } catch (Exception) {
                        continue;
                    }
                }

                // if the union type cannot be resolved and there is no attribute type, then throw an exception
                throw new ErrorException(sprintf('The property %s has a union type, but none of the types can be resolved', $reflectionProperty->getName()));
            }

            return $function($value);
        }

        // if the reflection type cannot be determined
        if ($reflectionType === null) {
            // check if there is an associated attribute on the property
            $function = $this->getFunctionForAttribute($reflectionProperty, $decorator);

            if ($function === null) {
                throw new ErrorException('Unable to deserialize via typehint if the type is unavailable');
            }

            return $function($value);
        }

        $typeName = $reflectionType->getName();

        $value = $this->extractDataFromArray($value);

        try {
            // if the value is null, then return null immediately, no need to waste time reflecting
            if ($value === null) {
                // however, return [] if it's expecting an array
                return $typeName === self::TYPE_ARRAY ? [] : null;
            }

            // if the type is mixed, throw an exception as mixed is not a valid type
            if ($typeName === self::TYPE_MIXED) {
                throw new ErrorException('Unable to deserialize via typehint if the type is mixed');
            }

            // if the type is CarbonInterface or CarbonImmutable, prefer returning CarbonImmutable
            if ($typeName === CarbonInterface::class || $typeName === CarbonImmutable::class) {
                return CarbonImmutable::parse($value);
            }

            if ($typeName === Carbon::class) {
                return Carbon::parse($value);
            }

            if ($typeName === self::TYPE_STRING) {
                return (string) $value;
            }

            if ($typeName === self::TYPE_FLOAT) {
                return (float) $value;
            }

            if ($typeName === self::TYPE_INTEGER) {
                return (int) $value;
            }

            if ($typeName === self::TYPE_BOOLEAN) {
                return (bool) $value;
            }

            // if the type is an array
            if ($typeName === self::TYPE_ARRAY || $typeName === self::TYPE_OBJECT) {
                // check if there is an associated attribute on the property
                $function = $this->getFunctionForAttribute($reflectionProperty, $decorator);

                if ($function !== null) {
                    return match ($typeName) {
                        self::TYPE_ARRAY  => array_combine(array_keys($value ?? []), array_map($function, $value ?? [])),
                        self::TYPE_OBJECT => $function($value),
                    };
                }

                throw new ErrorException(sprintf('The property %s has type array but does not have an #[AsModel] or #[AsScalar] attribute', $reflectionProperty->getName()));
            }

            /* @phpstan-ignore-next-line */
            return $this->resolveNestedClass($typeName, $value, $decorator);
        } catch (ReflectionException) {
            return $value;
        }
    }

    /**
     * @phpstan-param class-string<TValue>                                             $fullyQualifiedClassPath
     *
     * @phpstan-param TValue                                                           $value
     *
     * @phpstan-param null|Closure(class-string<TValue>, TValue):class-string<TValue> $decorator
     *
     * @throws DeserializationException
     * @throws ReflectionException
     *
     * @return array<object>|object
     */
    private function resolveNestedClass(
        string $fullyQualifiedClassPath,
        mixed $value,
        ?Closure $decorator): object | array {
        if ($decorator !== null) {
            $fullyQualifiedClassPath = $decorator($fullyQualifiedClassPath, $this->extractDataFromArray($value));
        }

        $matchedClass = new ReflectionClass($fullyQualifiedClassPath);

        // if the class that is being reflected implements the CanBeMappedToJsonModel interface, then
        // attempt recursion
        if (in_array(CanBeMappedToJsonModel::class, $matchedClass->getInterfaceNames(), true) === true) {
            if ($this->isAssociative($value) === false) {
                return array_map(fn (array $item) => $this->map($item, fn () => $fullyQualifiedClassPath, $decorator), $value);
            }

            return $this->map($value, fn () => $fullyQualifiedClassPath, $decorator);
        }

        return $value;
    }

    private function getFunctionForAttribute(ReflectionProperty $property, ?Closure $decorator): ?Closure {
        // check if there is an AsScalar attribute on the property

        /** @var ReflectionAttribute|null $scalarAttribute */
        $scalarAttribute = $property->getAttributes(AsScalar::class)[0] ?? null;

        if ($scalarAttribute !== null && ($asScalar = $scalarAttribute->newInstance()) instanceof AsScalar) {
            return function (mixed $element) use ($asScalar) {
                settype($element, $asScalar->type);

                return $element;
            };
        }

        // check if there is an AsModel attribute on the property

        /** @var ReflectionAttribute|null $modelAttribute */
        $modelAttribute = $property->getAttributes(AsModel::class)[0] ?? null;

        if ($modelAttribute !== null && ($asModel = $modelAttribute->newInstance()) instanceof AsModel) {
            /**
             * @phpstan-var AsModel $asModel
             *
             * @param mixed $element
             *
             * @throws DeserializationException
             * @throws ReflectionException
             *
             * @return array|object|object[]
             */
            return fn (mixed $element) => $this->resolveNestedClass($asModel->class, $element, $decorator);
        }

        return null;
    }

    /**
     * if there is a data key in the value, then raise the value one level higher.
     */
    private function extractDataFromArray(mixed $array): mixed {
        return is_array($array) === true && array_key_exists('data', $array) === true ? $array['data'] ?? [] : $array;
    }
}
