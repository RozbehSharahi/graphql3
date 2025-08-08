<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tests\Functional;

use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Builder\LanguageListNodeBuilder;
use RozbehSharahi\Graphql3\Builder\LanguageNodeBuilder;
use RozbehSharahi\Graphql3\Builder\RecordListNodeBuilder;
use RozbehSharahi\Graphql3\Builder\RecordNodeBuilder;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;
use RozbehSharahi\Graphql3\Type\QueryType;
use RozbehSharahi\Graphql3\Type\QueryTypeExtenderInterface;

class QueryTypeTest extends TestCase
{
    use FunctionalTrait;

    public function testHasWorkingPageNode(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->build();

        $response = $scope->graphqlRequest('{
            page(uid: 1) {
              title
            }
        }');

        self::assertEquals('root page', $response->get('data.page.title'));
    }

    public function testCanExtendQueryType(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->withAutoCreateGraphqlSchema(false)->build();

        $scope->get(SchemaRegistry::class)->registerCreator(fn () => new Schema([
            'query' => new QueryType(
                $scope->get(RecordNodeBuilder::class),
                $scope->get(RecordListNodeBuilder::class),
                $scope->get(LanguageNodeBuilder::class),
                $scope->get(LanguageListNodeBuilder::class),
                [
                    new class implements QueryTypeExtenderInterface {
                        public function extend(GraphqlNodeCollection $nodes): GraphqlNodeCollection
                        {
                            return $nodes
                                ->add(GraphqlNode::create('extraNode')->withResolver(fn () => 'Hello World'))
                            ;
                        }
                    },
                ]),
        ]));

        $response = $scope->doGraphqlRequest('{
            extraNode
        }');

        self::assertEquals('Hello World', $response['data']['extraNode']);
    }
}
