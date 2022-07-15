<?php

namespace RozbehSharahi\Graphql3\Registry;

use GraphQL\Type\Schema;
use RozbehSharahi\Graphql3\Exception\GraphqlException;

class SchemaRegistry
{
    protected ?Schema $schema = null;

    public function register(Schema $schema): self
    {
        $this->schema = $schema;

        return $this;
    }

    public function getSchema(): Schema
    {
        if (empty($this->schema)) {
            throw new GraphqlException('No schema registered. Did you call `'.self::class.'::registerSchema`. '.'For instance in a middleware?');
        }

        return $this->schema;
    }

    public function isRegistered(): bool
    {
        return !empty($this->schema);
    }
}
