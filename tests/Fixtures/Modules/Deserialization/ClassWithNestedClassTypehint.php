<?php

declare(strict_types=1);

namespace Crazynoob01\Common\Tests\Fixtures\Modules\Deserialization;

use Crazynoob01\Common\Modules\Deserialization\CanBeMappedToJsonModel;
use Crazynoob01\Common\Traits\OutputsObjectVarsAsJsonSerializable;
use JsonSerializable;

class ClassWithNestedClassTypehint implements CanBeMappedToJsonModel, JsonSerializable {
    use OutputsObjectVarsAsJsonSerializable;

    public function __construct(private StandardDeserializationClass $class) {
    }

    public function getClass(): StandardDeserializationClass {
        return $this->class;
    }
}
