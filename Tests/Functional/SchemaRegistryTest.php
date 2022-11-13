<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tests\Functional;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;

class SchemaRegistryTest extends TestCase
{
    use FunctionalTrait;

    public function testCanRegisterSchema(): void
    {
        $scope = $this
            ->getFunctionalScopeBuilder()
            ->withAutoCreateGraphqlSchema(false)
            ->build()
        ;

        $scope->get(SchemaRegistry::class)->registerCreator(fn () => new Schema([
            'query' => new ObjectType([
                'name' => 'Query',
                'fields' => GraphqlNodeCollection::create([
                    GraphqlNode::create('noop')->withResolver(fn () => 'noop'),
                ])->toArray(),
            ]),
        ]));

        $response = $scope->doServerRequest($this->createGraphqlRequest('{ noop }'));

        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('{"data":{"noop":"noop"}}', (string) $response->getBody());
    }

    public function testThrowsExceptionOnNoneRegisteredSchema(): void
    {
        $this->expectExceptionMessageMatches('/No schema registered/');
        (new SchemaRegistry())->create();
    }
}
