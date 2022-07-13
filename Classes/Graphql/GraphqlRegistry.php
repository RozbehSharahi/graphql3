<?php

namespace RozbehSharahi\Graphql3\Graphql;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class GraphqlRegistry
{
    private ?ObjectType $query;

    public function getQuery(): ObjectType
    {
        return $this->query = $this->query ?? new ObjectType([
            'name' => 'Query',
            'fields' => [
                'noop' => [
                    'type' => Type::string(),
                    'resolve' => fn ($rootValue, array $args) => 'noop',
                ],
            ],
        ]);
    }
}
