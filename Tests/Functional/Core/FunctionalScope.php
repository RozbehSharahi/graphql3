<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tests\Functional\Core;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\TableDiff;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RozbehSharahi\Graphql3\Domain\Model\JwtUser;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use RozbehSharahi\Graphql3\Security\JwtManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\StreamFactory;
use TYPO3\CMS\Frontend\Http\Application;

class FunctionalScope
{
    private ?JwtUser $user = null;

    public function __construct(protected ContainerInterface $container)
    {
    }

    public function loginUser(?JwtUser $jwtUser): self
    {
        $this->user = $jwtUser;

        return $this;
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

    public function doServerRequest(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->user) {
            $token = $this
                ->get(JwtManager::class)
                ->withEnvironmentVariables()
                ->create(new \DateTime('now + 60 seconds'), $this->user->toPayload())
            ;

            $request = $request->withHeader('Authorization', 'Bearer '.$token);
        }

        return $this->getApplication()->handle($request);
    }

    /**
     * @return array<string, mixed>
     *
     * @deprecated use FunctionalScope::graphqlRequest instead
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
     * @param array<string,string> $headers
     */
    public function graphqlRequest(string $graphql, array $headers = []): GraphqlResponse
    {
        try {
            $bodyStream = (new StreamFactory())
                ->createStream(json_encode(['query' => $graphql], JSON_THROW_ON_ERROR))
            ;
        } catch (Exception) {
            throw new \RuntimeException('Could not create graphql request in test.');
        }

        $request = new ServerRequest('/test-app/graphql', 'POST', $bodyStream, $headers);
        $response = $this->doServerRequest($request);

        return GraphqlResponse::fromResponse($response);
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

    public function createTable(Table $table): self
    {
        try {
            $this->getSchemaManager()->createTable($table);
        } catch (\Doctrine\DBAL\Exception $e) {
            throw new InternalErrorException('Could not create table in tests: '.$e->getMessage());
        }

        return $this;
    }

    public function updateTable(TableDiff $tableDiff): self
    {
        try {
            $this->getSchemaManager()->alterTable($tableDiff);
        } catch (\Doctrine\DBAL\Exception $e) {
            throw new InternalErrorException('Could not alter table in tests: '.$e->getMessage());
        }

        return $this;
    }

    protected function getSchemaManager(): AbstractSchemaManager
    {
        try {
            return $this
                ->getConnectionPool()
                ->getConnectionByName('Default')
                ->createSchemaManager()
            ;
        } catch (\Doctrine\DBAL\Exception) {
            throw new InternalErrorException('Could not create schema-manager in tests');
        }
    }
}
