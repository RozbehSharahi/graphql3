<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tests\Functional;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Node\LanguageNode;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;

class LanguageNodeTest extends TestCase
{
    use FunctionalTrait;

    public function testCanGetLanguagesById(): void
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
                    'language' => $scope->get(LanguageNode::class)->getGraphqlNode()->toArray(),
                ],
            ]),
        ]));

        $response = $scope->doGraphqlRequest('{
            first: language(id: 0) { title }
            second: language(id: 1) { title }
            third: language(id: 2) { title }
        }');

        self::assertEquals('English', $response['data']['first']['title']);
        self::assertEquals('Austrian', $response['data']['second']['title']);
        self::assertNull($response['data']['third']);
    }
}
