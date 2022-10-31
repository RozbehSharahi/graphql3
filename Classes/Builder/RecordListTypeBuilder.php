<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode as Node;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection as Collection;
use RozbehSharahi\Graphql3\Domain\Model\ListRequest;
use RozbehSharahi\Graphql3\Domain\Model\Tca\TableConfiguration;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use RozbehSharahi\Graphql3\Resolver\RecordListResolver;

class RecordListTypeBuilder implements TypeBuilderInterface
{
    /**
     * @var array<string, ObjectType>
     */
    protected static array $cache = [];

    protected TableConfiguration $table;

    public static function flushCache(): void
    {
        self::$cache = [];
    }

    public function __construct(
        protected RecordTypeBuilder $recordTypeBuilder,
        protected RecordListResolver $recordListResolver
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

    public function build(): ObjectType
    {
        if (empty($this->table)) {
            throw new InternalErrorException('No table defined, did you forget to call ->for?');
        }

        return self::$cache[$this->table->getName()] ?? self::$cache[$this->table->getName()] = new ObjectType([
                'name' => $this->table->getPascalSingularName().'List',
                'fields' => function () {
                    return Collection::create()
                        ->add(
                            Node::create('count')
                                ->withType(Type::int())
                                ->withResolver(fn (ListRequest $request) => $this
                                    ->recordListResolver
                                    ->for($this->table)
                                    ->resolveCount($request)
                                )
                        )
                        ->add(
                            Node::create('items')
                                ->withType(Type::listOf($this->recordTypeBuilder->for($this->table)->build()))
                                ->withResolver(fn (ListRequest $request) => $this
                                    ->recordListResolver
                                    ->for($this->table)
                                    ->resolveItems($request))
                        )
                        ->toArray()
                    ;
                },
            ]);
    }
}
