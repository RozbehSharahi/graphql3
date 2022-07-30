<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Site;

use TYPO3\CMS\Core\Site\Entity\Site;

class CurrentSite
{
    protected Site $site;

    public function __construct()
    {
    }

    public function set(Site $site): self
    {
        $this->site = $site;

        return $this;
    }

    public function get(): Site
    {
        return $this->site;
    }
}
