<?php

declare(strict_types=1);

namespace Crazynoob01\Common\Modules\Deserialization;

use Countable;
use Iterator;
use JsonSerializable;

/**
 * @template   TModel of \Crazynoob01\Common\Modules\Deserialization\CanBeMappedToJsonModel
 *
 * @implements Iterator<TModel>
 */
class PaginatedModel implements Iterator, Countable, JsonSerializable {
    private int $position = 0;

    private array $models;

    public function __construct(private Meta $meta, CanBeMappedToJsonModel ...$models) {
        $this->models = $models;
    }

    public static function createEmptyObject(): PaginatedModel {
        return new PaginatedModel(Meta::createEmptyObject());
    }

    /**
     * @phpstan-return array<int, TModel>
     *
     * @return CanBeMappedToJsonModel[]
     */
    public function getModels(): array {
        return $this->models;
    }

    public function getMeta(): Meta {
        return $this->meta;
    }

    /**
     * @phpstan-return TModel
     */
    public function current(): CanBeMappedToJsonModel {
        return $this->models[$this->position];
    }

    public function next(): void {
        $this->position++;
    }

    public function key(): int {
        return $this->position;
    }

    public function valid(): bool {
        return isset($this->models[$this->position]);
    }

    public function rewind(): void {
        $this->position = 0;
    }

    public function count(): int {
        return count($this->models);
    }

    public function jsonSerialize(): array {
        return [
            'items' => $this->getModels(),
            'meta'  => [
                'pagination' => [
                    'total'        => $this->getMeta()->getPagination()->getTotal(),
                    'count'        => $this->getMeta()->getPagination()->getCount(),
                    'per_page'     => $this->getMeta()->getPagination()->getPerPage(),
                    'current_page' => $this->getMeta()->getPagination()->getCurrentPage(),
                    'total_pages'  => $this->getMeta()->getPagination()->getTotalPages(),
                    'links'        => $this->getMeta()->getPagination()->getLinks(),
                    'previous'     => $this->getMeta()->getPagination()->getPreviousPage(),
                    'next'         => $this->getMeta()->getPagination()->getNextPage(),
                ],
            ],
        ];
    }
}
