<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgument;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode as Node;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Extbase\Service\ImageService;

class FileReferenceTypeBuilder implements TypeBuilderInterface
{
    protected static ?ObjectType $cache;

    public static function flushCache(): void
    {
        self::$cache = null;
    }

    public function __construct(protected ImageService $imageService)
    {
    }

    public function build(): ObjectType
    {
        if (!empty(self::$cache)) {
            return self::$cache;
        }

        return self::$cache = new ObjectType([
            'name' => 'FileReference',
            'fields' => fn () => $this->getFields(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getFields(): array
    {
        return GraphqlNodeCollection::create()
            ->add(
                Node::create()
                    ->withName('uid')
                    ->withType(Type::int())
                    ->withResolver(fn (FileReference $fileReference) => $fileReference->getUid())
            )
            ->add(
                Node::create()
                    ->withName('size')
                    ->withType(Type::int())
                    ->withResolver(fn (FileReference $fileReference) => $fileReference->getSize())
            )
            ->add(
                Node::create()
                    ->withName('extension')
                    ->withType(Type::string())
                    ->withResolver(fn (FileReference $fileReference) => $fileReference->getExtension())
            )
            ->add(
                Node::create()
                    ->withName('publicUrl')
                    ->withType(Type::string())
                    ->withResolver(fn (FileReference $fileReference) => $fileReference->getPublicUrl())
            )
            ->add(
                Node::create()
                    ->withName('imageUrl')
                    ->withArguments(
                        GraphqlArgumentCollection::create()->add(
                            GraphqlArgument::create('variant')
                                ->withDefaultValue('default')
                                ->withType(Type::string())
                        )
                    )
                    ->withType(Type::string())
                    ->withResolver(function (FileReference $fileReference, array $args) {
                        if (!in_array($fileReference->getExtension(), $this->getImageFileExtensions(), true)) {
                            return null;
                        }

                        if (!$fileReference->hasProperty('crop') || empty($fileReference->getProperty('crop'))) {
                            return $fileReference->getPublicUrl();
                        }

                        $cropVariant = CropVariantCollection::create($fileReference->getProperty('crop'))
                            ->getCropArea($args['variant'])
                        ;

                        $processedImage = $this->imageService->applyProcessingInstructions($fileReference, [
                            'crop' => $cropVariant->isEmpty()
                                ? null
                                : $cropVariant->makeAbsoluteBasedOnFile($fileReference),
                        ]);

                        return $this->imageService->getImageUri($processedImage);
                    })
            )
            ->toArray()
        ;
    }

    /**
     * @return string[]
     */
    private function getImageFileExtensions(): array
    {
        return !empty($GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'])
            ? explode(',', $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'])
            : ['gif', 'jpg', 'png'];
    }
}
