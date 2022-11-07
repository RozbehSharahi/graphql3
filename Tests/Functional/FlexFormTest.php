<?php

/** @noinspection RequiredAttributes */
/* @noinspection PhpUnhandledExceptionInspection */

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

    private string $flexFormExample = '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
        <T3FlexForms>
            <data>
                <sheet index="sDEF">
                    <language index="lDEF">
                        <field index="settings.flexFormField">
                            <value index="vDEF">example value</value>
                        </field>
                    </language>
                </sheet>
            </data>
        </T3FlexForms>
    ';

    public function testCanResolveManyToManyFields(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->build();

        $scope
            ->createRecord('tt_content', [
                'uid' => 1,
                'pid' => 1,
                'header' => 'Login',
                'pi_flexform' => $this->flexFormExample,
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
        self::assertSame('example value', $response->get('data.content.formatDefault.settings.flexFormField'));
        self::assertSame('example value', $response->get('data.content.formatObject.settings.flexFormField'));
        self::assertSame($this->flexFormExample, $response->get('data.content.formatFlex'));
    }

    public function testCanExtendRecordWithFlexFormFields(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->build();

        $scope->get(SchemaRegistry::class)->registerCreator(fn () => new Schema([
            'query' => $scope->get(QueryType::class),
        ]));

        $scope->createRecord('tt_content', [
            'pid' => 1,
            'header' => 'Some plugin',
            'pi_flexform' => '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
                <T3FlexForms>
                    <data>
                        <sheet index="sDEF">
                            <language index="lDEF">
                                <field index="settings.flexFormString">
                                    <value index="vDEF">string</value>
                                </field>
                                <field index="settings.flexFormInteger">
                                    <value index="vDEF">1</value>
                                </field>
                                <field index="settings.flexFormInteger">
                                    <value index="vDEF">1234</value>
                                </field>
                                <field index="settings.flexFormFloat">
                                    <value index="vDEF">1234.12</value>
                                </field>
                            </language>
                        </sheet>
                    </data>
                </T3FlexForms>
            ',
        ]);

        $GLOBALS['TCA']['tt_content']['graphql3']['flexFormColumns'] = [
            'flexFormString' => [
                'config' => [
                    'type' => 'input',
                    'flexFormPointer' => 'pi_flexform::settings.flexFormString',
                ],
            ],
            'flexFormBool' => [
                'config' => [
                    'type' => 'check',
                    'flexFormPointer' => 'pi_flexform::settings.flexFormBool',
                ],
            ],
            'flexFormInteger' => [
                'config' => [
                    'type' => 'number',
                    'flexFormPointer' => 'pi_flexform::settings.flexFormInteger',
                ],
            ],
            'flexFormFloat' => [
                'config' => [
                    'type' => 'number',
                    'format' => 'decimal',
                    'flexFormPointer' => 'pi_flexform::settings.flexFormFloat',
                ],
            ],
            'flexFormFloatNotSet' => [
                'config' => [
                    'type' => 'number',
                    'format' => 'decimal',
                    'flexFormPointer' => 'pi_flexform::settings.flexFormFloatNotSet',
                ],
            ],
        ];

        $response = $scope->graphqlRequest('{
            content(uid: 1) {
                flexFormString
                flexFormBool
                flexFormInteger
                flexFormFloat
                flexFormFloatNotSet
            }
        }');

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('string', $response->get('data.content.flexFormString'));
        self::assertFalse($response->get('data.content.flexFormBool'));
        self::assertSame(1234, $response->get('data.content.flexFormInteger'));
        self::assertIsFloat($response->get('data.content.flexFormFloat'));
        self::assertEqualsWithDelta(1234.12, 0.01, $response->get('data.content.flexFormFloat'));
        self::assertNull($response->get('data.content.flexFormFloatNotSet'));
    }

    public function testNoneHandledFlexFormColumnsThrowException(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->build();

        $scope->get(SchemaRegistry::class)->registerCreator(fn () => new Schema([
            'query' => $scope->get(QueryType::class),
        ]));

        $scope->createRecord('tt_content', [
            'pid' => 1,
            'header' => 'Some plugin',
        ]);

        $GLOBALS['TCA']['tt_content']['graphql3']['flexFormColumns'] = [
            'someField' => [
                'config' => [
                    'type' => 'some-thing-that-can-not-be-handle-by-any-creator',
                    'flexFormPointer' => 'pi_flexform::settings.someField',
                ],
            ],
        ];

        $response = $scope->graphqlRequest('{
            content(uid: 1) {
                someField
            }
        }');

        self::assertSame(500, $response->getStatusCode());
        self::assertStringContainsString('no flex-form-field-creator that could handle', $response->getErrorMessage());
    }

    public function testCanCreateMediaFlexFormField(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->build();

        $GLOBALS['TCA']['tt_content']['graphql3']['flexFormColumns'] = [
            'flexFormMedia' => [
                'config' => [
                    'type' => 'file',
                    'flexFormPointer' => 'pi_flexform::media',
                    'foreign_table_field' => 'tt_content',
                    'foreign_match_fields' => [
                        'fieldname' => 'pi_flexform.media',
                    ],
                ],
            ],
        ];

        $scope
            ->createRecord('tt_content', [
                'uid' => 1,
                'header' => 'some content element',
            ])
            ->createRecord('sys_file', [
                'uid' => 200,
                'identifier' => '/user_upload/whatever.txt',
                'extension' => 'txt',
                'name' => 'whatever.txt',
            ])
            ->createRecord('sys_file', [
                'uid' => 300,
                'identifier' => '/user_upload/whatever-2.txt',
                'extension' => 'txt',
                'name' => 'whatever-2.txt',
            ])
            ->createRecord('sys_file_reference', [
                'uid' => 1,
                'tablenames' => 'tt_content',
                'fieldname' => 'pi_flexform.media',
                'uid_local' => 200,
                'uid_foreign' => 1,
            ])
            ->createRecord('sys_file_reference', [
                'uid' => 2,
                'tablenames' => 'tt_content',
                'fieldname' => 'pi_flexform.media',
                'uid_local' => 300,
                'uid_foreign' => 1,
            ])
        ;

        $scope
            ->getSchemaRegistry()
            ->registerCreator(fn () => new Schema(['query' => $scope->get(QueryType::class)]))
        ;

        $response = $scope->graphqlRequest('{ 
            content(uid: 1) {
                flexFormMedia {
                  uid
                  publicUrl
                  extension
                  imageUrl
                }
            }
        }');

        self::assertSame(200, $response->getStatusCode());
    }
}
