<?php

namespace RozbehSharahi\Graphql3\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Routing\SiteRouteResult;

class GraphqlRequestMiddleware implements MiddlewareInterface
{

    public function __construct(protected ResponseFactoryInterface $responseFactory)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $siteRoute = $request->getAttribute('routing');

        if (!$siteRoute instanceof SiteRouteResult || $siteRoute->getTail() !== $this->getGraphqlRouteKey()) {
            return $handler->handle($request);
        }

        $response = $this->responseFactory->createResponse();
        $response->getBody()->write('Not implemented yet');
        return $response;
    }

    protected function getGraphqlRouteKey(): string
    {
        return 'graphql';
    }
}