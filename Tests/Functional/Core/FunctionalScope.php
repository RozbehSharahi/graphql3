<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace RozbehSharahi\Graphql3\Tests\Functional\Core;

use Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RozbehSharahi\Graphql3\Registry\QueryFieldRegistry;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Registry\TypeRegistry;
use RozbehSharahi\Graphql3\Type\RegistryBasedQueryType;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\StreamFactory;
use TYPO3\CMS\Frontend\Http\Application;

/**
 * @template T
 */
class FunctionalScope
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @param T $class
     *
     * @return T
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function get(string $class): mixed
    {
        return $this->container->get($class);
    }

    public function getApplication(): Application
    {
        return $this->container->get(Application::class);
    }

    public function getSchemaRegistry(): SchemaRegistry
    {
        return $this->getContainer()->get(SchemaRegistry::class);
    }

    public function getRegistryBasedQueryType(): RegistryBasedQueryType
    {
        return $this->getContainer()->get(RegistryBasedQueryType::class);
    }

    public function getQueryFieldRegistry(): QueryFieldRegistry
    {
        return $this->getContainer()->get(QueryFieldRegistry::class);
    }

    public function getTypeRegistry(): TypeRegistry
    {
        return $this->getContainer()->get(TypeRegistry::class);
    }

    public function doServerRequest(ServerRequestInterface $request): ResponseInterface
    {
        return $this->getApplication()->handle($request);
    }

    public function doGraphqlRequest(string $graphql): array
    {
        try {
            $bodyStream = (new StreamFactory())
                ->createStream(json_encode(['query' => $graphql], JSON_THROW_ON_ERROR));
        } catch (Exception) {
            throw new \RuntimeException('Could not create graphql request in test.');
        }

        $request = new ServerRequest('/test-app/graphql', 'POST', $bodyStream);
        $response = $this->getApplication()->handle($request);

        try {
            return json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception) {
            throw new \RuntimeException('Test failed since doGraphqlRequest return invalid graphql response');
        }
    }
}
