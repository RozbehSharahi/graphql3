<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Domain\Model\Tca;

use RozbehSharahi\Graphql3\Converter\CaseConverter;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ColumnConfiguration
{
    public const HARD_NAME_MAP = [
        'sys_language_uid' => 'language',
        'tstamp' => 'updatedAt',
        'crdate' => 'createdAt',
    ];

    public static function create(string|TableConfiguration $table, string $columnName): self
    {
        if (is_string($table)) {
            $table = TableConfiguration::create($table);
        }

        $configuration = $table->toArray()['columns'][$columnName] ?? null;

        if (null === $configuration) {
            throw new InternalErrorException("Cannot instantiate column-configuration from none TCA field ({$columnName}).");
        }

        return GeneralUtility::makeInstance(self::class, $table, $columnName, $configuration);
    }

    /**
     * @param array<string, mixed> $configuration
     */
    public function __construct(
        protected TableConfiguration $table,
        protected string $name,
        protected array $configuration
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFullName(): string
    {
        return $this->table->getName().'::'.$this->name;
    }

    public function getCamelName(): string
    {
        return $this->getConverter()->toCamel($this->name);
    }

    public function getPascalGraphqlName(): string
    {
        return $this->getConverter()->toPascal($this->getGraphqlName());
    }

    public function getGraphqlName(): ?string
    {
        if ($graphql3Name = $this->configuration['config']['graphql3']['name'] ?? null) {
            return $graphql3Name;
        }

        if ($hardMappedName = self::HARD_NAME_MAP[$this->name] ?? null) {
            return $hardMappedName;
        }

        return $this->getCamelName();
    }

    public function getTable(): TableConfiguration
    {
        return $this->table;
    }

    public function hasForeignTable(): bool
    {
        return !empty($this->configuration['config']['foreign_table']);
    }

    public function getForeignTable(): TableConfiguration
    {
        return TableConfiguration::create($this->configuration['config']['foreign_table']);
    }

    public function getForeignField(): ?string
    {
        return $this->configuration['config']['foreign_field'] ?? null;
    }

    public function hasRelationTable(): bool
    {
        return !empty($this->configuration['config']['MM']);
    }

    public function hasManyToManyOpposite(): bool
    {
        return !empty($this->configuration['config']['MM_opposite_field']);
    }

    public function hasDataStructure(): bool
    {
        return !empty($this->configuration['config']['ds']);
    }

    /**
     * @return array<string, string>
     */
    public function getDataStructure(): array
    {
        return $this->configuration['config']['ds'];
    }

    public function hasDataStructurePointer(): bool
    {
        return !empty($this->configuration['config']['ds_pointerField']);
    }

    public function getDataStructurePointer(): string
    {
        return $this->configuration['config']['ds_pointerField'];
    }

    public function hasInheritedDataStructure(): bool
    {
        return !empty($this->configuration['config']['ds_pointerField_searchParent']);
    }

    public function getManyToManyOpposite(): string
    {
        return $this->configuration['config']['MM_opposite_field'];
    }

    public function getRelationTable(): string
    {
        return $this->configuration['config']['MM'];
    }

    public function getType(): string
    {
        return $this->configuration['config']['type'];
    }

    public function getRenderType(): ?string
    {
        return $this->configuration['config']['renderType'] ?? null;
    }

    public function getFormat(): string
    {
        return $this->configuration['config']['format'] ?? 'integer';
    }

    public function hasFlexFormPointer(): bool
    {
        return !empty($this->configuration['config']['flexFormPointer']);
    }

    public function isValidFlexFormPointer(): bool
    {
        return str_contains($this->getFlexFormPointer(), '::');
    }

    public function getFlexFormPointer(): string
    {
        return $this->configuration['config']['flexFormPointer'];
    }

    public function isBool(): bool
    {
        return 'check' === $this->getType() && count($this->configuration['config']['items'] ?? []) <= 1;
    }

    public function isString(): bool
    {
        return 'input' === $this->getType() || 'text' === $this->getType();
    }

    public function isInt(): bool
    {
        return 'number' === $this->getType() && 'decimal' !== $this->getFormat();
    }

    public function isFloat(): bool
    {
        return 'number' === $this->getType() && 'decimal' === $this->getFormat();
    }

    public function isDateTime(): bool
    {
        return 'datetime' === $this->getType();
    }

    public function isOneToMany(): bool
    {
        return !$this->hasRelationTable() && 'inline' === $this->getType() && $this->hasForeignTable();
    }

    public function isFile(): bool
    {
        if ('file' === $this->getType()) {
            return true;
        }

        if ('inline' !== $this->getType()) {
            return false;
        }

        return $this->hasForeignTable() && 'sys_file_reference' === $this->getForeignTable()->getName();
    }

    public function isManyToOne(): bool
    {
        return 'select' === $this->getType() && 'selectSingle' === $this->getRenderType() && $this->hasForeignTable();
    }

    public function isManyToMany(): bool
    {
        return $this->hasRelationTable() && in_array($this->getType(), ['select', 'inline'], true);
    }

    public function isSingleSelect(): bool
    {
        return 'select' === $this->getType() && 'selectSingle' === $this->getRenderType() && !$this->hasForeignTable();
    }

    public function isLanguage(): bool
    {
        return 'language' === $this->getType();
    }

    public function isLanguageParent(): bool
    {
        return $this->table->hasLanguageParent() && $this->table->getLanguageParent() === $this->name;
    }

    public function isImageManipulation(): bool
    {
        return 'imageManipulation' === $this->getType();
    }

    public function isFlex(): bool
    {
        return 'flex' === $this->getType();
    }

    protected function getConverter(): CaseConverter
    {
        return GeneralUtility::makeInstance(CaseConverter::class);
    }

    public function getForeignTableField(): string
    {
        return $this->configuration['config']['foreign_table_field'];
    }

    /**
     * @return array<string, string>
     */
    public function getForeignMatchFields(): array
    {
        return $this->configuration['config']['foreign_match_fields'] ?? [];
    }

    public function isGraphqlActive(): bool
    {
        $active = $this->configuration['config']['graphql3']['active'] ?? true;

        return $active && !$this->isSensitive();
    }

    public function isSensitive(): bool
    {
        if ('TSconfig' === $this->name) {
            return true;
        }

        return $this->table->hasAccessControl() && $this->table->getAccessControl() === $this->name;
    }
}
