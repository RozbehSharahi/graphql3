<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tests\Functional;

use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalScope;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;
use RozbehSharahi\Graphql3\Type\QueryType;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;

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

    public function testPagesAreFilteredByDoktype(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->build();

        $scope->getSchemaRegistry()->registerCreator(
            fn () => new Schema(['query' => $scope->get(QueryType::class)])
        );

        $scope
            ->createRecord('pages', [
                'pid' => 1,
                'title' => 'some title',
                'doktype' => PageRepository::DOKTYPE_DEFAULT,
            ])
            ->createRecord('pages', [
                'pid' => 1,
                'title' => 'some title',
                'doktype' => PageRepository::DOKTYPE_SHORTCUT,
            ])
            ->createRecord('pages', [
                'pid' => 1,
                'title' => 'some title',
                'doktype' => PageRepository::DOKTYPE_BE_USER_SECTION,
            ])
            ->createRecord('pages', [
                'pid' => 1,
                'title' => 'some title',
                'doktype' => PageRepository::DOKTYPE_LINK,
            ])
            ->createRecord('pages', [
                'pid' => 1,
                'title' => 'some title',
                'doktype' => PageRepository::DOKTYPE_MOUNTPOINT,
            ])
            ->createRecord('pages', [
                'pid' => 1,
                'title' => 'some title',
                'doktype' => PageRepository::DOKTYPE_RECYCLER,
            ])
            ->createRecord('pages', [
                'pid' => 1,
                'title' => 'some title',
                'doktype' => PageRepository::DOKTYPE_SPACER,
            ])
            ->createRecord('pages', [
                'pid' => 1,
                'title' => 'some title',
                'doktype' => PageRepository::DOKTYPE_SYSFOLDER,
            ])
        ;

        $response = $scope->graphqlRequest('{
            pages {
                count
                items {
                    doktype
                }
            }
        }');

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(5, $response->get('data.pages.count')); // don't forget homepage

        $doktypes = array_map(static fn ($v) => $v['doktype'], $response->get('data.pages.items'));
        self::assertContainsEquals('0', $doktypes);
        self::assertContainsEquals(PageRepository::DOKTYPE_DEFAULT, $doktypes);
        self::assertContainsEquals(PageRepository::DOKTYPE_SHORTCUT, $doktypes);
        self::assertContainsEquals(PageRepository::DOKTYPE_LINK, $doktypes);
        self::assertContainsEquals(PageRepository::DOKTYPE_SPACER, $doktypes);

        $response = $scope->graphqlRequest('{
            pages(allDoktypes: true) {
                count
                items {
                    doktype
                }
            }
        }');

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(9, $response->get('data.pages.count')); // don't forget homepage

        $doktypes = array_map(static fn ($v) => $v['doktype'], $response->get('data.pages.items'));
        self::assertContainsEquals(PageRepository::DOKTYPE_SYSFOLDER, $doktypes);
    }

    public function testPageChildrenHasNoLanguageAttribute(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->build();

        $scope->get(SchemaRegistry::class)->registerCreator(fn () => new Schema([
            'query' => $scope->get(QueryType::class),
        ]));

        $response = $scope->graphqlRequest('{
            page(uid: 1) {
                children(language: "en") {
                    count
                }
            }
        }');

        self::assertSame(400, $response->getStatusCode());
        self::assertStringContainsString('Unknown argument "language"', $response->getErrorMessage());
    }

    private function createScope(): FunctionalScope
    {
        return $this->getFunctionalScopeBuilder()->build();
    }
}
