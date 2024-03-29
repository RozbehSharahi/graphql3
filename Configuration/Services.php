<?php

declare(strict_types=1);
namespace RozbehSharahi\Graphql3;

use RozbehSharahi\Graphql3\Builder\LanguageListNodeBuilder;
use RozbehSharahi\Graphql3\Builder\LanguageNodeBuilder;
use RozbehSharahi\Graphql3\Builder\RecordListNodeBuilder;
use RozbehSharahi\Graphql3\Builder\RecordListNodeExtenderInterface;
use RozbehSharahi\Graphql3\Builder\RecordNodeBuilder;
use RozbehSharahi\Graphql3\Builder\RecordNodeExtenderInterface;
use RozbehSharahi\Graphql3\Builder\RecordTypeBuilder;
use RozbehSharahi\Graphql3\Builder\RecordTypeBuilderExtenderInterface;
use RozbehSharahi\Graphql3\Controller\GraphqlController;
use RozbehSharahi\Graphql3\Environment\Typo3Environment;
use RozbehSharahi\Graphql3\FieldCreator\FieldCreatorInterface;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Resolver\RecordListResolverExtenderInterface;
use RozbehSharahi\Graphql3\Resolver\RecordResolver;
use RozbehSharahi\Graphql3\Security\AccessChecker;
use RozbehSharahi\Graphql3\Security\JwtManager;
use RozbehSharahi\Graphql3\Setup\SetupInterface;
use RozbehSharahi\Graphql3\Type\MutationType;
use RozbehSharahi\Graphql3\Type\MutationTypeExtenderInterface;
use RozbehSharahi\Graphql3\Type\QueryType;
use RozbehSharahi\Graphql3\Type\QueryTypeExtenderInterface;
use RozbehSharahi\Graphql3\Voter\VoterInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Site\SiteFinder;

return static function (ContainerConfigurator $container, ContainerBuilder $containerBuilder) {
    $containerBuilder
        ->registerForAutoconfiguration(SetupInterface::class)
        ->addTag('graphql3.setup');

    $containerBuilder
        ->registerForAutoconfiguration(RecordNodeExtenderInterface::class)
        ->addTag('graphql3.record_node_extender');

    $containerBuilder
        ->registerForAutoconfiguration(QueryTypeExtenderInterface::class)
        ->addTag('graphql3.query_type_extender');

    $containerBuilder
        ->registerForAutoconfiguration(MutationTypeExtenderInterface::class)
        ->addTag('graphql3.mutation_type_extender');

    $containerBuilder
        ->registerForAutoconfiguration(VoterInterface::class)
        ->addTag('graphql3.voter');

    $containerBuilder
        ->registerForAutoconfiguration(FieldCreatorInterface::class)
        ->addTag('graphql3.field_creator');

    $containerBuilder
        ->registerForAutoconfiguration(RecordTypeBuilderExtenderInterface::class)
        ->addTag('graphql3.record_type_builder_extender');

    $containerBuilder
        ->registerForAutoconfiguration(RecordListResolverExtenderInterface::class)
        ->addTag('graphql3.record_list_resolver_extender');

    $containerBuilder
        ->registerForAutoconfiguration(RecordListNodeExtenderInterface::class)
        ->addTag('graphql3.record_list_node_extender');

    if(Environment::getContext()->isTesting()) {
        $containerBuilder->registerForAutoconfiguration(RecordTypeBuilder::class)->setPublic(true);
        $containerBuilder->registerForAutoconfiguration(GraphqlController::class)->setPublic(true);
        $containerBuilder->registerForAutoconfiguration(SchemaRegistry::class)->setPublic(true);
        $containerBuilder->registerForAutoconfiguration(QueryType::class)->setPublic(true);
        $containerBuilder->registerForAutoconfiguration(SiteFinder::class)->setPublic(true);
        $containerBuilder->registerForAutoconfiguration(AccessChecker::class)->setPublic(true);
        $containerBuilder->registerForAutoconfiguration(LanguageListNodeBuilder::class)->setPublic(true);
        $containerBuilder->registerForAutoconfiguration(LanguageNodeBuilder::class)->setPublic(true);
        $containerBuilder->registerForAutoconfiguration(RecordNodeBuilder::class)->setPublic(true);
        $containerBuilder->registerForAutoconfiguration(RecordListNodeBuilder::class)->setPublic(true);
        $containerBuilder->registerForAutoconfiguration(RecordResolver::class)->setPublic(true);
        $containerBuilder->registerForAutoconfiguration(JwtManager::class)->setPublic(true);
        $containerBuilder->registerForAutoconfiguration(MutationType::class)->setPublic(true);
        $containerBuilder->registerForAutoconfiguration(Typo3Environment::class)->setPublic(true);
    }
};
