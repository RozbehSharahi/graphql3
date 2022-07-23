<?php

/** @noinspection DuplicatedCode */

namespace RozbehSharahi\Graphql3\Tests\Functional;

use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Controller\GraphqlController;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\StreamFactory;

class GraphqlRequestTest extends TestCase
{
    use FunctionalTrait;

    public function testCanRunAGraphqlRequest(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->build();

        $response = $scope->doServerRequest($this->createGraphqlRequest('{
          noop
        }'));

        self::assertEquals(200, (string) $response->getStatusCode());
        self::assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        self::assertEquals('{"data":{"noop":"noop"}}', (string) $response->getBody());
    }

    public function testGraphqlInterfaceRequestOnlyInDevelopmentMode(): void
    {
        $response = $this
            ->getFunctionalScopeBuilder()
            ->withContext('Production')
            ->build()
            ->doServerRequest(new ServerRequest('/test-app/graphiql'));

        self::assertEquals(404, (string) $response->getStatusCode());

        $response = $this
            ->getFunctionalScopeBuilder()
            ->withContext('Development/SomeDeveloper')
            ->build()
            ->doServerRequest(new ServerRequest('/test-app/graphiql'));

        self::assertEquals(200, (string) $response->getStatusCode());
        self::assertStringContainsString('graphQLFetcher', (string) $response->getBody());
    }

    public function testInvalidQueryRespondsAsJsonWithStatusCode400(): void
    {
        $request = $this->createGraphqlRequest('{
          this-is-for-sure-wrong
        }');

        $response = $this
            ->getFunctionalScopeBuilder()
            ->withContext('Testing/Production')
            ->build()
            ->doServerRequest($request);

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
            ->getFunctionalScopeBuilder()
            ->build()
            ->doServerRequest(new ServerRequest('/test-app/graphql', 'POST'));

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
            ->getFunctionalScopeBuilder()
            ->build()
            ->doServerRequest(
                new ServerRequest('/test-app/graphql', 'POST', (new StreamFactory())->createStream('"bla"'))
            );

        self::assertEquals(400, (string) $response->getStatusCode());
        self::assertEquals('application/json', $response->getHeaderLine('Content-Type'));

        $responseData = $this->decode((string) $response->getBody());
        self::assertArrayHasKey('errors', $responseData);
        self::assertCount(1, $responseData['errors']);
        self::assertArrayHasKey('message', $responseData['errors'][0]);
        self::assertEquals(GraphqlController::ERROR_MESSAGE_INVALID_INPUT, $responseData['errors'][0]['message']);
    }

    public function testNoSchemaActsAs404Page(): void
    {
        $response = $this
            ->getFunctionalScopeBuilder()
            ->withAutoCreateGraphqlSchema(false)
            ->build()
            ->doServerRequest(new ServerRequest('/test-app/graphql', 'POST'));

        self::assertEquals(404, $response->getStatusCode());
    }
}
