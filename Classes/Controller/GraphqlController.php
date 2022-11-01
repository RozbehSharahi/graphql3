<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Controller;

use JsonException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RozbehSharahi\Graphql3\Exception\BadRequestException;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use RozbehSharahi\Graphql3\Executor\Executor;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;

class GraphqlController
{
    public const CONTEXT_TESTING_PRODUCTION = 'Testing/Production';

    public const ERROR_MESSAGE_INVALID_INPUT = 'Invalid input given on graphql request: Missing or invalid query.';

    public function __construct(
        protected ResponseFactoryInterface $responseFactory,
        protected Executor $executor,
        protected SchemaRegistry $schemaRegistry
    ) {
    }

    public function graphqlAction(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $input = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new BadRequestException(self::ERROR_MESSAGE_INVALID_INPUT);
        }

        if (!is_array($input) || !is_string($input['query'] ?? null)) {
            throw new BadRequestException(self::ERROR_MESSAGE_INVALID_INPUT);
        }

        $output = $this
            ->executor
            ->withSchema($this->schemaRegistry->create())
            ->withQuery($input['query'])
            ->withVariables($input['variables'] ?? [])
            ->withErrorHandler(fn ($errors) => throw reset($errors))
            ->execute()
        ;

        $response = $this->responseFactory
            ->createResponse()
            ->withHeader('Content-Type', 'application/json')
        ;

        try {
            $response->getBody()->write(json_encode($output, JSON_THROW_ON_ERROR));
        } catch (\JsonException $e) {
            throw new InternalErrorException('Could not json encode graphql output: '.$e->getMessage());
        }

        return $response;
    }

    public function graphqlInterfaceAction(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->responseFactory->createResponse();
        $response->getBody()->write(file_get_contents(__DIR__.'/../../Resources/Private/Graphiql/Index.html'));

        return $response;
    }
}
