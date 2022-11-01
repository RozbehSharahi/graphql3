<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Registry;

use GraphQL\Type\Schema;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;

class SchemaRegistry
{
    protected \Closure|null $schemaCreator = null;

    public function registerCreator(\Closure $schemaCreator): self
    {
        $this->schemaCreator = $schemaCreator;

        return $this;
    }

    public function create(): Schema
    {
        return $this->getSchemaCreator()();
    }

    protected function getSchemaCreator(): \Closure
    {
        if (empty($this->schemaCreator)) {
            throw new InternalErrorException('No schema registered. Did you call `'.self::class.'::registerSchema`. For instance in a middleware?');
        }

        return $this->schemaCreator;
    }

    public function isRegistered(): bool
    {
        return !empty($this->schemaCreator);
    }
}
