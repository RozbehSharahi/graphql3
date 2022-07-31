<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Executor;

use GraphQL\Error\DebugFlag;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use RozbehSharahi\Graphql3\Exception\GraphqlException;
use TYPO3\CMS\Core\Core\Environment;

class Executor
{
    protected string $query;

    /**
     * @var array<string, mixed>
     */
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

    /**
     * @return array<string, mixed>
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * @param array<string, mixed> $variables
     *
     * @return $this
     */
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

    /**
     * @return array<string,mixed>
     */
    public function execute(): array
    {
        if (empty($this->query)) {
            throw new GraphqlException('No query provided to execute');
        }

        $debugFlag = Environment::getContext()->isTesting() ? DebugFlag::INCLUDE_DEBUG_MESSAGE : DebugFlag::NONE;

        return GraphQL::executeQuery($this->schema, $this->query, null, null, $this->variables)->toArray($debugFlag);
    }
}
