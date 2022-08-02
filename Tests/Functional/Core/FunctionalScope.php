<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tests\Functional\Core;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RozbehSharahi\Graphql3\Builder\Node\LanguageListNodeBuilder;
use RozbehSharahi\Graphql3\Builder\Node\LanguageNodeBuilder;
use RozbehSharahi\Graphql3\Middleware\GraphqlRequestMiddleware;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Security\AccessChecker;
use RozbehSharahi\Graphql3\Type\QueryType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\StreamFactory;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Frontend\Http\Application;

class FunctionalScope
{
    /**
     * @param array<string|mixed>|null $loggedInUser
     */
    public function __construct(protected ContainerInterface $container, protected ?array $loggedInUser)
    {
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public function get(string $class): object
    {
        return $this->container->get($class);
    }

    public function set(string $serviceName, mixed $service): self
    {
        $this->container->set($serviceName, $service);

        return $this;
    }

    public function getApplication(): Application
    {
        return $this->get(Application::class);
    }

    public function getConnectionPool(): ConnectionPool
    {
        return $this->get(ConnectionPool::class);
    }

    public function getSchemaRegistry(): SchemaRegistry
    {
        return $this->get(SchemaRegistry::class);
    }

    public function getQueryType(): QueryType
    {
        return $this->get(QueryType::class);
    }

    public function getSiteFinder(): SiteFinder
    {
        return $this->get(SiteFinder::class);
    }

    public function getAccessChecker(): AccessChecker
    {
        return $this->get(AccessChecker::class);
    }

    public function getLanguageNode(): LanguageNodeBuilder
    {
        return $this->get(LanguageNodeBuilder::class);
    }

    public function getLanguageListNode(): LanguageListNodeBuilder
    {
        return $this->get(LanguageListNodeBuilder::class);
    }

    public function doServerRequest(ServerRequestInterface $request): ResponseInterface
    {
        $user = $this->loggedInUser ?? [];

        $request = $request->withQueryParams([
            GraphqlRequestMiddleware::PARAMETER_LOGGED_IN_TEST_USER => $user['uid'] ?? null,
        ]);

        return $this->getApplication()->handle($request);
    }

    /**
     * @return array<string, mixed>>
     */
    public function doGraphqlRequest(string $graphql): array
    {
        try {
            $bodyStream = (new StreamFactory())
                ->createStream(json_encode(['query' => $graphql], JSON_THROW_ON_ERROR))
            ;
        } catch (Exception) {
            throw new \RuntimeException('Could not create graphql request in test.');
        }

        $request = new ServerRequest('/test-app/graphql', 'POST', $bodyStream);
        $response = $this->doServerRequest($request);

        if (404 === $response->getStatusCode()) {
            throw new \RuntimeException('Graphql request returned 404 page.');
        }

        try {
            return json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception) {
            throw new \RuntimeException('Test failed since doGraphqlRequest return invalid graphql response');
        }
    }

    /**
     * @param array<string, int|string|boolean> $data
     */
    public function createRecord(string $table, array $data): self
    {
        $query = $this->getConnectionPool()->getQueryBuilderForTable($table);
        $query
            ->insert($table)
            ->values($data)
            ->executeStatement()
        ;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getRecord(string $table, int $uid): array
    {
        $query = $this->getConnectionPool()->getQueryBuilderForTable($table);
        $query->from($table)->select('*')->where('uid='.$uid);

        try {
            return $query->executeQuery()->fetchAssociative();
        } catch (\Throwable) {
            throw new \RuntimeException('Could not fetch page with ID: '.$uid.' in test scope.');
        }
    }
}
