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

However, the main focus of `graphql3` is providing extendable builders/types/nodes/resolvers, which will facilitate the
introduction of GraphQL on TYPO3 sites.

For instance the following code is completely equivalent, but uses one of the in-house types.

```php
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Type\NoopQueryType;

/** @var SchemaRegistry $schemaRegistry */
$schemaRegistry->register((new NoopQueryType());
```

In order to have some real working TYPO3 code, continue to the next chapter `Getting started`.

# Getting started

We assume you have a working TYPO3 extension and a `Configuration/Services.yaml` (as following), which will make
constructor injection and service locating work.

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
use RozbehSharahi\Graphql3\Type\NoopQueryType;

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
        $this->schemaRegistry->register(new NoopQueryType());
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

Graphql3 brings a couple of handy builders, types, nodes & resolvers, which shall facilitate the introduction of GraphQL
on TYPO3 projects. However, without you telling `graphql3` what you want, nothing will happen.

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
> `GraphqlSetupInterface`, your namespace is correct and `Services.yaml` makes sense, graphql3 will find your class. :)

Registering a schema like this is possible, however, doing now everything by hand is boring.

Instead, we want to use one of the built-in query types.

Let's start off with the `QueryType`.

### QueryType

Query type is basic query configuration, which you can use to start off. It already provides a couple of root-nodes,
like `page`. However, it is possible to extend the query at run-time, which enables any extension to hook in.

```php
<?php

namespace Your\Extension;

use GraphQL\Type\Schema;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Setup\SetupInterface;
use RozbehSharahi\Graphql3\Type\QueryType;

class GraphqlSetup implements SetupInterface
{
    public function __construct(
        protected SchemaRegistry $schemaRegistry,
        protected QueryType $queryType
    ) {
    }

    public function setup(): void
    {
        $this->schemaRegistry->register(new Schema([
            'query' => $this->queryType,
        ]));
    }
}
```

`QueryType` exposes the configuration of root nodes/fields by `QueryTypeExtenderInterface`.

You might already check out your `/graphiql` route and for instance send a query as following:

```
{
  page(uid: 1) {
    uid
    title
    parentPage { title }
  }
}
```

### Record Type Builder

In order to expose a TYPO3 table, a record graphql type is needed. Graphql3 provides a TCA based record type builder.
The builder will generate `webonyx/graphql` object types based on TCA configuration.

```php
<?php

namespace Your\Code;

use RozbehSharahi\Graphql3\Builder\Type\RecordTypeBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

$recordTypeBuilder = GeneralUtility::makeInstance(RecordTypeBuilder::class);

// Build page type full automatically
$pageObjectType = $recordTypeBuilder->for('pages')->build();
```

Apart from just creating the record type on the fly, the builder will also provide extendability and type caching.

Any extension can hook into the type creation of any table by
implementing `\RozbehSharahi\Graphql3\Builder\Type\RecordTypeBuilderExtenderInterface`.

The following code shows how the pages type can be extended by an additional node `md5`.

```php
<?php

declare(strict_types=1);

namespace Your\Extension;

use RozbehSharahi\Graphql3\Builder\Type\RecordTypeBuilderExtenderInterface;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;

class Md5PageTypeExtender implements RecordTypeBuilderExtenderInterface
{
    public function supportsTable(string $table): bool
    {
        return 'pages' === $table;
    }

    public function extendNodes(GraphqlNodeCollection $nodes): GraphqlNodeCollection
    {
        return $nodes->add(
            GraphqlNode::create('md5')->withResolver(fn ($page) => md5(json_encode($page, JSON_THROW_ON_ERROR)))
        );
    }
}
```

As long as the class implements `\RozbehSharahi\Graphql3\Builder\Type\RecordTypeBuilderExtenderInterface` the position
does not matter. Symfony dependency injection will take care of loading the extender.

It is also possible to remove or edit existing fields by extenders. For this check out `GraphqlNodeCollection`
and `GraphqlNode`, which will be explained in chapter `GraphqlNode and GraphqlNodeCollection`.

In the following example a page node on root query is created with the help of record-type-builder.

```php
<?php

namespace Your\Extension;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Type\Definition\ObjectType;
use RozbehSharahi\Graphql3\Builder\Type\RecordTypeBuilder;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Setup\SetupInterface;

class GraphqlSetup implements SetupInterface
{
    public function __construct(
        protected SchemaRegistry $schemaRegistry,
        protected RecordTypeBuilder $recordTypeBuilder
    ) {
    }

    public function setup(): void
    {
        $this->schemaRegistry->register(new Schema([
            'query' => new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'page' => [
                        'type' => $this->recordTypeBuilder->for('pages')->build(),
                        'resolve' => fn () => [
                            'uid' => 1, 
                            'title' => 'A hard coded page, which should be loaded by a resolver'
                        ],
                    ],
                ],
            ]),
        ]));
    }
}
```

The given example only passes a hard-coded page array to the type.

It does make a lot of sense to have a `uid` parameter and a resolver which loads the page by that `uid`.

### Node builders

Graphql3 provides node builders in order to facilitate the creation of whole nodes. This includes type, resolver &
arguments.

Every node builder implements `NodeBuilderInterface` which by definition means it provides a build method which returns
an instance of `GraphqlNode`.

In following sections the record-type-builder is taken as an example.

When creating a meaningful page node we most likely need:

- A resolver to load a page
- An uid argument which defines which pages should be loaded
- A type which describes the fields of the page.

The `RecordNodeBuilder` can be used as following:

```php
<?php

declare(strict_types=1);

namespace Your\Extension;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use RozbehSharahi\Graphql3\Builder\Node\RecordNodeBuilder;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Setup\SetupInterface;

class GraphqlSetup implements SetupInterface
{
    public function __construct(
        protected SchemaRegistry $schemaRegistry,
        protected RecordNodeBuilder $recordNodeBuilder
    ) {
    }

    public function setup(): void
    {
        $this->schemaRegistry->register(new Schema([
            'query' => new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'page' => $this->recordNodeBuilder->for('pages')->build()->toArray()
                ],
            ]),
        ]));
    }
}
```

Under the hood this will:

- [x] Create an argument `uid`
- [x] Create a resolver via `\RozbehSharahi\Graphql3\Resolver\RecordResolver`
- [x] Create the page type via `\RozbehSharahi\Graphql3\Builder\Type\RecordTypeBuilder`

There is a lot of features which will be activated by this. For instance:

- [x] Access check
- [x] Extendability via Extenders

Check out `vendor/rozbehsharahi/graphql3/Classes/Builder/Node` to see which builders exist and can be used.

### GraphqlNode and GraphqlNodeCollection

When extending one of the build-in nodes/type/... you will receive relevant schema parts in your extender, which you are
free to change. For instance a query type extender implementing the `QueryTypeExtenderInterface`, would receive the
root-query nodes, which then can be edited. However, this will not be an array as you might expect. It will be
a `GraphqlNodeCollection`.

This chapter will give an intro on how `GraphqlNode` and `GraphqlNodeCollection` work.

`GraphqlNode` is simply a representation of a `webonyx/graphql-php` array. It is used to have auto-complete and can
simply be converted to an array.

```php
<?php

namespace Your\Namespace;

use GraphQL\Type\Definition\Type;use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;

$graphqlNode = GraphqlNode::create()
    ->withType(Type::string())
    ->withResolver(fn() => 'hey')
    ->toArray();

// is equivalent to

$graphqlNode = [
    'type' => Type::string(),
    'resolve' => fn() => 'hey'
];
```

On the other hand you have `GraphqlNodeCollection`, which is a collection of `GraphqlNode` objects.

```php
<?php

namespace Your\Namespace;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;

$nodes = (new GraphqlNodeCollection())
    ->add(GraphqlNode::create('myNode')->withType(Type::int()))
    ->add(GraphqlNode::create('myOtherNode')->withType(Type::string()))
    ->toArray();

// is equivalent to

$nodes = [
    'myNode' => [
        'type' => Type::int()
    ],
    'myOtherNode' => [
        'type' => Type::string()
    ]
];
```

Be aware, that graphql-nodes and -collections are immutables. Therefore, when calling methods like `add` or `remove`
you will need to use return value.

```php
<?php

namespace Your\Namespace;

use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;

$nodes = GraphqlNodeCollection::create();
$nodes = $nodes->add(GraphqlNode::create('aNewNode'));
```

The implementation of `GraphqlArgument` and `GraphqlArgumentCollection`, is pretty much the same. Checkout via
auto-complete, which options you have.

### Access control

The access control of `graphql3` is implemented on top of `symfony/security-core` package.

Under the hood, `PageNode` is using an in-house `PageResolver`, which is responsible to resolve a page based on the
uid/slug given. However, the page-resolver does a bit more than that. For instance providing access control, which can
be controlled on project level via `Voters`.

Whenever a page is resolved, it is passed to `AccessDecisionManager` of the symfony package `symfony/security-core`.
This is also the case inside of `PageListNode`, where every loaded page is checked for access.

In order to modify/add access control to your project, you can simply create a class which
implements `\RozbehSharahi\Graphql3\Security\Voter\VoterInterface`. When implementing the interface, your voter will be
automatically added to the stack of voters, no matter where you place it.

> Make sure to implement the `Graphql3` variant of `VoterInterface`, instead of the `Symfony` `VoterInterface`.

```php
<?php

namespace RozbehSharahi\Graphql3\Security\Voter;

use RozbehSharahi\Graphql3\Domain\Model\Record;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PageVoter implements VoterInterface
{
    public function vote(TokenInterface $token, mixed $subject, array $attributes): int
    {
        if (!$subject instanceof Record || $subject->getTable() !== 'pages') {
            return self::ACCESS_ABSTAIN;
        }

        // do your access check here
        $allowed = true;
        
        return $allowed ? self::ACCESS_GRANTED : self::ACCESS_DENIED;
    }
}
```

The access-decision-manager is configured to use the `UnanimousStrategy`. This means all voters must grant or abstain
access. If all voters abstain, access is given. Checkout `symfony/security` documentation for further understanding.

Modifying the existing `\RozbehSharahi\Graphql3\Security\Voter\PageVoter` can be done via symfony's dependency injection
container in any extension's `Configuration/Services.yaml`.

## Contribution & known issues

[Continue here](contributing.md)