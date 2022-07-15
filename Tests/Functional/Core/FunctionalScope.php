<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace RozbehSharahi\Graphql3\Tests\Functional\Core;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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

    public function doServerRequest(ServerRequestInterface $request): ResponseInterface
    {
        return $this->getApplication()->handle($request);
    }
}