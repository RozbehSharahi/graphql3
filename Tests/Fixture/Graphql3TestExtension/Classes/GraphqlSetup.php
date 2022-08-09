<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

declare(strict_types=1);

namespace RozbehSharahi\Graphql3TestExtension;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use RozbehSharahi\Graphql3\Builder\Node\RecordNodeBuilder;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Setup\SetupInterface;

class GraphqlSetup implements SetupInterface
{
    public function __construct(
        protected SchemaRegistry $schemaRegistry,
        protected RecordNodeBuilder $recordNodeBuilder
    ) {
    }

    public function setup(): void
    {
        $this->schemaRegistry->register(new Schema([
            'query' => new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'page' => $this->recordNodeBuilder->for('pages')->build()->toArray(),
                ],
            ]),
        ]));
    }
}
