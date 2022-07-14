<?php

use RozbehSharahi\Graphql3\Builder\NoopSchemaBuilder;
use RozbehSharahi\Graphql3\Registry\SiteSchemaRegistry;

$siteSchemaRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(SiteSchemaRegistry::class);
$siteSchemaRegistry->registerSiteSchema('main', (new NoopSchemaBuilder())->build());
