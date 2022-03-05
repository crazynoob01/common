<?php

declare(strict_types=1);

namespace Crazynoob01\Common\Modules\Deserialization;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class AsScalar {
    public function __construct(public string $type) {
    }
}
