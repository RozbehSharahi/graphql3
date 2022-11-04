<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Domain\Model;

use RozbehSharahi\Graphql3\Domain\Model\Tca\ColumnConfiguration;
use RozbehSharahi\Graphql3\Domain\Model\Tca\TableConfiguration;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use RozbehSharahi\Graphql3\Session\CurrentSession;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Record
{
    /**
     * @param array<string, mixed> $row
     */
    public static function create(string|TableConfiguration $table, array $row): self
    {
        if (is_string($table)) {
            $table = TableConfiguration::create($table);
        }

        return GeneralUtility::makeInstance(self::class, $table, $row);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(protected TableConfiguration $table, protected array $data)
    {
    }

    public function get(ColumnConfiguration|string $column): mixed
    {
        if ($column instanceof ColumnConfiguration) {
            $column = $column->getName();
        }

        return $this->data[$column] ?? null;
    }

    public function getTable(): TableConfiguration
    {
        return $this->table;
    }

    public function getUid(): int
    {
        return $this->data['uid'];
    }

    public function getPid(): int
    {
        return $this->data['pid'];
    }

    public function isRoot(): bool
    {
        return empty($this->getPid());
    }

    public function getLanguageUid(): int
    {
        return $this->data[$this->table->getLanguage()];
    }

    public function getLanguage(): SiteLanguage
    {
        return GeneralUtility::makeInstance(CurrentSession::class)->getSite()->getLanguageById($this->getLanguageUid());
    }

    public function getCreationDate(): \DateTimeImmutable
    {
        return (new \DateTimeImmutable())->setTimestamp($this->data[$this->table->getCreatedAt()]);
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return (new \DateTimeImmutable())->setTimestamp($this->data[$this->table->getUpdatedAt()]);
    }

    public function isTranslation(): bool
    {
        return $this->table->hasLanguage() && $this->getLanguageUid() > 0;
    }

    public function hasLanguageParent(): bool
    {
        return $this->table->hasLanguageParent() && !empty($this->data[$this->table->getLanguageParent()]);
    }

    public function hasLanguageField(): bool
    {
        return $this->table->hasLanguage();
    }

    public function getLanguageParentUid(): int
    {
        return $this->data[$this->table->getLanguageParent()];
    }

    /**
     * @return array<int, string>
     */
    public function getFrontendGroups(): array
    {
        if (!$this->table->hasAccessControl()) {
            return [];
        }

        $groupList = $this->data[$this->table->getAccessControl()] ?? null;

        return !empty($groupList) ? explode(',', $groupList) : [];
    }

    public function isShowAtAnyLogin(): bool
    {
        return in_array('-2', $this->getFrontendGroups(), false);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    public function assertRootPageLanguageIntegrity(): self
    {
        if ('pages' !== $this->table->getName() || !$this->isRoot()) {
            return $this;
        }

        if ($this->isTranslation() && !$this->hasLanguageParent()) {
            throw new InternalErrorException('Integrity failure found on resolving page children. A root page (uid='.$this->data['uid'].') was found of language='.$this->getLanguageUid().' but without parent-language relation (l10n_parent).');
        }

        return $this;
    }
}
