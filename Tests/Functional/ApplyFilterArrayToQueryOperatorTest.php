<?php

namespace RozbehSharahi\Graphql3\Tests\Functional;

use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Operator\ApplyFilterArrayToQueryOperator;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class ApplyFilterArrayToQueryOperatorTest extends TestCase
{
    use FunctionalTrait;

    public function testCanApplyFilterArrayToQueryBuilder(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->build();
        $query = $scope->getConnectionPool()->getQueryBuilderForTable('pages');

        (new ApplyFilterArrayToQueryOperator())($query, [
            ['type' => 'equals', 'field' => 'uid', 'value' => 123],
            ['type' => 'gt', 'field' => 'uid', 'value' => 100],
        ]);

        $sql = $this->getTestableSql($query);

        self::assertStringContainsString('("uid" = "123") AND ("uid" > "100")', $sql);
    }

    private function getTestableSql(QueryBuilder $query): string
    {
        $sql = $query->getSQL();

        $parametersCount = count($query->getParameters());
        for ($i = 1; $i <= $parametersCount; ++$i) {
            $sql = str_replace(":dcValue{$i}", '"'.$query->getParameter("dcValue{$i}").'"', $sql);
        }

        return $sql;
    }
}
