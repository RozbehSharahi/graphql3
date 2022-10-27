<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\Node;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Builder\Type\RecordTypeBuilder;
use RozbehSharahi\Graphql3\Converter\CaseConverter;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgument;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\ItemRequest;
use RozbehSharahi\Graphql3\Domain\Model\Tca\TableConfiguration;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use RozbehSharahi\Graphql3\Resolver\RecordResolver;

class RecordNodeBuilder implements NodeBuilderInterface
{
    protected TableConfiguration $table;

    /**
     * @param iterable<RecordNodeExtenderInterface> $extenders
     */
    public function __construct(
        protected RecordTypeBuilder $recordTypeBuilder,
        protected RecordResolver $recordResolver,
        protected CaseConverter $caseConverter,
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
        if (empty($this->table)) {
            throw new InternalErrorException('No table given, did you forget to call ->for?');
        }

        $arguments = GraphqlArgumentCollection::create([
            GraphqlArgument::create('uid')->withType(Type::nonNull(Type::int())),
            GraphqlArgument::create('publicRequest')->withType(Type::boolean())->withDefaultValue(true),
        ]);

        foreach ($this->extenders as $extender) {
            if ($extender->supportsTable($this->table)) {
                $arguments = $extender->extendArguments($this->table, $arguments);
            }
        }

        return GraphqlNode::create($this->table->getCamelSingularName())
            ->withType($this->recordTypeBuilder->for($this->table)->build())
            ->withArguments($arguments)
            ->withResolver(fn ($_, $args) => $this
                ->recordResolver
                ->for($this->table)->resolve(ItemRequest::create($args)))
        ;
    }
}
