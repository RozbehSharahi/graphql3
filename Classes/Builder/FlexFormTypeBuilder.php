<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder;

use GraphQL\Type\Definition\ObjectType;
use RozbehSharahi\Graphql3\Domain\Model\Tca\ColumnConfiguration;
use RozbehSharahi\Graphql3\Domain\Model\Tca\TableConfiguration;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;

class FlexFormTypeBuilder implements TypeBuilderInterface
{
    public const ERROR_NOT_CONFIGURED = 'flex-form-type-builder is not configured. Did you forget to call ->for(...)?';

    public const TYPE_DEFAULT = 'default';

    /**
     * @var array<string, ObjectType>
     */
    protected static array $cache = [];

    protected ColumnConfiguration $column;

    protected string $type = self::TYPE_DEFAULT;

    public static function flushCache(): void
    {
        self::$cache = [];
    }

    public function __construct(protected FlexFormTools $flexFormTools, protected RecordNodeBuilder $recordNodeBuilder)
    {
    }

    public function for(ColumnConfiguration $column, string $type = self::TYPE_DEFAULT): self
    {
        $clone = clone $this;
        $clone->column = $column;
        $clone->type = $type;

        return $clone;
    }

    public function getColumn(): ColumnConfiguration
    {
        return $this->column;
    }

    public function build(): ObjectType
    {
        $this->assertConfigured();

        $cacheKey = $this->column->getTable()->getName().'::'.$this->column->getName();

        return self::$cache[$cacheKey] ?? (self::$cache[$cacheKey] = $this->buildType());
    }

    protected function buildType(): ObjectType
    {
        $typeName = $this->column->getTable()->getPascalSingularName().$this->column->getPascalGraphqlName();

        $fakeRow = [$this->column->getName() => null];

        if ($this->column->hasDataStructurePointer()) {
            $pointers = explode(',', $this->column->getDataStructurePointer());
            $pointers = array_map('trim', $pointers);
            foreach ($pointers as $pointer) {
                $fakeRow[$pointer] = ''; // this will currently always cause default type
            }
        }

        $dataStructureIdentifier = $this->flexFormTools->getDataStructureIdentifier(
            $this->column->getConfiguration(),
            $this->column->getTable()->getName(),
            $this->column->getName(),
            $fakeRow
        );

        $structure = $this->flexFormTools->parseDataStructureByIdentifier($dataStructureIdentifier);
        $structure = $this->flexFormTools->removeElementTceFormsRecursive($structure);

        $elements = $structure['sheets']['sDEF']['ROOT']['el'] ?? [];

        $fakeTable = new TableConfiguration($typeName, ['columns' => $elements]);

        return $this->recordNodeBuilder->for($fakeTable)->buildType();
    }

    protected function assertConfigured(): self
    {
        if (empty($this->column) || empty($this->type)) {
            throw new InternalErrorException(self::ERROR_NOT_CONFIGURED);
        }

        return $this;
    }
}
