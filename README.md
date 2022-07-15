# GraphQL 3

This package enables you to register a graphql schema for your TYPO3 page.

If you register a schema, it is accessible via the tail `/graphql` or `/graphiql` on its root-page:
> https://www.example.com/my-site-root/graphql  
> https://www.example.com/my-site-root/graphiql (TYPO3_CONTEXT=Development only)

## Usage

We register our schema with normal `webonyx/graphql-php`-package types.

Documentation: https://webonyx.github.io/graphql-php/

Schematically the usage of `graphql3` is as following.

```php
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use GraphQL\Type\Schema;

/** @var SchemaRegistry $schemaRegistry */
$schemaRegistry->register(new Schema([
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

After that you should already be able to access your graphql endpoint.

The method `registerSiteSchema` expects a schema of `webonyx/graphql-php` package, so you are free to do whatever you
wish from here on.

However, the main focus of `graphql3` is providing builders/types/registries, which will facilitate the introduction of
GraphQL on TYPO3 sites.

For instance the following code is completely equivalent, but uses one of the in-house builders.

```php
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Builder\NoopSchemaBuilder;

/** @var SchemaRegistry $schemaRegistry */
$schemaRegistry->register((new NoopSchemaBuilder())->build())
```

In order to have some real working TYPO3 code, continue to the next chapter `Getting started`.

# Getting started

We assume you have a working TYPO3 extension and a `Configuration/Services.yaml` (as following), which will make
constructor injection work. (For instance on middleware).

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

Now you need to register a middleware in order to register a schema via `SchemaRegistry`.

```php
namespace Your\Extension\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RozbehSharahi\Graphql3\Builder\NoopSchemaBuilder;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;

class GraphqlRegistrationMiddleware implements MiddlewareInterface
{
    public function __construct(protected SchemaRegistry $schemaRegistry)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // You can pass any schema you want here
        // https://webonyx.github.io/graphql-php/
        $this->schemaRegistry->register((new NoopSchemaBuilder())->build());

        return $handler->handle($request);
    }
}
```

> Recommendation: Delegate your schema registration into a dedicated GraphqlRegistration class.
> Checkout `\RozbehSharahi\Graphql3TestExtension\Middleware\GraphqlRegistrationMiddleware`

Finally, activate your middleware on `Configuration/RequestMiddlewares.php`:

```php
use RozbehSharahi\Graphql3\Middleware\GraphqlRequestMiddleware;
use Your\Extension\Middleware\GraphqlRegistrationMiddleware;

return [
    'frontend' => [
        GraphqlRegistrationMiddleware::class => [
            'after' => ['typo3/cms-frontend/site'],
            'before' => [GraphqlRequestMiddleware::class],
            'target' => GraphqlRegistrationMiddleware::class,
        ],
    ],
];

```

At this point your graphql endpoint should already be accessible.

```
https://www.example.com/site-root/graphql
```

> Please make sure your registration middleware runs before `GraphqlRequestMiddleware`. Otherwise,
> you will receive a 404-page on `/graphql` and `/graphiql`.

## Documentation

> Please note that the given examples are always using doc-comments to clarify the service used in a variable.
>
> In the context of a project these doc-comment variables like `SchemaRegistry`,`RegistryBasedQueryType`, would be 
> injected usually, or instantiated via `GeneralUtility::makeInstance`.

Graphql3 brings a couple of handy types, registries & builders which shall facilitate the introduction of GraphQL on
TYPO3 projects. Without you telling `graphql3` what you want, nothing will happen.

First step every project should take, is registering a schema.

```php
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use GraphQL\Type\Schema;

/** @var SchemaRegistry $schemaRegistry */
$schemaRegistry->register(new \GraphQL\Type\Schema([...]))
```

However, doing everything by hand this way is possible but boring.

Instead, we want to use a built-in query types, which offer a couple of TYPO3 related features.

Let's start off with the `RegistryBasedQueryType`.

### RegistryBasedQueryType

Instead of defining a hardcoded object-type for our main graphql query node, we will use `RegistryBasedQueryType`.

```php
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use GraphQL\Type\Schema;
use RozbehSharahi\Graphql3\Type\RegistryBasedQueryType;

/** @var RegistryBasedQueryType $registryBasedQueryType */
/** @var SchemaRegistry $schemaRegistry */

$schemaRegistry->register(new \GraphQL\Type\Schema([
    'query' => $registryBasedQueryType
]))
```

`RegistryBasedQueryType` exposes the configuration of root nodes/fields by `QueryFieldRegistry`.

```php
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use GraphQL\Type\Schema;
use RozbehSharahi\Graphql3\Type\RegistryBasedQueryType;
use RozbehSharahi\Graphql3\Registry\QueryFieldRegistry;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;

/** @var QueryFieldRegistry $queryFieldRegistry */
/** @var RegistryBasedQueryType $registryBasedQueryType */
/** @var SchemaRegistry $schemaRegistry */

$schemaRegistry->register(new \GraphQL\Type\Schema([
    'query' => $registryBasedQueryType
]))

$queryFieldRegistry
    ->register(GraphqlNode::create('sayHello')->withResolver(fn() => 'Hi !'))
    ->register(GraphqlNode::create('sayNo')->withResolver(fn() => 'No !'));
```

From this point your graphql schema supports following query:

```
{ sayHello }
```
```json
{
  "data": {
    "sayHello": "Hi !"
  }
}
```

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

Installation steps should be fast and easy if you use sqlite (hopefully :)).

When using my `doka` package, the path would be `http://localhost:8080/typo3/install.php`, according to configuration
in `.doka.env`.

# Well known open tasks

- Need tests for installation of extension ext_localconf.php of Graphql3TestExtension.
- Need good test base in order to test if extending graphql3 via extensions is working