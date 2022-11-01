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
            ->registerCreator(fn () => new Schema(['query' => $scope->get(QueryType::class)]))
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

    public function testCanFetchMediaOnContent(): void
    {
        $scope = $this->createScope();

        $scope
            ->createRecord('sys_file', [
                'uid' => 200,
                'identifier' => '/user_upload/whatever.txt',
                'extension' => 'txt',
                'name' => 'whatever.txt',
            ])
            ->createRecord('sys_file', [
                'uid' => 300,
                'identifier' => '/user_upload/whatever-2.txt',
                'extension' => 'txt',
                'name' => 'whatever-2.txt',
            ])
            ->createRecord('sys_file_reference', [
                'uid' => 1,
                'tablenames' => 'pages',
                'fieldname' => 'media',
                'uid_local' => 200,
                'uid_foreign' => 1,
            ])
            ->createRecord('sys_file_reference', [
                'uid' => 2,
                'tablenames' => 'different-entity',
                'fieldname' => 'some-thing-else',
                'uid_local' => 200,
                'uid_foreign' => 9999,
            ])
            ->createRecord('sys_file_reference', [
                'uid' => 3,
                'tablenames' => 'different-entity',
                'fieldname' => 'some-thing-else',
                'uid_local' => 300,
                'uid_foreign' => 1,
            ])
        ;

        $scope
            ->getSchemaRegistry()
            ->registerCreator(fn () => new Schema(['query' => $scope->get(QueryType::class)]))
        ;

        $response = $scope->doGraphqlRequest('{ 
            page(uid: 1) {
                title
                media {
                  uid
                  publicUrl
                  extension
                  imageUrl
                }
            }
        }');

        self::assertArrayHasKey('page', $response['data']);
        self::assertNotEmpty($response['data']['page']);
        self::assertSame('root page', $response['data']['page']['title']);
        self::assertCount(1, $response['data']['page']['media']);
        self::assertSame(1, $response['data']['page']['media'][0]['uid']);
        self::assertSame('user_upload/whatever.txt', $response['data']['page']['media'][0]['publicUrl']);
        self::assertSame('txt', $response['data']['page']['media'][0]['extension']);
        self::assertNull($response['data']['page']['media'][0]['imageUrl']);
    }

    private function createScope(): FunctionalScope
    {
        return $this->getFunctionalScopeBuilder()->build();
    }
}
