<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Domain\Model\Tca;

use RozbehSharahi\Graphql3\Converter\CaseConverter;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ColumnConfiguration
{
    public const HARD_NAME_MAP = [
        'sys_language_uid' => 'language',
        'tstamp' => 'updatedAt',
        'crdate' => 'createdAt',
    ];

    public static function fromTableAndColumnOrNull(string $table, string $column): ?self
    {
        return !empty($GLOBALS['TCA'][$table]['columns'][$column])
            ? self::fromTableAndColumn($table, $column)
            : null;
    }

    public static function fromTableAndColumn(string $table, string $column): self
    {
        return new self($table, $column, $GLOBALS['TCA'][$table]['columns'][$column]);
    }

    /**
     * @param array<string, mixed> $configuration
     */
    public static function fromConfiguration(string $table, string $column, array $configuration): self
    {
        return new self($table, $column, $configuration);
    }

    /**
     * @param array<string, mixed> $configuration
     */
    public function __construct(protected string $table, protected string $column, protected array $configuration)
    {
    }

    public function getForeignTable(): ?string
    {
        return $this->configuration['config']['foreign_table'] ?? null;
    }

    public function getGraphqlName(): ?string
    {
        if ($graphql3Name = $this->configuration['config']['graphql3']['name'] ?? null) {
            return $graphql3Name;
        }

        if ($hardMappedName = self::HARD_NAME_MAP[$this->column] ?? null) {
            return $hardMappedName;
        }

        return $this->getConverter()->toCamel($this->column);
    }

    public function getType(): ?string
    {
        return $this->configuration['config']['type'] ?? null;
    }

    public function getRenderType(): ?string
    {
        return $this->configuration['config']['renderType'] ?? null;
    }

    public function getFormat(): ?string
    {
        return $this->configuration['config']['format'] ?? null;
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
        return 'inline' === $this->getType() && $this->getForeignTable();
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
        return TableConfiguration::fromTableName($this->table)->getLanguageParentFieldName() === $this->column;
    }

    protected function getConverter(): CaseConverter
    {
        return GeneralUtility::makeInstance(CaseConverter::class);
    }
}
