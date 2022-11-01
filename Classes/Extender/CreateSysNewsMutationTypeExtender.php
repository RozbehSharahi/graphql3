<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Extender;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgument;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use RozbehSharahi\Graphql3\Security\AccessChecker;
use RozbehSharahi\Graphql3\Type\MutationTypeExtenderInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * This extender only exists to act as a template for project specific mutations.
 *
 * Feel free to copy, extend, remove or override it via your services.yaml.
 */
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
