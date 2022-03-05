<?php

declare(strict_types=1);

namespace Crazynoob01\Common\Tests\Fixtures\Modules\Deserialization;

use Crazynoob01\Common\Modules\Deserialization\AsScalar;
use Crazynoob01\Common\Modules\Deserialization\CanBeMappedToJsonModel;
use Crazynoob01\Common\Modules\Deserialization\JsonModelMapper;
use Crazynoob01\Common\Traits\OutputsObjectVarsAsJsonSerializable;
use JsonSerializable;

class ClassWithArrayScalarAttribute implements CanBeMappedToJsonModel, JsonSerializable {
    use OutputsObjectVarsAsJsonSerializable;

    public function __construct(
        #[AsScalar(type: JsonModelMapper::TYPE_INTEGER)]
        private array $integers
    ) {
    }

    /**
     * @return int[]
     */
    public function getIntegers(): array {
        return $this->integers;
    }
}
