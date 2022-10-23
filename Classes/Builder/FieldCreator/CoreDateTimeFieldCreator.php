<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\FieldCreator;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgument;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;

class CoreDateTimeFieldCreator implements FieldCreatorInterface
{
    public const COLUMN_NAME_MAP = [
        'tstamp' => 'updatedAt',
        'crdate' => 'createdAt',
    ];

    public static function getPriority(): int
    {
        return 0;
    }

    public function supportsField(string $tableName, string $columnName): bool
    {
        return array_key_exists($columnName, self::COLUMN_NAME_MAP);
    }

    public function createField(string $tableName, string $columnName): GraphqlNode
    {
        $arguments = GraphqlArgumentCollection::create([
            GraphqlArgument::create('format')->withType(Type::nonNull(Type::string()))->withDefaultValue('Y-m-d H:i'),
        ]);

        return GraphqlNode::create()
            ->withName(self::COLUMN_NAME_MAP[$columnName])
            ->withArguments($arguments)
            ->withResolver(fn ($record, $args) => date($args['format'], $record[$columnName]))
        ;
    }
}
