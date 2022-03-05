<?php

declare(strict_types=1);

namespace Crazynoob01\Common\Tests\Fixtures\Modules\Deserialization;

use Crazynoob01\Common\Modules\Deserialization\AsModel;

class StandardDeserializationClassWithPolymorphicClassWithProtectedProperty {
    public function __construct(
        #[AsModel(class: PolymorphicClassWithProtectedPropertyA::class)] private $object,
        #[AsModel(class: PolymorphicClassWithProtectedPropertyB::class)]
        private array $array
    ) {
    }

    public function getObject(): PolymorphicClassWithProtectedPropertyA {
        return $this->object;
    }

    public function getArray(): array {
        return $this->array;
    }
}
