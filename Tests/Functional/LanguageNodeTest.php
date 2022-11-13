<?php

/** @noinspection PhpRedundantVariableDocTypeInspection */

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tests\Functional;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Builder\LanguageNodeBuilder;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalScope;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;

class LanguageNodeTest extends TestCase
{
    use FunctionalTrait;

    public function testCanGetLanguagesById(): void
    {
        /** @var FunctionalScope $scope */
        $scope = $this
            ->getFunctionalScopeBuilder()
            ->withAutoCreateHomepage(true)
            ->withAutoCreateGraphqlSchema(false)
            ->build()
        ;

        $scope->get(SchemaRegistry::class)->registerCreator(fn () => new Schema([
            'query' => new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'language' => $scope->get(LanguageNodeBuilder::class)->build()->toArray(),
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
