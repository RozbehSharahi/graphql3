<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class NoopQueryType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name' => 'Query',
            'fields' => [
                'noop' => [
                    'type' => Type::string(),
                    'resolve' => fn () => 'noop',
                ],
            ],
        ]);
    }
}
