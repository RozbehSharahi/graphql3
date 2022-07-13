<?php

namespace RozbehSharahi\Graphql3\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RozbehSharahi\Graphql3\Controller\GraphqlController;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Routing\SiteRouteResult;

class GraphqlRequestMiddleware implements MiddlewareInterface
{
    public function __construct(protected GraphqlController $graphqlController)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $siteRoute = $request->getAttribute('routing');

        if (!$siteRoute instanceof SiteRouteResult) {
            return $handler->handle($request);
        }

        if (!$this->isGraphqlRoute($siteRoute) && !$this->isGraphqlInterfaceRoute($siteRoute)) {
            return $handler->handle($request);
        }

        if ($this->isGraphqlInterfaceRoute($siteRoute) && !Environment::getContext()->isDevelopment()) {
            return $handler->handle($request);
        }

        if ($this->isGraphqlInterfaceRoute($siteRoute)) {
            return $this->graphqlController->graphqlInterfaceAction($request);
        }

        return $this->graphqlController->graphqlAction($request);
    }

    protected function isGraphqlRoute(SiteRouteResult $route): bool
    {
        return $route->getTail() === $this->getGraphqlRouteKey();
    }

    protected function isGraphqlInterfaceRoute(SiteRouteResult $route): bool
    {
        return $route->getTail() === $this->getGraphqlInterfaceRouteKey();
    }

    protected function getGraphqlRouteKey(): string
    {
        return 'graphql';
    }

    protected function getGraphqlInterfaceRouteKey(): string
    {
        return 'graphiql';
    }
}
