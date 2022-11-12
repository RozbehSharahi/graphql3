<?php

/** @noinspection RequiredAttributes */
/* @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tests\Functional;

use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Domain\Model\Tca\FlexFormFieldConfiguration;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;
use RozbehSharahi\Graphql3\Type\QueryType;

class FlexFormTest extends TestCase
{
    use FunctionalTrait;

    public function testCanCreateFlexFormPassThroughField(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->build();

        $flexFormData = $this->createFlexFormData([
            'settings.flexFormField' => 'example value',
        ]);

        $scope
            ->createRecord('tt_content', [
                'uid' => 1,
                'pid' => 1,
                'header' => 'Login',
                'pi_flexform' => $flexFormData,
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
        self::assertSame($flexFormData, $response->get('data.content.formatFlex'));
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
            'pi_flexform' => $this->createFlexFormData([
                'settings.string' => 'string',
                'settings.bool' => 1,
                'settings.integer' => 1234,
                'settings.float' => 1234.12,
            ]),
        ]);

        $GLOBALS['TCA']['tt_content']['columns']['pi_flexform']['config']['ds'] = [
            'default' => '
                <T3DataStructure>
                  <ROOT>
                    <type>array</type>
                    <el>
                      <settings.string>
                        <config>
                          <type>input</type>
                          <graphql3><name>string</name></graphql3>
                        </config>
                      </settings.string>
                      <settings.bool>
                        <config>
                          <type>check</type>
                          <graphql3><name>bool</name></graphql3>
                        </config>
                      </settings.bool>
                      <settings.integer>
                        <config>
                          <type>number</type>
                          <graphql3><name>integer</name></graphql3>
                        </config>
                      </settings.integer>
                      <settings.float>
                        <config>
                          <type>number</type>
                          <format>decimal</format>
                          <graphql3><name>float</name></graphql3>
                        </config>
                      </settings.float>
                      <settings.floatNotSet>
                        <config>
                          <type>number</type>
                          <format>decimal</format>
                        </config>
                      </settings.floatNotSet>
                    </el>
                  </ROOT>
                </T3DataStructure>
            ',
        ];

        $GLOBALS['TCA']['tt_content']['graphql3']['flexFormColumns'] = [
            'pi_flexform::default::settings.string',
            'pi_flexform::default::settings.bool',
            'pi_flexform::default::settings.integer',
            'pi_flexform::default::settings.float',
            'pi_flexform::default::settings.floatNotSet',
        ];

        $response = $scope->graphqlRequest('{
            content(uid: 1) {
                string
                bool
                integer
                float
                flex_piFlexform_settings_floatNotSet
            }
        }');

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('string', $response->get('data.content.string'));
        self::assertTrue($response->get('data.content.bool'));
        self::assertSame(1234, $response->get('data.content.integer'));
        self::assertIsFloat($response->get('data.content.float'));
        self::assertEqualsWithDelta(1234.12, 0.01, $response->get('data.content.float'));
        self::assertNull($response->get('data.content.flex_piFlexform_settings_floatNotSet'));
    }

    public function testFlexFormFileReferenceCanBeResolve(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->build();

        $scope->get(SchemaRegistry::class)->registerCreator(fn () => new Schema([
            'query' => $scope->get(QueryType::class),
        ]));

        $scope->createRecord('tt_content', [
            'pid' => 1,
            'header' => 'Some plugin',
            'pi_flexform' => $this->createFlexFormData([
                'settings.string' => 'string',
            ]),
        ]);

        $GLOBALS['TCA']['tt_content']['columns']['pi_flexform']['config']['ds'] = [
            'default' => 'FILE:EXT:graphql3/Tests/Fixture/FlexForm/flex-form.string-field.xml',
        ];

        $GLOBALS['TCA']['tt_content']['graphql3']['flexFormColumns'] = [
            'pi_flexform::default::settings.string',
        ];

        $response = $scope->graphqlRequest('{
            content(uid: 1) {
                string
            }
        }');

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('string', $response->get('data.content.string'));
    }

    public function testUndefinedFlexFormFieldConfigurationThrowsReasonableErrorMessage(): void
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
            'pi_flexform::default::thisFieldDoesNotExist',
        ];

        $response = $scope->graphqlRequest('{
            content(uid: 1) {
                someField
            }
        }');

        self::assertSame(500, $response->getStatusCode());
        self::assertStringContainsString('Could not find flex field', $response->getErrorMessage());
    }

    public function testCanCreateFileRelation(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->build();

        $GLOBALS['TCA']['tt_content']['graphql3']['flexFormColumns'] = ['pi_flexform::default::file'];
        $GLOBALS['TCA']['tt_content']['columns']['pi_flexform'] = [
            'config' => [
                'type' => 'flex',
                'ds' => [
                    'default' => '
                        <T3DataStructure>
                          <ROOT>
                            <type>array</type>
                            <el>
                              <file>
                                <config>
                                  <type>file</type>
                                  <foreign_match_fields>
                                    <fieldname>pi_flexform.file</fieldname>
                                  </foreign_match_fields>
                                </config>
                              </file>
                            </el>
                          </ROOT>
                        </T3DataStructure>
                    ',
                ],
            ],
        ];

        $scope
            ->createRecord('tt_content', [
                'uid' => 1,
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
                'fieldname' => 'pi_flexform.file',
                'uid_local' => 200,
                'uid_foreign' => 1,
            ])
            ->createRecord('sys_file_reference', [
                'uid' => 2,
                'tablenames' => 'tt_content',
                'fieldname' => 'pi_flexform.file',
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
                flex_piFlexform_file {
                  uid
                  publicUrl
                  extension
                  imageUrl
                }
            }
        }');

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('user_upload/whatever.txt', $response->get('data.content.flex_piFlexform_file.0.publicUrl'));
        self::assertSame('txt', $response->get('data.content.flex_piFlexform_file.0.extension'));
        self::assertNull($response->get('data.content.flex_piFlexform_file.0.imageUrl'));
    }

    public function testCanCreateFlexFormSingleSelect(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->build();

        $scope->get(SchemaRegistry::class)->registerCreator(fn () => new Schema([
            'query' => $scope->get(QueryType::class),
        ]));

        $GLOBALS['TCA']['tt_content']['graphql3']['flexFormColumns'] = [
            'pi_flexform::default::flexSingleSelect',
        ];

        $GLOBALS['TCA']['tt_content']['columns']['pi_flexform'] = [
            'config' => [
                'type' => 'flex',
                'ds' => [
                    'default' => '
                        <T3DataStructure>
                          <ROOT>
                            <type>array</type>
                            <el>
                              <flexSingleSelect>
                                <config>
                                  <type>select</type>
                                  <renderType>selectSingle</renderType>
                                  <graphql3><name>flexSingleSelect</name></graphql3>
                                </config>
                              </flexSingleSelect>
                            </el>
                          </ROOT>
                        </T3DataStructure>
                    ',
                ],
            ],
        ];

        $scope
            ->createRecord('tt_content', [
                'uid' => 1,
                'pi_flexform' => $this->createFlexFormData([
                    'flexSingleSelect' => 'some-other-value',
                ]),
            ])
        ;

        $response = $scope->graphqlRequest('{
            content(uid: 1) {
                flexSingleSelect
            }
        }');

        self::assertSame('some-other-value', $response->get('data.content.flexSingleSelect'));
    }

    public function testCanCreateFlexFormLanguageType(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->build();

        $scope->get(SchemaRegistry::class)->registerCreator(fn () => new Schema([
            'query' => $scope->get(QueryType::class),
        ]));

        $GLOBALS['TCA']['tt_content']['graphql3']['flexFormColumns'] = [
            'pi_flexform::default::flexLanguage',
        ];

        $GLOBALS['TCA']['tt_content']['columns']['pi_flexform'] = [
            'config' => [
                'type' => 'flex',
                'ds' => [
                    'default' => '
                        <T3DataStructure>
                          <ROOT>
                            <type>array</type>
                            <el>
                              <flexLanguage>
                                <config>
                                  <type>language</type>
                                  <graphql3><name>flexLanguage</name></graphql3>
                                </config>
                              </flexLanguage>
                            </el>
                          </ROOT>
                        </T3DataStructure>
                    ',
                ],
            ],
        ];

        $scope
            ->createRecord('tt_content', [
                'uid' => 1,
                'pi_flexform' => $this->createFlexFormData([
                    'flexLanguage' => 1,
                ]),
            ])
        ;

        $response = $scope->graphqlRequest('{
            content(uid: 1) {
                flexLanguage {
                    twoLetterIsoCode
                }
            }
        }');

        self::assertSame('de', $response->get('data.content.flexLanguage.twoLetterIsoCode'));
    }

    public function testDynamicFlexFormStructuresAreNotSupported(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->build();

        $scope->get(SchemaRegistry::class)->registerCreator(fn () => new Schema([
            'query' => $scope->get(QueryType::class),
        ]));

        $GLOBALS['TCA']['pages']['columns']['flexform_data'] = [
            'config' => [
                'type' => 'flex',
            ],
        ];
        $GLOBALS['TCA']['pages']['graphql3']['flexFormColumns'] = ['flexform_data::default::settings.string'];

        $response = $scope->graphqlRequest('{ 
            page(uid: 1) { 
                title 
            } 
        }');

        self::assertSame(500, $response->getStatusCode());
        self::assertStringContainsString(
            sprintf(FlexFormFieldConfiguration::ERROR_MISSING_DATA_STRUCTURE, 'flexform_data'),
            $response->getErrorMessage()
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function createFlexFormData(array $data): string
    {
        $properties = '';

        foreach ($data as $property => $value) {
            $properties .= '
                <field index="'.$property.'">
                    <value index="vDEF">'.$value.'</value>
                </field>
            ';
        }

        return '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
            <T3FlexForms>
                <data>
                    <sheet index="sDEF">
                        <language index="lDEF">
                            '.$properties.'
                        </language>
                    </sheet>
                </data>
            </T3FlexForms>
        ';
    }
}
