<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tests\Functional;

use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;
use RozbehSharahi\Graphql3\Type\QueryType;
use RozbehSharahi\Graphql3\Type\QueryTypeExtenderInterface;

class QueryTypeTest extends TestCase
{
    use FunctionalTrait;

    public function testHasWorkingPageNode(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->withAutoCreateGraphqlSchema(false)->build();

        $scope->getSchemaRegistry()->register(new Schema([
            'query' => $scope->getQueryType(),
        ]));

        $response = $scope->doGraphqlRequest('{
            page(uid: 1) {
              title
            }
        }');

        self::assertEquals('root page', $response['data']['page']['title']);
    }

    public function testCanExtendQueryType(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->withAutoCreateGraphqlSchema(false)->build();

        $scope->getSchemaRegistry()->register(new Schema([
            'query' => new QueryType($scope->getPageNode(), [
                new class() implements QueryTypeExtenderInterface {
                    public function extend(GraphqlNodeCollection $nodes): GraphqlNodeCollection
                    {
                        return $nodes
                            ->add(GraphqlNode::create('extraNode')->withResolver(fn () => 'Hello World'));
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
