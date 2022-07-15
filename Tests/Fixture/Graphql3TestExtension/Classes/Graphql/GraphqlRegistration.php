<?php

namespace RozbehSharahi\Graphql3TestExtension\Graphql;

use RozbehSharahi\Graphql3\Builder\NoopSchemaBuilder;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use TYPO3\CMS\Core\SingletonInterface;

class GraphqlRegistration implements SingletonInterface
{
    public function __construct(protected SchemaRegistry $registry, protected NoopSchemaBuilder $noopSchemaBuilder)
    {
    }

    public function register(): void
    {
        $this->registry->register($this->noopSchemaBuilder->build());
    }
}
