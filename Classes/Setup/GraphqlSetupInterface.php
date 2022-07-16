<?php

namespace RozbehSharahi\Graphql3\Setup;

use TYPO3\CMS\Core\SingletonInterface;

interface GraphqlSetupInterface extends SingletonInterface
{
    public function setup(): void;
}
