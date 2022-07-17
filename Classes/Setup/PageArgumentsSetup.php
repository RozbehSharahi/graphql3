<?php

namespace RozbehSharahi\Graphql3\Setup;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgument;
use RozbehSharahi\Graphql3\Registry\PageArgumentRegistry;

class PageArgumentsSetup implements SetupInterface
{
    public function __construct(protected PageArgumentRegistry $pageArgumentRegistry)
    {
    }

    public function setup(): void
    {
        $this
            ->pageArgumentRegistry
            ->register(GraphqlArgument::create('uid')->withType(Type::nonNull(Type::int())));
    }
}
