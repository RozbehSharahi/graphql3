<?php

namespace RozbehSharahi\Graphql3\Controller;

use Exception;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlErrorCollection;
use RozbehSharahi\Graphql3\Encoder\JsonEncoder;
use RozbehSharahi\Graphql3\Graphql\GraphqlErrorResponseBuilder;
use RozbehSharahi\Graphql3\Graphql\GraphqlExecutor;

class GraphqlController
{
    public const ERROR_MESSAGE_INVALID_INPUT = 'Invalid input for graphql input';

    public function __construct(
        protected ResponseFactoryInterface $responseFactory,
        protected JsonEncoder $encoder,
        protected GraphqlExecutor $executor,
        protected GraphqlErrorResponseBuilder $errorResponseBuilder
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

        return $this->encoder->isValidJson($json) && is_array($this->encoder->decode($json));
    }
}
