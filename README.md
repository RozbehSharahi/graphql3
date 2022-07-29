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

However, the main focus of `graphql3` is providing extendable types/nodes/resolvers, which will facilitate the
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

Graphql3 brings a couple of handy types, nodes & resolvers, which shall facilitate the introduction of GraphQL on TYPO3
projects. However, without you telling `graphql3` what you want, nothing will happen.

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
like `page`. However, it is possible to extend the query at run-time, which enables any extension to hook in. Extension
of query type will be covered in an extra chapter.

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

You might already checkout your `/graphiql` route and for instance send a query as following:

```
{
  page(uid: 1) {
    uid
    title
    parent { title }
  }
}
```

### PageNode

`QueryType` already comes with a pack of predefined root-nodes. It makes assumptions on how a typical TYPO3 schema needs
to look like.

However, this might be not the case in all business logics. In order to have more control, we will go a layer deeper and
configure our query manually by using the `PageNode`. Using `PageNode` also shows how `graphql3` is structured.

```php
<?php

namespace Yout\Extension;

use GraphQL\Type\Definition\ObjectType;
use RozbehSharahi\Graphql3\Node\PageNode;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Setup\SetupInterface;

class GraphqlSetup implements SetupInterface
{
    public function __construct(
        protected SchemaRegistry $schemaRegistry,
        protected PageNode $pageNode
    ) {
    }

    public function setup(): void
    {
        // Register schema
        $this->schemaRegistry->register(new Schema([
           'query' => new ObjectType([
                'page' => $this->pageNode->getGraphqlNode()
            ]),
        ]));
    }
}
```

With the shown example, you are already able to do following query.

```
{
  page(uid: 1) {
    uid
    title
  }
}
```

The page node is extendable in terms of arguments and resolving though. By implementing the `PageNodeExtenderInterface`.
If you lack patients just checkout the interface to see what options you have.

For instance, you could extend the node to have an option to allow slug lookups.

### GraphqlNode and GraphqlNodeCollection

When extending one of the build-it nodes/type/... you will receive relevant schema parts in your extender which you are
free to change. For instance a query type extender implementing the QueryTypeExtenderInterface, would receive the
root-query nodes, which then can be edited. However, this will not be an array as you might expect. It will be
a `GraphqlNodeCollection`.

This chapter will give an intro on how `GraphqlNode` and `GraphqlNodeCollection` work.

`GraphqlNode` is simply a representation of a `webonyx/graphql-php` array. It is used to have auto-complete and can
simply be converted to an array.

```php
<?php

namespace Your\Namespace;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgument;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;

$node = GraphqlNode::create('myNode')
    ->withType(Type::int())
    ->withArguments([
        GraphqlArgument::create('myArgument')->withType(Type::string())
    ])
    ->withResolver(fn() => 123)
    ->toArray();
    
// is equivalent to

$node = [
    'type' => Type::int(),
    'args' => [
        'myArgument' => [
            'type' => Type::string()
        ] 
    ],
    'resolve' => fn() => 123
];

```

On the other hand you have `GraphqlNodeCollection`, which is a collection of `GraphqlNode`.

```php
<?php

namespace Your\Namespace;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;

$nodes = new GraphqlNodeCollection()
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

use RozbehSharahi\Graphql3\Domain\Model\Page;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PageVoter implements VoterInterface
{
    public function vote(TokenInterface $token, mixed $subject, array $attributes): int
    {
        if (!$subject instanceof Page) {
            return self::ACCESS_ABSTAIN;
        }

        // do your access check here
        $allowed = true;
        
        return $allowed ? self::ACCESS_GRANTED : self::ACCESS_DENIED;
    }
}
```

The access-decision-manager is configured to use the `UnanimousStrategy`. This means all voters must grant or abstain
access. If all voters abstain, access is denied. (not sure if this is smart, might change soon)
Checkout `symfony/security` documentation for further understanding.

Modifying the existing `\RozbehSharahi\Graphql3\Security\Voter\PageVoter` can be done via symfony's dependency injection
container in any extension's `Configuration/Services.yaml`.

## Contribution & known issues

[Continue here](contributing.md)