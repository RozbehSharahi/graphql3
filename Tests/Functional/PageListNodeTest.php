<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tests\Functional;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;

class PageListNodeTest extends TestCase
{
    use FunctionalTrait;

    public function testCanListPages(): void
    {
        $scope = $this
            ->getFunctionalScopeBuilder()
            ->withAutoCreateHomepage(false)
            ->withAutoCreateGraphqlSchema(false)
            ->build()
        ;

        $scope
            ->createRecord('pages', ['uid' => 1, 'title' => 'Page 1'])
            ->createRecord('pages', ['uid' => 2, 'title' => 'Page 2'])
            ->createRecord('pages', ['uid' => 3, 'title' => 'Page 3'])
            ->createRecord('pages', ['uid' => 4, 'title' => 'Page 4'])
        ;

        $scope->getSchemaRegistry()->register(new Schema([
            'query' => new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'pages' => $scope->getPageListNode()->getGraphqlNode()->toArray(),
                ],
            ]),
        ]));

        $response = $scope->doGraphqlRequest('{
            pages(page: 1, pageSize:2, orderBy: [{field: "title", direction: "desc"}]) {
              count
              items {
                title
              }
            }
        }');

        self::assertArrayNotHasKey('errors', $response);
        self::assertCount(2, $response['data']['pages']['items']);
        self::assertEquals(4, $response['data']['pages']['count']);
        self::assertEquals('Page 4', $response['data']['pages']['items'][0]['title']);
        self::assertEquals('Page 3', $response['data']['pages']['items'][1]['title']);

        $response = $scope->doGraphqlRequest('{
            pages(page: 2, pageSize:2, orderBy: {field: "uid", direction: "desc"}) {
              count
              items {
                title
              }
            }
        }');

        self::assertEquals('Page 2', $response['data']['pages']['items'][0]['title']);
        self::assertEquals('Page 1', $response['data']['pages']['items'][1]['title']);
    }

    public function testCanFilterPageList(): void
    {
        $scope = $this
            ->getFunctionalScopeBuilder()
            ->withAutoCreateHomepage(false)
            ->withAutoCreateGraphqlSchema(false)
            ->build()
        ;

        $scope
            ->createRecord('pages', ['uid' => 1, 'title' => 'Page 1', 'subtitle' => 'red'])
            ->createRecord('pages', ['uid' => 2, 'title' => 'Page 2', 'subtitle' => 'green'])
            ->createRecord('pages', ['uid' => 3, 'title' => 'Page 3', 'subtitle' => 'blue'])
            ->createRecord('pages', ['uid' => 4, 'title' => 'Page 4', 'subtitle' => 'red'])
            ->createRecord('pages', ['uid' => 5, 'title' => 'Page 5', 'subtitle' => 'black'])
            ->createRecord('pages', ['uid' => 6, 'title' => 'Page 6', 'subtitle' => 'yellow'])
            ->createRecord('pages', ['uid' => 7, 'title' => 'Page 7', 'subtitle' => 'pink'])
        ;

        $scope->getSchemaRegistry()->register(new Schema([
            'query' => new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'pages' => $scope->getPageListNode()->getGraphqlNode()->toArray(),
                ],
            ]),
        ]));

        $response = $scope->doGraphqlRequest('{
            pages(filters: [{field: "subtitle", value: "red"}, {type: "gte", field: "uid", value: "4"}]) {
              count
              items {
                title
              }
            }
        }');

        self::assertCount(1, $response['data']['pages']['items']);
        self::assertEquals(1, $response['data']['pages']['count']);
        self::assertEquals('Page 4', $response['data']['pages']['items'][0]['title']);

        $response = $scope->doGraphqlRequest('{
            pages(filters: {type: "in", field: "subtitle", values: ["red","green"]}) {
              count
            }
        }');

        self::assertEquals(3, $response['data']['pages']['count']);
    }
}
