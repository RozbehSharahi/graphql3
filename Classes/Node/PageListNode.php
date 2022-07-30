<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Node;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgument;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\ListRequest;
use RozbehSharahi\Graphql3\Type\FilterInputType;
use RozbehSharahi\Graphql3\Type\OrderItemInputType;
use RozbehSharahi\Graphql3\Type\PageListType;

class PageListNode implements NodeInterface
{
    public function __construct(
        protected PageListType $pageListType,
        protected OrderItemInputType $orderFieldType,
        protected FilterInputType $filterInputType
    ) {
    }

    public function getGraphqlNode(): GraphqlNode
    {
        return GraphqlNode::create('pages')
            ->withArguments(GraphqlArgumentCollection::create([
                GraphqlArgument::create('page')->withType(Type::nonNull(Type::int()))->withDefaultValue(1),
                GraphqlArgument::create('pageSize')->withType(Type::nonNull(Type::int()))->withDefaultValue(10),
                GraphqlArgument::create('orderBy')->withType(Type::listOf($this->orderFieldType)),
                GraphqlArgument::create('filters')->withType(Type::listOf($this->filterInputType)),
                GraphqlArgument::create('publicRequest')->withType(Type::boolean())->withDefaultValue(true),
            ]))
            ->withType($this->pageListType)
            ->withResolver(fn ($_, $args) => new ListRequest($args))
        ;
    }
}
