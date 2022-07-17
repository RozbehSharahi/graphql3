<?php

namespace RozbehSharahi\Graphql3\Setup;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Builder\RegistryBasedPageQueryBuilder;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Registry\PageFieldRegistry;
use RozbehSharahi\Graphql3\Registry\TypeRegistry;
use RozbehSharahi\Graphql3\Type\RegistryBasedPageType;

class PageFieldsSetup implements SetupInterface
{
    public function __construct(
        protected PageFieldRegistry $pageFieldRegistry,
        protected TypeRegistry $typeRegistry,
        protected RegistryBasedPageQueryBuilder $pageQueryBuilder
    ) {
    }

    public function setup(): void
    {
        $this->pageFieldRegistry
            ->register(
                GraphqlNode::create('uid')
                    ->withType(Type::int())
                    ->withResolver(fn (array $page) => $page['uid'])
            )
            ->register(
                GraphqlNode::create('title')
                    ->withResolver(fn (array $page) => $page['title'])
            )
            ->register(
                GraphqlNode::create('parent')
                    ->withType($this->typeRegistry->get(RegistryBasedPageType::class))
                    ->withResolver(
                        fn (array $page) => $page['pid']
                            ? $this->pageQueryBuilder->withArguments(['uid' => $page['pid']])->getPage()
                            : null
                    )
            );
    }
}
