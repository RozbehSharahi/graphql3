<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace RozbehSharahi\Graphql3\Tests\Functional\Core;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RozbehSharahi\Graphql3\Registry\QueryFieldRegistry;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Type\RegistryBasedQueryType;
use TYPO3\CMS\Frontend\Http\Application;

class FunctionalScope
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
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

    public function doServerRequest(ServerRequestInterface $request): ResponseInterface
    {
        return $this->getApplication()->handle($request);
    }
}
