<?php

declare(strict_types=1);

namespace Crazynoob01\Common\Tests\Fixtures\Modules\Deserialization;

use Crazynoob01\Common\Modules\Deserialization\CanBeMappedToJsonModel;

class PolymorphicClassWithProtectedProperty implements CanBeMappedToJsonModel {
    public function __construct(protected string $type) {
    }

    public function getType(): string {
        return $this->type;
    }
}
