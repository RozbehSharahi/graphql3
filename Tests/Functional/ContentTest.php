<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tests\Functional;

use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalScope;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;

class ContentTest extends TestCase
{
    use FunctionalTrait;

    public function testCanFetchContent(): void
    {
        $scope = $this->createScope();

        $scope
            ->createRecord('tt_content', [
                'uid' => 1,
                'pid' => 1,
                'header' => 'My content',
                'header_link' => 't3://page?uid=1',
            ])
            ->createRecord('tt_content', [
                'uid' => 2,
                'pid' => 1,
                'header' => 'My second content',
                'header_link' => 'https://www.google.com',
            ])
        ;

        $response = $scope->graphqlRequest('{ 
            content1: content(uid: 1) {
                header
                headerLink
            }
            content2: content(uid: 2) {
                header
                headerLink
            }
        }');

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('My content', $response->get('data.content1.header'));
        self::assertSame('/test-app/', $response->get('data.content1.headerLink'));
        self::assertSame('https://www.google.com', $response->get('data.content2.headerLink'));
    }

    private function createScope(): FunctionalScope
    {
        return $this->getFunctionalScopeBuilder()->build();
    }
}
