<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\FieldCreator;

use RozbehSharahi\Graphql3\Builder\Type\LanguageTypeBuilder;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\ItemRequest;
use RozbehSharahi\Graphql3\Domain\Model\Tca\ColumnConfiguration;
use RozbehSharahi\Graphql3\Resolver\LanguageResolver;

class LanguageFieldCreator implements FieldCreatorInterface
{
    public function __construct(protected LanguageTypeBuilder $languageTypeBuilder, protected LanguageResolver $languageResolver)
    {
    }

    public static function getPriority(): int
    {
        return 0;
    }

    public function supportsField(string $tableName, string $columnName): bool
    {
        return ColumnConfiguration::fromTableAndColumnOrNull($tableName, $columnName)?->isLanguage() ?: false;
    }

    public function createField(string $tableName, string $columnName): GraphqlNode
    {
        return GraphqlNode::create()
            ->withName(ColumnConfiguration::fromTableAndColumn($tableName, $columnName)->getGraphqlName())
            ->withType($this->languageTypeBuilder->build())
            ->withResolver(fn (array $record) => $this->languageResolver->resolve(new ItemRequest([
                'id' => $record[$columnName] ?? 0,
            ])))
        ;
    }
}