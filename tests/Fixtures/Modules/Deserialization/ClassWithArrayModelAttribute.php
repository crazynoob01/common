<?php

declare(strict_types=1);

namespace Crazynoob01\Common\Tests\Fixtures\Modules\Deserialization;

use Crazynoob01\Common\Modules\Deserialization\AsModel;
use Crazynoob01\Common\Modules\Deserialization\CanBeMappedToJsonModel;
use Crazynoob01\Common\Traits\OutputsObjectVarsAsJsonSerializable;
use JsonSerializable;

class ClassWithArrayModelAttribute implements CanBeMappedToJsonModel, JsonSerializable {
    use OutputsObjectVarsAsJsonSerializable;

    public function __construct(
        #[AsModel(class: StandardDeserializationClass::class)]
        private array $classes
    ) {
    }

    /**
     * @return StandardDeserializationClass[]
     */
    public function getClasses(): array {
        return $this->classes;
    }
}
