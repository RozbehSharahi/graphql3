<?php

namespace RozbehSharahi\Graphql3\Controller;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GraphqlController
{
    public function __construct(protected ResponseFactoryInterface $responseFactory)
    {
    }

    public function graphqlAction(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->responseFactory->createResponse();
        $response->getBody()->write('Not implemented yet');

        return $response;
    }

    public function graphqlInterfaceAction(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->responseFactory->createResponse();
        $response->getBody()->write(file_get_contents(__DIR__.'/../../Resources/Private/Graphiql/Index.html'));

        return $response;
    }
}
