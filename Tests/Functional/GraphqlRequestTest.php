<?php

namespace RozbehSharahi\Graphql3\Tests\Functional;

use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Http\ServerRequest;

class GraphqlRequestTest extends TestCase
{
    public function testCanRunAGraphqlRequest(): void
    {
        $response = $this
            ->createFunctionalApp()
            ->getApplication()
            ->handle(new ServerRequest('/test-app/graphql'));

        self::assertEquals(200, (string) $response->getStatusCode());
        self::assertEquals('Not implemented yet', (string) $response->getBody());
    }

    public function createFunctionalApp(): FunctionAppBuilder
    {
        return (new FunctionAppBuilder())
            ->withAutoCreateHomepage(true)
            ->withAutoCreateSchema(true)
            ->withAutoCreateSite(true)
            ->build();
    }
}
