<?php

declare(strict_types=1);

namespace Crazynoob01\Common\Tests\Modules\Deserialization;

use ArgumentCountError;
use ErrorException;
use Crazynoob01\Common\Modules\Deserialization\JsonModelMapper;
use Crazynoob01\Common\Modules\Deserialization\PaginatedModel;
use Crazynoob01\Common\Modules\Deserialization\Pagination;
use Crazynoob01\Common\Tests\AbstractTestCase;
use Crazynoob01\Common\Tests\Fixtures\Modules\Deserialization\ClassToOverride;
use Crazynoob01\Common\Tests\Fixtures\Modules\Deserialization\ClassWithArrayModelAttribute;
use Crazynoob01\Common\Tests\Fixtures\Modules\Deserialization\ClassWithArrayScalarAttribute;
use Crazynoob01\Common\Tests\Fixtures\Modules\Deserialization\ClassWithNestedClassTypehint;
use Crazynoob01\Common\Tests\Fixtures\Modules\Deserialization\ClassWithoutArrayAttribute;
use Crazynoob01\Common\Tests\Fixtures\Modules\Deserialization\ClassWithoutTypeHintsWithAttributes;
use Crazynoob01\Common\Tests\Fixtures\Modules\Deserialization\ClassWithoutTypes;
use Crazynoob01\Common\Tests\Fixtures\Modules\Deserialization\OverrideClass;
use Crazynoob01\Common\Tests\Fixtures\Modules\Deserialization\PolymorphicClassWithPrivatePropertyA;
use Crazynoob01\Common\Tests\Fixtures\Modules\Deserialization\PolymorphicClassWithPrivatePropertyB;
use Crazynoob01\Common\Tests\Fixtures\Modules\Deserialization\PolymorphicClassWithProtectedPropertyA;
use Crazynoob01\Common\Tests\Fixtures\Modules\Deserialization\PolymorphicClassWithProtectedPropertyB;
use Crazynoob01\Common\Tests\Fixtures\Modules\Deserialization\StandardDeserializationClass;
use Crazynoob01\Common\Tests\Fixtures\Modules\Deserialization\StandardDeserializationClassWithOverride;
use Crazynoob01\Common\Tests\Fixtures\Modules\Deserialization\StandardDeserializationClassWithPolymorphicClassWithPrivateProperty;
use Crazynoob01\Common\Tests\Fixtures\Modules\Deserialization\StandardDeserializationClassWithPolymorphicClassWithProtectedProperty;
use Crazynoob01\Common\Tests\Fixtures\Modules\Deserialization\StandardDeserializationClassWithPublicProperties;
use JsonException;

/**
 * @covers    \Crazynoob01\Common\Modules\Deserialization\JsonModelMapper
 */
class JsonModelMapperTest extends AbstractTestCase {
    /**
     * @throws ErrorException
     * @throws JsonException
     */
    public function testItDeserializesTheClass(): void {
        $class = new StandardDeserializationClass('test', 1.0, ['test', 'array']);

        $object = JsonModelMapper::mapFromJson(json_encode(['data' => $class], JSON_THROW_ON_ERROR), fn () => StandardDeserializationClass::class);
        static::assertInstanceOf(StandardDeserializationClass::class, $object);
        static::assertSame('test', $object->getString());
        static::assertSame(1.0, $object->getFloat());
        static::assertSame(['test', 'array'], $object->getArray());
    }

    /**
     * @throws ErrorException
     * @throws JsonException
     */
    public function testItDeserializesTheClassViaMethod(): void {
        $class = new StandardDeserializationClass('test', 1.0, ['test', 'array']);

        $object = (new JsonModelMapper())->deserialise(json_encode(['data' => $class], JSON_THROW_ON_ERROR), fn () => StandardDeserializationClass::class);
        static::assertInstanceOf(StandardDeserializationClass::class, $object);
        static::assertSame('test', $object->getString());
        static::assertSame(1.0, $object->getFloat());
        static::assertSame(['test', 'array'], $object->getArray());
    }

    /**
     * @throws ErrorException
     * @throws JsonException
     */
    public function testItSupportsPublicPropertiesWhenSet(): void {
        $class = new StandardDeserializationClassWithPublicProperties('test', 1.0, ['test', 'array']);

        $object = (new JsonModelMapper(true))->deserialise(json_encode(['data' => $class], JSON_THROW_ON_ERROR),
            fn () => StandardDeserializationClassWithPublicProperties::class);
        static::assertInstanceOf(StandardDeserializationClassWithPublicProperties::class, $object);
        static::assertSame('test', $object->string);
        static::assertSame(1.0, $object->float);
        static::assertSame(['test', 'array'], $object->array);
    }

    /**
     * @throws ErrorException
     * @throws JsonException
     */
    public function testItDoesNotSupportPublicPropertiesWhenNotSet(): void {
        $this->expectException(ArgumentCountError::class);
        $class = new StandardDeserializationClassWithPublicProperties('test', 1.0, ['test', 'array']);

        (new JsonModelMapper(false))->deserialise(json_encode(['data' => $class], JSON_THROW_ON_ERROR),
            fn () => StandardDeserializationClassWithPublicProperties::class);
    }

    /**
     * @throws ErrorException
     * @throws JsonException
     */
    public function testItDeserializesTheClassWithoutDataParameter(): void {
        $class = new StandardDeserializationClass('test', 1.0, ['test', 'array']);

        $object = JsonModelMapper::mapFromJson(json_encode($class, JSON_THROW_ON_ERROR),
            fn () => StandardDeserializationClass::class
        );
        static::assertInstanceOf(StandardDeserializationClass::class, $object);
        static::assertSame('test', $object->getString());
        static::assertSame(1.0, $object->getFloat());
        static::assertSame(['test', 'array'], $object->getArray());
    }

    /**
     * @throws ErrorException
     * @throws JsonException
     */
    public function testItDeserializesTheClassWithPagination(): void {
        $class      = new StandardDeserializationClass('test', 1.0, ['test', 'array']);
        $pagination = new Pagination(1, 1, 15, 1, 1, []);

        $object = JsonModelMapper::mapFromJson(json_encode([
            'data' => [$class],
            'meta' => ['pagination' => $pagination],
        ], JSON_THROW_ON_ERROR), fn () => StandardDeserializationClass::class);
        static::assertInstanceOf(PaginatedModel::class, $object);
        static::assertContainsOnlyInstancesOf(StandardDeserializationClass::class, $object);
        static::assertCount(1, $object);

        foreach ($object as $model) {
            static::assertInstanceOf(StandardDeserializationClass::class, $model);
            static::assertSame('test', $model->getString());
            static::assertSame(1.0, $model->getFloat());
            static::assertSame(['test', 'array'], $model->getArray());
        }
    }

    /**
     * @throws ErrorException
     * @throws JsonException
     */
    public function testItDeserializesTheClassWithArrayScalarAttribute(): void {
        /** @var ClassWithArrayScalarAttribute $object */
        $object = JsonModelMapper::mapFromJson(
            json_encode(
                [
                    'data' => ['integers' => [1, 2]],
                ],
                JSON_THROW_ON_ERROR
            ),
            fn () => ClassWithArrayScalarAttribute::class
        );
        static::assertInstanceOf(ClassWithArrayScalarAttribute::class, $object);
        static::assertContainsOnly('integer', $object->getIntegers());
        static::assertCount(2, $object->getIntegers());
        static::assertSame([1, 2], $object->getIntegers());
    }

    /**
     * @throws ErrorException
     * @throws JsonException
     */
    public function testItCastsTheAttributeWhenDeserializingTheClassWithArrayScalarAttribute(): void {
        /** @var ClassWithArrayScalarAttribute $object */
        $object = JsonModelMapper::mapFromJson(
            json_encode(
                [
                    'data' => ['integers' => ['1', '2']],
                ],
                JSON_THROW_ON_ERROR
            ),
            fn () => ClassWithArrayScalarAttribute::class
        );
        static::assertInstanceOf(ClassWithArrayScalarAttribute::class, $object);
        static::assertContainsOnly('integer', $object->getIntegers());
        static::assertCount(2, $object->getIntegers());
        static::assertSame([1, 2], $object->getIntegers());
    }

    /**
     * @throws ErrorException
     * @throws JsonException
     */
    public function testItDeserializesTheClassWithArrayModelAttribute(): void {
        $class = new StandardDeserializationClass('test', 1.0, ['test', 'array']);

        /** @var ClassWithArrayModelAttribute $object */
        $object = JsonModelMapper::mapFromJson(
            json_encode(
                [
                    'data' => ['classes' => [$class, $class]],
                ],
                JSON_THROW_ON_ERROR
            ),
            fn () => ClassWithArrayModelAttribute::class
        );
        static::assertInstanceOf(ClassWithArrayModelAttribute::class, $object);
        static::assertContainsOnlyInstancesOf(StandardDeserializationClass::class, $object->getClasses());
        static::assertCount(2, $object->getClasses());

        foreach ($object->getClasses() as $model) {
            static::assertSame('test', $model->getString());
            static::assertSame(1.0, $model->getFloat());
            static::assertSame(['test', 'array'], $model->getArray());
        }
    }

    /**
     * @throws ErrorException
     * @throws JsonException
     */
    public function testItDeserializesTheNestedClass(): void {
        $class = new StandardDeserializationClass('test', 1.0, ['test', 'array']);

        /** @var ClassWithNestedClassTypehint $object */
        $object = JsonModelMapper::mapFromJson(
            json_encode(
                [
                    'data' => ['class' => $class],
                ],
                JSON_THROW_ON_ERROR
            ),
            fn () => ClassWithNestedClassTypehint::class
        );
        static::assertInstanceOf(ClassWithNestedClassTypehint::class, $object);
        static::assertSame(1.0, $object->getClass()->getFloat());
        static::assertSame('test', $object->getClass()->getString());
        static::assertSame(['test', 'array'], $object->getClass()->getArray());
    }

    /**
     * @throws ErrorException
     * @throws JsonException
     */
    public function testItDoesNotDeserializeTheClassWithoutAttributeOnArray(): void {
        $this->expectException(ErrorException::class);

        $class = new StandardDeserializationClass('test', 1.0, ['test', 'array']);

        JsonModelMapper::mapFromJson(
            json_encode(
                [
                    'data' => ['classes' => [$class, $class]],
                ],
                JSON_THROW_ON_ERROR
            ),
            fn () => ClassWithoutArrayAttribute::class
        );
    }

    /**
     * @throws ErrorException
     * @throws JsonException
     */
    public function testItDoesNotDeserializeTheClassWithoutTypesButWithAttributes(): void {
        $this->expectException(ErrorException::class);

        $class = new ClassWithoutTypeHintsWithAttributes('test', 1.0, ['test', 'array']);

        JsonModelMapper::mapFromJson(
            json_encode(['data' => $class], JSON_THROW_ON_ERROR),
            fn () => ClassWithoutTypeHintsWithAttributes::class
        );
    }

    /**
     * @throws ErrorException
     * @throws JsonException
     */
    public function testItDoesNotDeserializeTheClassWithoutTypes(): void {
        $this->expectException(ErrorException::class);

        $class = new ClassWithoutTypes('test', 1.0, ['test', 'array']);

        JsonModelMapper::mapFromJson(
            json_encode(['data' => $class], JSON_THROW_ON_ERROR),
            fn () => ClassWithoutTypes::class
        );
    }

    /**
     * Note that the JSON for this test contains a nested ['data'] field.
     *
     * @throws ErrorException
     * @throws JsonException
     */
    public function testItDecoratesTheFullyQualifiedClassPath(): void {
        $object = JsonModelMapper::mapFromJson(
            json_encode(
                ['data' => ['class_to_override' => ['data' => ['foo' => 'override']]]],
                JSON_THROW_ON_ERROR
            ),
            fn () => StandardDeserializationClassWithOverride::class,
            fn (
                string $fullyQualifiedClassName,
                $value
            ) => ($value['foo'] ?? null) === null ? ClassToOverride::class : OverrideClass::class
        );
        static::assertInstanceOf(StandardDeserializationClassWithOverride::class, $object);
        static::assertInstanceOf(OverrideClass::class, $object->getClassToOverride());
    }

    /**
     * Note that the JSON for this test does not have a nested ['data'] field.
     *
     * @throws ErrorException
     * @throws JsonException
     */
    public function testItDeserializesPolymorphicClassesWithProtectedProperties(): void {
        /** @var StandardDeserializationClassWithPolymorphicClassWithProtectedProperty $object */
        $object = JsonModelMapper::mapFromJson(
            json_encode(
                ['data' => ['object' => ['type' => 'A'], 'array' => [['type' => 'A'], ['type' => 'B']]]],
                JSON_THROW_ON_ERROR
            ),
            fn () => StandardDeserializationClassWithPolymorphicClassWithProtectedProperty::class,
            fn (
                string $fullyQualifiedClassName,
                $value
            ) => ($value['type'] ?? null) === 'A' ? PolymorphicClassWithProtectedPropertyA::class : PolymorphicClassWithProtectedPropertyB::class
        );
        static::assertInstanceOf(StandardDeserializationClassWithPolymorphicClassWithProtectedProperty::class, $object);
        /** @noinspection UnnecessaryAssertionInspection */
        static::assertInstanceOf(PolymorphicClassWithProtectedPropertyA::class, $object->getObject());
        static::assertInstanceOf(PolymorphicClassWithProtectedPropertyA::class, $object->getArray()[0]);
        static::assertInstanceOf(PolymorphicClassWithProtectedPropertyB::class, $object->getArray()[1]);
    }

    /**
     * @throws ErrorException
     * @throws JsonException
     */
    public function testItDeserializesPolymorphicClassesWithPrivateProperties(): void {
        /** @var StandardDeserializationClassWithPolymorphicClassWithPrivateProperty $object */
        $object = JsonModelMapper::mapFromJson(
            json_encode(
                [
                    'data' => [
                        'object' => ['data' => ['type' => 'A']],
                        'array'  => [
                            'data' => [
                                ['type' => 'A'],
                                ['type' => 'B'],
                            ],
                        ],
                    ],
                ],
                JSON_THROW_ON_ERROR
            ),
            fn () => StandardDeserializationClassWithPolymorphicClassWithPrivateProperty::class,
            fn (
                string $fullyQualifiedClassName,
                $value
            ) => ($value['type'] ?? null) === 'A' ? PolymorphicClassWithPrivatePropertyA::class : PolymorphicClassWithPrivatePropertyB::class
        );
        static::assertInstanceOf(StandardDeserializationClassWithPolymorphicClassWithPrivateProperty::class, $object);
        /** @noinspection UnnecessaryAssertionInspection */
        static::assertInstanceOf(PolymorphicClassWithPrivatePropertyA::class, $object->getObject());
        static::assertInstanceOf(PolymorphicClassWithPrivatePropertyA::class, $object->getArray()[0]);
        static::assertInstanceOf(PolymorphicClassWithPrivatePropertyB::class, $object->getArray()[1]);
    }

    /**
     * @throws ErrorException
     * @throws JsonException
     */
    public function testItDeserializesTheClassWithArrayWithStringOrIntKeys(): void {
        $class = new StandardDeserializationClass('test', 1.0, ['key_one' => 'test', 'key_two' => 'array']);

        $object = JsonModelMapper::mapFromJson(
            json_encode(['data' => $class], JSON_THROW_ON_ERROR), fn () => StandardDeserializationClass::class
        );
        static::assertInstanceOf(StandardDeserializationClass::class, $object);
        static::assertSame('test', $object->getString());
        static::assertSame(1.0, $object->getFloat());
        static::assertSame(['key_one' => 'test', 'key_two' => 'array'], $object->getArray());
    }
}
