<?php

namespace RozbehSharahi\Graphql3\Type;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgument;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;

class OrderItemInputType extends InputObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name' => 'OrderField',
            'fields' => fn () => GraphqlArgumentCollection::create([
                GraphqlArgument::create('field')->withType(Type::string()),
                GraphqlArgument::create('direction')->withType(Type::string())->withDefaultValue('asc'),
            ])->toArray(),
        ]);
    }
}
