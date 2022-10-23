<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\FieldCreator;

use RozbehSharahi\Graphql3\Builder\Type\RecordListTypeBuilder;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\ListRequest;
use RozbehSharahi\Graphql3\Resolver\RecordListResolver;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class PageChildrenFieldCreator implements FieldCreatorInterface
{
    public function __construct(
        protected RecordListTypeBuilder $recordListTypeBuilder,
        protected RecordListResolver $recordListResolver
    ) {
    }

    public static function getPriority(): int
    {
        return 0;
    }

    public function supportsField(string $tableName, string $columnName): bool
    {
        return 'pages' === $tableName && 'children' === $columnName;
    }

    public function createField(string $tableName, string $columnName): GraphqlNode
    {
        return GraphqlNode::create()
            ->withName('children')
            ->withType($this->recordListTypeBuilder->for('pages')->build())
            ->withResolver(
                fn (array $row) => (new ListRequest())
                    ->withQueryModifier(fn (QueryBuilder $q) => $q->andWhere($q->expr()->eq('pid', $row['uid'])))
            )
        ;
    }
}
