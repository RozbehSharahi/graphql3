<?php

namespace RozbehSharahi\Graphql3\Setup;

use RozbehSharahi\Graphql3\Registry\TypeRegistry;
use RozbehSharahi\Graphql3\Type\NoopQueryType;
use RozbehSharahi\Graphql3\Type\RegistryBasedQueryType;

class Setup implements SetupInterface
{
    public function __construct(
        protected TypeRegistry $typeRegistry,
        protected NoopQueryType $noopQueryType,
        protected RegistryBasedQueryType $registryBasedQueryType
    ) {
    }

    public function setup(): void
    {
        $this->typeRegistry
            ->register($this->noopQueryType)
            ->register($this->registryBasedQueryType);
    }
}
