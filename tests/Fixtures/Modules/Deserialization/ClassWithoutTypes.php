<?php

declare(strict_types=1);

namespace Crazynoob01\Common\Tests\Fixtures\Modules\Deserialization;

use Crazynoob01\Common\Modules\Deserialization\CanBeMappedToJsonModel;
use Crazynoob01\Common\Traits\OutputsObjectVarsAsJsonSerializable;
use JsonSerializable;

class ClassWithoutTypes implements CanBeMappedToJsonModel, JsonSerializable {
    use OutputsObjectVarsAsJsonSerializable;

    public function __construct(private $string, private $float, private $array) {
    }

    public function getString() {
        return $this->string;
    }

    public function getFloat() {
        return $this->float;
    }

    public function getArray() {
        return $this->array;
    }
}
