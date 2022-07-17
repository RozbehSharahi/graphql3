<?php

namespace RozbehSharahi\Graphql3\Tests\Functional;

use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;

class PageQueryTypeTest extends TestCase
{
    use FunctionalTrait;

    public function testPageQueryType(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->build();

        $scope
            ->getQueryFieldRegistry()
            ->register(
                GraphqlNode::create('page')->withResolver(fn () => 'Shall be the page')
            );

        $scope->getSchemaRegistry()->register(new Schema(['query' => $scope->getRegistryBasedQueryType()]));

        $response = $scope->doGraphqlRequest('{ page }');

        self::assertArrayHasKey('page', $response['data']);
        self::assertNotEmpty($response['data']['page']);
    }
}
