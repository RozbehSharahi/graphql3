<?php

namespace RozbehSharahi\Graphql3TestExtension;

use GraphQL\Type\Schema;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
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
        protected QueryFieldRegistry $queryFieldRegistry
    ) {
    }

    public function setup(): void
    {
        // Register schema
        $this->schemaRegistry->register(new Schema([
            'query' => $this->typeRegistry->get(RegistryBasedQueryType::class),
        ]));

        // Register some query fields
        $this->queryFieldRegistry
            ->register(GraphqlNode::create('noop')->withResolver(fn () => 'noop'))
            ->register(GraphqlNode::create('bar')->withResolver(fn () => 'foo'))
            ->register(GraphqlNode::create('foo')->withResolver(fn () => 'bar'))
            ->register(
                GraphqlNode::create('page')
                    ->withType($this->typeRegistry->get(RegistryBasedPageType::class))
                    ->withResolver(fn () => ['uid' => 1, 'title' => 'This is a hard-coded fake page record'])
            );
    }
}
