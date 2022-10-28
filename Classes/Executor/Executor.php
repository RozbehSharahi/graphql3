<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Executor;

use GraphQL\Error\DebugFlag;
use GraphQL\Error\Error;
use GraphQL\Error\SyntaxError;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use RozbehSharahi\Graphql3\Exception\BadRequestException;
use RozbehSharahi\Graphql3\Exception\Graphql3ExceptionInterface;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use RozbehSharahi\Graphql3\Handler\ErrorHandler;
use Throwable;
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
            throw new BadRequestException('No query provided to execute');
        }

        $debugFlag = Environment::getContext()->isTesting() || Environment::getContext()->isDevelopment()
            ? DebugFlag::INCLUDE_DEBUG_MESSAGE
            : DebugFlag::NONE;

        return GraphQL::executeQuery($this->schema, $this->query, null, null, $this->variables)
            ->setErrorsHandler(fn (array $errors) => $this->noMercyErrorHandler($errors))
            ->toArray($debugFlag)
        ;
    }

    /**
     * Override of default graphql error handling.
     *
     * Graphql3 in current state reduces to one error per request. This makes error-handling a bit easier.
     *
     * Main error handling however takes place in ErrorHandler.
     *
     * @see ErrorHandler
     *
     * @param Throwable[] $errors
     *
     * @throws Throwable
     */
    private function noMercyErrorHandler(array $errors): void
    {
        $error = reset($errors);

        $previous = $error->getPrevious();

        if ($previous instanceof Graphql3ExceptionInterface) {
            throw $previous;
        }

        if ($error instanceof SyntaxError) {
            throw new BadRequestException($error->getMessage());
        }

        if ($error instanceof Error && $error->isClientSafe()) {
            throw new BadRequestException($error->getMessage());
        }

        throw new InternalErrorException('Unhandled graphql exception: '.$error->getMessage());
    }
}
