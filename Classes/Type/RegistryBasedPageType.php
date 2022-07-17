<?php

namespace RozbehSharahi\Graphql3\Type;

use GraphQL\Type\Definition\ObjectType;
use RozbehSharahi\Graphql3\Registry\PageFieldRegistry;
use RozbehSharahi\Graphql3\Registry\TypeRegistry;

class RegistryBasedPageType extends ObjectType
{
    public function __construct(protected TypeRegistry $typeRegistry, protected PageFieldRegistry $pageFieldRegistry)
    {
        parent::__construct([
            'name' => 'Page',
            'fields' => fn () => $this->pageFieldRegistry->getFields()->toArray(),
        ]);
    }
}
