<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

declare(strict_types=1);

namespace RozbehSharahi\Graphql3TestExtension;

use GraphQL\Type\Schema;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Setup\SetupInterface;
use RozbehSharahi\Graphql3\Type\MutationType;
use RozbehSharahi\Graphql3\Type\QueryType;

class GraphqlSetup implements SetupInterface
{
    public function __construct(
        protected SchemaRegistry $schemaRegistry,
        protected QueryType $queryType,
        protected MutationType $mutationType
    ) {
    }

    public function setup(): void
    {
        $this->schemaRegistry->registerCreator(fn () => new Schema([
            'query' => $this->queryType,
            'mutation' => $this->mutationType,
        ]));
    }
}
