<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder;

use GraphQL\Type\Definition\Type;
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
    public const ERROR_NO_TABLE_SET = 'Can not create node without table give, did you call ->for?';
    protected TableConfiguration $table;

    /**
     * @param iterable<RecordListNodeExtenderInterface> $extenders
     */
    public function __construct(
        protected RecordListTypeBuilder $recordListTypeBuilder,
        protected OrderItemInputType $orderFieldType,
        protected FilterInputType $filterInputType,
        protected iterable $extenders
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
        $this->assertTable();

        return GraphqlNode::create()
            ->withName($this->table->getCamelPluralName())
            ->withArguments($this->buildArguments())
            ->withType($this->buildType())
            ->withResolver($this->buildResolver())
        ;
    }

    public function buildArguments(): GraphqlArgumentCollection
    {
        $this->assertTable();

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

        foreach ($this->extenders as $extender) {
            if ($extender->supportsTable($this->table)) {
                $arguments = $extender->extendArguments($this->table, $arguments);
            }
        }

        return $arguments;
    }

    public function buildType(): Type
    {
        $this->assertTable();

        return $this->recordListTypeBuilder->for($this->table)->build();
    }

    public function buildResolver(): \Closure
    {
        return static fn ($_, $args) => ListRequest::create($args);
    }

    protected function assertTable(): self
    {
        if (empty($this->table)) {
            throw new InternalErrorException(self::ERROR_NO_TABLE_SET);
        }

        return $this;
    }
}
