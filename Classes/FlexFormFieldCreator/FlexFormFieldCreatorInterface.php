<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\FlexFormFieldCreator;

use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\Tca\ColumnConfiguration;

interface FlexFormFieldCreatorInterface
{
    public function supportsField(ColumnConfiguration $column): bool;

    public function createField(ColumnConfiguration $column): GraphqlNode;
}
