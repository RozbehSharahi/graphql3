<?php

namespace RozbehSharahi\Graphql3TestExtension\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RozbehSharahi\Graphql3TestExtension\Graphql\GraphqlRegistration;

class GraphqlRegistrationMiddleware implements MiddlewareInterface
{
    public function __construct(protected GraphqlRegistration $graphqlRegistration)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->graphqlRegistration->register();

        return $handler->handle($request);
    }
}
