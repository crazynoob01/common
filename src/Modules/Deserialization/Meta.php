<?php

declare(strict_types=1);

namespace Crazynoob01\Common\Modules\Deserialization;

final class Meta implements CanBeMappedToJsonModel {
    public function __construct(private Pagination $pagination) {
    }

    public static function createEmptyObject(): Meta {
        return new Meta(Pagination::createEmptyObject());
    }

    public function getPagination(): Pagination {
        return $this->pagination;
    }
}
