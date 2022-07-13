<?php

/** @noinspection DuplicatedCode */

namespace RozbehSharahi\Graphql3\Tests\Functional;

use Exception;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Controller\GraphqlController;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\StreamFactory;

class GraphqlRequestTest extends TestCase
{
    public function testCanRunAGraphqlRequest(): void
    {
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
          this-is-definitly-wrong
        }');

        $response = $this
            ->getFunctionalAppBuilder()
            ->build()
            ->getApplication()
            ->handle($request);

        $responseData = $this->decode((string) $response->getBody());

        self::assertEquals(400, (string) $response->getStatusCode());
        self::assertEquals('application/json', $response->getHeaderLine('Content-Type'));
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

        $responseData = $this->decode((string) $response->getBody());

        self::assertEquals(400, (string) $response->getStatusCode());
        self::assertEquals('application/json', $response->getHeaderLine('Content-Type'));
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

        $responseData = $this->decode((string) $response->getBody());

        self::assertEquals(400, (string) $response->getStatusCode());
        self::assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        self::assertArrayHasKey('errors', $responseData);
        self::assertCount(1, $responseData['errors']);
        self::assertArrayHasKey('message', $responseData['errors'][0]);
        self::assertEquals(GraphqlController::ERROR_MESSAGE_INVALID_INPUT, $responseData['errors'][0]['message']);
    }

    public function getFunctionalAppBuilder(): FunctionAppBuilder
    {
        return (new FunctionAppBuilder())
            ->withAutoCreateHomepage(true)
            ->withAutoCreateSchema(true)
            ->withAutoCreateSite(true);
    }

    protected function createGraphqlRequest(string $graphqlBody): ServerRequest
    {
        $streamFactory = new StreamFactory();

        try {
            return new ServerRequest('/test-app/graphql', 'POST', $streamFactory->createStream(
                json_encode(['query' => $graphqlBody], JSON_THROW_ON_ERROR)
            ));
        } catch (Exception) {
            $this->fail('Could not generate graphql request in '.__METHOD__);
        }
    }

    protected function decode(string $json): mixed
    {
        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception) {
            $this->fail('wrong json given in test '.self::class);
        }

        return $data;
    }
}
