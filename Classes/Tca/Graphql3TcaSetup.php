<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tca;

class Graphql3TcaSetup
{
    public static function setupPagesTca(): void
    {
        $GLOBALS['TCA']['pages']['columns']['nav_title']['config']['graphql3']['name'] = 'navigationTitle';
        $GLOBALS['TCA']['pages']['columns']['nav_hide']['config']['graphql3']['name'] = 'navigationHide';
        $GLOBALS['TCA']['pages']['columns']['is_siteroot']['config']['graphql3']['name'] = 'siteRoot';
        $GLOBALS['TCA']['pages']['columns']['l10n_parent']['config']['graphql3']['name'] = 'languageParent';
        $GLOBALS['TCA']['pages']['columns']['rowDescription']['config']['graphql3']['active'] = false;
        $GLOBALS['TCA']['pages']['columns']['php_tree_stop']['config']['graphql3']['active'] = false;
        $GLOBALS['TCA']['pages']['columns']['extendToSubpages']['config']['graphql3']['active'] = false;
        $GLOBALS['TCA']['pages']['columns']['lastUpdated']['config']['graphql3']['active'] = false;
    }
}
