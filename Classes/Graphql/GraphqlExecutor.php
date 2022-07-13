<?php

namespace RozbehSharahi\Graphql3\Graphql;

use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use RozbehSharahi\Graphql3\Exception\GraphqlException;

class GraphqlExecutor
{
    protected string $query;

    protected array $variables = [];

    protected Schema $schema;

    public function __construct(GraphqlRegistry $registry)
    {
        $this->schema = new Schema([
            'query' => $registry->getQuery(),
        ]);
    }

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

    public function execute(): array
    {
        if (empty($this->query)) {
            throw new GraphqlException('No query provided to execute');
        }

        return GraphQL::executeQuery($this->schema, $this->query, null, null, $this->variables)->toArray();
    }
}
