<?php

namespace RozbehSharahi\Graphql3\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RozbehSharahi\Graphql3\Controller\GraphqlController;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Setup\SetupInterface;
use RozbehSharahi\Graphql3\Site\CurrentSite;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Routing\SiteRouteResult;

class GraphqlRequestMiddleware implements MiddlewareInterface
{
    /**
     * @param iterable<SetupInterface> $setups
     */
    public function __construct(
        protected GraphqlController $graphqlController,
        protected SchemaRegistry $schemaRegistry,
        protected CurrentSite $currentSite,
        protected iterable $setups
    ) {
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

        // setup current site
        $this->currentSite->set($siteRoute->getSite());

        // Call all instances of GraphqlSetupInterface
        foreach ($this->setups as $setup) {
            $setup->setup();
        }

        if (!$this->schemaRegistry->isRegistered()) {
            return $handler->handle($request);
        }

        return $this->isGraphqlRoute($siteRoute)
            ? $this->graphqlController->graphqlAction($request)
            : $this->graphqlController->graphqlInterfaceAction($request);
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
