<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder;

use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection as Arguments;
use RozbehSharahi\Graphql3\Domain\Model\Tca\TableConfiguration;

interface RecordNodeExtenderInterface
{
    public function supportsTable(TableConfiguration $table): bool;

    public function extendArguments(TableConfiguration $table, Arguments $arguments): Arguments;
}
