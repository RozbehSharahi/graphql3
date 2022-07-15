<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tests\Functional;

use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Registry\SiteSchemaRegistry;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;

class SiteSchemaRegistryTest extends TestCase
{
    use FunctionalTrait;

    public function testCanRegisterSiteSchema(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->build();
        $response = $scope->doServerRequest($this->createGraphqlRequest('{ noop }'));

        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('{"data":{"noop":"noop"}}', (string) $response->getBody());
    }

    public function testThrowsExceptionOnNoneExistingSiteSchema(): void
    {
        $this->expectExceptionMessageMatches('/No schema registered for/');
        (new SiteSchemaRegistry())->getSchema('i-dont-exist');
    }
}
