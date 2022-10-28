<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\Node;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Builder\Type\RecordListTypeBuilder;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgument;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\ListRequest;
use RozbehSharahi\Graphql3\Domain\Model\Tca\TableConfiguration;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use RozbehSharahi\Graphql3\Type\FilterInputType;
use RozbehSharahi\Graphql3\Type\OrderItemInputType;

class RecordListNodeBuilder implements NodeBuilderInterface
{
    protected TableConfiguration $table;

    public function __construct(
        protected RecordListTypeBuilder $recordListTypeBuilder,
        protected OrderItemInputType $orderFieldType,
        protected FilterInputType $filterInputType
    ) {
    }

    public function getTable(): TableConfiguration
    {
        return $this->table;
    }

    public function for(string|TableConfiguration $table): self
    {
        $clone = clone $this;

        if (is_string($table)) {
            $table = TableConfiguration::create($table);
        }

        $clone->table = $table;

        return $clone;
    }

    public function build(): GraphqlNode
    {
        if (empty($this->table)) {
            throw new InternalErrorException('Can not create node without table give, did you call ->for?');
        }

        $arguments = GraphqlArgumentCollection::create([
            GraphqlArgument::create('page')->withType(Type::nonNull(Type::int()))->withDefaultValue(1),
            GraphqlArgument::create('pageSize')->withType(Type::nonNull(Type::int()))->withDefaultValue(10),
            GraphqlArgument::create('orderBy')->withType(Type::listOf($this->orderFieldType)),
            GraphqlArgument::create('filters')->withType(Type::listOf($this->filterInputType)),
            GraphqlArgument::create('publicRequest')->withType(Type::boolean())->withDefaultValue(true),
        ]);

        if ($this->table->hasLanguage()) {
            $arguments = $arguments->add(GraphqlArgument::create('language')->withType(Type::string()));
        }

        return GraphqlNode::create($this->table->getCamelPluralName())
            ->withArguments($arguments)
            ->withType($this->recordListTypeBuilder->for($this->table)->build())
            ->withResolver(fn ($_, $args) => ListRequest::create($args))
        ;
    }
}
