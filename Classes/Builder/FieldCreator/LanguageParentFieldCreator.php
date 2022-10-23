<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\FieldCreator;

use RozbehSharahi\Graphql3\Builder\Type\RecordTypeBuilder;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\ItemRequest;
use RozbehSharahi\Graphql3\Domain\Model\Tca\ColumnConfiguration;
use RozbehSharahi\Graphql3\Resolver\RecordResolver;

class LanguageParentFieldCreator implements FieldCreatorInterface
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
        $config = ColumnConfiguration::fromTableAndColumnOrNull($tableName, $columnName);

        return
            $config &&
            $config->isLanguageParent() &&
            $config->isManyToOne()
        ;
    }

    public function createField(string $tableName, string $columnName): GraphqlNode
    {
        $config = ColumnConfiguration::fromTableAndColumn($tableName, $columnName);

        return GraphqlNode::create()
            ->withName('languageParent')
            ->withType($this->recordTypeBuilder->for($config->getForeignTable())->build())
            ->withResolver(fn ($record) => $this
                ->recordResolver->for($config->getForeignTable())->resolve(new ItemRequest(['uid' => $record[$columnName] ?? null]))
            )
        ;
    }
}
