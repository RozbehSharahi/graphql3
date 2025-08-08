<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tests\Functional;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\TableDiff;
use Doctrine\DBAL\Types\Type;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Builder\RecordNodeBuilder;
use RozbehSharahi\Graphql3\Environment\Typo3Environment;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;

class FieldCreatorTest extends TestCase
{
    use FunctionalTrait;

    public function testCanResolveManyToManyFields(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->build();

        $table = (new Table('tx_pages_sys_news_mm'));
        $table->addColumn('uid_local', 'integer');
        $table->addColumn('uid_foreign', 'integer');
        $scope->createTable($table);

        $scope
            ->createRecord('sys_news', [
                'uid' => 1,
                'title' => 'First news',
            ])
            ->createRecord('sys_news', [
                'uid' => 2,
                'title' => 'Second news',
            ])
            ->createRecord('tx_pages_sys_news_mm', [
                'uid_local' => 1,
                'uid_foreign' => 1,
            ])
            ->createRecord('tx_pages_sys_news_mm', [
                'uid_local' => 1,
                'uid_foreign' => 2,
            ])
        ;

        $recordNodeBuilder = $scope->get(RecordNodeBuilder::class);
        $scope->get(SchemaRegistry::class)->registerCreator(fn () => new Schema([
            'query' => new ObjectType([
                'name' => 'Query',
                'fields' => fn () => [
                    'sysNews' => $recordNodeBuilder->for('sys_news')->build()->toArray(),
                    'page' => $recordNodeBuilder->for('pages')->build()->toArray(),
                ],
            ]),
        ]));

        $GLOBALS['TCA']['pages']['columns']['news'] = [
            'label' => 'News',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'MM' => 'tx_pages_sys_news_mm',
                'foreign_table' => 'sys_news',
            ],
        ];

        $GLOBALS['TCA']['sys_news']['columns']['pages'] = [
            'label' => 'News',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'MM' => 'tx_pages_sys_news_mm',
                'foreign_table' => 'pages',
                'MM_opposite_field' => 'news',
            ],
        ];

        $response = $scope->graphqlRequest('{
          page(uid: 1) {
            news {
              count
              items {
                uid
                title
              }
            }
          }
          sysNews(uid: 1) {
            pages {
              items {
                title
              }
            }
          }
        }');

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(2, $response->get('data.page.news.count'));
        self::assertSame('First news', $response->get('data.page.news.items.0.title'));
        self::assertSame('Second news', $response->get('data.page.news.items.1.title'));
        self::assertSame('root page', $response->get('data.sysNews.pages.items.0.title'));
    }

    public function testCanSetListArgumentsAlsoIfNested(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->build();
        $scope
            ->createRecord('pages', ['pid' => 1, 'title' => 'page 1'])
            ->createRecord('pages', ['pid' => 1, 'title' => 'page 2'])
            ->createRecord('pages', ['pid' => 1, 'title' => 'page 3'])
            ->createRecord('pages', ['pid' => 1, 'title' => 'page 4'])
            ->createRecord('pages', ['pid' => 1, 'title' => 'page 5'])
            ->createRecord('pages', ['pid' => 1, 'title' => 'page 6'])
        ;

        $response = $scope->graphqlRequest('{
            page(uid: 1) {
                title
                children(page: 2, pageSize: 2) {
                  count
                  items {
                    title
                  }
                }
            }
        }');

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(6, $response->get('data.page.children.count'));
        self::assertCount(2, $response->get('data.page.children.items'));
        self::assertSame('page 3', $response->get('data.page.children.items.0.title'));
        self::assertSame('page 4', $response->get('data.page.children.items.1.title'));
    }

    public function testCanCreateOneToManyRelations(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->build();

        $scope
            ->createRecord('sys_news', ['pid' => 1, 'title' => 'News 1'])
            ->createRecord('sys_news', ['pid' => 1, 'title' => 'News 2'])
            ->createRecord('sys_news', ['pid' => 1, 'title' => 'News 3 (hidden)', 'hidden' => 1])
        ;

        $GLOBALS['TCA']['pages']['columns']['news'] = [
            'label' => 'News',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'sys_news',
                'foreign_field' => 'pid',
            ],
        ];

        $response = $scope->graphqlRequest('{
            page(uid: 1) {
                news(page: 2, pageSize: 1) {
                    count
                    items {
                        title
                    }
                }
            }
        }');

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(2, $response->get('data.page.news.count'));
        self::assertSame('News 2', $response->get('data.page.news.items.0.title'));
    }

    public function testCanCreateManyToOneRelation(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->withAutoCreateHomepage(false)->build();

        $table = (new Typo3Environment())->isVersion(13) ? new Table('pages') : 'pages';

        $tableDiff = new TableDiff($table, [
            (new Column('news', Type::getType('integer')))->setNotnull(false),
        ]);

        $scope->updateTable($tableDiff);

        $scope
            ->createRecord('pages', ['uid' => 1, 'title' => 'root-page', 'news' => 555])
            ->createRecord('sys_news', ['uid' => 555, 'pid' => 1, 'title' => 'News 555'])
        ;

        $GLOBALS['TCA']['pages']['columns']['news'] = [
            'label' => 'News',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'sys_news',
            ],
        ];

        $response = $scope->graphqlRequest('{
            page(uid: 1) {
                title
                news {
                    title
                }
            }
        }');

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('News 555', $response->get('data.page.news.title'));
    }

    public function testLanguageParentHasNoUidParameter(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->build();

        $response = $scope->graphqlRequest('{
          page(uid: 1) {
            languageParent(uid: 123) {
                title
            }
          }
        }');

        self::assertSame(400, $response->getStatusCode());
        self::assertStringContainsString('Unknown argument "uid"', $response->getErrorMessage());
    }
}
