<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Extender;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Builder\RecordTypeBuilderExtenderInterface;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgument;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use RozbehSharahi\Graphql3\Domain\Model\Record;
use RozbehSharahi\Graphql3\Domain\Model\Tca\TableConfiguration;

class DatesRecordTypeBuilderExtender implements RecordTypeBuilderExtenderInterface
{
    public function supportsTable(TableConfiguration $table): bool
    {
        return true;
    }

    public function extendNodes(TableConfiguration $table, GraphqlNodeCollection $nodes): GraphqlNodeCollection
    {
        $arguments = GraphqlArgumentCollection::create([
            GraphqlArgument::create('format')->withType(Type::nonNull(Type::string()))->withDefaultValue('Y-m-d H:i'),
        ]);

        if ($table->hasCreatedAt()) {
            $nodes = $nodes->add(
                GraphqlNode::create()
                    ->withName('createdAt')
                    ->withArguments($arguments)
                    ->withResolver(fn (Record $record, $args) => $record->getCreationDate()->format($args['format']))
            );
        }

        if ($table->hasUpdatedAt()) {
            $nodes = $nodes->add(
                GraphqlNode::create()
                    ->withName('updatedAt')
                    ->withArguments($arguments)
                    ->withResolver(fn (Record $record, $args) => $record->getUpdatedAt()->format($args['format']))
            );
        }

        return $nodes;
    }
}
