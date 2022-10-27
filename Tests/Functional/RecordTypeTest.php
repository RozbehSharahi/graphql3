<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tests\Functional;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Builder\Type\RecordTypeBuilder;
use RozbehSharahi\Graphql3\Builder\Type\RecordTypeBuilderExtenderInterface;
use RozbehSharahi\Graphql3\Converter\CaseConverter;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use RozbehSharahi\Graphql3\Domain\Model\Record;
use RozbehSharahi\Graphql3\Domain\Model\Tca\TableConfiguration;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalScope;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;

class RecordTypeTest extends TestCase
{
    use FunctionalTrait;

    public function testCanCreatePageType(): void
    {
        $scope = $this->createScope();

        // Register schema
        $scope
            ->getSchemaRegistry()
            ->register(new Schema([
                'query' => new ObjectType([
                    'name' => 'Query',
                    'fields' => [
                        'page' => [
                            'type' => $scope->get(RecordTypeBuilder::class)->for('pages')->build(),
                            'resolve' => fn () => Record::create('pages', [
                                'uid' => 12345,
                                'pid' => 1,
                                'title' => 'My title',
                            ]),
                        ],
                    ],
                ]),
            ]))
        ;

        $response = $scope->doGraphqlRequest('{ 
            page {
                uid
                title
                language {
                  id
                  title
                }
                parentPage {
                  title
                }
                __typename 
            }
        }');

        self::assertArrayHasKey('page', $response['data']);
        self::assertNotEmpty($response['data']['page']);
        self::assertSame(12345, $response['data']['page']['uid']);
        self::assertSame('My title', $response['data']['page']['title']);
        self::assertSame('root page', $response['data']['page']['parentPage']['title']);
        self::assertSame(0, $response['data']['page']['language']['id']);
        self::assertSame('English', $response['data']['page']['language']['title']);
        self::assertSame('Page', $response['data']['page']['__typename']);
    }

    public function testCanExtendRecordTypeBuilder(): void
    {
        $scope = $this->createScope();

        $recordTypeBuilder = new RecordTypeBuilder($scope->get(CaseConverter::class), [], [
            new class() implements RecordTypeBuilderExtenderInterface {
                public function supportsTable(TableConfiguration $table): bool
                {
                    return 'pages' === $table->getName();
                }

                public function extendNodes(
                    TableConfiguration $table,
                    GraphqlNodeCollection $nodes
                ): GraphqlNodeCollection {
                    return $nodes->add(
                        GraphqlNode::create('titleHash')
                            ->withResolver(fn ($page) => md5($page['title']))
                    );
                }
            },
        ]);

        $scope->set(RecordTypeBuilder::class, $recordTypeBuilder);

        // Register schema
        $scope
            ->getSchemaRegistry()
            ->register(new Schema([
                'query' => new ObjectType([
                    'name' => 'Query',
                    'fields' => [
                        'page' => [
                            'type' => $recordTypeBuilder->for('pages')->build(),
                            'resolve' => fn () => ['uid' => 12345, 'pid' => 0, 'title' => 'My title'],
                        ],
                    ],
                ]),
            ]))
        ;

        $response = $scope->doGraphqlRequest('{ 
            page {
                titleHash 
            }
        }');

        self::assertSame(md5('My title'), $response['data']['page']['titleHash']);
    }

    private function createScope(): FunctionalScope
    {
        return $this->getFunctionalScopeBuilder()->build();
    }
}
