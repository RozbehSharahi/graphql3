<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\FieldCreator;

use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\Tca\ColumnConfiguration;

interface FieldCreatorInterface
{
    public static function getPriority(): int;

    public function supportsField(ColumnConfiguration $column): bool;

    public function createField(ColumnConfiguration $column): GraphqlNode;
}
