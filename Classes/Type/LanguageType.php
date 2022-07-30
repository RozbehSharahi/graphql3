<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode as Node;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage as Lang;

class LanguageType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name' => 'Language',
            'fields' => $this->getFieldClosure(),
        ]);
    }

    private function getFieldClosure(): \Closure
    {
        return static function () {
            return GraphqlNodeCollection::create()
                ->add(Node::create('id')->withType(Type::int())->withResolver(fn (Lang $site) => $site->getLanguageId()))
                ->add(Node::create('title')->withResolver(fn (Lang $site) => $site->getTitle()))
                ->add(Node::create('twoLetterIsoCode')->withResolver(fn (Lang $site) => $site->getTwoLetterIsoCode()))
                ->add(Node::create('base')->withResolver(fn (Lang $site) => $site->getBase()))
                ->add(Node::create('locale')->withResolver(fn (Lang $site) => $site->getLocale()))
                ->add(Node::create('direction')->withResolver(fn (Lang $site) => $site->getDirection()))
                ->add(Node::create('flagIdentifier')->withResolver(fn (Lang $site) => $site->getFlagIdentifier()))
                ->add(Node::create('hrefLang')->withResolver(fn (Lang $site) => $site->getHreflang()))
                ->add(Node::create('navigationTitle')->withResolver(fn (Lang $site) => $site->getNavigationTitle()))
                ->add(Node::create('typo3Language')->withResolver(fn (Lang $site) => $site->getTypo3Language()))
                ->add(
                    Node::create('fallbackLanguageIds')
                        ->withType(Type::listOf(Type::int()))
                        ->withResolver(fn (Lang $site) => $site->getFallbackLanguageIds())
                )
                ->toArray()
            ;
        };
    }
}
