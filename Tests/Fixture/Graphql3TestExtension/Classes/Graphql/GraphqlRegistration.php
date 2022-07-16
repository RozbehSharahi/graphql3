<?php

namespace RozbehSharahi\Graphql3TestExtension\Graphql;

use GraphQL\Type\Schema;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Registry\QueryFieldRegistry;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Registry\TypeRegistry;
use RozbehSharahi\Graphql3\Type\RegistryBasedQueryType;
use TYPO3\CMS\Core\SingletonInterface;

class GraphqlRegistration implements SingletonInterface
{
    public function __construct(
        protected SchemaRegistry $schemaRegistry,
        protected TypeRegistry $typeRegistry,
        protected QueryFieldRegistry $queryFieldRegistry
    ) {
    }

    public function register(): void
    {
        // Register schema
        $this->schemaRegistry->register(new Schema([
            'query' => $this->typeRegistry->get(RegistryBasedQueryType::class),
        ]));

        // Register some query fields
        $this->queryFieldRegistry
            ->register(GraphqlNode::create('noop')->withResolver(fn () => 'noop'))
            ->register(GraphqlNode::create('bar')->withResolver(fn () => 'foo'))
            ->register(GraphqlNode::create('foo')->withResolver(fn () => 'bar'));
    }
}
