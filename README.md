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
use RozbehSharahi\Graphql3\Setup\SetupInterface;
use RozbehSharahi\Graphql3\Builder\NoopSchemaBuilder;

class GraphqlSetup implements SetupInterface
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
use RozbehSharahi\Graphql3\Setup\SetupInterface;

class GraphqlSetup implements SetupInterface
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
use RozbehSharahi\Graphql3\Setup\SetupInterface;
use RozbehSharahi\Graphql3\Registry\TypeRegistry;
use RozbehSharahi\Graphql3\Type\RegistryBasedQueryType;

class GraphqlSetup implements SetupInterface
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
use RozbehSharahi\Graphql3\Setup\SetupInterface;
use RozbehSharahi\Graphql3\Registry\TypeRegistry;
use RozbehSharahi\Graphql3\Type\RegistryBasedQueryType;
use RozbehSharahi\Graphql3\Registry\QueryFieldRegistry;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;

class GraphqlSetup implements SetupInterface
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

Since extendability is not only needed on root level query type, we will follow up with the
chapter `RegistryBasdPageType`, which covers the exposure of page objects on graphql including extendability via
registries.

### RegistryBasedPageType

The registry based page type is a graphql object type, which is extendable via `PageFieldRegistry`. However, it already
comes with a pack of predefined properties, fields like `uid`, `title`, ...

```php
<?php

namespace Yout\Extension;

use GraphQL\Type\Schema;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Registry\QueryFieldRegistry;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Registry\TypeRegistry;
use RozbehSharahi\Graphql3\Setup\SetupInterface;
use RozbehSharahi\Graphql3\Type\RegistryBasedPageType;
use RozbehSharahi\Graphql3\Type\RegistryBasedQueryType;

class GraphqlSetup implements SetupInterface
{
    public function __construct(
        protected SchemaRegistry $schemaRegistry,
        protected TypeRegistry $typeRegistry,
        protected QueryFieldRegistry $queryFieldRegistry
    ) {
    }

    public function setup(): void
    {
        // Register schema
        $this->schemaRegistry->register(new Schema([
           'query' => $this->typeRegistry->get(RegistryBasedQueryType::class),
        ]));

        $this->queryFieldRegistry
            ->register(
                GraphqlNode::create('page')
                    ->withType($this->typeRegistry->get(RegistryBasedPageType::class))
                    ->withResolver(fn () => ['uid' => 1, 'title' => 'This is a hard-coded fake page record for now'])
            );
    }
}
```

With the shown example, you are already able to do following query.

```
{
  page {
    uid
    title
  }
}
```

which will result into

```json
{
  "data": {
    "page": {
      "uid": 1,
      "title": "This is a hard-coded fake page record for now"
    }
  }
}
```

While `RegistryBasedPageType` comes with predefined fields (uid, title, ...), it can be extended any time as
with `RegistryBasedQueryType`.

```php
<?php

namespace Your\Extension;

use GraphQL\Type\Schema;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Registry\PageFieldRegistry;use RozbehSharahi\Graphql3\Registry\QueryFieldRegistry;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Registry\TypeRegistry;
use RozbehSharahi\Graphql3\Setup\SetupInterface;
use RozbehSharahi\Graphql3\Type\RegistryBasedPageType;
use RozbehSharahi\Graphql3\Type\RegistryBasedQueryType;

class GraphqlSetup implements SetupInterface
{
    public function __construct(
        protected SchemaRegistry $schemaRegistry,
        protected TypeRegistry $typeRegistry,
        protected QueryFieldRegistry $queryFieldRegistry,
        protected PageFieldRegistry $pageFieldRegistry
    ) {
    }

    public function setup(): void
    {
        // Register schema
        $this->schemaRegistry->register(new Schema([
           'query' => $this->typeRegistry->get(RegistryBasedQueryType::class),
        ]));

        $this->queryFieldRegistry
            ->register(
                GraphqlNode::create('page')
                    ->withType($this->typeRegistry->get(RegistryBasedPageType::class))
                    ->withResolver(fn () => ['uid' => 1, 'title' => 'This is a hard-coded fake page record for now'])
            );
            
        // Here:
        // We are adding a new page type field hash here
        // However, this could happen in any extension, model, class, ... that implements the `SetupInterface`.
        $this->pageFieldRegistry
            ->register(
               GraphqlNode::create('hash')->withResolver(fn(array $page) => md5(json_encode($page)))
            );
    }
}
```

Now you can query a new field `hash` on you page.

In all given examples of this chapter the page record was passed as a hardcoded array. Of course this does make little
sense. The following chapter `Setup for query field 'page'` will cover possible ways of implementing a reasonable page
node/field on root query.

### Setup for query field 'page'

Setting up a query field was already covered in the previous chapters. This chapter will describe the creation of a
parameterized page node on root query. The task of the node is to enable us to do the following query:

```
{
  page(uid: 1) {
    uid
    title
  }
}
```

In a complete hardcoded setup we could do as following:

```php
<?php

namespace RozbehSharahi\Graphql3TestExtension;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Setup\SetupInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;

class GraphqlSetup implements SetupInterface
{
    public function __construct(
        protected SchemaRegistry $schemaRegistry,
        protected ConnectionPool $connectionPool
    ) {
    }

    public function setup(): void
    {
        $this->schemaRegistry->register(new Schema([
            'query' => new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'page' => [
                    
                        // uid argument to pass the request page id
                        'args' => [
                            'uid' => Type::nonNull(Type::int()),
                        ],

                        'resolve' => fn($_, $args) => $this->getPage($args['uid']),
                        'type' => new ObjectType([
                            'name' => 'Page',
                            'fields' => [
                                'uid' => [
                                    'type' => Type::int(),
                                    'resolve' => fn ($page) => $page['uid'],
                                ],
                                'title' => [
                                    'type' => Type::string(),
                                    'resolve' => fn ($page) => $page['title'],
                                ],
                            ],
                        ]),
                    ],
                ],
            ]),
        ]));
    }

    protected function getPage(int $id): array
    {
        $query = $this
            ->connectionPool
            ->getQueryBuilderForTable('page');

        return $query
            ->select('*')
            ->from('pages')
            ->where('uid='.$query->createNamedParameter($id, \PDO::PARAM_INT))
            ->executeQuery()
            ->fetchAssociative();
    }
}
```

In order to make this code extendable, we will start of by using `PageArgumentRegistry` instead of hard-coding the page
arguments (uid: non-null-int).

```php
<?php

namespace Your\Extension;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use RozbehSharahi\Graphql3\Registry\PageArgumentRegistry;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Setup\SetupInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;

class GraphqlSetup implements SetupInterface
{
    public function __construct(
        protected SchemaRegistry $schemaRegistry,
        protected ConnectionPool $connectionPool,
        protected PageArgumentRegistry $pageArgumentRegistry
    ) {
    }

    public function setup(): void
    {
        $this->schemaRegistry->register(new Schema([
            'query' => new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'page' => [

                        // Use page argument registry now
                        'args' => $this->pageArgumentRegistry->getArguments()->toArray(),

                        'resolve' => fn ($_, $args) => $this->getPage($args['uid']),
                        'type' => new ObjectType([
                            'name' => 'Page',
                            'fields' => [
                                'uid' => [
                                    'type' => Type::int(),
                                    'resolve' => fn ($page) => $page['uid'],
                                ],
                                'title' => [
                                    'type' => Type::string(),
                                    'resolve' => fn ($page) => $page['title'],
                                ],
                            ],
                        ]),
                    ],
                ],
            ]),
        ]));
    }

    protected function getPage(int $id): array
    {
        $query = $this
            ->connectionPool
            ->getQueryBuilderForTable('page');

        return $query
            ->select('*')
            ->from('pages')
            ->where('uid='.$query->createNamedParameter($id, \PDO::PARAM_INT))
            ->executeQuery()
            ->fetchAssociative();
    }
}
```

The page argument registry enables us once again to extend the argument of the root level query page node at any time.

Finally, this is how we should do it, to keep all parts of our graphql config extendable.

```php
<?php

namespace Your\Extension;

use GraphQL\Type\Schema;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
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
        $this->schemaRegistry->register(new Schema([
            'query' => $this->typeRegistry->get(RegistryBasedQueryType::class),
        ]));

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

        return $query
            ->select('*')
            ->from('pages')
            ->where('uid='.$query->createNamedParameter($id, \PDO::PARAM_INT))
            ->executeQuery()
            ->fetchAssociative();
    }
}
```

In the last given example, most of the code is using in-house types and registries. By this, the schema definition stays
extendable and lines of code are reduced.

The next chapter `RegistryBasedPageQueryBuilder` will describe, how to make also page resolving extendable.

### RegistryBasedPageQueryBuilder

Most project will probably have a page node in order to query pages. However, the business logic on fetching pages might
differ from project to project. In order to further reduce the hard-coded amount of business logic you might extend the
last given example in the previous chapter `Setup for query field 'page''` by `RegistryBasedPageQueryBuilder`.

The registry based page query builder also takes care of fetching the page record from DB. By that our Setup code
shrinks again to:

```php
<?php

namespace Your\Extension;

use GraphQL\Type\Schema;
use RozbehSharahi\Graphql3\Builder\RegistryBasedPageQueryBuilder;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Registry\PageArgumentRegistry;
use RozbehSharahi\Graphql3\Registry\QueryFieldRegistry;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Registry\TypeRegistry;
use RozbehSharahi\Graphql3\Setup\SetupInterface;
use RozbehSharahi\Graphql3\Type\RegistryBasedPageType;
use RozbehSharahi\Graphql3\Type\RegistryBasedQueryType;

class GraphqlSetup implements SetupInterface
{
    public function __construct(
        protected SchemaRegistry $schemaRegistry,
        protected TypeRegistry $typeRegistry,
        protected QueryFieldRegistry $queryFieldRegistry,
        protected PageArgumentRegistry $pageArgumentRegistry,
        protected RegistryBasedPageQueryBuilder $pageQueryBuilder,
    ) {
    }

    public function setup(): void
    {
        $this->schemaRegistry->register(new Schema([
            'query' => $this->typeRegistry->get(RegistryBasedQueryType::class),
        ]));

        $this->queryFieldRegistry
            ->register(
                GraphqlNode::create('page')
                    ->withType($this->typeRegistry->get(RegistryBasedPageType::class))
                    ->withArguments($this->pageArgumentRegistry->getArguments())
                    ->withResolver(fn ($_, $args) => $this->pageQueryBuilder->withArguments($args)->getPage())
            );
    }
}
```

`RegistryBasedPageQueryBuilder` also brings the possibility to extend the query for fetching pages at any time via
PageQueryExtenderRegistry.

```php
use RozbehSharahi\Graphql3\Domain\Model\QueryExtender;
use RozbehSharahi\Graphql3\Registry\PageQueryExtenderRegistry;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

/** @var PageQueryExtenderRegistry $pageQueryExtender **/
$pageQueryExtender->register(
    QueryExtender::create('my-extension')
        ->withClosure(fn(QueryBuilder $query) => $query->andWhere('...'))
);
```

So far the amount of complexity might be overwhelming. However, all the chapters until here, were only supposed to give
an in depth knowledge on how `graphql3` is working (on fetching data).

In fact the amount of code needed to introduce graphql by this extension, can be even minimized harder. This can be
achieved by a good amount of builders, which are explained in the next chapter `Node builders`.

## Contribution & known issues

[Continue here](contributing.md)