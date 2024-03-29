<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Domain\Model\Tca;

use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FlexFormFieldConfiguration
{
    public const ERROR_INVALID_PATH = 'Invalid path given on flex form field configuration: %s';

    public const ERROR_TYPE_MISSING = 'Flex form field %s must have a config.type: %s';

    public const ERROR_MISSING_DATA_STRUCTURE = 'The column "%s" has not data structure. Flex form configurations without explicit data structure definition, are not supported.';

    public const ERROR_MISSING_DATA_STRUCTURE_TYPE = 'The column "%s" has a data-structure however the given type %s could not be found on TCA configuration.';

    public const ERROR_FLEX_COLUMN_NOT_FOUND_IN_DATA_STRUCTURE = 'Could not find flex field %s in structure: %s';

    public static function createFromString(TableConfiguration $table, string $path): self
    {
        if (!substr_count($path, '::')) {
            throw new InternalErrorException(sprintf(self::ERROR_INVALID_PATH, $path));
        }

        [$columnName, $type, $flexColumnName] = explode('::', $path);

        return GeneralUtility::makeInstance(self::class, $table, $type, $columnName, $flexColumnName);
    }

    public function __construct(
        protected TableConfiguration $table,
        protected string $type,
        protected string $columnName,
        protected string $flexColumnName
    ) {
    }

    public function getTable(): TableConfiguration
    {
        return $this->table;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getPath(): string
    {
        return $this->columnName.'::'.$this->flexColumnName;
    }

    public function getColumnName(): string
    {
        return $this->columnName;
    }

    public function getColumn(): ColumnConfiguration
    {
        return $this->table->getColumn($this->columnName);
    }

    public function getFlexColumnName(): string
    {
        return $this->flexColumnName;
    }

    public function getFlexColumn(): ColumnConfiguration
    {
        $column = $this->table->getColumn($this->getColumnName());

        if (!$column->hasDataStructure()) {
            throw new InternalErrorException(sprintf(self::ERROR_MISSING_DATA_STRUCTURE, $this->getColumnName()));
        }

        $structure = $column->getDataStructure()[$this->getType()] ?? null;

        if (!$structure) {
            throw new InternalErrorException(sprintf(self::ERROR_MISSING_DATA_STRUCTURE_TYPE, $this->getColumnName(), $this->getType()));
        }

        if (str_starts_with($structure, 'FILE:')) {
            $structureFile = GeneralUtility::getFileAbsFileName(str_replace('FILE:', '', $structure));
            $structure = file_get_contents($structureFile);
        }

        $xml = new \DOMDocument();
        $xml->loadXML($structure);

        $fieldXml = $xml->getElementsByTagName($this->getFlexColumnName())->item(0) ?: null;

        if (!$fieldXml) {
            throw new InternalErrorException(sprintf(self::ERROR_FLEX_COLUMN_NOT_FOUND_IN_DATA_STRUCTURE, $this->getPath(), $structure));
        }

        $fieldXml = $fieldXml->ownerDocument->saveXML($fieldXml);
        $tcaConfiguration = GeneralUtility::xml2array($fieldXml);

        if (empty($tcaConfiguration['config']['type'])) {
            throw new InternalErrorException(sprintf(self::ERROR_TYPE_MISSING, $this->getPath(), $structure));
        }

        return GeneralUtility::makeInstance(ColumnConfiguration::class,
            $this->table,
            'flex::'.$this->getPath(),
            $tcaConfiguration
        );
    }
}
