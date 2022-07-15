<?php

namespace RozbehSharahi\Graphql3\Type;

use GraphQL\Type\Definition\ObjectType;
use RozbehSharahi\Graphql3\Registry\QueryFieldRegistry;

class RegistryBasedQueryType extends ObjectType
{
    public function __construct(protected QueryFieldRegistry $fieldRegistry)
    {
        parent::__construct([
            'name' => 'Query',
            'fields' => fn () => $this->fieldRegistry->getFields()->toArray(),
        ]);
    }
}
