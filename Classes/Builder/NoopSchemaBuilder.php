<?php

namespace RozbehSharahi\Graphql3\Builder;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

class NoopSchemaBuilder
{
    public function build(): Schema
    {
        return new Schema([
            'query' => new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'noop' => [
                        'type' => Type::string(),
                        'resolve' => fn ($rootValue, array $args) => 'noop',
                    ],
                ],
            ]),
        ]);
    }
}
