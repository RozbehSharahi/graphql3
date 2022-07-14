<?php

namespace RozbehSharahi\Graphql3\Tests\Functional\Traits;

use Exception;
use RozbehSharahi\Graphql3\Tests\Functional\FunctionAppBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\StreamFactory;

/**
 * @method fail(string $message)
 */
trait FunctionalUtilsTrait
{
    public function getFunctionalAppBuilder(): FunctionAppBuilder
    {
        return (new FunctionAppBuilder())
            ->withAutoCreateHomepage(true)
            ->withAutoCreateSchema(true)
            ->withAutoCreateSite(true);
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

        throw new \RuntimeException('Should never happen');
    }
}
