<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\FieldCreator;

use RozbehSharahi\Graphql3\Builder\Type\RecordTypeBuilder;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\ItemRequest;
use RozbehSharahi\Graphql3\Resolver\RecordResolver;

class ManyToOneRelationFieldCreator extends AbstractFieldCreator implements FieldCreatorInterface
{
    public function __construct(
        protected RecordTypeBuilder $recordTypeBuilder,
        protected RecordResolver $recordResolver
    ) {
    }

    public static function getPriority(): int
    {
        return 0;
    }

    public function supportsField(string $tableName, string $columnName): bool
    {
        $configuration = $this->getFieldConfiguration($tableName, $columnName);

        if ('pid' === $columnName) {
            return true;
        }

        return 'select' === $configuration['type']
            && 'selectSingle' === $configuration['renderType']
            && !empty($configuration['foreign_table']);
    }

    public function createField(string $tableName, string $columnName): GraphqlNode
    {
        $name = $this->getName($tableName, $columnName);
        $foreignTable = $this->getFieldConfiguration($tableName, $columnName)['foreign_table'] ?? null;

        if ('pid' === $columnName) {
            $name = 'parentPage';
            $foreignTable = 'pages';
        }

        if ($columnName === $this->getLanguageParentColumnName($tableName)) {
            $name = 'languageParent';
        }

        return GraphqlNode::create()
            ->withName($name)
            ->withType($this->recordTypeBuilder->for($foreignTable)->build())
            ->withResolver(fn ($record) => $this
                ->recordResolver->for($foreignTable)->resolve(new ItemRequest(['uid' => $record[$columnName] ?? null]))
            )
        ;
    }
}
