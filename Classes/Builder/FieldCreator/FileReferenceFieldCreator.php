<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\FieldCreator;

use RozbehSharahi\Graphql3\Builder\Type\RecordListTypeBuilder;
use RozbehSharahi\Graphql3\Builder\Type\RecordTypeBuilder;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\ListRequest;
use RozbehSharahi\Graphql3\Domain\Model\Tca\ColumnConfiguration;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class FileReferenceFieldCreator implements FieldCreatorInterface
{
    public function __construct(
        protected RecordTypeBuilder $recordTypeBuilder,
        protected RecordListTypeBuilder $recordListTypeBuilder,
    ) {
    }

    public static function getPriority(): int
    {
        return 0;
    }

    public function supportsField(ColumnConfiguration $column): bool
    {
        return $column->isFile();
    }

    public function createField(ColumnConfiguration $column): GraphqlNode
    {
        return GraphqlNode::create()
            ->withName($column->getGraphqlName())
            ->withType($this->recordListTypeBuilder->for('sys_file_reference')->build())
            ->withResolver(fn (array $row) => (new ListRequest())
                ->withQueryModifier(static function (QueryBuilder $q) use ($column, $row) {
                    $q
                        ->andWhere($q->expr()->eq('uid_foreign', $row['uid']))
                        ->andWhere($q->expr()->eq('tablenames', $q->createNamedParameter($column->getTable())))
                    ;

                    foreach ($column->getForeignMatchFields() as $fieldName => $value) {
                        $q->andWhere($q->expr()->eq($fieldName, $q->createNamedParameter($value)));
                    }
                }))
        ;
    }
}
