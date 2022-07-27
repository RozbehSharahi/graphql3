<?php

namespace RozbehSharahi\Graphql3\Operator;

use RozbehSharahi\Graphql3\Exception\GraphqlException;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class ApplyFilterArrayToQueryOperator
{
    protected array $expressionCreators;

    public function __construct()
    {
        $this->expressionCreators = [
            'eq' => [$this, 'createEqualsExpression'],
            'gte' => [$this, 'createGreaterThanEqualsExpression'],
            'gt' => [$this, 'createGreaterThanExpression'],
            'or' => [$this, 'createOrExpression'],
        ];
    }

    public function __invoke(QueryBuilder $query, array $filters): self
    {
        foreach ($filters as $filter) {
            $type = $filter['type'] ?? 'unknown';

            $this->assertTypeIsValid($type);

            $query->andWhere(
                $this->expressionCreators[$type]($query, $filter)
            );
        }

        return $this;
    }

    protected function createEqualsExpression(QueryBuilder $query, array $filter): string
    {
        $this->asserFieldAndValueIsSet('eq', $filter);

        return $query->expr()->eq($filter['field'], $query->createNamedParameter($filter['value']));
    }

    protected function createGreaterThanEqualsExpression(QueryBuilder $query, array $filter): string
    {
        $this->asserFieldAndValueIsSet('gte', $filter);

        return $query->expr()->gte($filter['field'], $query->createNamedParameter($filter['value']));
    }

    protected function createGreaterThanExpression(QueryBuilder $query, array $filter): string
    {
        $this->asserFieldAndValueIsSet('gt', $filter);

        return $query->expr()->gt($filter['field'], $query->createNamedParameter($filter['value']));
    }

    protected function createOrExpression(QueryBuilder $query, array $filter): string
    {
        $this->assertChildrenAreSet('or', $filter);

        $expressions = [];
        foreach ($filter['children'] as $childFilter) {
            $type = $childFilter['type'] ?? 'unknown';

            $this->assertTypeIsValid($type);

            // Call one of the operators
            $expressions[] = $this->expressionCreators[$type]($query, $childFilter);
        }

        return $query->expr()->or(...$expressions);
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
        if (!array_key_exists($type, $this->expressionCreators)) {
            throw GraphqlException::createClientSafe("Given filter type '{$type}' is not valid.");
        }

        return $this;
    }
}
