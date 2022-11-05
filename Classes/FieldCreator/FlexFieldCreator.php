<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\FieldCreator;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgument;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\Record;
use RozbehSharahi\Graphql3\Domain\Model\Tca\ColumnConfiguration;
use RozbehSharahi\Graphql3\Exception\BadRequestException;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use RozbehSharahi\Graphql3\Type\UnknownType;
use TYPO3\CMS\Core\Service\FlexFormService;

class FlexFieldCreator implements FieldCreatorInterface
{
    public const ALLOWED_FORMATS = [self::FORMAT_FLEX, self::FORMAT_OBJECT];

    public const FORMAT_OBJECT = 'object';

    public const FORMAT_FLEX = 'flex';

    public const DEFAULT_FORMAT = self::FORMAT_OBJECT;

    public static function getPriority(): int
    {
        return 0;
    }

    public function __construct(protected FlexFormService $flexFormService, protected UnknownType $unknownType)
    {
    }

    public function supportsField(ColumnConfiguration $column): bool
    {
        return $column->isFlex();
    }

    public function createField(ColumnConfiguration $column): GraphqlNode
    {
        return GraphqlNode::create()
            ->withName($column->getGraphqlName())
            ->withType($this->unknownType)
            ->withArguments(GraphqlArgumentCollection::create([
                GraphqlArgument::create()
                    ->withName('format')
                    ->withType(Type::nonNull(Type::string()))
                    ->withDefaultValue(self::DEFAULT_FORMAT),
            ]))
            ->withResolver(function (Record $record, array $args) use ($column) {
                $format = $args['format'];

                if (!in_array($format, self::ALLOWED_FORMATS, true)) {
                    throw new BadRequestException('Flex form format is invalid ('.$format.'). Allowed values are: '.implode(', ', self::ALLOWED_FORMATS));
                }

                $value = $record->get($column);

                if (empty($value)) {
                    return null;
                }

                if (self::FORMAT_FLEX === $format) {
                    return $value;
                }

                return $this->flexFormService->convertFlexFormContentToArray($value);
            })
        ;
    }

    protected function createJson(string $value): string
    {
        try {
            return json_encode(
                $this->flexFormService->convertFlexFormContentToArray($value),
                JSON_THROW_ON_ERROR
            );
        } catch (\JsonException) {
            throw new InternalErrorException('Could not parse flexform value: '.$value);
        }
    }
}
