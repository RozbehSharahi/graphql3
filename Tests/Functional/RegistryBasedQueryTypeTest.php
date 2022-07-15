<?php

/** @noinspection DuplicatedCode */

namespace RozbehSharahi\Graphql3\Tests\Functional;

use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;

class RegistryBasedQueryTypeTest extends TestCase
{
    use FunctionalTrait;

    public function testCanUseRegistryBasedQueryType(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->withAutoCreateGraphqlSchema(false)->build();

        $scope
            ->getQueryFieldRegistry()
            ->register(
                GraphqlNode::create('sayHello')->withResolver(fn () => 'hi')
            );

        $scope->getSchemaRegistry()->register(new Schema(['query' => $scope->getRegistryBasedQueryType()]));

        $response = $scope->doServerRequest($this->createGraphqlRequest('{
            sayHello
        }'));

        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('{"data":{"sayHello":"hi"}}', (string) $response->getBody());
    }
}
