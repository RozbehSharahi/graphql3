<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Operator;

use RozbehSharahi\Graphql3\Exception\BadRequestException;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class ApplyFilterArrayToQueryOperator
{
    /**
     * @var array<string, callable>
     */
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

    /**
     * @param array<int, array<string, mixed>> $filters
     */
    public function operate(QueryBuilder $query, array $filters): self
    {
        foreach ($filters as $filter) {
            $type = $filter['type'] ?? 'unknown';

            $this->assertTypeIsValid($type);

            $query->andWhere($this->typeToExpressionCreatorMap[$type]($type, $query, $filter));
        }

        return $this;
    }

    /**
     * @param array<string, mixed> $filter
     */
    protected function createExpression(string $type, QueryBuilder $query, array $filter): string
    {
        $this->asserFieldAndValueIsSet($type, $filter);

        return $query->expr()->{$type}($filter['field'], $query->createNamedParameter($filter['value']));
    }

    /**
     * @param array<string, mixed> $filter
     */
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

    /**
     * @param array<string, mixed> $filter
     */
    private function createListExpression(string $type, QueryBuilder $query, array $filter): string
    {
        $this->assertValuesAreSet($type, $filter);

        $valueParameters = array_map(
            static fn (string $value) => $query->createNamedParameter($value),
            $filter['values']
        );

        return $query->expr()->{$filter['type']}($filter['field'], $valueParameters);
    }

    /**
     * @param array<string, mixed> $filter
     */
    protected function asserFieldAndValueIsSet(string $type, array $filter): self
    {
        if (empty($filter['field'])) {
            throw new BadRequestException("'field' is mandatory on filters of type '{$type}'");
        }

        if (!isset($filter['value'])) {
            throw new BadRequestException("'value' is mandatory on filters of type '{$type}'");
        }

        return $this;
    }

    /**
     * @param array<string, mixed> $filter
     */
    protected function assertChildrenAreSet(string $type, array $filter): self
    {
        if (empty($filter['children'])) {
            throw new BadRequestException("'children' are mandatory on filters of type '{$type}'");
        }

        return $this;
    }

    /**
     * @param array<string, mixed> $filter
     */
    protected function assertValuesAreSet(string $type, array $filter): self
    {
        if (empty($filter['values'])) {
            throw new BadRequestException("'values' are mandatory on filters of type '{$type}'");
        }

        return $this;
    }

    protected function assertTypeIsValid(string $type): self
    {
        if (!isset($this->typeToExpressionCreatorMap[$type])) {
            throw new BadRequestException("Given filter type '{$type}' is not valid.");
        }

        return $this;
    }
}
