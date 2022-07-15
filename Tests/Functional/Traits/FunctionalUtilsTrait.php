<?php

namespace RozbehSharahi\Graphql3\Tests\Functional\Traits;

use Exception;
use GraphQL\Type\Schema;
use RozbehSharahi\Graphql3\Builder\NoopSchemaBuilder;
use RozbehSharahi\Graphql3\Registry\SiteSchemaRegistry;
use RozbehSharahi\Graphql3\Tests\Functional\FunctionAppBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\StreamFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @method fail(string $message)
 */
trait FunctionalUtilsTrait
{
    protected function getFunctionalAppBuilder(): FunctionAppBuilder
    {
        return GeneralUtility::makeInstance(FunctionAppBuilder::class)
            ->withAutoCreateHomepage(true)
            ->withAutoCreateSchema(true)
            ->withAutoCreateSite(true)
            ->withAutoCreateSiteSchema(true);
    }

    protected function getSiteSchemaRegistry()
    {
        return GeneralUtility::makeInstance(SiteSchemaRegistry::class);
    }

    protected function getNoopSchema(): Schema
    {
        return (new NoopSchemaBuilder())->build();
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

    protected function createGraphqlRequest(string $graphqlBody, string $url = '/test-app/graphql'): ServerRequest
    {
        $streamFactory = new StreamFactory();

        try {
            return new ServerRequest($url, 'POST', $streamFactory->createStream(
                json_encode(['query' => $graphqlBody], JSON_THROW_ON_ERROR)
            ));
        } catch (Exception) {
            $this->fail('Could not generate graphql request in '.__METHOD__);
        }

        throw new \RuntimeException('Should never happen');
    }
}
