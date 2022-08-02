<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode as Node;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage as Lang;

class LanguageTypeBuilder implements TypeBuilderInterface
{
    protected static ?ObjectType $cache;

    public static function flushCache(): void
    {
        self::$cache = null;
    }

    public function build(): ObjectType
    {
        if (!empty(self::$cache)) {
            return self::$cache;
        }

        return self::$cache = new ObjectType([
            'name' => 'Language',
            'fields' => fn () => $this->getFields(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getFields(): array
    {
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
            ->toArray()
        ;
    }
}
