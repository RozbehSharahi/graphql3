<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Controller;

use JsonException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RozbehSharahi\Graphql3\Builder\ErrorResponseBuilder;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlErrorCollection;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use RozbehSharahi\Graphql3\Executor\Executor;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use Throwable;
use TYPO3\CMS\Core\Core\Environment;

class GraphqlController
{
    public const CONTEXT_TESTING_PRODUCTION = 'Testing/Production';

    public const ERROR_MESSAGE_INVALID_INPUT = 'Invalid input given on graphql request: Missing or invalid query.';

    public function __construct(
        protected ResponseFactoryInterface $responseFactory,
        protected Executor $executor,
        protected SchemaRegistry $schemaRegistry,
        protected ErrorResponseBuilder $errorResponseBuilder,
    ) {
    }

    public function graphqlAction(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $input = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $this->errorResponseBuilder->withMessage(self::ERROR_MESSAGE_INVALID_INPUT)->build();
        }

        if (!is_array($input) || !is_string($input['query'] ?? null)) {
            return $this->errorResponseBuilder->withMessage(self::ERROR_MESSAGE_INVALID_INPUT)->build();
        }

        try {
            $output = $this
                ->executor
                ->withSchema($this->schemaRegistry->getSchema())
                ->withQuery($input['query'])
                ->withVariables($input['variables'] ?? [])
                ->execute()
            ;
        } catch (Throwable $e) {
            $this->throwIfTestingMode($e->getMessage());

            return $this->errorResponseBuilder->withMessage($e->getMessage())->build();
        }

        if ($output['errors'] ?? null) {
            $this->throwIfTestingMode($output['errors']);

            return $this
                ->errorResponseBuilder
                ->withErrors(GraphqlErrorCollection::createFromArray($output['errors']))
                ->build()
            ;
        }

        $response = $this->responseFactory
            ->createResponse()
            ->withHeader('Content-Type', 'application/json')
        ;

        try {
            $response->getBody()->write(json_encode($output, JSON_THROW_ON_ERROR));
        } catch (JsonException $e) {
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

    protected function throwIfTestingMode(mixed $data): self
    {
        if (self::CONTEXT_TESTING_PRODUCTION === (string) Environment::getContext()) {
            return $this;
        }

        if (Environment::getContext()->isTesting()) {
            /* @noinspection JsonEncodingApiUsageInspection */
            throw new InternalErrorException('An exception was thrown in test: '.json_encode($data));
        }

        return $this;
    }
}
