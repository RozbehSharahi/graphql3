<?php

namespace RozbehSharahi\Graphql3\Tests\Functional;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;
use RozbehSharahi\Graphql3\Type\PageType;
use RozbehSharahi\Graphql3\Type\PageTypeExtenderInterface;

class PageTypeTest extends TestCase
{
    use FunctionalTrait;

    public function testPageQueryType(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->build();

        // Register schema
        $scope
            ->getSchemaRegistry()
            ->register(new Schema([
                'query' => new ObjectType([
                    'name' => 'Query',
                    'fields' => [
                        'page' => [
                            'type' => $scope->getPageType(),
                            'resolve' => fn () => ['uid' => 12345, 'pid' => 1, 'title' => 'My title'],
                        ],
                    ],
                ]),
            ]));

        $response = $scope->doGraphqlRequest('{ 
            page {
                uid
                title
                parent {
                  title
                }
                __typename 
            }
        }');

        self::assertArrayHasKey('page', $response['data']);
        self::assertNotEmpty($response['data']['page']);
        self::assertSame(12345, $response['data']['page']['uid']);
        self::assertSame('My title', $response['data']['page']['title']);
        self::assertSame('root page', $response['data']['page']['parent']['title']);
        self::assertSame('Page', $response['data']['page']['__typename']);
    }

    public function testCanExtendPageType(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->build();

        // Register schema
        $scope
            ->getSchemaRegistry()
            ->register(new Schema([
                'query' => new ObjectType([
                    'name' => 'Query',
                    'fields' => [
                        'page' => [
                            'type' => new PageType($scope->getRecordResolver(), [
                                new class() implements PageTypeExtenderInterface {
                                    public function extendNodes(GraphqlNodeCollection $nodes): GraphqlNodeCollection
                                    {
                                        return $nodes->add(
                                            GraphqlNode::create('titleHash')
                                                ->withResolver(fn ($page) => md5($page['title']))
                                        );
                                    }
                                },
                            ]),
                            'resolve' => fn () => ['uid' => 12345, 'pid' => 0, 'title' => 'My title'],
                        ],
                    ],
                ]),
            ]));

        $response = $scope->doGraphqlRequest('{ 
            page {
                uid
                titleHash 
            }
        }');

        self::assertSame(md5('My title'), $response['data']['page']['titleHash']);
    }
}
