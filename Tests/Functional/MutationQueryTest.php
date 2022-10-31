<?php

/** @noinspection PhpRedundantVariableDocTypeInspection */

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tests\Functional;

use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalScope;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;
use RozbehSharahi\Graphql3\Type\MutationType;

class MutationQueryTest extends TestCase
{
    use FunctionalTrait;

    public function testCanCreateSysNews(): void
    {
        /** @var FunctionalScope $scope */
        $scope = $this
            ->getFunctionalScopeBuilder()
            ->withAutoCreateGraphqlSchema(false)
            ->build()
        ;

        $scope->get(SchemaRegistry::class)->register(new Schema([
            'mutation' => $scope->get(MutationType::class),
        ]));

        $response = $scope->graphqlRequest('
            mutation {
              createSysNews(item: {
                title: "A news"
                content: "Some content"
              })
            }
        ');

        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals(1, $response->get('data.createSysNews'));
    }
}
