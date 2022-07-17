<?php

namespace RozbehSharahi\Graphql3\Tests\Functional;

use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;

class PageNodeBuilderTest extends TestCase
{
    use FunctionalTrait;

    public function testCanBuildPageNodeBasedOnRegistryData(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->withAutoCreateGraphqlSchema(false)->build();

        $scope->getTypesSetup()->setup();
        $scope->getPageArgumentsSetup()->setup();

        $scope->getSchemaRegistry()->register(new Schema([
            'query' => $scope->getRegistryBasedQueryType(),
        ]));

        $scope->getQueryFieldRegistry()
            // A simple query field
            ->register(GraphqlNode::create('returnA')->withResolver(fn () => 'A'))
            // The page node
            ->register($scope->getPageNodeBuilder()->build());

        // Now extend page type
        $scope->getPageFieldRegistry()
            ->register(GraphqlNode::create('returnB')->withResolver(fn () => 'B'));

        $response = $scope->getGraphqlController()->graphqlAction($this->createGraphqlRequest('{
            returnA
            page (uid: 1) {
              returnB
            }
        }'));
        $responseData = $this->decode((string) $response->getBody());

        self::assertSame('A', $responseData['data']['returnA']);
        self::assertSame('B', $responseData['data']['page']['returnB']);
    }
}
