<?php

namespace RozbehSharahi\Graphql3\Domain\Model;

use RozbehSharahi\Graphql3\Exception\GraphqlException;

class GraphqlArgumentCollection
{
    public static function create(array $items = []): self
    {
        return new self($items);
    }

    /**
     * @param GraphqlArgument[] $items
     */
    public function __construct(protected array $items)
    {
        foreach ($this->items as $error) {
            if (!$error instanceof GraphqlArgument) {
                throw new GraphqlException(self::class.' only allows '.GraphqlArgument::class.' items.');
            }
        }
    }

    public function add(GraphqlArgument $item): self
    {
        return $this->withItems([...$this->items, $item]);
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function withItems(array $items): self
    {
        $clone = clone $this;
        $clone->items = $items;

        return $clone;
    }

    public function toArray(): array
    {
        $array = [];
        foreach ($this->items as $item) {
            $array[$item->getName()] = $item->toArray();
        }

        return $array;
    }
}
