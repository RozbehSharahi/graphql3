<?php

namespace RozbehSharahi\Graphql3\Controller;

use Exception;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlErrorCollection;
use RozbehSharahi\Graphql3\Exception\GraphqlException;
use RozbehSharahi\Graphql3\Graphql\GraphqlErrorResponseBuilder;
use RozbehSharahi\Graphql3\Graphql\GraphqlExecutor;

class GraphqlController
{
    public const ERROR_MESSAGE_INVALID_INPUT = 'Invalid input for graphql input';

    public function __construct(
        protected ResponseFactoryInterface $responseFactory,
        protected GraphqlExecutor $executor,
        protected GraphqlErrorResponseBuilder $errorResponseBuilder
    ) {
    }

    public function graphqlAction(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isValidInput($request)) {
            return $this->errorResponseBuilder->withMessage(self::ERROR_MESSAGE_INVALID_INPUT)->build();
        }

        $input = $this->decode((string) $request->getBody());

        try {
            $output = $this
                ->executor
                ->withQuery($input['query'])
                ->withVariables($input['variables'] ?? [])
                ->execute();
        } catch (Exception $e) {
            return $this->errorResponseBuilder->withMessage($e->getMessage())->build();
        }

        if ($output['errors'] ?? null) {
            return $this->errorResponseBuilder
                ->withErrors(GraphqlErrorCollection::createFromArray($output['errors']))
                ->build();
        }

        $response = $this->responseFactory
            ->createResponse()
            ->withHeader('Content-Type', 'application/json');

        $response->getBody()->write($this->encode($output));

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
        try {
            $data = $this->decode((string) $request->getBody());
        } catch (Exception) {
            return false;
        }

        if (!is_array($data)) {
            return false;
        }

        return true;
    }

    protected function decode(string $json): mixed
    {
        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception) {
            throw new GraphqlException(self::ERROR_MESSAGE_INVALID_INPUT);
        }

        return $data;
    }

    protected function encode(array $data): string
    {
        try {
            return json_encode($data, JSON_THROW_ON_ERROR);
        } catch (Exception) {
            throw new GraphqlException('GraphQL library responded with unexpected value');
        }
    }
}
