<?php

namespace RozbehSharahi\Graphql3TestExtension;

use GraphQL\Type\Schema;
use RozbehSharahi\Graphql3\Builder\RegistryBasedPageQueryBuilder;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Registry\PageArgumentRegistry;
use RozbehSharahi\Graphql3\Registry\QueryFieldRegistry;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Registry\TypeRegistry;
use RozbehSharahi\Graphql3\Setup\SetupInterface;
use RozbehSharahi\Graphql3\Type\RegistryBasedPageType;
use RozbehSharahi\Graphql3\Type\RegistryBasedQueryType;

class GraphqlSetup implements SetupInterface
{
    public function __construct(
        protected SchemaRegistry $schemaRegistry,
        protected TypeRegistry $typeRegistry,
        protected QueryFieldRegistry $queryFieldRegistry,
        protected PageArgumentRegistry $pageArgumentRegistry,
        protected RegistryBasedPageQueryBuilder $pageQueryBuilder,
    ) {
    }

    public function setup(): void
    {
        $this->schemaRegistry->register(new Schema([
            'query' => $this->typeRegistry->get(RegistryBasedQueryType::class),
        ]));

        $this->queryFieldRegistry
            ->register(
                GraphqlNode::create('page')
                    ->withType($this->typeRegistry->get(RegistryBasedPageType::class))
                    ->withArguments($this->pageArgumentRegistry->getArguments())
                    ->withResolver(fn ($_, $args) => $this->pageQueryBuilder->withArguments($args)->getPage())
            );
    }
}
