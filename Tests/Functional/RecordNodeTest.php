<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tests\Functional;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Builder\RecordNodeBuilder;
use RozbehSharahi\Graphql3\Builder\RecordNodeExtenderInterface;
use RozbehSharahi\Graphql3\Builder\RecordTypeBuilder;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgument;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;
use RozbehSharahi\Graphql3\Domain\Model\Tca\TableConfiguration;
use RozbehSharahi\Graphql3\Resolver\RecordResolver;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;

class RecordNodeTest extends TestCase
{
    use FunctionalTrait;

    public function testCanExtendRecordNode(): void
    {
        $scope = $this
            ->getFunctionalScopeBuilder()
            ->withAutoCreateGraphqlSchema(false)
            ->withAutoCreateHomepage(false)
            ->build()
        ;

        $scope->createRecord('pages', ['uid' => 1, 'pid' => 0, 'title' => 'Root page']);
        $scope->createRecord('pages', [
            'uid' => 2,
            'pid' => 1,
            'title' => 'Second level page',
            'slug' => 'my-page',
            'hidden' => 1,
        ]);

        $extenders = [
            new class() implements RecordNodeExtenderInterface {
                public function supportsTable(TableConfiguration $table): bool
                {
                    return 'pages' === $table->getName();
                }

                public function extendArguments(
                    TableConfiguration $table,
                    GraphqlArgumentCollection $arguments
                ): GraphqlArgumentCollection {
                    return $arguments
                        ->add(GraphqlArgument::create('removeRestrictions')->withType(Type::boolean()))
                    ;
                }
            },
        ];

        $scope->getSchemaRegistry()->registerCreator(fn () => new Schema([
            'query' => new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'page' => (new RecordNodeBuilder(
                        $scope->get(RecordTypeBuilder::class),
                        $scope->get(RecordResolver::class),
                        $extenders
                    ))->for('pages')->build()->toArray(),
                ],
            ]),
        ]));

        $response = $scope->graphqlRequest('{
            page (uid: 2, removeRestrictions: true) {
              title
            }
        }');

        self::assertSame(200, $response->getStatusCode());
    }

    public function skippedTestPublicRequestCausesNullInsteadOfAccessDeniedOnRestrictedPage(): void
    {
        // $this->markTestSkipped('Not yet implemented but important');

        $scope = $this
            ->getFunctionalScopeBuilder()
            ->withAutoCreateGraphqlSchema(false)
            ->withAutoCreateHomepage(false)
            ->withSiteRootPageId(1)
            ->build()
        ;

        $scope->createRecord('pages', ['uid' => 1, 'title' => 'Restricted page', 'fe_group' => '-2']);

        $response = $scope->doGraphqlRequest('{
            page (uid: 1) {
              title
              parent { title }
            }
        }');

        self::assertNull($response['data']['page']);
    }
}
