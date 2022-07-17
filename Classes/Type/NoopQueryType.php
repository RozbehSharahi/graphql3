<?php

namespace RozbehSharahi\Graphql3\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Registry\TypeRegistry;
use RozbehSharahi\Graphql3\Setup\SetupInterface;

class NoopQueryType extends ObjectType implements SetupInterface
{
    public function __construct(protected ?TypeRegistry $typeRegistry = null)
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

    public function setup(): void
    {
        $this->typeRegistry?->register($this);
    }
}
