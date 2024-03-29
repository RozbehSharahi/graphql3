<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Domain\Model;

use RozbehSharahi\Graphql3\Exception\InternalErrorException;

class GraphqlArgumentCollection
{
    /**
     * @var array<string, GraphqlArgument>
     */
    protected array $items;

    /**
     * @param GraphqlArgument[] $items
     */
    public static function create(array $items = []): self
    {
        return new self($items);
    }

    /**
     * @param GraphqlArgument[] $items
     */
    public function __construct(array $items)
    {
        $this->items = $this->createArrayByName($items);
    }

    public function add(GraphqlArgument $item): self
    {
        return $this->withItems([...$this->items, $item]);
    }

    public function remove(string $name): self
    {
        $clone = clone $this;

        if (isset($clone->items[$name])) {
            unset($clone->items[$name]);
        }

        return $clone;
    }

    /**
     * @return array<string, GraphqlArgument>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param GraphqlArgument[] $items
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

    /**
     * @return array<string, array<string, mixed>>
     */
    public function toArray(): array
    {
        return array_map(static fn ($item) => $item->toArray(), $this->items);
    }

    /**
     * @param GraphqlArgument[] $items
     *
     * @return array<string, GraphqlArgument>
     */
    protected function createArrayByName(array $items): array
    {
        $this->assertAllGraphqlArguments($items);
        $byName = [];

        foreach ($items as $item) {
            $byName[$item->getName()] = $item;
        }

        return $byName;
    }

    /**
     * @param GraphqlArgument[] $items
     */
    protected function assertAllGraphqlArguments(array $items): self
    {
        foreach ($items as $item) {
            if (!$item instanceof GraphqlArgument) {
                throw new InternalErrorException(self::class.' only allows '.GraphqlArgument::class.' items.');
            }
        }

        return $this;
    }
}
