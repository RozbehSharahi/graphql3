<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Environment;

use TYPO3\CMS\Core\Utility\VersionNumberUtility;

class Typo3Environment
{
    public function getMainVersion(): int
    {
        return (int) explode('.', VersionNumberUtility::getCurrentTypo3Version())[0];
    }

    public function isVersion(int $version): bool
    {
        return $this->getMainVersion() === $version;
    }
}
