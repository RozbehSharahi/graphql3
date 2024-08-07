<?php

declare(strict_types=1);

$GLOBALS['TCA']['sys_news']['types']['1']['showitem'] .= ',flexform';

$typo3Environment = new RozbehSharahi\Graphql3\Environment\Typo3Environment();

$filesFlexForm = '
  <settings.files>
    <label>Files</label>
    <config>
        <type>inline</type>
        <foreign_table>sys_file_reference</foreign_table>
        <foreign_field>uid_foreign</foreign_field>
        <foreign_sortby>sorting_foreign</foreign_sortby>
        <foreign_table_field>tablenames</foreign_table_field>
        <foreign_match_fields>
            <fieldname>files</fieldname>
        </foreign_match_fields>
        <foreign_label>uid_local</foreign_label>
        <foreign_selector>uid_local</foreign_selector>
        <overrideChildTca>
            <columns>
                <uid_local>
                    <config>
                        <appearance>
                            <elementBrowserType>file</elementBrowserType>
                            <elementBrowserAllowed></elementBrowserAllowed>
                        </appearance>
                    </config>
                </uid_local>
            </columns>
        </overrideChildTca>
        <filter>
            <userFunc>TYPO3\CMS\Core\Resource\Filter\FileExtensionFilter->filterInlineChildren</userFunc>
            <parameters>
                <allowedFileExtensions></allowedFileExtensions>
                <disallowedFileExtensions></disallowedFileExtensions>
            </parameters>
        </filter>
        <appearance>
            <useSortable>1</useSortable>
            <headerThumbnail>
                <field>uid_local</field>
                <width>45</width>
                <height>45c</height>
            </headerThumbnail>
            <enabledControls>
                <info>1</info>
                <new>0</new>
                <dragdrop>1</dragdrop>
                <sort>0</sort>
                <hide>1</hide>
                <delete>1</delete>
            </enabledControls>
        </appearance>
    </config>
  </settings.files>
';

// Currently, typo3 12 flexform handling is broken. Upload will not work !
if (12 === $typo3Environment->getMainVersion()) {
    $filesFlexForm = '
      <settings.files>
        <label>Files</label>
        <config>
          <type>file</type>
        </config>
      </settings.files>
    ';
}

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
                      '.$filesFlexForm.'
                      <settings.color>
                        <label>Select color</label>
                        <config>
                          <type>select</type>
                          <renderType>selectSingle</renderType>
                          <items>
                            <numIndex index="0">
                                <numIndex index="0">None</numIndex>
                                <numIndex index="1"></numIndex>
                            </numIndex>
                            <numIndex index="1">
                                <numIndex index="0">Red</numIndex>
                                <numIndex index="1">red</numIndex>
                            </numIndex>
                            <numIndex index="2">
                                <numIndex index="0">Green</numIndex>
                                <numIndex index="1">green</numIndex>
                            </numIndex>
                          </items>
                        </config>
                      </settings.color>
                    </el>
                  </ROOT>
                </T3DataStructure>
            ',
        ],
    ],
];
$GLOBALS['TCA']['sys_news']['graphql3']['flexFormColumns'] = [
    'flexform::default::settings.color',
    'flexform::default::settings.files',
];
