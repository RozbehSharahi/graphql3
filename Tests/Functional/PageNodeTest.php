<?php

namespace RozbehSharahi\Graphql3\Tests\Functional;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;

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
}
