<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder;

use GraphQL\Type\Definition\ObjectType;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use RozbehSharahi\Graphql3\Domain\Model\Tca\ColumnConfiguration;
use RozbehSharahi\Graphql3\Domain\Model\Tca\TableConfiguration;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use RozbehSharahi\Graphql3\FieldCreator\FieldCreatorInterface;

class RecordTypeBuilder implements TypeBuilderInterface
{
    public const ERROR_MISSING_FLEX_FORM_POINTER = 'Missing graphql3 flex form field pointer on %s.';

    public const ERROR_INVALID_FLEX_FORM_POINTER = 'Graphql3 flex form field pointer on %s is in valid. Correct type: [table-field-name]::[flex-form-field-path]';

    public const ERROR_COULD_NOT_CREATE_FLEX_FORM_FIELD = 'The flex-form-field %s was configured via TCA, however, there was no flex-form-field-creator that could handle it. Either remove the field from TCA or introduce your own flex-form-field-creator that can handle the field.';

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
            throw new InternalErrorException('No table defined, did you forget to call ->for?');
        }

        return self::$cache[$this->table->getName()] ?? self::$cache[$this->table->getName()] = new ObjectType([
            'name' => $this->table->getPascalSingularName(),
            'fields' => function () {
                $fields = GraphqlNodeCollection::create();

                $columns = [...$this->table->getColumns(), ...$this->table->getFlexFormColumns()];

                foreach ($columns as $column) {
                    $node = $this->resolveNode($column);

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

    protected function resolveNode(ColumnConfiguration $column): ?GraphqlNode
    {
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
