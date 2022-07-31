<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tests\Functional\Core;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use GraphQL\Type\Schema;
use Psr\Container\ContainerInterface;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Type\NoopQueryType;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Core\ApplicationContext;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Schema\SchemaMigrator;
use TYPO3\CMS\Core\Database\Schema\SqlReader;
use TYPO3\CMS\Core\Middleware\VerifyHostHeader;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Frontend\Http\Application;

class FunctionScopeBuilder
{
    public const DEFAULT_INSTANCE_NAME = 'test-app';

    public const DEFAULT_AUTO_CREATE_SCHEMA = true;

    public const DEFAULT_AUTO_CREATE_HOMEPAGE = true;

    public const DEFAULT_AUTO_CREATE_SITE = true;

    public const DEFAULT_AUTO_CREATE_GRAPHQL_SCHEMA = true;

    public const DEFAULT_CONTEXT = 'Testing';

    public const DEFAULT_FRESH_DATABASE = false;

    public const DEFAULT_SITE_ROOT_PAGE_ID = 1;

    public const DEFAULT_LOCAL_CONFIGURATION = [
        'SYS' => [
            'encryptionKey' => 'testing',
            'trustedHostsPattern' => VerifyHostHeader::ENV_TRUSTED_HOSTS_PATTERN_ALLOW_ALL,
        ],
        'DB' => [
            'Connections' => [
                'Default' => [
                    'charset' => 'utf8',
                    'driver' => 'pdo_sqlite',
                    'path' => null,
                ],
            ],
        ],
    ];

    protected string $instanceName = self::DEFAULT_INSTANCE_NAME;

    /**
     * @var array<string, mixed>
     */
    protected array $configuration = self::DEFAULT_LOCAL_CONFIGURATION;

    protected bool $autoCreateSchema = self::DEFAULT_AUTO_CREATE_SCHEMA;

    protected bool $autoCreateHomepage = self::DEFAULT_AUTO_CREATE_HOMEPAGE;

    protected bool $autoCreateSite = self::DEFAULT_AUTO_CREATE_SITE;

    protected bool $autoCreateGraphqlSchema = self::DEFAULT_AUTO_CREATE_GRAPHQL_SCHEMA;

    protected bool $freshDatabase = self::DEFAULT_FRESH_DATABASE;

    protected int $siteRootPageId = self::DEFAULT_SITE_ROOT_PAGE_ID;

    protected string $context = self::DEFAULT_CONTEXT;

    /**
     * @var array<string,int|boolean|string|null>|null
     */
    protected ?array $loggedInUser = null;

    protected ContainerInterface $container;

    protected Application $application;

    public function getInstanceName(): string
    {
        return $this->instanceName;
    }

    public function withInstanceName(string $instanceName): self
    {
        $clone = clone $this;
        $clone->instanceName = $instanceName;

        return $clone;
    }

    public function getSiteRootPageId(): int
    {
        return $this->siteRootPageId;
    }

    public function withSiteRootPageId(int $siteRootPageId): self
    {
        $clone = clone $this;
        $clone->siteRootPageId = $siteRootPageId;

        return $clone;
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    /**
     * @param array<string, mixed> $configuration
     */
    public function withAdditionalConfiguration(array $configuration): self
    {
        $clone = clone $this;
        $clone->configuration = array_replace_recursive($this->configuration, $configuration);

        return $clone;
    }

    public function isAutoCreateHomepage(): bool
    {
        return $this->autoCreateHomepage;
    }

    public function withAutoCreateHomepage(bool $autoCreateHomepage): self
    {
        $clone = clone $this;
        $clone->autoCreateHomepage = $autoCreateHomepage;

        return $clone;
    }

    public function isAutoCreateSchema(): bool
    {
        return $this->autoCreateSchema;
    }

    public function withAutoCreateSchema(bool $autoCreateSchema): self
    {
        $clone = clone $this;
        $clone->autoCreateSchema = $autoCreateSchema;

        return $clone;
    }

    public function isFreshDatabase(): bool
    {
        return $this->freshDatabase;
    }

    public function withFreshDatabase(bool $freshDatabase): self
    {
        $clone = clone $this;
        $clone->freshDatabase = $freshDatabase;

        return $clone;
    }

    public function isAutoCreateSite(): bool
    {
        return $this->autoCreateSite;
    }

    public function withAutoCreateSite(bool $autoCreateSite): self
    {
        $clone = clone $this;
        $clone->autoCreateSite = $autoCreateSite;

        return $clone;
    }

    public function isAutoCreateGraphqlSchema(): bool
    {
        return $this->autoCreateGraphqlSchema;
    }

    public function withAutoCreateGraphqlSchema(bool $autoCreateGraphqlSchema): self
    {
        $clone = clone $this;
        $clone->autoCreateGraphqlSchema = $autoCreateGraphqlSchema;

        return $clone;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getLoggedInUser(): ?array
    {
        return $this->loggedInUser;
    }

    /**
     * @param array<string, string|int|boolean|null>|null $loggedInUser
     */
    public function withLoggedInUser(?array $loggedInUser): self
    {
        $clone = clone $this;
        $clone->loggedInUser = $loggedInUser;

        return $clone;
    }

    public function getContext(): string
    {
        return $this->context;
    }

    public function withContext(string $context): self
    {
        $clone = clone $this;
        $clone->context = $context;

        return $clone;
    }

    public function getInstancePath(): string
    {
        $root = realpath(__DIR__.'/../../..');

        return $root.'/var/tests/functional-'.$this->instanceName;
    }

    public function getDatabasePath(): string
    {
        return $this->getPath('/database.sqlite');
    }

    public function getPath(string $subPath = ''): string
    {
        return $this->getInstancePath().$subPath;
    }

    public function build(): FunctionalScope
    {
        $classLoader = require __DIR__.'/../../../vendor/autoload.php';

        @unlink($this->getDatabasePath());
        @mkdir($this->getInstancePath(), 0777, true);
        @mkdir($this->getPath('/public'), 0777, true);
        @mkdir($this->getPath('/public/typo3conf'), 0777, true);

        $configuration = $this->configuration;
        $configuration['DB']['Connections']['Default']['path'] = $this->getDatabasePath();

        file_put_contents(
            $this->getPath('/public/typo3conf/LocalConfiguration.php'),
            $this->getPhpFile($configuration)
        );

        SystemEnvironmentBuilder::run();

        Environment::initialize(
            new ApplicationContext($this->context),
            false,
            true,
            $this->getInstancePath(),
            $this->getPath('/public'),
            $this->getPath('/var'),
            $this->getPath('/config'),
            $this->getPath('/index.php'),
            'UNIX'
        );

        $container = Bootstrap::init($classLoader, false);
        ob_end_clean();

        if ($this->autoCreateSchema) {
            $this->createDatabaseStructure();
        }

        if ($this->autoCreateHomepage) {
            $this->createHomepage();
        }

        if ($this->autoCreateSite) {
            $this->createSite();
        }

        if ($this->autoCreateGraphqlSchema) {
            /** @var SchemaRegistry $schemaRegistry */
            $schemaRegistry = $container->get(SchemaRegistry::class);
            $schemaRegistry->register(new Schema(['query' => new NoopQueryType()]));
        }

        $this->clearSiteFinderCache();

        if (!$container instanceof \Symfony\Component\DependencyInjection\ContainerInterface) {
            throw new \RuntimeException('Expected to have symfony container interface, but didnt');
        }

        $scope = new FunctionalScope($container, $this->loggedInUser);

        if ($this->loggedInUser) {
            $scope->createRecord('fe_users', $this->loggedInUser);
        }

        return $scope;
    }

    protected function createHomepage(): self
    {
        $query = $this->getQueryBuilder('pages');

        $query
            ->insert('pages')
            ->values(['uid' => 1, 'pid' => 0, 'title' => 'root page'])
            ->executeStatement()
        ;

        return $this;
    }

    protected function createSite(): self
    {
        $configuration = [
            'rootPageId' => $this->siteRootPageId,
            'base' => '/'.$this->instanceName,
            'languages' => [
                [
                    'title' => 'English',
                    'enabled' => true,
                    'languageId' => 0,
                    'base' => '/',
                    'typo3Language' => 'default',
                    'locale' => 'en_US.UTF-8',
                    'iso-639-1' => 'en',
                    'navigationTitle' => '',
                    'hreflang' => '',
                    'direction' => '',
                    'flag' => 'us',
                ],
                [
                    'title' => 'Austrian',
                    'enabled' => true,
                    'languageId' => 1,
                    'base' => '/de',
                    'typo3Language' => 'de',
                    'locale' => 'de_AT.UTF-8',
                    'iso-639-1' => 'de',
                    'navigationTitle' => '',
                    'hreflang' => '',
                    'direction' => '',
                    'flag' => 'de',
                ],
            ],
            'errorHandling' => [],
            'routes' => [],
        ];

        @mkdir($this->getPath('/config/sites/'.$this->instanceName), 0777, true);
        file_put_contents(
            $this->getPath('/config/sites/'.$this->instanceName.'/config.yaml'),
            Yaml::dump($configuration, 99, 2)
        );

        return $this;
    }

    protected function createDatabaseStructure(): self
    {
        $this->getConnection()->close();

        if (!$this->freshDatabase) {
            copy(__DIR__.'/../../Fixture/Database/base-database.sqlite', $this->getDatabasePath());

            return $this;
        }

        $schemaManager = $this->getSchemaManager();

        foreach ($schemaManager->listTableNames() as $tableName) {
            $this->getConnection()->truncate($tableName);
        }

        $schemaMigrationService = GeneralUtility::makeInstance(SchemaMigrator::class);
        $sqlReader = GeneralUtility::makeInstance(SqlReader::class);
        $sqlCode = $sqlReader->getTablesDefinitionString(true);

        $createTableStatements = $sqlReader->getCreateTableStatementArray($sqlCode);

        $schemaMigrationService->install($createTableStatements);

        $insertStatements = $sqlReader->getInsertStatementArray($sqlCode);
        $schemaMigrationService->importStaticData($insertStatements);

        return $this;
    }

    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }

    protected function getConnection(): Connection
    {
        return $this->getConnectionPool()->getConnectionByName('Default');
    }

    protected function getQueryBuilder(string $table): QueryBuilder
    {
        return $this->getConnectionPool()->getQueryBuilderForTable($table);
    }

    protected function getSchemaManager(): AbstractSchemaManager
    {
        return $this->getConnection()->createSchemaManager();
    }

    /**
     * @param array<string, mixed> $configuration
     */
    protected function getPhpFile(array $configuration): string
    {
        return '<?php return '.var_export($configuration, true).';';
    }

    /** @noinspection PhpExpressionResultUnusedInspection */
    protected function clearSiteFinderCache(): self
    {
        /** @var SiteFinder $siteFinder */
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);

        // Typo3 BUG: does not empty mappingRootPageIdToIdentifier on useCache = false
        $reflection = new \ReflectionClass($siteFinder);
        $property = $reflection->getProperty('mappingRootPageIdToIdentifier');
        $property->setAccessible(true);
        $property->setValue($siteFinder, []);

        $siteFinder->getAllSites(false);

        RootlineUtility::purgeCaches();
        GeneralUtility::makeInstance(CacheManager::class)->getCache('rootline')->flush();

        return $this;
    }
}
