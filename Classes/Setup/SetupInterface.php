<?php

namespace RozbehSharahi\Graphql3\Setup;

use TYPO3\CMS\Core\SingletonInterface;

interface SetupInterface extends SingletonInterface
{
    public function setup(): void;
}
