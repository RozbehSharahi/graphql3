<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\Type;

use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use RozbehSharahi\Graphql3\Domain\Model\Tca\TableConfiguration;

interface RecordTypeBuilderExtenderInterface
{
    public function supportsTable(TableConfiguration $table): bool;

    public function extendNodes(TableConfiguration $table, GraphqlNodeCollection $nodes): GraphqlNodeCollection;
}
