<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tests\Functional;

use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalScope;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;
use RozbehSharahi\Graphql3\Type\QueryType;

class LocalizationTest extends TestCase
{
    use FunctionalTrait;

    public function testPageTypeChildrenField(): void
    {
        $scope = $this->createScope();

        $scope
            ->createRecord('pages', ['pid' => 0, 'uid' => 2, 'sys_language_uid' => 1, 'title' => 'German root'])
            ->createRecord('pages', ['pid' => 1, 'uid' => 3, 'sys_language_uid' => 1, 'title' => 'German subpage'])
            ->createRecord('pages', ['pid' => 1, 'uid' => 4, 'title' => 'Some english subpage'])
        ;

        $scope
            ->getSchemaRegistry()
            ->register(new Schema(['query' => $scope->get(QueryType::class)]))
        ;

        $response = $scope->doGraphqlRequest('{ 
            pages(language: "de") {
                count
                items {
                    title
                }
            }
        }');

        self::assertSame(2, $response['data']['pages']['count']);
        self::assertCount(2, $response['data']['pages']['items']);
        self::assertSame('German root', $response['data']['pages']['items'][0]['title']);
        self::assertSame('German subpage', $response['data']['pages']['items'][1]['title']);
    }

    private function createScope(): FunctionalScope
    {
        return $this->getFunctionalScopeBuilder()->build();
    }
}
