<?php

namespace RozbehSharahi\Graphql3\Tests\Functional;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Domain\Model\Context;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgument;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;
use RozbehSharahi\Graphql3\Node\PageNode;
use RozbehSharahi\Graphql3\Node\PageNodeExtenderInterface;
use RozbehSharahi\Graphql3\Resolver\PageResolver;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class PageNodeTest extends TestCase
{
    use FunctionalTrait;

    public function testCanBuildPageNodeBasedOnRegistryData(): void
    {
        $scope = $this
            ->getFunctionalScopeBuilder()
            ->withAutoCreateGraphqlSchema(false)
            ->withAutoCreateHomepage(false)
            ->build();

        $scope->createRecord('pages', ['uid' => 1, 'pid' => 0, 'title' => 'Root page']);
        $scope->createRecord('pages', ['uid' => 2, 'pid' => 1, 'title' => 'Second level page']);

        $scope->getSchemaRegistry()->register(new Schema([
            'query' => new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'page' => $scope->getPageNode()->getGraphqlNode()->toArray(),
                ],
            ]),
        ]));

        $response = $scope->doGraphqlRequest('{
            page (uid: 2) {
              title
              parent {
                title
              }
            }
        }');

        self::assertSame('Second level page', $response['data']['page']['title']);
        self::assertSame('Root page', $response['data']['page']['parent']['title']);
    }

    public function testCanExtendPageNode(): void
    {
        $scope = $this
            ->getFunctionalScopeBuilder()
            ->withAutoCreateGraphqlSchema(false)
            ->withAutoCreateHomepage(false)
            ->build();

        $scope->createRecord('pages', ['uid' => 1, 'pid' => 0, 'title' => 'Root page']);
        $scope->createRecord('pages', ['uid' => 2, 'pid' => 1, 'title' => 'Second level page', 'slug' => 'my-page']);

        $extenders = [
            new class() implements PageNodeExtenderInterface {
                public function supportsContext(Context $context): bool
                {
                    return true;
                }

                public function extendArguments(GraphqlArgumentCollection $arguments
                ): GraphqlArgumentCollection {
                    return $arguments
                        ->add(
                            GraphqlArgument::create('uid')
                                ->withDefaultValue(1)
                        )
                        ->add(
                            GraphqlArgument::create('slug')
                                ->withType(Type::nonNull(Type::string()))
                        );
                }

                public function extendQuery(QueryBuilder $query, array $arguments): QueryBuilder
                {
                    return $query
                        ->select('*')
                        ->resetQueryParts(['where'])
                        ->from('pages')
                        ->where($query->expr()->eq('slug', $query->createNamedParameter($arguments['slug'])));
                }
            },
        ];

        $scope->getSchemaRegistry()->register(new Schema([
            'query' => new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'page' => (new PageNode(
                        $scope->getPageType(),
                        new PageResolver($scope->getConnectionPool(), $extenders),
                        $extenders
                    ))->getGraphqlNode()->toArray(),
                ],
            ]),
        ]));

        $response = $scope->doGraphqlRequest('{
            page (slug: "my-page") {
              title
              parent {
                title
              }
            }
        }');

        self::assertSame('Second level page', $response['data']['page']['title']);
    }

    public function testCanUseForPageBySlug(): void
    {
        $scope = $this
            ->getFunctionalScopeBuilder()
            ->withAutoCreateGraphqlSchema(false)
            ->withAutoCreateHomepage(false)
            ->build();

        $scope->createRecord('pages', ['uid' => 1, 'pid' => 0, 'title' => 'Root page']);
        $scope->createRecord('pages', ['uid' => 2, 'pid' => 1, 'title' => 'Second level page', 'slug' => 'my-page']);

        $scope->getSchemaRegistry()->register(new Schema([
            'query' => new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'pageBySlug' => $scope
                        ->getPageNode()
                        ->forSlug()
                        ->getGraphqlNode()
                        ->toArray(),
                ],
            ]),
        ]));

        $response = $scope->doGraphqlRequest('{
            pageBySlug (slug: "my-page") {
              title
              parent {
                title
              }
            }
        }');

        self::assertSame('Second level page', $response['data']['pageBySlug']['title']);
        self::assertSame('Root page', $response['data']['pageBySlug']['parent']['title']);
    }

    public function testPageBySlugOnlyFindsInCurrentSite(): void
    {
        $scope = $this
            ->getFunctionalScopeBuilder()
            ->withAutoCreateGraphqlSchema(false)
            ->withAutoCreateHomepage(false)
            ->withSiteRootPageId(3)
            ->build();

        $scope
            ->createRecord('pages', ['uid' => 1, 'pid' => 0, 'title' => 'Root page 1'])
            ->createRecord('pages', ['uid' => 2, 'pid' => 1, 'title' => 'A page', 'slug' => '/a-page'])
            ->createRecord('pages', ['uid' => 3, 'pid' => 0, 'title' => 'Root page 2'])
            ->createRecord('pages', ['uid' => 4, 'pid' => 3, 'title' => 'Another page', 'slug' => '/a-page']);

        $scope->getSchemaRegistry()->register(new Schema([
            'query' => new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'pageBySlug' => $scope
                        ->getPageNode()
                        ->forSlug()
                        ->getGraphqlNode()
                        ->toArray(),
                ],
            ]),
        ]));

        $response = $scope->doGraphqlRequest('{
            pageBySlug (slug: "/a-page") {
              title
              parent { title }
            }
        }');

        self::assertSame('Another page', $response['data']['pageBySlug']['title']);
        self::assertSame('Root page 2', $response['data']['pageBySlug']['parent']['title']);
    }
}
