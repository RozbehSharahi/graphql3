<?php

namespace RozbehSharahi\Graphql3TestExtension\Graphql;

use GraphQL\Type\Schema;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Registry\QueryFieldRegistry;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Type\RegistryBasedQueryType;
use TYPO3\CMS\Core\SingletonInterface;

class GraphqlRegistration implements SingletonInterface
{
    public function __construct(
        protected SchemaRegistry $registry,
        protected QueryFieldRegistry $queryFieldRegistry,
        protected RegistryBasedQueryType $registryBasedQueryType
    ) {
    }

    public function register(): void
    {
        $this->queryFieldRegistry
            ->register(GraphqlNode::create('noop')->withResolver(fn () => 'noop'))
            ->register(GraphqlNode::create('foo')->withResolver(fn () => 'bar'));

        $this->registry->register(new Schema([
            'query' => $this->registryBasedQueryType,
        ]));
    }
}
