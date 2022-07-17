<?php

namespace RozbehSharahi\Graphql3\Registry;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Exception\GraphqlException;

class TypeRegistry
{
    protected array $types = [];

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
