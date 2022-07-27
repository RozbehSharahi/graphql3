<?php

namespace RozbehSharahi\Graphql3\Operator;

use RozbehSharahi\Graphql3\Exception\GraphqlException;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class ApplyFilterArrayToQueryOperator
{
    protected array $expressions = ['eq', 'gte', 'gt', 'lt', 'lte', 'neq'];

    protected array $nestedExpressions = ['or', 'and'];

    public function operate(QueryBuilder $query, array $filters): self
    {
        foreach ($filters as $filter) {
            $type = $filter['type'] ?? 'unknown';

            $this->assertTypeIsValid($type);

            if (in_array($type, $this->expressions, true)) {
                $query->andWhere($this->createExpression($type, $query, $filter));
            } else {
                $query->andWhere($this->createNestedExpression($type, $query, $filter));
            }
        }

        return $this;
    }

    protected function createExpression(string $type, QueryBuilder $query, array $filter): string
    {
        $this->asserFieldAndValueIsSet($type, $filter);

        return $query->expr()->{$type}($filter['field'], $query->createNamedParameter($filter['value']));
    }

    private function createNestedExpression(mixed $type, QueryBuilder $query, mixed $filter): string
    {
        $this->assertChildrenAreSet($type, $filter);

        $expressions = [];
        foreach ($filter['children'] as $childFilter) {
            $type = $childFilter['type'] ?? 'unknown';

            if (in_array($type, $this->expressions, true)) {
                $expressions[] = $this->createExpression($type, $query, $childFilter);
            } else {
                $expressions[] = $this->createNestedExpression($type, $query, $childFilter);
            }
        }

        return $query->expr()->{$filter['type']}(...$expressions);
    }

    protected function asserFieldAndValueIsSet(string $type, array $filter): self
    {
        if (empty($filter['field'])) {
            throw GraphqlException::createClientSafe("'field' is mandatory on filters of type '{$type}'");
        }

        if (!isset($filter['value'])) {
            throw GraphqlException::createClientSafe("'value' is mandatory on filters of type '{$type}'");
        }

        return $this;
    }

    protected function assertChildrenAreSet(string $type, array $filter): self
    {
        if (empty($filter['children'])) {
            throw GraphqlException::createClientSafe("'children' are mandatory on filters of type '{$type}'");
        }

        return $this;
    }

    protected function assertTypeIsValid(string $type): self
    {
        if (!in_array($type, $this->expressions, true) && !in_array($type, $this->nestedExpressions, true)) {
            throw GraphqlException::createClientSafe("Given filter type '{$type}' is not valid.");
        }

        return $this;
    }
}
