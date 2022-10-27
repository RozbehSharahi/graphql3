<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Domain\Model\Tca;

use RozbehSharahi\Graphql3\Converter\CaseConverter;
use RozbehSharahi\Graphql3\Exception\GraphqlException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ColumnConfiguration
{
    public const HARD_NAME_MAP = [
        'sys_language_uid' => 'language',
        'tstamp' => 'updatedAt',
        'crdate' => 'createdAt',
    ];

    public static function create(string $tableName, string $columnName): self
    {
        $table = TableConfiguration::create($tableName);

        $configuration = $table->toArray()['columns'][$columnName] ?? null;

        if (null === $configuration) {
            throw new GraphqlException("Cannot instantiate column-configuration from none TCA field ({$columnName}).");
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

    public function getName(): string
    {
        return $this->name;
    }

    public function getCamelName(): string
    {
        return $this->getConverter()->toCamel($this->name);
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

    public function getForeignTable(): ?string
    {
        return $this->configuration['config']['foreign_table'] ?? null;
    }

    public function getForeignField(): ?string
    {
        return $this->configuration['config']['foreign_field'] ?? null;
    }

    public function hasRelationTable(): bool
    {
        return !empty($this->configuration['config']['MM']);
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
        return !$this->hasRelationTable() && 'inline' === $this->getType() && $this->getForeignTable();
    }

    public function isFile(): bool
    {
        return
            'file' === $this->getType() ||
            ('inline' === $this->getType() && 'sys_file_reference' === $this->getForeignTable());
    }

    public function isManyToOne(): bool
    {
        return 'select' === $this->getType() && 'selectSingle' === $this->getRenderType() && $this->getForeignTable();
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
