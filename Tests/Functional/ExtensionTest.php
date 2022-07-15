<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tests\Functional;

use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Registry\SiteSchemaRegistry;
use RozbehSharahi\Graphql3\Tests\Functional\Traits\FunctionalUtilsTrait;

class ExtensionTest extends TestCase
{
    use FunctionalUtilsTrait;

    public function testCanRegisterSiteSchema(): void
    {
        $schemaRegistry = new SiteSchemaRegistry();
        $schemaRegistry->registerSiteSchema('test-app', $this->getNoopSchema());

        $response = $this
            ->getFunctionalAppBuilder()
            ->build()
            ->getApplication()
            ->handle($this->createGraphqlRequest('{ noop }'));

        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('{"data":{"noop":"noop"}}', (string) $response->getBody());
    }

    public function testThrowsExceptionOnNoneExistingSiteSchema(): void
    {
        $this->expectExceptionMessageMatches('/No schema registered for/');
        (new SiteSchemaRegistry())->getSchema('i-dont-exist');
    }
}
