<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Site;

use TYPO3\CMS\Core\Site\Entity\SiteInterface;

class CurrentSite
{
    protected SiteInterface $site;

    public function __construct()
    {
    }

    public function set(SiteInterface $site): self
    {
        $this->site = $site;

        return $this;
    }

    public function get(): SiteInterface
    {
        return $this->site;
    }
}
