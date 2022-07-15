<?php

namespace RozbehSharahi\Graphql3\Registry;

use GraphQL\Type\Schema;
use RozbehSharahi\Graphql3\Exception\GraphqlException;

class SiteSchemaRegistry
{
    /**
     * @var array<string,string>
     */
    protected array $siteSchemas = [];

    public function registerSiteSchema(string $siteIdentifier, Schema $schema): self
    {
        $this->siteSchemas[$siteIdentifier] = $schema;

        return $this;
    }

    public function getSchema(string $siteIdentifier): Schema
    {
        if (empty($this->siteSchemas[$siteIdentifier])) {
            throw new GraphqlException('No schema registered for site: '.$siteIdentifier);
        }

        return $this->siteSchemas[$siteIdentifier];
    }

    public function hasSiteSchema(string $siteIdentifier): bool
    {
        return !empty($this->siteSchemas[$siteIdentifier]);
    }
}
