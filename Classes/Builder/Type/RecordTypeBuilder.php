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

    protected string $table;

    protected TableConfiguration $tableConfiguration;

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

    public function for(string $table): self
    {
        $clone = clone $this;
        $clone->table = $table;
        $clone->tableConfiguration = TableConfiguration::fromTableName($table);

        return $clone;
    }

    public function build(): ObjectType
    {
        if (empty($this->table)) {
            throw new GraphqlException('No table defined, did you forget to call ->for?');
        }

        return self::$cache[$this->table] ?? self::$cache[$this->table] = new ObjectType([
                'name' => $this->caseConverter->toPascalSingular($this->table),
                'fields' => function () {
                    $fields = GraphqlNodeCollection::create();

                    foreach ($this->tableConfiguration->getColumns() as $columnName) {
                        $node = $this->resolveNode($columnName);

                        if (!$node) {
                            continue;
                        }

                        $fields = $fields->add($node);
                    }

                    foreach ($this->extenders as $extender) {
                        if ($extender->supportsTable($this->tableConfiguration)) {
                            $fields = $extender->extendNodes($this->tableConfiguration, $fields);
                        }
                    }

                    return $fields->toArray();
                },
            ]);
    }

    protected function resolveNode(string $columnName): ?GraphqlNode
    {
        $column = $this->tableConfiguration->getColumn($columnName);

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
