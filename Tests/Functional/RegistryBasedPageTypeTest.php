<?php

namespace RozbehSharahi\Graphql3\Tests\Functional;

use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;
use RozbehSharahi\Graphql3\Type\RegistryBasedPageType;

class RegistryBasedPageTypeTest extends TestCase
{
    use FunctionalTrait;

    public function testPageQueryType(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->build();
        $scope->get(RegistryBasedPageType::class)->setup();

        // Register schema
        $scope
            ->getSchemaRegistry()
            ->register(new Schema(['query' => $scope->getRegistryBasedQueryType()]));

        // Register root field for page
        $scope
            ->getQueryFieldRegistry()
            ->register(
                GraphqlNode::create('page')
                    ->withType($scope->getTypeRegistry()->get(RegistryBasedPageType::class))
                    ->withResolver(fn () => ['uid' => 12345, 'title' => 'My title'])
            );

        $response = $scope->doGraphqlRequest('{ 
            page { 
                uid
                title
                __typename 
            }
        }');

        self::assertArrayHasKey('page', $response['data']);
        self::assertNotEmpty($response['data']['page']);
        self::assertSame(12345, $response['data']['page']['uid']);
        self::assertSame('My title', $response['data']['page']['title']);
        self::assertSame('Page', $response['data']['page']['__typename']);
    }
}
