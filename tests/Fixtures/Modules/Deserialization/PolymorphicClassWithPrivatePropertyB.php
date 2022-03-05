<?php

declare(strict_types=1);

namespace Crazynoob01\Common\Tests\Fixtures\Modules\Deserialization;

class PolymorphicClassWithPrivatePropertyB extends PolymorphicClassWithPrivateProperty {
    public function __construct(private string $type) {
    }
}
