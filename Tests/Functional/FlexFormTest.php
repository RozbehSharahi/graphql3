<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tests\Functional;

use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;
use RozbehSharahi\Graphql3\Type\QueryType;

class FlexFormTest extends TestCase
{
    use FunctionalTrait;

    public function testCanResolveManyToManyFields(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->build();

        /** @noinspection RequiredAttributes */
        $flexForm = '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
            <T3FlexForms>
                <data>
                    <sheet index="sDEF">
                        <language index="lDEF">
                            <field index="settings.showPermaLogin">
                                <value index="vDEF">1</value>
                            </field>
                        </language>
                    </sheet>
                </data>
            </T3FlexForms>
        ';

        $scope
            ->createRecord('tt_content', [
                'uid' => 1,
                'pid' => 1,
                'header' => 'Login',
                'pi_flexform' => $flexForm,
            ])
        ;

        $scope->get(SchemaRegistry::class)->registerCreator(fn () => new Schema([
            'query' => $scope->get(QueryType::class),
        ]));

        $response = $scope->graphqlRequest('{
          content(uid: 1) {
            formatDefault: piFlexform
            formatFlex: piFlexform(format: "flex")
            formatObject: piFlexform(format: "object")
          }
        }');

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('1', $response->get('data.content.formatDefault.settings.showPermaLogin'));
        self::assertSame('1', $response->get('data.content.formatObject.settings.showPermaLogin'));
        self::assertSame($flexForm, $response->get('data.content.formatFlex'));
    }
}
