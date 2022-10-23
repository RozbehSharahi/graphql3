<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tests\Functional;

use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalScope;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;
use RozbehSharahi\Graphql3\Type\QueryType;

class PageTest extends TestCase
{
    use FunctionalTrait;

    public function testPageTypeChildrenField(): void
    {
        $scope = $this->createScope();

        $scope
            ->createRecord('pages', ['pid' => 1, 'uid' => 2, 'title' => 'First subpage'])
            ->createRecord('pages', ['pid' => 1, 'uid' => 3, 'title' => 'Second subpage'])
        ;

        $scope
            ->getSchemaRegistry()
            ->register(new Schema(['query' => $scope->get(QueryType::class)]))
        ;

        $response = $scope->doGraphqlRequest('{ 
            page(uid: 1) {
                title
                children {
                    items {
                        title
                    }
                }
            }
        }');

        self::assertArrayHasKey('page', $response['data']);
        self::assertNotEmpty($response['data']['page']);
        self::assertSame('root page', $response['data']['page']['title']);
        self::assertCount(2, $response['data']['page']['children']['items']);
        self::assertSame('First subpage', $response['data']['page']['children']['items'][0]['title']);
        self::assertSame('Second subpage', $response['data']['page']['children']['items'][1]['title']);
    }

    private function createScope(): FunctionalScope
    {
        return $this->getFunctionalScopeBuilder()->build();
    }
}
