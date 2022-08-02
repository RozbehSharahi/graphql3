<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Converter\CaseConverter;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode as Node;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection as Collection;
use RozbehSharahi\Graphql3\Domain\Model\ListRequest;
use RozbehSharahi\Graphql3\Exception\GraphqlException;
use RozbehSharahi\Graphql3\Resolver\RecordListResolver;

class RecordListTypeBuilder implements TypeBuilderInterface
{
    /**
     * @var array<string, ObjectType>
     */
    protected static array $cache = [];

    protected string $table;

    /**
     * @var array<string, mixed>
     */
    protected array $configuration;

    public static function flushCache(): void
    {
        self::$cache = [];
    }

    public function __construct(
        protected CaseConverter $caseConverter,
        protected RecordTypeBuilder $recordTypeBuilder,
        protected RecordListResolver $recordListResolver
    ) {
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function for(string $table): self
    {
        $clone = clone $this;
        $clone->table = $table;
        $clone->configuration = $clone->getTca();

        return $clone;
    }

    public function build(): ObjectType
    {
        if (empty($this->table)) {
            throw new GraphqlException('No table defined, did you forget to call ->for?');
        }

        return self::$cache[$this->table] ?? self::$cache[$this->table] = new ObjectType([
                'name' => $this->caseConverter->toPascalSingular($this->table).'List',
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

    /**
     * @return array<string, mixed>
     */
    protected function getTca(): array
    {
        $tca = $GLOBALS['TCA'][$this->table] ?? null;

        if (!$tca) {
            throw new GraphqlException('Cannot create a graphql type for a table without TCA definition.');
        }

        return $tca;
    }
}
