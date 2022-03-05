<?php

declare(strict_types=1);

namespace Crazynoob01\Common\Tests\Fixtures\Modules\Deserialization;

use Crazynoob01\Common\Modules\Deserialization\AsModel;

class StandardDeserializationClassWithPolymorphicClassWithPrivateProperty {
    public function __construct(
        #[AsModel(class: PolymorphicClassWithPrivatePropertyA::class)] private $object,
        #[AsModel(class: PolymorphicClassWithPrivatePropertyB::class)]
        private array $array
    ) {
    }

    public function getObject(): PolymorphicClassWithPrivatePropertyA {
        return $this->object;
    }

    public function getArray(): array {
        return $this->array;
    }
}
