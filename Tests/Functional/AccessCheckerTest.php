<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tests\Functional;

use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Domain\Model\JwtUser;
use RozbehSharahi\Graphql3\Exception\UnauthorizedException;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalScope;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;
use Symfony\Component\HttpFoundation\Response;

class AccessCheckerTest extends TestCase
{
    use FunctionalTrait;

    public function testLoginProtectedPageIsAccessChecked(): void
    {
        $scope = $this->createScope(['uid' => 1, 'title' => 'Root', 'fe_group' => '-2']);

        $response = $scope->graphqlRequest('{
            page(uid: 1, publicRequest: false) {
                title
            }
        }');

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        self::assertSame(UnauthorizedException::DEFAULT_MESSAGE, $response->get('errors.0.message'));

        $scope->loginUser(new JwtUser('test-user', ['ROLE_USER']));

        $response = $scope->graphqlRequest('{
            page(uid: 1, publicRequest: false) {
                title
            }
        }');

        self::assertEquals('Root', $response->get('data.page.title'));
    }

    public function testFeGroupProtectedPageIsAccessChecked(): void
    {
        $scope = $this->createScope(['uid' => 1, 'title' => 'Root', 'fe_group' => '1']);
        $scope->loginUser(new JwtUser('test-user', ['ROLE_USER']));

        $response = $scope->graphqlRequest('{
            page(uid: 1, publicRequest: false) {
                title
            }
        }');

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        self::assertSame(UnauthorizedException::DEFAULT_MESSAGE, $response->get('errors.0.message'));

        $scope->loginUser(new JwtUser('test-user', [JwtUser::createGroupIdRole(1)]));

        $response = $scope->graphqlRequest('{
            page(uid: 1, publicRequest: false) {
                title
            }
        }');

        self::assertEquals('Root', $response->get('data.page.title'));
    }

    public function testFeGroupProtectedContentIsAccessChecked(): void
    {
        $scope = $this->createScope(['uid' => 1, 'title' => 'Root']);

        $scope->createRecord('tt_content', ['uid' => 1, 'header' => 'my content', 'fe_group' => '-2']);

        $response = $scope->graphqlRequest('{
            content(uid: 1, publicRequest: false) {
                header
            }
        }');

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        self::assertSame(UnauthorizedException::DEFAULT_MESSAGE, $response->get('errors.0.message'));

        $scope->loginUser(new JwtUser('test-user', ['ROLE_USER']));

        $response = $scope->graphqlRequest('{
            content(uid: 1, publicRequest: false) {
                header
            }
        }');

        self::assertEquals('my content', $response->get('data.content.header'));
    }

    /**
     * @param array<string, mixed> $rootPage
     */
    protected function createScope(array $rootPage): FunctionalScope
    {
        $scope = $this
            ->getFunctionalScopeBuilder()
            ->withAutoCreateHomepage(false)
            ->withAutoCreateSchema(true)
            ->withAutoCreateSite(true)
            ->withAutoCreateGraphqlSchema(false)
            ->build()
        ;

        $scope->createRecord('pages', $rootPage);

        $scope->getSchemaRegistry()->register(new Schema([
            'query' => $scope->getQueryType(),
        ]));

        return $scope;
    }
}
