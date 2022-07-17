<?php

namespace RozbehSharahi\Graphql3\Tests\Functional;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;

class PageArgumentRegistryTest extends TestCase
{
    use FunctionalTrait;

    public function testCanUseRegistryBasedPageArguments(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->build();
        $scope->getPageArgumentsSetup()->setup();

        $pageArgumentRegistry = $scope->getPageArgumentRegistry();

        $scope->getSchemaRegistry()->register(new Schema([
            'query' => new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'page' => [
                        'args' => $pageArgumentRegistry->getArguments()->toArray(),
                        'type' => new ObjectType([
                            'name' => 'Page',
                            'fields' => ['uid' => ['type' => Type::int(), 'resolve' => fn ($v) => $v['uid']]],
                        ]),
                        'resolve' => fn ($_, $args) => ['uid' => $args['uid']],
                    ],
                ],
            ]),
        ]));

        $response = $scope->doGraphqlRequest('{ page(uid: 1234) { uid } }');
        self::assertNotEmpty($response);
        self::assertSame(1234, $response['data']['page']['uid']);
    }
}
