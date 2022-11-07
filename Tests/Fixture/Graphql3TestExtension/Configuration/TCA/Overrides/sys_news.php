<?php

declare(strict_types=1);

$GLOBALS['TCA']['sys_news']['types']['1']['showitem'] .= ',flexform';
$GLOBALS['TCA']['sys_news']['columns']['flexform'] = [
    'label' => 'Testing flexform',
    'config' => [
        'type' => 'flex',
        'ds' => [
            'default' => '
                <T3DataStructure>
                  <ROOT>
                    <type>array</type>
                    <el>
                      <files>
                        <label>Files</label>
                        <config>
                            <type>file</type>
                        </config>
                      </files>
                    </el>
                  </ROOT>
                </T3DataStructure>
            ',
        ],
    ],
];
$GLOBALS['TCA']['sys_news']['graphql3']['flexFormColumns'] = [
    'files' => [
        'config' => [
            'flexFormPointer' => 'flexform::files',
            'type' => 'file',
            'foreign_match_fields' => [
                'fieldname' => 'files',
            ],
        ],
    ],
];
