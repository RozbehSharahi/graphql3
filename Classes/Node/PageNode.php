<?php

namespace RozbehSharahi\Graphql3\Node;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgument;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Resolver\PageResolver;
use RozbehSharahi\Graphql3\Type\PageType;

class PageNode implements NodeInterface
{
    /**
     * @param iterable<PageNodeExtenderInterface> $extenders
     */
    public function __construct(
        protected PageType $pageType,
        protected PageResolver $pageResolver,
        protected iterable $extenders
    ) {
    }

    public function getGraphqlNode(): GraphqlNode
    {
        $arguments = GraphqlArgumentCollection::create([
            GraphqlArgument::create('uid')->withType(Type::nonNull(Type::int())),
        ]);

        foreach ($this->extenders as $extender) {
            $arguments = $extender->extendArguments($arguments);
        }

        return GraphqlNode::create('page')
            ->withType($this->pageType)
            ->withArguments($arguments)
            ->withResolver($this->pageResolver->getCallable());
    }
}
