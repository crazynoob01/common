<?php

declare(strict_types=1);

namespace Crazynoob01\Common\Traits;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait OutputsObjectVarsAsJsonSerializable {
    public function jsonSerialize(): array {
        $collection = new Collection(get_object_vars($this));

        // convert values of specific types to specific format
        $collection = $collection->map(function ($value) {
            if ($value instanceof CarbonInterface) {
                return $value->toDateTimeString();
            }

            return $value;
        });

        // convert the property names to snake case
        $keys = $collection->keys()->map(fn (string $element) => Str::snake($element));

        return $keys->combine($collection->values())->toArray();
    }
}
