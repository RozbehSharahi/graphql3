<?php

namespace RozbehSharahi\Graphql3\Domain\Model;

use RozbehSharahi\Graphql3\Exception\GraphqlException;

class GraphqlNodeCollection
{
    /**
     * @var array<string, GraphqlNode>
     */
    protected array $items = [];

    public static function create(array $items = []): self
    {
        return new self($items);
    }

    /**
     * @param GraphqlNode[] $items
     */
    public function __construct(array $items)
    {
        $this->items = $this->createArrayByName($items);
    }

    public function add(GraphqlNode $item): self
    {
        return $this->withItems([...$this->items, $item]);
    }

    public function remove(string $nodeName): self
    {
        $items = $this->items;

        if ($items[$nodeName] ?? null) {
            unset($items[$nodeName]);
        }

        return $this->withItems($items);
    }

    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param GraphqlNode[] $items
     *
     * @return $this
     */
    public function withItems(array $items): self
    {
        $clone = clone $this;
        $clone->items = $this->createArrayByName($items);

        return $clone;
    }

    public function getLength(): int
    {
        return count($this->items);
    }

    public function toArray(): array
    {
        return array_map(static fn ($item) => $item->toArray(), $this->items);
    }

    /**
     * @param GraphqlNode[] $items
     *
     * @return array<string, GraphqlNode>
     */
    protected function createArrayByName(array $items): array
    {
        $this->assertAllGraphqlNodes($items);
        $byName = [];

        foreach ($items as $item) {
            $byName[$item->getName()] = $item;
        }

        return $byName;
    }

    protected function assertAllGraphqlNodes(array $items): self
    {
        foreach ($items as $item) {
            if (!$item instanceof GraphqlNode) {
                throw new GraphqlException(self::class.' only allows '.GraphqlNode::class.' items.');
            }
        }

        return $this;
    }
}
