<?php

namespace RozbehSharahi\Graphql3\Executor;

use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use RozbehSharahi\Graphql3\Exception\GraphqlException;

class Executor
{
    protected string $query;

    protected array $variables = [];

    protected Schema $schema;

    public function getQuery(): string
    {
        return $this->query;
    }

    public function withQuery(string $query): self
    {
        $clone = clone $this;
        $clone->query = $query;

        return $clone;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function withVariables(array $variables): self
    {
        $clone = clone $this;
        $clone->variables = $variables;

        return $clone;
    }

    public function getSchema(): Schema
    {
        return $this->schema;
    }

    public function withSchema(Schema $schema): self
    {
        $clone = clone $this;
        $clone->schema = $schema;

        return $clone;
    }

    public function execute(): array
    {
        if (empty($this->query)) {
            throw new GraphqlException('No query provided to execute');
        }

        return GraphQL::executeQuery($this->schema, $this->query, null, null, $this->variables)->toArray();
    }
}
