<?php

namespace RozbehSharahi\Graphql3\Builder;

use GraphQL\Type\Schema;
use RozbehSharahi\Graphql3\Type\NoopQueryType;

class NoopSchemaBuilder
{
    public function build(): Schema
    {
        return new Schema([
            'query' => new NoopQueryType(),
        ]);
    }
}
