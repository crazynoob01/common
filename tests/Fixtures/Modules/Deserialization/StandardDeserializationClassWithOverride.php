<?php

declare(strict_types=1);

namespace Crazynoob01\Common\Tests\Fixtures\Modules\Deserialization;

use Crazynoob01\Common\Modules\Deserialization\AsModel;
use Crazynoob01\Common\Modules\Deserialization\CanBeMappedToJsonModel;
use Crazynoob01\Common\Traits\OutputsObjectVarsAsJsonSerializable;

class StandardDeserializationClassWithOverride implements CanBeMappedToJsonModel {
    use OutputsObjectVarsAsJsonSerializable;

    public function __construct(
        #[AsModel(class: OverrideClass::class)] private $classToOverride
    ) {
    }

    public function getClassToOverride(): object {
        return $this->classToOverride;
    }
}
