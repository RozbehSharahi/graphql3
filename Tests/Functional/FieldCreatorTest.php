<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tests\Functional;

use Doctrine\DBAL\Schema\Table;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Builder\RecordNodeBuilder;
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
}
