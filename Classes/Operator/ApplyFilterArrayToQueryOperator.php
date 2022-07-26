<?php

namespace RozbehSharahi\Graphql3\Operator;

use RozbehSharahi\Graphql3\Exception\GraphqlException;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class ApplyFilterArrayToQueryOperator
{
    protected array $types;

    public function __construct()
    {
        $this->types = [
            'equals' => [$this, 'applyEqualsType'],
        ];
    }

    public function __invoke(QueryBuilder $query, array $filters): self
    {
        foreach ($filters as $filter) {
            $type = $filter['type'] ?? 'unknown';

            if (!array_key_exists($type, $this->types)) {
                throw GraphqlException::createClientSafe("Given filter type '{$type}' is not valid.");
            }

            // Call one of the operators
            $this->types[$type]($query, $filter);
        }

        return $this;
    }

    protected function applyEqualsType(QueryBuilder $query, array $filter): self
    {
        if (empty($filter['field'])) {
            throw GraphqlException::createClientSafe("'field' is mandatory on filters of type 'equal'");
        }

        if (!isset($filter['value'])) {
            throw GraphqlException::createClientSafe("'value' is mandatory on filters of type 'equal'");
        }

        $query->andWhere(
            $query->expr()->eq($filter['field'], $query->createNamedParameter($filter['value']))
        );

        return $this;
    }
}
