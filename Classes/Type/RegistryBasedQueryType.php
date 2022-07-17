<?php

namespace RozbehSharahi\Graphql3\Type;

use GraphQL\Type\Definition\ObjectType;
use RozbehSharahi\Graphql3\Registry\QueryFieldRegistry;
use RozbehSharahi\Graphql3\Registry\TypeRegistry;
use RozbehSharahi\Graphql3\Setup\SetupInterface;

class RegistryBasedQueryType extends ObjectType implements SetupInterface
{
    public function __construct(protected TypeRegistry $typeRegistry, protected QueryFieldRegistry $fieldRegistry)
    {
        parent::__construct([
            'name' => 'Query',
            'fields' => fn () => $this->fieldRegistry->getFields()->toArray(),
        ]);
    }

    public function setup(): void
    {
        $this->typeRegistry->register($this);
    }
}
