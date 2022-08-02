<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\FieldCreator;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgument;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;

class DateTimeFieldCreator extends AbstractFieldCreator implements FieldCreatorInterface
{
    public static function getPriority(): int
    {
        return 0;
    }

    public function supportsField(string $tableName, string $columnName): bool
    {
        $configuration = $this->getFieldConfiguration($tableName, $columnName);

        if (in_array($columnName, ['tstamp', 'crdate'])) {
            return true;
        }

        return 'datetime' === $configuration['type'];
    }

    public function createField(string $tableName, string $columnName): GraphqlNode
    {
        $name = $this->getName($tableName, $columnName);

        if ('crdate' === $columnName) {
            $name = 'createdAt';
        }

        if ('tstamp' === $columnName) {
            $name = 'updatedAt';
        }

        return GraphqlNode::create()
            ->withName($name)
            ->withArguments(GraphqlArgumentCollection::create([
                GraphqlArgument::create('format')
                    ->withType(Type::nonNull(Type::string()))
                    ->withDefaultValue('Y-m-d H:i'),
            ]))
            ->withResolver(
                fn ($record, $args) => date($args['format'], $record[$columnName])
            )
        ;
    }
}
