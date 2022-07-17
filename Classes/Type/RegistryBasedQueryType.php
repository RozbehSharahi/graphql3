<?php

namespace RozbehSharahi\Graphql3\Type;

use GraphQL\Type\Definition\ObjectType;
use RozbehSharahi\Graphql3\Registry\QueryFieldRegistry;
use RozbehSharahi\Graphql3\Registry\TypeRegistry;

class RegistryBasedQueryType extends ObjectType
{
    public function __construct(protected TypeRegistry $typeRegistry, protected QueryFieldRegistry $fieldRegistry)
    {
        parent::__construct([
            'name' => 'Query',
            'fields' => fn () => $this->fieldRegistry->getFields()->toArray(),
        ]);
    }
}
