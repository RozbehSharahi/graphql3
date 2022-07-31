<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Node\Nested;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Exception\GraphqlException;
use RozbehSharahi\Graphql3\Node\NodeInterface;
use RozbehSharahi\Graphql3\Resolver\RecordResolver;
use RozbehSharahi\Graphql3\Type\PageType;

class NestedPageNode implements NodeInterface
{
    use NestedNodeTrait;

    protected \Closure $idResolver;

    public function __construct(protected RecordResolver $recordResolver, protected PageType $pageType)
    {
    }

    public function getIdResolver(): \Closure
    {
        return $this->idResolver;
    }

    public function withIdResolver(\Closure $recordIdResolver): self
    {
        $clone = clone $this;
        $clone->idResolver = $recordIdResolver;

        return $clone;
    }

    public function getGraphqlNode(): GraphqlNode
    {
        if (empty($this->idResolver)) {
            throw new GraphqlException('Did you forget to define a id resolver?');
        }

        return GraphqlNode::create($this->name)->withType($this->pageType)->withResolver(
            fn ($input) => $this->recordResolver->resolve('pages', ($this->idResolver)($input))
        );
    }

    public function getChildrenGraphqlNode(): GraphqlNode
    {
        if (empty($this->idResolver)) {
            throw new GraphqlException('Did you forget to define a id resolver?');
        }

        return GraphqlNode::create($this->name)->withType(Type::listOf($this->pageType))->withResolver(
            fn ($input) => $this->recordResolver->resolveManyByPid('pages', ($this->idResolver)($input))
        );
    }
}
