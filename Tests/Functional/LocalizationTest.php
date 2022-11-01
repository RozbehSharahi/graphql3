<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tests\Functional;

use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalScope;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;
use RozbehSharahi\Graphql3\Type\QueryType;

class LocalizationTest extends TestCase
{
    use FunctionalTrait;

    public function testLanguageFilter(): void
    {
        $scope = $this->createScope();

        $scope
            ->createRecord('pages', ['pid' => 0, 'uid' => 2, 'sys_language_uid' => 1, 'title' => 'German root'])
            ->createRecord('pages', ['pid' => 1, 'uid' => 3, 'sys_language_uid' => 1, 'title' => 'German subpage'])
            ->createRecord('pages', ['pid' => 1, 'uid' => 4, 'title' => 'Some english subpage'])
        ;

        $scope
            ->getSchemaRegistry()
            ->registerCreator(fn () => new Schema(['query' => $scope->get(QueryType::class)]))
        ;

        $response = $scope->doGraphqlRequest('{ 
            pages(language: "de") {
                count
                items {
                    title
                }
            }
        }');

        self::assertSame(2, $response['data']['pages']['count']);
        self::assertCount(2, $response['data']['pages']['items']);
        self::assertSame('German root', $response['data']['pages']['items'][0]['title']);
        self::assertSame('German subpage', $response['data']['pages']['items'][1]['title']);
    }

    public function testCanInheritLanguageFilterToPageChildren(): void
    {
        $scope = $this->createScope();

        $scope
            ->createRecord('pages', [
                'pid' => 0,
                'uid' => 2,
                'sys_language_uid' => 1,
                'l10n_parent' => 1,
                'title' => 'German root',
            ])
            ->createRecord('pages', ['pid' => 1, 'uid' => 3, 'sys_language_uid' => 1, 'title' => 'German child 1'])
            ->createRecord('pages', ['pid' => 1, 'uid' => 4, 'sys_language_uid' => 1, 'title' => 'German child 2'])
            ->createRecord('pages', ['pid' => 1, 'uid' => 5, 'sys_language_uid' => 1, 'title' => 'German child 3'])
            ->createRecord('pages', ['pid' => 1, 'uid' => 6, 'sys_language_uid' => 0, 'title' => 'English child 1'])
            ->createRecord('pages', ['pid' => 1, 'uid' => 7, 'sys_language_uid' => 0, 'title' => 'English child 2'])
        ;

        $scope
            ->getSchemaRegistry()
            ->registerCreator(fn () => new Schema(['query' => $scope->get(QueryType::class)]))
        ;

        $response = $scope->doGraphqlRequest('{ 
            pages(filters: { type: "eq", field: "pid", value: "0" }) {
                count
                items {
                    title
                    language {twoLetterIsoCode}
                    children {
                        count
                        items {
                            title
                            language {twoLetterIsoCode}
                        }
                    }
                }
            }
        }');

        self::assertSame(2, $response['data']['pages']['count']);
        self::assertCount(2, $response['data']['pages']['items']);
        self::assertSame(2, $response['data']['pages']['items'][0]['children']['count']);
        self::assertSame(3, $response['data']['pages']['items'][1]['children']['count']);
        self::assertSame('English child 1', $response['data']['pages']['items'][0]['children']['items'][0]['title']);
        self::assertSame('English child 2', $response['data']['pages']['items'][0]['children']['items'][1]['title']);
        self::assertSame('German child 1', $response['data']['pages']['items'][1]['children']['items'][0]['title']);
        self::assertSame('German child 2', $response['data']['pages']['items'][1]['children']['items'][1]['title']);
        self::assertSame('German child 3', $response['data']['pages']['items'][1]['children']['items'][2]['title']);
    }

    private function createScope(): FunctionalScope
    {
        return $this->getFunctionalScopeBuilder()->build();
    }
}
