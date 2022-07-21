<?php

declare(strict_types=1);
namespace RozbehSharahi\Graphql3;

use RozbehSharahi\Graphql3\Controller\GraphqlController;
use RozbehSharahi\Graphql3\Node\PageNode;
use RozbehSharahi\Graphql3\Node\PageNodeExtenderInterface;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Resolver\RecordResolver;
use RozbehSharahi\Graphql3\Setup\SetupInterface;
use RozbehSharahi\Graphql3\Type\PageType;
use RozbehSharahi\Graphql3\Type\PageTypeExtenderInterface;
use RozbehSharahi\Graphql3\Type\QueryType;
use RozbehSharahi\Graphql3\Type\QueryTypeExtenderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Site\SiteFinder;

return static function (ContainerConfigurator $container, ContainerBuilder $containerBuilder) {
    $containerBuilder
        ->registerForAutoconfiguration(SetupInterface::class)
        ->addTag('graphql3.setup');

    $containerBuilder
        ->registerForAutoconfiguration(PageNodeExtenderInterface::class)
        ->addTag('graphql3.page_node_extender');

    $containerBuilder
        ->registerForAutoconfiguration(QueryTypeExtenderInterface::class)
        ->addTag('graphql3.query_type_extender');

    $containerBuilder
        ->registerForAutoconfiguration(PageTypeExtenderInterface::class)
        ->addTag('graphql3.page_type_extender');

    if(Environment::getContext()->isTesting()) {
        $containerBuilder->registerForAutoconfiguration(GraphqlController::class)->setPublic(true);
        $containerBuilder->registerForAutoconfiguration(SchemaRegistry::class)->setPublic(true);
        $containerBuilder->registerForAutoconfiguration(PageNode::class)->setPublic(true);
        $containerBuilder->registerForAutoconfiguration(QueryType::class)->setPublic(true);
        $containerBuilder->registerForAutoconfiguration(PageType::class)->setPublic(true);
        $containerBuilder->registerForAutoconfiguration(RecordResolver::class)->setPublic(true);
        $containerBuilder->registerForAutoconfiguration(SiteFinder::class)->setPublic(true);
    }
};
