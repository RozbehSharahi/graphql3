<?php

namespace RozbehSharahi\Graphql3TestExtension;

use GraphQL\Type\Schema;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Exception\GraphqlException;
use RozbehSharahi\Graphql3\Registry\PageArgumentRegistry;
use RozbehSharahi\Graphql3\Registry\QueryFieldRegistry;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Registry\TypeRegistry;
use RozbehSharahi\Graphql3\Setup\SetupInterface;
use RozbehSharahi\Graphql3\Type\RegistryBasedPageType;
use RozbehSharahi\Graphql3\Type\RegistryBasedQueryType;
use TYPO3\CMS\Core\Database\ConnectionPool;

class GraphqlSetup implements SetupInterface
{
    public function __construct(
        protected SchemaRegistry $schemaRegistry,
        protected TypeRegistry $typeRegistry,
        protected QueryFieldRegistry $queryFieldRegistry,
        protected PageArgumentRegistry $pageArgumentRegistry,
        protected ConnectionPool $connectionPool
    ) {
    }

    public function setup(): void
    {
        // Register schema
        $this->schemaRegistry->register(new Schema([
            'query' => $this->typeRegistry->get(RegistryBasedQueryType::class),
        ]));

        // Register some query fields
        $this->queryFieldRegistry
            ->register(
                GraphqlNode::create('page')
                    ->withType($this->typeRegistry->get(RegistryBasedPageType::class))
                    ->withArguments($this->pageArgumentRegistry->getArguments())
                    ->withResolver(fn ($_, $args) => $this->getPage($args['uid']))
            );
    }

    protected function getPage(int $id): array
    {
        $query = $this
            ->connectionPool
            ->getQueryBuilderForTable('page');

        $query
            ->select('*')
            ->from('pages')
            ->where('uid='.$query->createNamedParameter($id, \PDO::PARAM_INT));

        try {
            $page = $query->executeQuery()->fetchAssociative();
        } catch (\Throwable $e) {
            throw new GraphqlException('Error on fetching page from database :'.$e->getMessage());
        }

        if (!$page) {
            throw GraphqlException::createClientSafe('Could not fetch page with id:'.$id);
        }

        return $page;
    }
}
