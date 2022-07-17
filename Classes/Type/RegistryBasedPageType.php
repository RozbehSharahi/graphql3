<?php

namespace RozbehSharahi\Graphql3\Type;

use GraphQL\Type\Definition\ObjectType;
use RozbehSharahi\Graphql3\Registry\PageFieldRegistry;
use RozbehSharahi\Graphql3\Registry\TypeRegistry;
use RozbehSharahi\Graphql3\Setup\SetupInterface;

class RegistryBasedPageType extends ObjectType implements SetupInterface
{
    public function __construct(protected TypeRegistry $typeRegistry, protected PageFieldRegistry $pageFieldRegistry)
    {
        parent::__construct([
            'name' => 'Page',
            'fields' => fn () => $this->pageFieldRegistry->getFields()->toArray(),
        ]);
    }

    public function setup(): void
    {
        $this->typeRegistry->register($this);
    }
}
