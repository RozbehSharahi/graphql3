<?php

namespace RozbehSharahi\Graphql3\Registry;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Exception\GraphqlException;
use RozbehSharahi\Graphql3\Type\RegistryBasedQueryType;

class TypeRegistry
{
    protected array $types = [];

    /**
     * Here we register our graphql3 core types.
     */
    public function __construct(RegistryBasedQueryType $registryBasedQueryType)
    {
        $this
            ->register($registryBasedQueryType);
    }

    public function register(Type $type): self
    {
        $this->types[get_class($type)] = $type;

        return $this;
    }

    public function get(string $typeName): Type
    {
        if (empty($this->types[$typeName])) {
            throw new GraphqlException("The type {$typeName} was not registered");
        }

        return $this->types[$typeName];
    }
}
