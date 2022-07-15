<?php

namespace RozbehSharahi\Graphql3\Domain\Model;

use RozbehSharahi\Graphql3\Exception\GraphqlException;

class GraphqlNodeCollection
{
    public static function create(array $items = []): self
    {
        return new self($items);
    }

    /**
     * @param GraphqlNode[] $items
     */
    public function __construct(protected array $items)
    {
        foreach ($this->items as $error) {
            if (!$error instanceof GraphqlNode) {
                throw new GraphqlException(self::class.' only allows '.GraphqlNode::class.' items.');
            }
        }
    }

    public function getItems(): array
    {
        return $this->items;
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