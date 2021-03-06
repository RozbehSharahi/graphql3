<?php

namespace RozbehSharahi\Graphql3\Node;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgument;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\ListRequest;
use RozbehSharahi\Graphql3\Type\PageListType;

class PageListNode implements NodeInterface
{
    public function __construct(protected PageListType $pageListType)
    {
    }

    public function getGraphqlNode(): GraphqlNode
    {
        return GraphqlNode::create('pages')
            ->withArguments([
                GraphqlArgument::create('page')->withType(Type::nonNull(Type::int()))->withDefaultValue(1),
                GraphqlArgument::create('pageSize')->withType(Type::nonNull(Type::int()))->withDefaultValue(10),
            ])
            ->withType($this->pageListType)
            ->withResolver(fn ($_, $args) => new ListRequest($args));
    }
}
