<?php

namespace RozbehSharahi\Graphql3\Setup;

use RozbehSharahi\Graphql3\Registry\TypeRegistry;
use RozbehSharahi\Graphql3\Type\NoopQueryType;
use RozbehSharahi\Graphql3\Type\RegistryBasedPageType;
use RozbehSharahi\Graphql3\Type\RegistryBasedQueryType;

class TypesSetup implements SetupInterface
{
    public static function getDefaultPriority(): int
    {
        return 1000000;
    }

    public function __construct(
        protected TypeRegistry $typeRegistry,
        protected NoopQueryType $noopQueryType,
        protected RegistryBasedPageType $registryBasedPageType,
        protected RegistryBasedQueryType $registryBasedQueryType
    ) {
    }

    public function setup(): void
    {
        $this->typeRegistry
            ->register($this->noopQueryType)
            ->register($this->registryBasedPageType)
            ->register($this->registryBasedQueryType);
    }
}
