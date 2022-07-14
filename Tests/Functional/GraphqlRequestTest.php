<?php

/** @noinspection DuplicatedCode */

namespace RozbehSharahi\Graphql3\Tests\Functional;

use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Controller\GraphqlController;
use RozbehSharahi\Graphql3\Tests\Functional\Traits\FunctionalUtilsTrait;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\StreamFactory;

class GraphqlRequestTest extends TestCase
{
    use FunctionalUtilsTrait;

    public function testCanRunAGraphqlRequest(): void
    {
        $this
            ->getGraphqlSchemaRegistry()
            ->registerSiteSchema('test-app', $this->getNoopSchema());

        $request = $this->createGraphqlRequest('{
          noop
        }');

        $response = $this
            ->getFunctionalAppBuilder()
            ->build()
            ->getApplication()
            ->handle($request);

        self::assertEquals(200, (string) $response->getStatusCode());
        self::assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        self::assertEquals('{"data":{"noop":"noop"}}', (string) $response->getBody());
    }

    public function testGraphqlInterfaceRequestOnlyInDevelopmentMode(): void
    {
        $this
            ->getGraphqlSchemaRegistry()
            ->registerSiteSchema('test-app', $this->getNoopSchema());

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

    public function testInvalidQueryRespondsAsJsonWithStatusCode400(): void
    {
        $request = $this->createGraphqlRequest('{
          this-is-for-sure-wrong
        }');

        $response = $this
            ->getFunctionalAppBuilder()
            ->build()
            ->getApplication()
            ->handle($request);

        self::assertEquals(400, (string) $response->getStatusCode());
        self::assertEquals('application/json', $response->getHeaderLine('Content-Type'));

        $responseData = $this->decode((string) $response->getBody());
        self::assertArrayHasKey('errors', $responseData);
        self::assertCount(1, $responseData['errors']);
        self::assertArrayHasKey('message', $responseData['errors'][0]);
        self::assertStringContainsString('Invalid', $responseData['errors'][0]['message']);
    }

    public function testNoQueryRespondsAsJsonWithStatusCode400(): void
    {
        $response = $this
            ->getFunctionalAppBuilder()
            ->build()
            ->getApplication()
            ->handle(new ServerRequest('/test-app/graphql', 'POST'));

        self::assertEquals(400, (string) $response->getStatusCode());
        self::assertEquals('application/json', $response->getHeaderLine('Content-Type'));

        $responseData = $this->decode((string) $response->getBody());
        self::assertArrayHasKey('errors', $responseData);
        self::assertCount(1, $responseData['errors']);
        self::assertArrayHasKey('message', $responseData['errors'][0]);
        self::assertEquals(GraphqlController::ERROR_MESSAGE_INVALID_INPUT, $responseData['errors'][0]['message']);
    }

    public function testNoArrayQueryRespondsAsJsonWithStatusCode400(): void
    {
        $response = $this
            ->getFunctionalAppBuilder()
            ->build()
            ->getApplication()
            ->handle(new ServerRequest('/test-app/graphql', 'POST', (new StreamFactory())->createStream('"bla"')));

        self::assertEquals(400, (string) $response->getStatusCode());
        self::assertEquals('application/json', $response->getHeaderLine('Content-Type'));

        $responseData = $this->decode((string) $response->getBody());
        self::assertArrayHasKey('errors', $responseData);
        self::assertCount(1, $responseData['errors']);
        self::assertArrayHasKey('message', $responseData['errors'][0]);
        self::assertEquals(GraphqlController::ERROR_MESSAGE_INVALID_INPUT, $responseData['errors'][0]['message']);
    }
}
