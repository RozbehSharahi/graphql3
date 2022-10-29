<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RozbehSharahi\Graphql3\Controller\GraphqlController;
use RozbehSharahi\Graphql3\Handler\ErrorHandler;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Session\CurrentRequest;
use RozbehSharahi\Graphql3\Session\CurrentSite;
use RozbehSharahi\Graphql3\Setup\SetupInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Routing\SiteRouteResult;

class GraphqlRequestMiddleware implements MiddlewareInterface
{
    public const PARAMETER_LOGGED_IN_TEST_USER = 'loggedInTestUser';

    /**
     * @param iterable<SetupInterface> $setups
     */
    public function __construct(
        protected GraphqlController $graphqlController,
        protected SchemaRegistry $schemaRegistry,
        protected CurrentSite $currentSite,
        protected CurrentRequest $currentRequest,
        protected ErrorHandler $errorHandler,
        protected iterable $setups
    ) {
    }

    /**
     * @param ServerRequest $request
     */
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

        // setup current request
        $this->currentRequest->set($request);

        // Call all instances of GraphqlSetupInterface
        foreach ($this->setups as $setup) {
            $setup->setup();
        }

        if (!$this->schemaRegistry->isRegistered()) {
            return $handler->handle($request);
        }

        try {
            return $this->isGraphqlRoute($siteRoute)
                ? $this->graphqlController->graphqlAction($request)
                : $this->graphqlController->graphqlInterfaceAction($request);
        } catch (\Throwable $throwable) {
            return $this->errorHandler->handle($throwable);
        }
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
