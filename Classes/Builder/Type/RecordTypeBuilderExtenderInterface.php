<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\Type;

use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;

interface RecordTypeBuilderExtenderInterface
{
    public function supportsTable(string $table): bool;

    public function extendNodes(GraphqlNodeCollection $nodes): GraphqlNodeCollection;
}
