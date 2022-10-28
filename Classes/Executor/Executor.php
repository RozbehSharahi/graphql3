<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Executor;

use Closure;
use GraphQL\Error\DebugFlag;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use TYPO3\CMS\Core\Core\Environment;

class Executor
{
    protected string $query;

    /**
     * @var array<string, mixed>
     */
    protected array $variables = [];

    protected Schema $schema;

    protected ?Closure $errorHandler = null;

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

    public function getErrorHandler(): Closure
    {
        return $this->errorHandler;
    }

    public function withErrorHandler(?Closure $errorHandler): self
    {
        $clone = clone $this;
        $clone->errorHandler = $errorHandler;

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
            throw new InternalErrorException('No query provided to execute.');
        }

        $debugFlag = Environment::getContext()->isTesting() || Environment::getContext()->isDevelopment()
            ? DebugFlag::INCLUDE_DEBUG_MESSAGE
            : DebugFlag::NONE;

        $executionResult = GraphQL::executeQuery($this->schema, $this->query, null, null, $this->variables);

        if ($this->errorHandler) {
            $executionResult->setErrorsHandler($this->errorHandler);
        }

        return $executionResult
            ->setErrorsHandler($this->errorHandler)
            ->toArray($debugFlag)
        ;
    }
}
