<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Type;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgument;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;

class FilterInputType extends InputObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name' => 'FilterInput',
            'fields' => fn () => GraphqlArgumentCollection::create([
                GraphqlArgument::create('type')->withDefaultValue('eq'),
                GraphqlArgument::create('field'),
                GraphqlArgument::create('value'),
                GraphqlArgument::create('values')->withType(Type::listOf(Type::string())),
                GraphqlArgument::create('children')->withType(Type::listOf($this)),
            ])->toArray(),
        ]);
    }
}
