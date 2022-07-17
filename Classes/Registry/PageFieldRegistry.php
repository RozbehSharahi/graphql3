<?php

namespace RozbehSharahi\Graphql3\Registry;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use RozbehSharahi\Graphql3\Setup\SetupInterface;

class PageFieldRegistry implements SetupInterface
{
    protected array $fields = [];

    public function register(GraphqlNode $graphqlNode): self
    {
        $this->fields[$graphqlNode->getName()] = $graphqlNode;

        return $this;
    }

    public function getFields(): GraphqlNodeCollection
    {
        return new GraphqlNodeCollection($this->fields);
    }

    public function setup(): void
    {
        $this
            ->register(
                GraphqlNode::create('uid')
                    ->withType(Type::int())
                    ->withResolver(fn (array $page) => $page['uid'])
            )
            ->register(
                GraphqlNode::create('title')
                    ->withResolver(fn (array $page) => $page['title'])
            );
    }
}
