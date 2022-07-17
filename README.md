# GraphQL 3

This package enables you to register a graphql schema for your TYPO3 page.

If you register a schema, it is accessible via the tail `/graphql` or `/graphiql` on your site's root-page:
> https://www.example.com/my-site-root/graphql  
> https://www.example.com/my-site-root/graphiql (TYPO3_CONTEXT=Development only)

## Usage

Schemas are registered using `webonyx/graphql-php` package types.

Documentation: https://webonyx.github.io/graphql-php/

Schematically the usage of `graphql3` is as following:

```php
use GraphQL\Type\Schema;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;

/** @var SchemaRegistry $schemaRegistry */
$schemaRegistry->register(new Schema([
    'query' => new ObjectType([
        'name' => 'Query',
        'fields' => [
            'noop' => [
                'type' => Type::string(),
                'resolve' => fn () => 'noop',
            ],
        ],
    ]),
]))
```

After that you should already be able to access your graphql endpoint.

The method `register` expects a schema of `webonyx/graphql-php` package, so you are free to do whatever you wish from
here on.

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
constructor injection work.

https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ApiOverview/DependencyInjection/Index.html#dependency-injection-autoconfigure

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

Create a graphql setup class, somewhere in your extension's `Classes` directory.

```php
<?php

namespace Your\Extension;

use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Setup\GraphqlSetupInterface;
use RozbehSharahi\Graphql3\Builder\NoopSchemaBuilder;

class GraphqlSetup implements GraphqlSetupInterface
{
    public function __construct(
        protected SchemaRegistry $schemaRegistry,
    ) {
    }

    public function setup(): void
    {
        // You can pass any schema you want here
        // https://webonyx.github.io/graphql-php/
        $this->schemaRegistry->register((new NoopSchemaBuilder())->build());
    }
}
```

> It does not matter, where you place your `GraphqlSetup` class. As long as you implement `GraphqlSetupInterface`
> graphql3 will auto-detect your class and call the `setup` method.

At this point your graphql endpoint should already be accessible.

```
https://../your-site-root/graphql
```

Registering a schema on graphql3 will activate the graphql endpoint. This is task should be done by only one single
extension, in most cases your main project extension. Other extensions should only provide schema fields and types, ...
which will be explained in the following chapter `Documentation`.

## Documentation

Graphql3 brings a couple of handy types, registries & builders which shall facilitate the introduction of GraphQL on
TYPO3 projects. However, without you telling `graphql3` what you want, nothing will happen.

First step every project should take, is creating a setup class which registers a schema.

```php
<?php

namespace Your\Extension;

use GraphQL\Type\Schema;
use GraphQL\Type\Definition\ObjectType;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Setup\GraphqlSetupInterface;

class GraphqlSetup implements GraphqlSetupInterface
{
    public function __construct(
        protected SchemaRegistry $schemaRegistry,
    ) {
    }

    public function setup(): void
    {
        $this->schemaRegistry->register(new Schema([
            'query' => new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'sayHi' => [
                        'type' => Type::string(),
                        'resolve' => fn () => 'Hi !',
                    ],
                ],
            ]),
        ]));
    }
}
```

> The position of your setup class within your extension's `Classes` directory is irrelevant. As long as you implement
> `GraphqlSetupInterface` and your namespace is correct, graphql3 will find your class. :)

Registering a schema like this is possible, however, doing now everything by hand is boring.

Instead, we want to use on of the built-in query types.

Let's start off with the `RegistryBasedQueryType`.

### RegistryBasedQueryType

Instead of defining a hardcoded object-type for our main graphql query node, we will use `RegistryBasedQueryType`. Types
on graphql3 can be registered and retrieved via `TypeRegistry`.

```php
<?php

namespace Your\Extension;

use GraphQL\Type\Schema;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Setup\GraphqlSetupInterface;
use RozbehSharahi\Graphql3\Registry\TypeRegistry;
use RozbehSharahi\Graphql3\Type\RegistryBasedQueryType;

class GraphqlSetup implements GraphqlSetupInterface
{
    public function __construct(
        protected SchemaRegistry $schemaRegistry,
        protected TypeRegistry $typeRegistry
    ) {
    }

    public function setup(): void
    {
        $this->schemaRegistry->register(new Schema([
            'query' => $this->typeRegistry->get(RegistryBasedQueryType::class),
        ]));
    }
}
```

`RegistryBasedQueryType` exposes the configuration of root nodes/fields by `QueryFieldRegistry`. In the given example
our graphql endpoint will show an error, since no fields are yet defined.

In order to get rid of this error, we will define our first query field. This way we will also see the advantage
of `RegistryBasedQueryType`.

```php
<?php

namespace Your\Extension;

use GraphQL\Type\Schema;
use GraphQL\Type\Definition\ObjectType;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Setup\GraphqlSetupInterface;
use RozbehSharahi\Graphql3\Registry\TypeRegistry;
use RozbehSharahi\Graphql3\Type\RegistryBasedQueryType;
use RozbehSharahi\Graphql3\Registry\QueryFieldRegistry;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;

class GraphqlSetup implements GraphqlSetupInterface
{
    public function __construct(
        protected SchemaRegistry $schemaRegistry,
        protected TypeRegistry $typeRegistry,
        protected QueryFieldRegistry $queryFieldRegistry
    ) {
    }

    public function setup(): void
    {
        $this->schemaRegistry->register(new Schema([
            'query' => $this->typeRegistry->get(RegistryBasedQueryType::class),
        ]));
        
        $this->queryFieldRegistry
            ->register(GraphqlNode::create('sayHello')->withResolver(fn() => 'Hi !'))
            ->register(GraphqlNode::create('sayNo')->withResolver(fn() => 'No !'))
    }
}
```

At this point your graphql schema supports following query:

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

Using `RegistryBasedQueryType` enables any extension to extend the root query at any time and place. By this we don't
need to know the project's final query fields at the time of creation and can let further extensions participate on
creating our schema.

## Contribution

[Continue here](contributing.md)

# Well known open tasks

- Need tests for installation of extension ext_localconf.php of Graphql3TestExtension.
- Need good test base in order to test if extending graphql3 via extensions is working