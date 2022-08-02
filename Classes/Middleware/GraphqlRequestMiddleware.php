<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Middleware;

use Doctrine\DBAL\Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RozbehSharahi\Graphql3\Controller\GraphqlController;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Setup\SetupInterface;
use RozbehSharahi\Graphql3\Site\CurrentSite;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Routing\SiteRouteResult;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

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

        // Possibility to fake user aspect for testing
        $this->loginUserForTesting($request);

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

    protected function loginUserForTesting(ServerRequestInterface $request): self
    {
        $testUserId = $request->getQueryParams()[self::PARAMETER_LOGGED_IN_TEST_USER] ?? null;
        $isTesting = Environment::getContext()->isTesting();

        if (!$isTesting || !$testUserId) {
            return $this;
        }

        $context = GeneralUtility::makeInstance(Context::class);

        try {
            $query = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('fe_users');
            $user = $query
                ->select('*')
                ->from('fe_users')
                ->where('uid='.$query->createNamedParameter($testUserId, \PDO::PARAM_INT))
                ->executeQuery()
                ->fetchAssociative()
            ;
        } catch (Exception) {
            return $this;
        }

        try {
            $query = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('fe_groups');
            $userGroups = !empty($user['usergroup']) ? $query
                ->select('*')
                ->from('fe_groups')
                ->where('uid IN ('.$user['usergroup'].')')
                ->executeQuery()
                ->fetchAllAssociative() : [];
        } catch (Exception) {
            return $this;
        }

        $frontendUserAuthentication = new FrontendUserAuthentication();
        $frontendUserAuthentication->user = $user;
        $frontendUserAuthentication->userGroups = [];

        foreach ($userGroups as $userGroup) {
            $frontendUserAuthentication->userGroups[$userGroup['uid']] = $userGroup;
        }

        $context->setAspect('frontend.user', new UserAspect($frontendUserAuthentication));

        return $this;
    }
}
