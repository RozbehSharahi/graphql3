# GraphQL3

> > **Caution: Currently only as pre-release version v1.0.0-alpha available.**

As this is a very new extension, I can not yet recommend to use in production yet. It is not well tested yet. It is very
welcome to inform me about [issues](https://github.com/RozbehSharahi/graphql3/issues).

---

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
$schemaRegistry->registerCreator(fn() => new Schema([
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
$schemaRegistry->registerCreator(fn () => (new NoopQueryType());
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
        $this->schemaRegistry->registerCreator(fn() => new Schema([
            'query' => $this->queryType,
        ]));
    }
}
```

> It does not matter, where you place your `GraphqlSetup` class. As long as you implement `GraphqlSetupInterface`
> graphql3 will auto-detect your class and call the `setup` method. **However, make sure to clear all caches!**

At this point your graphql endpoint should already be accessible.

```
https://../your-site-root/graphql
https://../your-site-root/graphiql # in dev mode
```

The build-in `QueryType` provides already a tca-based schema for following entities:

- pages
- tt_content
- languages

Try for instance:

```
{
  pages {
    items {
      title
      subtitle
      parentPage {
        title
      }
      createdAt(format: "Y-m-d h:i")
      media {
        uid
        extension
        size
        publicUrl
        imageUrl(variant: "default", maxHeight: 100)
      }
    }
  }
}
```

Extending the schema is as simple as implementing the `QueryTypeExtenderInterface`.

```php
<?php

declare(strict_types=1);

namespace Your\Extension;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use RozbehSharahi\Graphql3\Type\QueryTypeExtenderInterface;

class ExampleQueryTypeExtender implements QueryTypeExtenderInterface
{
    public function extend(GraphqlNodeCollection $nodes): GraphqlNodeCollection
    {
        return $nodes->add(
            GraphqlNode::create('someNode')
                ->withType(Type::string())
                ->withResolver(fn () => 'Hello World')
        );
    }
}
```

The `QueryType` implementation is using built-in tca-based builders. If you want to add your own entities to graphql you
can use them as well:

```php
<?php

declare(strict_types=1);

namespace Your\Extension;

use RozbehSharahi\Graphql3\Builder\RecordListNodeBuilder;
use RozbehSharahi\Graphql3\Builder\RecordNodeBuilder;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use RozbehSharahi\Graphql3\Type\QueryTypeExtenderInterface;

class ExampleQueryTypeExtender implements QueryTypeExtenderInterface
{
    public function __construct(
        protected RecordNodeBuilder $recordNodeBuilder,
        protected RecordListNodeBuilder $recordListNodeBuilder
    ) {
    }

    public function extend(GraphqlNodeCollection $nodes): GraphqlNodeCollection
    {
        return $nodes
            ->add($this->recordListNodeBuilder->for('sys_log')->build())
            ->add($this->recordNodeBuilder->for('sys_log')->build())
        ;
    }
}
```

In the given example, record-node-builder and record-list-node-builder were used. These classes will auto generate a
graphql schema, based on TCA configuration. Now it is for instance possible to run following query:

```
{
  sysLogs {
    items {
      updatedAt
      details
      type
    }
  }
}
```

Registering a schema on graphql3 will activate the graphql endpoint. This is task should be done by only one single
extension, in most cases your main project extension. Other extensions should only provide schema fields and types, ...
which will be explained in the following chapter `Documentation`.

## Documentation

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
        $this->schemaRegistry->registerCreator(fn() => new Schema([
            'query' => $this->queryType,
        ]));
    }
}
```

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

`QueryType` exposes the configuration of root nodes/fields by `QueryTypeExtenderInterface`. By implementing the
interface you can edit all nodes on your root query.

### Record Type Builder

In order to expose a TYPO3 table, a record graphql type is needed. Graphql3 provides a TCA based record type builder.
The builder will generate `webonyx/graphql` object types based on TCA configuration.

In the following example a page node on root query is created with the help of record-type-builder.

```php
<?php

namespace Your\Extension;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use RozbehSharahi\Graphql3\Builder\RecordTypeBuilder;
use RozbehSharahi\Graphql3\Domain\Model\Record;
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
        $this->schemaRegistry->registerCreator(fn() => new Schema([
            'query' => new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'page' => [
                        'type' => $this->recordTypeBuilder->for('pages')->build(),
                        'resolve' => fn () => Record::create('pages', [
                            'uid' => 1, 
                            'title' => 'A hard coded page, which should be loaded by a resolver'
                        ]),
                    ],
                ],
            ]),
        ]));
    }
}
```

The given example only passes a hard-coded page array to the type.

> It does make a lot of sense to have a `uid` parameter and a resolver which loads the page by that `uid`. This will
> be tackled in the following chapters.

Apart from just creating the record type on the fly, the builder will also provide extendability and type caching.

Any extension can hook into the type creation of any table by
implementing `\RozbehSharahi\Graphql3\Builder\RecordTypeBuilderExtenderInterface`.

The following code shows how the pages type can be extended by an additional node `md5`.

```php
<?php

declare(strict_types=1);

namespace Your\Extension;

use RozbehSharahi\Graphql3\Builder\RecordTypeBuilderExtenderInterface;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use RozbehSharahi\Graphql3\Domain\Model\Tca\TableConfiguration;

class Md5PageTypeExtender implements RecordTypeBuilderExtenderInterface
{
    public function supportsTable(TableConfiguration $tableConfiguration): bool
    {
        return 'pages' === $tableConfiguration->getName();
    }

    public function extendNodes(
        TableConfiguration $tableConfiguration, 
        GraphqlNodeCollection $nodes
    ): GraphqlNodeCollection {
        return $nodes->add(
            GraphqlNode::create('md5')->withResolver(fn ($page) => md5(json_encode($page, JSON_THROW_ON_ERROR)))
        );
    }
}
```

> As long as the class implements `\RozbehSharahi\Graphql3\Builder\RecordTypeBuilderExtenderInterface` the position
> does not matter. Symfony dependency injection will take care of loading the extender. **However, clear the caches!**

It is also possible to remove or edit existing fields by extenders. For this check out `GraphqlNodeCollection`
and `GraphqlNode`, which will be explained in chapter `GraphqlNode and GraphqlNodeCollection`.

### Node builders

Graphql3 provides node builders in order to facilitate the creation of whole nodes. This includes type, resolver &
arguments.

Every node builder implements `NodeBuilderInterface` which by definition means it provides a build method which returns
an instance of `GraphqlNode`.

In following sections the record-type-builder is taken as an example. Check
out `vendor/rozbehsharahi/graphql3/Classes/Builder` to see which other builders exist and can be used.

---

When creating a meaningful page node we most likely need:

- A resolver to load a page
- An uid argument which defines which page should be loaded
- A type which describes the fields of the page.

The `RecordNodeBuilder` can be used as following:

```php
<?php

declare(strict_types=1);

namespace Your\Extension;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use RozbehSharahi\Graphql3\Builder\RecordNodeBuilder;
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
        $this->schemaRegistry->registerCreator(fn() => new Schema([
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
- [x] Create the page type via `\RozbehSharahi\Graphql3\Builder\RecordTypeBuilder`

There is a lot of features which will be activated by this. For instance:

- [x] Access check
- [x] Extendability via Extenders
- [x] Flexible mapping of tca fields to graphql fields

### List node builders

As record-node-builders will create singular nodes as `page`, `content`, `logs`, list-node-builders will create `pages`
, `content`, `logs`.

They come in with a pack of built-in features as:

- [x] Pagination
- [x] Filtering
- [x] Counting
- [ ] Faceting (will follow)

Let's say you added `sys_log` to your graphql endpoint as following:

```php
<?php

declare(strict_types=1);

namespace Your\Extension;

use RozbehSharahi\Graphql3\Builder\RecordListNodeBuilder;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use RozbehSharahi\Graphql3\Type\QueryTypeExtenderInterface;

class ExampleQueryTypeExtender implements QueryTypeExtenderInterface
{
    public function __construct(
        protected RecordListNodeBuilder $recordListNodeBuilder
    ) {
    }

    public function extend(GraphqlNodeCollection $nodes): GraphqlNodeCollection
    {
        return $nodes
            ->add($this->recordListNodeBuilder->for('sys_log')->build())
        ;
    }
}
```

By this you can already do following query:

```
  sysLogs(
    pageSize:2 
    page: 2 
    filters: [
      {type: "eq", field: "type", value: "2"}
    ]
  ) {
    items {
      updatedAt
      details
      type
    }
  }
```

A more complete documentation of built-in filters will follow.

#### Languages

The list-node-builder follows this logic regarding `sys_language_uid` relations:

Whenever a table has `langagueField` set on `TCA` it will add an extra query parameter for language filtering.

```
{
  pages(language: "de") {
   ...
  }
}
```

The parameter expects currently the `twoLetterIsoCode`. If the given language is available on the current site it will
be set as a filter to the query. A none-available language will cause an exception with a descriptive error message.

Languages are inherited to child-relations. For instance fetching a page record for language "en" will result in
children to be filtered for language "en" as well.

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

The access control of `graphql3` is implemented on top of `symfony/security-core` package. It is implemented as a jwt
token secured application.

#### JWT auth

All access control related code on `graphql3` is based on jwt-authentication, which is abstract within the
class `\RozbehSharahi\Graphql3\Domain\Model\JwtUser`. From core code perspective there is only authentication via JWT
auth headers (Bearer token, header-line: Authorization). The core code will therefore not know the origin of the token
but will require two fields to be set on jwt token `username` (string) and `roles` (array of string).

In order to create an access token you can use following commands:

```shell
# Manual creation of a token (asks for username and roles)
vendor/bin/typo3 graphql3:create-token:manual

# Creation of a token by frontend-user in database
vendor/bin/typo3 graphql3:create-token:frontend-user [user-uid]
```

All code will always act as if jwt-token authentication has taken place, however if `graphql3` finds a currently logged
in fe-user it will map that user to a jwt-token. This is reflected within the
implemented `\RozbehSharahi\Graphql3\Domain\Model\JwtUser::createFromTypo3Session`.

In order to provide compatibility between `JwtUser::$roles` (array of strings) and TYPO3, a convention for mapping was
implemented. Whenever a jwt-user shall match a TYPO3-Backend user group it has to contain a user-role with following
scheme: `ROLE_GROUP_ID::[fe_group.uid]`. By this convention in-house voters will decide, whether the user has access to
a specific record, which is restricted to certain typo3-user-groups. In order to abstract this convention there is a
method `JwtUser::hasGroupId(x)`, which will under the hood call `JwtUser::hasRole('ROLE_GROUP_ID::x')`.

**The mapping of TYPO3 users to JwtUser is currently very basic. Most of the cases should be satisfied by that.
Nevertheless, if you for instance need user-group inheritance, an extension of `graphql3` is necessary. Please let me
know in an Issue-Entry on Github if you happen to need such.**

##### Jwt auth setup / configuration

Currently `graphql3` only supports following algorithms:

- [x] RS256
- [x] RS256 with password secured secret
- [x] HS256

Following env vars you will need to set. If no public key is defined it will fall back to private key for
none-asymmetric signatures as HS256.

- [x] Private key (needed for creating tokens, for instance `vendor/bin/typo3 graphql3:create-token:manual`)
- [x] Public key (needed on some algorithms like RS256 for validating tokens)
- [x] Algorithm (for instance RS256, HS256, default RS256)
- [x] Passphrase (needed if private key is password encrypted)

The following example configuration should be the most common setup:

```dotenv
GRAPHQL3_JWT_ALGORITHM: "RS256"
GRAPHQL3_JWT_PRIVATE_KEY: "file://my-path-to/private.pem"
GRAPHQL3_JWT_PUBLIC_KEY: "file://my-path-to/public.pem"
GRAPHQL3_JWT_PASSPHRASE: "" # Can stay empty if private key is not secured
```

With a correctly set up environment you can use the command to generate a token.

Please make sure your apache configuration or nginx configuration allows authorization headers.

```
// on apache .htaccess this line might be needed.
CGIPassAuth On
```

#### Voting & ACL

Under the hood, `RecordNodeBuilder` is using an in-house `RecordResolve`, which is responsible to resolve a record based
on the uid/slug given. However, the resolver does a bit more than that. For instance providing access control, which can
be controlled on project level via `Voters`.

Whenever a record is resolved, it is passed to `AccessDecisionManager`. This is also the case inside
of `RecordListNodeBuilder`, where every loaded record is checked for access.

In order to modify/add access control to your project, you can simply create a class which
implements `\RozbehSharahi\Graphql3\Voter\VoterInterface`. When implementing the interface, your voter will be
automatically added to the stack of voters, no matter where you place it.

> Make sure to implement the `Graphql3` variant of `VoterInterface`, instead of the `Symfony` `VoterInterface`.

```php
<?php

namespace Your\Extension;

use RozbehSharahi\Graphql3\Domain\Model\JwtUser;
use RozbehSharahi\Graphql3\Domain\Model\Record;
use RozbehSharahi\Graphql3\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PageVoter implements VoterInterface
{
    public function vote(TokenInterface $token, mixed $subject, array $attributes): int
    {
        if (!$subject instanceof Record || $subject->getTable()->getName() !== 'pages') {
            return self::ACCESS_ABSTAIN;
        }
        
        return $token->getUser() instanceof JwtUser ? self::ACCESS_GRANTED : self::ACCESS_DENIED;
    }
}
```

A voter as shown here, is of course not needed, as this is already handled by a generic in-house `RecordResolver`.

The access-decision-manager is configured to use the `UnanimousStrategy`. This means all voters must grant or abstain
access. If all voters abstain, access is given. Checkout `symfony/security` documentation for further understanding.

Modifying the existing `\RozbehSharahi\Graphql3\Voter\RecordVoter` can be done via symfony's dependency injection
container in any extension's `Configuration/Services.yaml`.

### Mutations

For demonstration purposes `graphql3` comes with a mutation for creating `sys_news` items. However, the implementation
of mutations is very project & context specific and `graphql3` will not make any assumptions on that. The existing
example mutation `createSysNews` will only be available, when having a token with a role `ROLE_CREATE::sys_news`.

Mutations can be added via `MutationTypeExtenderInterface` and of course you can implement access-control by injection
of `AccessChecker`.

Following code shows the implementation of a mutation:

```php
<?php

declare(strict_types=1);

namespace Your\Extension;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgument;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use RozbehSharahi\Graphql3\Security\AccessChecker;
use RozbehSharahi\Graphql3\Type\MutationTypeExtenderInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;

class CreateSysNewsMutationTypeExtender implements MutationTypeExtenderInterface
{
    public function __construct(protected ConnectionPool $connectionPool, protected AccessChecker $accessChecker)
    {
    }

    public function extend(GraphqlNodeCollection $nodes): GraphqlNodeCollection
    {
        if (!$this->accessChecker->check(['ROLE_CREATE::sys_news'])) {
            return $nodes;
        }

        return $nodes->add(
            GraphqlNode::create('createSysNews')
                ->withType(Type::int())
                ->withArguments(
                    GraphqlArgumentCollection::create()->add(
                        GraphqlArgument::create('item')->withType(Type::nonNull(
                            new InputObjectType([
                                'name' => 'SysNewsInput',
                                'fields' => fn () => GraphqlNodeCollection::create()
                                    ->add(GraphqlNode::create('title')->withType(Type::string()))
                                    ->add(GraphqlNode::create('content')->withType(Type::string()))
                                    ->toArray(),
                            ])
                        ))
                    )
                )
                ->withResolver(function ($rootValue, $args) {
                    $query = $this->connectionPool->getQueryBuilderForTable('sys_news');
                    $query->insert('sys_news')->values([
                        'title' => $args['item']['title'],
                        'content' => $args['item']['content'],
                    ]);
                    $query->executeStatement();

                    return $query->getConnection()->lastInsertId('sys_news');
                })
        );
    }
}
```

In fact there is no difference between mutations and queries as you can also read up in `webonyx/graphql` documentation,
therefore you can use the same methods and classes you use for queries on mutations.

## Contribution & known issues

[Continue here](CONTRIBUTING.md)