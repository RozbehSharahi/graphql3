<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder;

use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;
use RozbehSharahi\Graphql3\Domain\Model\Tca\TableConfiguration;

interface RecordListNodeExtenderInterface
{
    public function supportsTable(TableConfiguration $table): bool;

    public function extendArguments(TableConfiguration $table, GraphqlArgumentCollection $arguments): GraphqlArgumentCollection;
}
