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

In order to have some real working TYPO3 code, follow the next chapter `Getting started`.

# Getting started

We assume you have a working TYPO3 extension and a site with identifier `my-site`. 

Also make sure to have a proper `Configuration/Services.yaml` similar to this one running:

```yaml
services:
  _defaults:
    autowire: true
    autoconfigure: true
    public: false

  Your\Extension\:
    resource: '../Classes/*'
    exclude: '../Classes/Domain/Model/*'

```
... otherwise constructor injection of `GraphqlRegistration` might not work as expected.

Now create a registration class in your project's main extension:

```php
namespace Your\Extension\Graphql;

class GraphqlRegistration
{

    public function __construct(
        protected SiteSchemaRegistry $registry, 
        protected NoopSchemaBuilder $noopSchemaBuilder
    ) {
    }

    public function register(): void
    {
        $this
            ->registry
            ->registerSiteSchema('my-site', $this->noopSchemaBuilder->build());
    }

}
```

Now you need to call the register method on your `ext_localconf.php`.

```php
use Your\Extension\Graphql\GraphqlRegistration;

\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(GraphqlRegistration::class)->register();
```

After that you should be able to call your graphql endpoint. Also, graphiql endpoint will be available if TYPO3_CONTEXT
is set to `Development/*`.

> Please consider that your graphql endpoint will only be available on your sites root page path with an additional `/graphql` tail.
> 
> https://my-page.com/my-site-root/    
> https://my-page.com/my-site-root/graphql  
> https://my-page.com/my-site-root/graphiql

## Contribution

When interested in contribution, you will need to set up your own test environment (php, xdebug, imagemagick,...). I
personally use my own repo https://github.com/RozbehSharahi/doka, which you are also free to use ofc.

### Testing (and sniffing)

```
composer run tests
```

### Testing build

In order to test your changes on an active TYPO3 installation you can use the `.build` directory, which contains
a `composer.json` that should be able to setup your build.

In order to install the build do:

```
cd .build
composer install
```

This will create a fresh TYPO3 installation. Navigate to `http://[HOST]:[PORT]/`, where it should ask you to create
a `FIRST_INSTALL` file.

For the time being it is still mandatory to call the site-identifier manually `main` in order to have graphql route
activated. See: `/Tests/Fixture/Graphql3TestExtension/ext_localconf.php`.

Installation steps should be fast and easy if you use sqlite (hopefully :)).

When using my `doka` package, the path would be `http://localhost:8080/typo3/install.php`, according to configuration
in `.doka.env`.

# Well known open tasks

- Need tests for installation of extension ext_localconf.php of Graphql3TestExtension.
- Need good test base in order to test if extending graphql3 via extensions is working