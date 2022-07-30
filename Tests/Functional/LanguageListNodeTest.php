<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tests\Functional;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Node\LanguageListNode;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;

class LanguageListNodeTest extends TestCase
{
    use FunctionalTrait;

    public function testCanListLanguages(): void
    {
        $scope = $this
            ->getFunctionalScopeBuilder()
            ->withAutoCreateHomepage(true)
            ->withAutoCreateGraphqlSchema(false)
            ->build()
        ;

        $scope->getSchemaRegistry()->register(new Schema([
            'query' => new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'languages' => $scope->get(LanguageListNode::class)->getGraphqlNode()->toArray(),
                ],
            ]),
        ]));

        $response = $scope->doGraphqlRequest('{
            languages {
              title
            }
        }');

        self::assertCount(2, $response['data']['languages']);
        self::assertEquals('English', $response['data']['languages'][0]['title']);
        self::assertEquals('Austrian', $response['data']['languages'][1]['title']);
    }
}
