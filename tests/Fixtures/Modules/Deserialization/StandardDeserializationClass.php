<?php

declare(strict_types=1);

namespace Crazynoob01\Common\Tests\Fixtures\Modules\Deserialization;

use Crazynoob01\Common\Modules\Deserialization\AsScalar;
use Crazynoob01\Common\Modules\Deserialization\CanBeMappedToJsonModel;
use Crazynoob01\Common\Modules\Deserialization\JsonModelMapper;
use Crazynoob01\Common\Traits\OutputsObjectVarsAsJsonSerializable;
use JsonSerializable;

class StandardDeserializationClass implements CanBeMappedToJsonModel, JsonSerializable {
    use OutputsObjectVarsAsJsonSerializable;

    public function __construct(
        private string $string,
        private float $float,
        #[AsScalar(type: JsonModelMapper::TYPE_STRING)]
        private array $array
    ) {
    }

    public function getString(): string {
        return $this->string;
    }

    public function getFloat(): float {
        return $this->float;
    }

    public function getArray(): array {
        return $this->array;
    }
}
