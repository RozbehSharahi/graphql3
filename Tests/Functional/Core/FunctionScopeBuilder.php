<?php

/** @noinspection PhpReturnValueOfMethodIsNeverUsedInspection */

/** @noinspection PhpUnhandledExceptionInspection */

namespace RozbehSharahi\Graphql3\Tests\Functional\Core;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Psr\Container\ContainerInterface;
use RozbehSharahi\Graphql3\Builder\NoopSchemaBuilder;
use RozbehSharahi\Graphql3\Registry\SiteSchemaRegistry;
use Symfony\Component\Yaml\Yaml;
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
use TYPO3\CMS\Frontend\Http\Application;

class FunctionScopeBuilder
{
    public const DEFAULT_INSTANCE_NAME = 'test-app';

    public const DEFAULT_AUTO_CREATE_SCHEMA = true;

    public const DEFAULT_AUTO_CREATE_HOMEPAGE = true;

    public const DEFAULT_AUTO_CREATE_SITE = true;

    public const DEFAULT_AUTO_CREATE_SITE_SCHEMA = true;

    public const DEFAULT_CONTEXT = 'Testing';

    public const DEFAULT_FRESH_DATABASE = false;

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

    protected array $configuration = self::DEFAULT_LOCAL_CONFIGURATION;

    protected bool $autoCreateSchema = self::DEFAULT_AUTO_CREATE_SCHEMA;

    protected bool $autoCreateHomepage = self::DEFAULT_AUTO_CREATE_HOMEPAGE;

    protected bool $autoCreateSite = self::DEFAULT_AUTO_CREATE_SITE;

    protected bool $autoCreateSiteSchema = self::DEFAULT_AUTO_CREATE_SITE_SCHEMA;

    protected bool $freshDatabase = self::DEFAULT_FRESH_DATABASE;

    protected string $context = self::DEFAULT_CONTEXT;

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

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

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

    public function isAutoCreateSiteSchema(): bool
    {
        return $this->autoCreateSiteSchema;
    }

    public function withAutoCreateSiteSchema(bool $autoCreateSiteSchema): self
    {
        $clone = clone $this;
        $clone->autoCreateSiteSchema = $autoCreateSiteSchema;

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

        if ($this->autoCreateSiteSchema) {
            /** @var SiteSchemaRegistry $siteSchemaRegistry */
            $siteSchemaRegistry = $container->get(SiteSchemaRegistry::class);
            $siteSchemaRegistry->registerSiteSchema($this->instanceName, $container->get(NoopSchemaBuilder::class)->build());
        }

        /** @var SiteFinder $siteFinder */
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        $siteFinder->getAllSites(false);

        return new FunctionalScope($container);
    }

    protected function createHomepage(): self
    {
        $query = $this->getQueryBuilder('pages');

        $query
            ->insert('pages')
            ->values(['uid' => 1, 'pid' => 0, 'title' => 'root page'])
            ->executeStatement();

        return $this;
    }

    protected function createSite(): self
    {
        $configuration = [
            'rootPageId' => 1,
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

    protected function getPhpFile(array $configuration): string
    {
        return '<?php return '.var_export($configuration, true).';';
    }
}
