<?php

namespace RozbehSharahi\Graphql3\Tests\Functional;

use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalScope;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionScopeBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AccessCheckerTest extends TestCase
{
    use FunctionalTrait;

    public function testLoginProtectedPageIsAccessChecked(): void
    {
        $scope = $this->createScope(
            rootPage: ['uid' => 1, 'title' => 'Root', 'fe_group' => '-2']
        );

        $this->assertThrowsException(function () use ($scope) {
            $scope->doGraphqlRequest('{
                page(uid: 1, publicRequest: false) {
                    title
                }
            }');
        });

        $scope = $this->createScope(
            rootPage: ['uid' => 1, 'title' => 'Root', 'fe_group' => '-2'],
            frontendUser: ['uid' => 1, 'username' => 'test-user']
        );

        $response = $scope->doGraphqlRequest('{
            page(uid: 1, publicRequest: false) {
                title
            }
        }');

        self::assertEquals('Root', $response['data']['page']['title']);
    }

    protected function assertThrowsException(\Closure $closure): self
    {
        try {
            $closure();
            $this->fail('Exception expected, but was never thrown.');
        } catch (\Throwable $e) {
            self::assertInstanceOf(\Throwable::class, $e);
        }

        return $this;
    }

    protected function createScope(array $rootPage, ?array $frontendUser = null): FunctionalScope
    {
        $scope = $this->getFunctionalScopeBuilder()
            ->withAutoCreateHomepage(false)
            ->withAutoCreateSchema(true)
            ->withAutoCreateSite(true)
            ->withAutoCreateGraphqlSchema(false)
            ->withLoggedInUser($frontendUser)
            ->build();

        $scope->createRecord('pages', $rootPage);

        $scope->getSchemaRegistry()->register(new Schema([
            'query' => $scope->getQueryType(),
        ]));

        return $scope;
    }

    protected function getFunctionalScopeBuilder(): FunctionScopeBuilder
    {
        return GeneralUtility::makeInstance(FunctionScopeBuilder::class)
            ->withAutoCreateHomepage(false)
            ->withAutoCreateSchema(true)
            ->withAutoCreateSite(true)
            ->withAutoCreateGraphqlSchema(false);
    }
}
