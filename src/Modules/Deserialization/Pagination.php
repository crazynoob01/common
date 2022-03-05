<?php

declare(strict_types=1);

namespace Crazynoob01\Common\Modules\Deserialization;

use Crazynoob01\Common\Traits\OutputsObjectVarsAsJsonSerializable;
use JsonSerializable;

final class Pagination implements CanBeMappedToJsonModel, JsonSerializable {
    use OutputsObjectVarsAsJsonSerializable;

    private const PAGE_REGEX = '/^\/\?page=([\d]+)/';

    public function __construct(
        private int $total,
        private int $count,
        private int $perPage,
        private int $currentPage,
        private int $totalPages,
        #[AsScalar(type: JsonModelMapper::TYPE_STRING)]
        private array $links
    ) {
    }

    public static function createEmptyObject(): Pagination {
        return new Pagination(0, 0, 0, 1, 1, []);
    }

    public function getTotal(): int {
        return $this->total;
    }

    public function getCount(): int {
        return $this->count;
    }

    public function getPerPage(): int {
        return $this->perPage;
    }

    public function getCurrentPage(): int {
        return $this->currentPage;
    }

    public function getTotalPages(): int {
        return $this->totalPages;
    }

    /**
     * @return string[]
     */
    public function getLinks(): array {
        return $this->links;
    }

    public function getPreviousPage(): ?int {
        return isset($this->links['previous']) === true ? (int) preg_replace(
            self::PAGE_REGEX,
            '$1',
            $this->links['previous']
        ) : null;
    }

    public function getNextPage(): ?int {
        return isset($this->links['next']) === true ? (int) preg_replace(
            self::PAGE_REGEX,
            '$1',
            $this->links['next']
        ) : null;
    }
}
