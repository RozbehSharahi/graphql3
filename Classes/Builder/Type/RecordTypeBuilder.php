<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\Type;

use GraphQL\Type\Definition\ObjectType;
use RozbehSharahi\Graphql3\Builder\FieldCreator\FieldCreatorInterface;
use RozbehSharahi\Graphql3\Converter\CaseConverter;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use RozbehSharahi\Graphql3\Exception\GraphqlException;

class RecordTypeBuilder implements TypeBuilderInterface
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
        $clone->configuration = $clone->getTca();

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

                    $columNames = array_keys($this->configuration['columns'] ?? []);

                    // Add core fields which are not present in TCA
                    $columNames = ['uid', 'pid', 'tstamp', 'crdate', 'deleted', 'children', ...$columNames];

                    foreach ($columNames as $columnName) {
                        $node = $this->resolveNode($columnName);

                        if (!$node) {
                            continue;
                        }

                        $fields = $fields->add($node);
                    }

                    foreach ($this->extenders as $extender) {
                        if ($extender->supportsTable($this->table)) {
                            $fields = $extender->extendNodes($fields);
                        }
                    }

                    return $fields->toArray();
                },
            ]);
    }

    protected function resolveNode(string $columnName): ?GraphqlNode
    {
        if (!$this->isColumnActive($columnName) || $this->isSensitive($columnName)) {
            return null;
        }

        foreach ($this->fieldCreators as $fieldCreator) {
            if ($fieldCreator->supportsField($this->table, $columnName)) {
                return $fieldCreator->createField($this->table, $columnName);
            }
        }

        return null;
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

    protected function isColumnActive(string $columnName): bool
    {
        return $GLOBALS['TCA'][$this->table]['columns'][$columnName]['config']['graphql3']['active'] ?? true;
    }

    protected function isSensitive(string $columnName): bool
    {
        if ('TSconfig' === $columnName) {
            return true;
        }

        return ($GLOBALS['TCA'][$this->table]['ctrl']['enablecolumns']['fe_group'] ?? null) === $columnName;
    }
}
