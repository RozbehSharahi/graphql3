<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Extender;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Builder\RecordListNodeExtenderInterface;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgument;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;
use RozbehSharahi\Graphql3\Domain\Model\Tca\TableConfiguration;

class DoktypeFilterRecordListNodeExtender implements RecordListNodeExtenderInterface
{
    public function supportsTable(TableConfiguration $table): bool
    {
        return 'pages' === $table->getName();
    }

    public function extend(TableConfiguration $table, GraphqlArgumentCollection $arguments): GraphqlArgumentCollection
    {
        return $arguments->add(
            GraphqlArgument::create('allDoktypes')
                ->withType(Type::nonNull(Type::boolean()))
                ->withDefaultValue(false)
        );
    }
}
