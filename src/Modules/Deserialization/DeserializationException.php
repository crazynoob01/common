<?php

declare(strict_types=1);

namespace Crazynoob01\Common\Modules\Deserialization;

use ErrorException;

class DeserializationException extends ErrorException {
    public function __construct(string $property, string $class) {
        parent::__construct(sprintf(
            'Deserialization failed for class %s as it is missing typehints or attributes for property %s',
            $class,
            $property
        ));
    }
}
