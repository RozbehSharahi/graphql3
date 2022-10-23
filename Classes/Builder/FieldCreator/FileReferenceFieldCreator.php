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

    public function supportsField(string $tableName, string $columnName): bool
    {
        return ColumnConfiguration::fromTableAndColumnOrNull($tableName, $columnName)?->isFile() ?: false;
    }

    public function createField(string $tableName, string $columnName): GraphqlNode
    {
        $config = ColumnConfiguration::fromTableAndColumn($tableName, $columnName);

        return GraphqlNode::create()
            ->withName($config->getGraphqlName())
            ->withType($this->recordListTypeBuilder->for('sys_file_reference')->build())
            ->withResolver(fn (array $row) => (new ListRequest())
                ->withQueryModifier(static function (QueryBuilder $q) use ($tableName, $config, $row) {
                    $q
                        ->andWhere($q->expr()->eq('uid_foreign', $row['uid']))
                        ->andWhere($q->expr()->eq('tablenames', $q->createNamedParameter($tableName)))
                    ;

                    foreach ($config->getForeignMatchFields() as $fieldName => $value) {
                        $q->andWhere($q->expr()->eq($fieldName, $q->createNamedParameter($value)));
                    }
                }))
        ;
    }
}
