<?php

namespace RozbehSharahi\Graphql3\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Registry\PageFieldRegistry;
use RozbehSharahi\Graphql3\Registry\TypeRegistry;
use RozbehSharahi\Graphql3\Setup\SetupInterface;

class RegistryBasedPageType extends ObjectType implements SetupInterface
{
    public function __construct(protected TypeRegistry $typeRegistry, protected PageFieldRegistry $pageFieldRegistry)
    {
        parent::__construct([
            'name' => 'Page',
            'fields' => fn () => $this->pageFieldRegistry->getFields()->toArray(),
        ]);
    }

    public function setup(): void
    {
        $this->typeRegistry->register($this);

        $this->pageFieldRegistry
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
