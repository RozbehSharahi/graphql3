<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tests\Functional;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Builder\RecordTypeBuilder;
use RozbehSharahi\Graphql3\Domain\Model\Record;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalScope;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;

class RecordTypeBuilderTest extends TestCase
{
    use FunctionalTrait;

    public function testCanBuildTypeBasedOnTca(): void
    {
        $scope = $this->createScope();

        $recordTypeBuilder = $scope->get(RecordTypeBuilder::class);
        $pageType = $recordTypeBuilder->for('pages')->build();

        self::assertInstanceOf(StringType::class, $pageType->getField('title')->getType());

        self::assertContains('title', $pageType->getFieldNames());
        self::assertContains('subtitle', $pageType->getFieldNames());
        self::assertContains('navigationTitle', $pageType->getFieldNames());
        self::assertContains('target', $pageType->getFieldNames());
        self::assertContains('url', $pageType->getFieldNames());
        self::assertContains('cacheTags', $pageType->getFieldNames());
        self::assertContains('author', $pageType->getFieldNames());
        self::assertContains('uid', $pageType->getFieldNames());
        self::assertContains('parentPage', $pageType->getFieldNames());
        self::assertContains('languageParent', $pageType->getFieldNames());
    }

    public function testCanBuildPageType(): void
    {
        $scope = $this->createScope();

        $scope
            ->createRecord('pages', [
                'uid' => 1,
                'pid' => 0,
                'title' => 'root-page',
                'slug' => '/',
                'crdate' => 338079600 + 7200,
                'tstamp' => 338079600 + 7200,
                'deleted' => 0,
                'hidden' => 0,
                'nav_hide' => 1,
            ])
        ;

        $builder = $scope->get(RecordTypeBuilder::class);

        $scope->get(SchemaRegistry::class)->registerCreator(fn () => new Schema([
            'query' => new ObjectType([
                'name' => 'Query',
                'fields' => fn () => [
                    'page' => [
                        'type' => $builder->for('pages')->build(),
                        'resolve' => fn () => Record::create('pages', $scope->getRecord('pages', 1)),
                    ],
                ],
            ]),
        ]));

        $response = $scope->graphqlRequest('{
            page {
                title
                slug
                sorting
                parentPage { title }
                languageParent { title }
                createdAt(format: "Y-m-d")
                updatedAt(format: "Y-m-d")
                hidden
                deleted
                navigationHide
            }
        }');

        self::assertSame('root-page', $response->get('data.page.title'));
        self::assertNull($response->get('data.page.languageParent'));
        self::assertNull($response->get('data.page.parentPage'));
        self::assertSame('1980-09-18', $response->get('data.page.createdAt'));
        self::assertSame('1980-09-18', $response->get('data.page.updatedAt'));
        self::assertFalse($response->get('data.page.hidden'));
        self::assertFalse($response->get('data.page.deleted'));
        self::assertTrue($response->get('data.page.navigationHide'));
        self::assertIsInt($response->get('data.page.sorting'));
    }

    public function testCanResolveParentPage(): void
    {
        $scope = $this->createScope();

        $scope
            ->createRecord('pages', ['uid' => 1, 'pid' => 0, 'title' => 'root-page'])
            ->createRecord('pages', ['uid' => 2, 'pid' => 1, 'title' => 'sub-page'])
            ->createRecord('pages', ['uid' => 3, 'pid' => 1, 'title' => 'sub-page-austrian', 'l10n_parent' => 2])
        ;

        $builder = $scope->get(RecordTypeBuilder::class);

        $scope->get(SchemaRegistry::class)->registerCreator(fn () => new Schema([
            'query' => new ObjectType([
                'name' => 'Query',
                'fields' => fn () => [
                    'subPage' => [
                        'type' => $builder->for('pages')->build(),
                        'resolve' => fn () => Record::create('pages', $scope->getRecord('pages', 3)),
                    ],
                ],
            ]),
        ]));

        $response = $scope->doGraphqlRequest('{
            subPage {
                title
                parentPage { title }
                languageParent { title }
            }
        }');

        self::assertSame('sub-page-austrian', $response['data']['subPage']['title']);
        self::assertSame('sub-page', $response['data']['subPage']['languageParent']['title']);
        self::assertSame('root-page', $response['data']['subPage']['parentPage']['title']);
    }

    public function testCanResolveDateTimes(): void
    {
        $scope = $this->createScope();

        $scope
            ->createRecord('pages', [
                'uid' => 1,
                'pid' => 0,
                'title' => 'root-page',
                'tstamp' => 338079600 + 7200,
                'crdate' => 338079600 + 7200,
            ])
        ;

        $builder = $scope->get(RecordTypeBuilder::class);

        $scope->get(SchemaRegistry::class)->registerCreator(fn () => new Schema([
            'query' => new ObjectType([
                'name' => 'Query',
                'fields' => fn () => [
                    'page' => [
                        'type' => $builder->for('pages')->build(),
                        'resolve' => fn () => Record::create('pages', $scope->getRecord('pages', 1)),
                    ],
                ],
            ]),
        ]));

        $response = $scope->doGraphqlRequest('{
            page {
                createdAt(format: "Y-m-d")
                updatedAt(format: "Y-m-d")
            }
        }');

        self::assertSame('1980-09-18', $response['data']['page']['createdAt']);
        self::assertSame('1980-09-18', $response['data']['page']['updatedAt']);
    }

    private function createScope(): FunctionalScope
    {
        return $this
            ->getFunctionalScopeBuilder()
            ->withAutoCreateHomepage(false)
            ->withAutoCreateGraphqlSchema(false)
            ->build()
        ;
    }
}
