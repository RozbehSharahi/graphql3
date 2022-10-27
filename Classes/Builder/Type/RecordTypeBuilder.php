<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\Type;

use GraphQL\Type\Definition\ObjectType;
use RozbehSharahi\Graphql3\Builder\FieldCreator\FieldCreatorInterface;
use RozbehSharahi\Graphql3\Converter\CaseConverter;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use RozbehSharahi\Graphql3\Domain\Model\Tca\TableConfiguration;
use RozbehSharahi\Graphql3\Exception\GraphqlException;

class RecordTypeBuilder implements TypeBuilderInterface
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

    /**
     * @param iterable<FieldCreatorInterface>              $fieldCreators
     * @param iterable<RecordTypeBuilderExtenderInterface> $extenders
     */
    public function __construct(
        protected CaseConverter $caseConverter,
        protected iterable $fieldCreators,
        protected iterable $extenders
    ) {
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
            throw new GraphqlException('No table defined, did you forget to call ->for?');
        }

        return self::$cache[$this->table->getName()] ?? self::$cache[$this->table->getName()] = new ObjectType([
                'name' => $this->table->getPascalSingularName(),
                'fields' => function () {
                    $fields = GraphqlNodeCollection::create();

                    foreach ($this->table->getColumns() as $columnName) {
                        $node = $this->resolveNode($columnName);

                        if (!$node) {
                            continue;
                        }

                        $fields = $fields->add($node);
                    }

                    foreach ($this->extenders as $extender) {
                        if ($extender->supportsTable($this->table)) {
                            $fields = $extender->extendNodes($this->table, $fields);
                        }
                    }

                    return $fields->toArray();
                },
            ]);
    }

    protected function resolveNode(string $columnName): ?GraphqlNode
    {
        $column = $this->table->getColumn($columnName);

        if (!$column->isGraphqlActive()) {
            return null;
        }

        foreach ($this->fieldCreators as $fieldCreator) {
            if ($fieldCreator->supportsField($column)) {
                return $fieldCreator->createField($column);
            }
        }

        return null;
    }
}
