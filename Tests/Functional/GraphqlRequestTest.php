<?php

namespace RozbehSharahi\Graphql3\Tests\Functional;

use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Http\ServerRequest;

class GraphqlRequestTest extends TestCase
{
    public function testCanRunAGraphqlRequest(): void
    {
        $response = $this
            ->getFunctionalAppBuilder()
            ->build()
            ->getApplication()
            ->handle(new ServerRequest('/test-app/graphql'));

        self::assertEquals(200, (string) $response->getStatusCode());
        self::assertEquals('Not implemented yet', (string) $response->getBody());
    }

    public function testGraphqlInterfaceRequestOnlyInDevelopmentMode(): void
    {
        $response = $this
            ->getFunctionalAppBuilder()
            ->withContext('Production')
            ->build()
            ->getApplication()
            ->handle(new ServerRequest('/test-app/graphiql'));

        self::assertEquals(404, (string) $response->getStatusCode());

        $response = $this
            ->getFunctionalAppBuilder()
            ->withContext('Development/SomeDeveloper')
            ->build()
            ->getApplication()
            ->handle(new ServerRequest('/test-app/graphiql'));

        self::assertEquals(200, (string) $response->getStatusCode());
        self::assertStringContainsString('graphQLFetcher', (string) $response->getBody());
    }

    public function getFunctionalAppBuilder(): FunctionAppBuilder
    {
        return (new FunctionAppBuilder())
            ->withAutoCreateHomepage(true)
            ->withAutoCreateSchema(true)
            ->withAutoCreateSite(true);
    }
}
