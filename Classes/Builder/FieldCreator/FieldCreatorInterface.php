<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\FieldCreator;

use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;

interface FieldCreatorInterface
{
    public static function getPriority(): int;

    public function supportsField(string $tableName, string $columnName): bool;

    public function createField(string $tableName, string $columnName): GraphqlNode;
}
