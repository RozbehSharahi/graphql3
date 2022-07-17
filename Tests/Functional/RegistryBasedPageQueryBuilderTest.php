<?php

namespace RozbehSharahi\Graphql3\Tests\Functional;

use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Builder\RegistryBasedPageQueryBuilder;
use RozbehSharahi\Graphql3\Domain\Model\QueryExtender;
use RozbehSharahi\Graphql3\Registry\PageQueryExtenderRegistry;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class RegistryBasedPageQueryBuilderTest extends TestCase
{
    use FunctionalTrait;

    public function testPageQueryExtenderRegistryCanResolvePages(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->build();

        $pageQueryExtenderRegistry = $scope->get(PageQueryExtenderRegistry::class);
        $pageQueryExtenderRegistry->setup();
        $pageQueryExtenderRegistry->register(
            QueryExtender::create('my-extension', static fn (QueryBuilder $query) => $query->andWhere('2=2'))
        );

        $query = $scope->get(RegistryBasedPageQueryBuilder::class)->withArguments(['uid' => 1234])->build();
        self::assertStringContainsString('AND (2=2)', $query->getSQL());
        self::assertStringContainsString('("uid" = :dcValue1)', $query->getSQL());

        $page = $scope->get(RegistryBasedPageQueryBuilder::class)->withArguments(['uid' => 1])->getPage();
        self::assertNotEmpty($page);
        self::assertSame(1, $page['uid']);
        self::assertSame('root page', $page['title']);
    }

    public function testExceptionOnNoneExistingPage(): void
    {
        $this->expectExceptionMessageMatches('/Could not fetch page with id: 1234/');
        $scope = $this->getFunctionalScopeBuilder()->build();
        $scope->get(PageQueryExtenderRegistry::class)->setup();
        $scope->get(RegistryBasedPageQueryBuilder::class)->withArguments(['uid' => 1234])->getPage();
    }
}
