<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Domain\Model;

use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class GraphqlNodeCollection
{
    /**
     * @var array<string, GraphqlNode>
     */
    protected array $items = [];

    /**
     * @param array<int, GraphqlNode> $items
     */
    public static function create(array $items = []): self
    {
        return GeneralUtility::makeInstance(
            self::class,
            $items
        );
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

        return $this->withItems(array_values($items));
    }

    /**
     * @return array<string, GraphqlNode>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param array<int, GraphqlNode> $items
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
     * @param array<int, GraphqlNode> $items
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

    /**
     * @param GraphqlNode[] $items
     */
    protected function assertAllGraphqlNodes(array $items): self
    {
        foreach ($items as $item) {
            if (!$item instanceof GraphqlNode) { // @phpstan-ignore-line
                throw new InternalErrorException(self::class.' only allows '.GraphqlNode::class.' items.');
            }
        }

        return $this;
    }
}
