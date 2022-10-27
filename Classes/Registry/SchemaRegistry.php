<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Registry;

use GraphQL\Type\Schema;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;

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
            throw new InternalErrorException('No schema registered. Did you call `'.self::class.'::registerSchema`. For instance in a middleware?');
        }

        return $this->schema;
    }

    public function isRegistered(): bool
    {
        return !empty($this->schema);
    }
}
