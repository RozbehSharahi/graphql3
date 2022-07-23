<?php

namespace RozbehSharahi\Graphql3\Controller;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RozbehSharahi\Graphql3\Builder\ErrorResponseBuilder;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlErrorCollection;
use RozbehSharahi\Graphql3\Encoder\JsonEncoder;
use RozbehSharahi\Graphql3\Exception\GraphqlException;
use RozbehSharahi\Graphql3\Executor\Executor;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use Throwable;
use TYPO3\CMS\Core\Core\Environment;

class GraphqlController
{
    public const ERROR_MESSAGE_INVALID_INPUT = 'Invalid input given on graphql request: Missing or invalid query.';

    public function __construct(
        protected ResponseFactoryInterface $responseFactory,
        protected JsonEncoder $encoder,
        protected Executor $executor,
        protected SchemaRegistry $schemaRegistry,
        protected ErrorResponseBuilder $errorResponseBuilder,
    ) {
    }

    public function graphqlAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isValidInput($request)) {
            return $this->errorResponseBuilder->withMessage(self::ERROR_MESSAGE_INVALID_INPUT)->build();
        }

        $input = $this->encoder->decode((string) $request->getBody());

        try {
            $output = $this
                ->executor
                ->withSchema($this->schemaRegistry->getSchema())
                ->withQuery($input['query'])
                ->withVariables($input['variables'] ?? [])
                ->execute();
        } catch (Throwable|GraphqlException $e) {
            return $this
                ->throwIfTestingMode($e->getMessage())
                ->errorResponseBuilder
                ->withMessage($e->getMessage())
                ->build();
        }

        if ($output['errors'] ?? null) {
            return $this
                ->throwIfTestingMode($output['errors'][0]['message'])
                ->errorResponseBuilder
                ->withErrors(GraphqlErrorCollection::createFromArray($output['errors']))
                ->build();
        }

        $response = $this->responseFactory
            ->createResponse()
            ->withHeader('Content-Type', 'application/json');

        $response->getBody()->write($this->encoder->encode($output));

        return $response;
    }

    public function graphqlInterfaceAction(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->responseFactory->createResponse();
        $response->getBody()->write(file_get_contents(__DIR__.'/../../Resources/Private/Graphiql/Index.html'));

        return $response;
    }

    protected function isValidInput(ServerRequestInterface $request): bool
    {
        $json = (string) $request->getBody();

        if (!$this->encoder->isValidJson($json)) {
            return false;
        }

        $input = $this->encoder->decode($json);

        return !(!is_array($input) || !is_string($input['query'] ?? null));
    }

    protected function throwIfTestingMode(string $message): self
    {
        if ('Testing/Production' === (string) Environment::getContext()) {
            return $this;
        }

        if (Environment::getContext()->isTesting()) {
            throw new \RuntimeException($message);
        }

        return $this;
    }
}
