# GraphQL 3

Graphql3 is based on TYPO3 sites.

Each site can register it's schema.

```php
use RozbehSharahi\Graphql3\Registry\SiteSchemaRegistry;
use GraphQL\Type\Schema;

/** @var SiteSchemaRegistry $siteSchemaRegistry */
$siteSchemaRegistry->registerSiteSchema('my-site', new Schema([
    'query' => new ObjectType([
        'name' => 'Query',
        'fields' => [
            'noop' => [
                'type' => Type::string(),
                'resolve' => fn ($rootValue, array $args) => 'noop',
            ],
        ],
    ]),
]))
```

After that you should be able to access your graphql endpoint

```
https://[HOST]:[PORT]/my-site/graphql
https://[HOST]:[PORT]/my-site/grapihql
```

The second parameter of `registerSiteSchema` actually expects a Schema of webonyx graphql-php package.

Therefore, you are free to define whatever you wish to.

https://webonyx.github.io/graphql-php/

Nevertheless, the main work of this extension is providing builders which will facilitate the introduction of GraphQL on
your TYPO3 site.

For instance the following code is completely equivalent, but uses one of the in-house builders:

```php
use RozbehSharahi\Graphql3\Registry\SiteSchemaRegistry;
use RozbehSharahi\Graphql3\Builder\NoopSchemaBuilder;

/** @var SiteSchemaRegistry $siteSchemaRegistry */
$siteSchemaRegistry->registerSiteSchema('my-site', (new NoopSchemaBuilder())->build())
```

In order to have an real working TYPO3 code, put this code on your extensions `ext_localconf.php`.

```php
use RozbehSharahi\Graphql3\Builder\NoopSchemaBuilder;
use RozbehSharahi\Graphql3\Registry\SiteSchemaRegistry;

$siteSchemaRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(SiteSchemaRegistry::class);
$siteSchemaRegistry->registerSiteSchema('my-site', (new NoopSchemaBuilder())->build());
```

## Contribution

When interested in contribution, you will need to setup your own test environment (php, xdebug, imagemagick,...). I
personally use my own repo https://github.com/RozbehSharahi/doka.

### Code sniffing

```
vendor/bin/php-cs-fixer fix

// dry run for pipeline
vendor/bin/php-cs-fixer fix --dry-run
```

### Testing

```
vendor/bin/phpunit
```

### Testing build

In order to test your changes on an active TYPO3 installation you can use the `.build` directory, which contains
a `composer.json` that should be able to setup your build.

In order to install the build do:

```
cd .build
composer install
```

It will create a fresh installation and you might navigation to `http://YOUR_LOCAL_DOMAIN:PORT/`. It should ask you to
create a `FIRST_INSTALL` file.

By now it is still mandatory to call the site-identifier manually `main` in order to have graphql route
active. See: `/Tests/Fixture/Graphql3TestExtension/ext_localconf.php`.

Installation steps should be fast and easy if you use sqlite (hopefully :)).

When using my `doka` package the path would be `http://localhost:8080/typo3/install.php`, according to configuration
in `.doka.env`.