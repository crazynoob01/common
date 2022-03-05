<?php

declare(strict_types=1);

namespace Crazynoob01\Common\Modules\Deserialization;

use Attribute;

/**
 * @template  T
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class AsModel {
    /**
     * @phpstan-param class-string<T> $class
     */
    public function __construct(public string $class) {
    }
}
