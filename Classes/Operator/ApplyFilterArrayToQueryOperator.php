<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Operator;

use RozbehSharahi\Graphql3\Exception\GraphqlException;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class ApplyFilterArrayToQueryOperator
{
    protected array $typeToExpressionCreatorMap;

    public function __construct()
    {
        $this->typeToExpressionCreatorMap = [
            'eq' => [$this, 'createExpression'],
            'neq' => [$this, 'createExpression'],
            'gt' => [$this, 'createExpression'],
            'gte' => [$this, 'createExpression'],
            'lt' => [$this, 'createExpression'],
            'lte' => [$this, 'createExpression'],
            'or' => [$this, 'createNestedExpression'],
            'and' => [$this, 'createNestedExpression'],
            'in' => [$this, 'createListExpression'],
            'notIn' => [$this, 'createListExpression'],
        ];
    }

    public function operate(QueryBuilder $query, array $filters): self
    {
        foreach ($filters as $filter) {
            $type = $filter['type'] ?? 'unknown';

            $this->assertTypeIsValid($type);

            $query->andWhere($this->typeToExpressionCreatorMap[$type]($type, $query, $filter));
        }

        return $this;
    }

    protected function createExpression(string $type, QueryBuilder $query, array $filter): string
    {
        $this->asserFieldAndValueIsSet($type, $filter);

        return $query->expr()->{$type}($filter['field'], $query->createNamedParameter($filter['value']));
    }

    private function createNestedExpression(string $type, QueryBuilder $query, array $filter): string
    {
        $this->assertChildrenAreSet($type, $filter);

        $expressions = [];
        foreach ($filter['children'] as $childFilter) {
            $type = $childFilter['type'] ?? 'unknown';

            $this->assertTypeIsValid($type);

            $expressions[] = $this->typeToExpressionCreatorMap[$type]($type, $query, $childFilter);
        }

        return (string) $query->expr()->{$filter['type']}(...$expressions);
    }

    private function createListExpression(string $type, QueryBuilder $query, array $filter): string
    {
        $this->assertValuesAreSet($type, $filter);

        $valueParameters = array_map(
            static fn (string $value) => $query->createNamedParameter($value),
            $filter['values']
        );

        return $query->expr()->{$filter['type']}($filter['field'], $valueParameters);
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

    protected function assertValuesAreSet(string $type, array $filter): self
    {
        if (empty($filter['values'])) {
            throw GraphqlException::createClientSafe("'values' are mandatory on filters of type '{$type}'");
        }

        return $this;
    }

    protected function assertTypeIsValid(string $type): self
    {
        if (!isset($this->typeToExpressionCreatorMap[$type])) {
            throw GraphqlException::createClientSafe("Given filter type '{$type}' is not valid.");
        }

        return $this;
    }
}
