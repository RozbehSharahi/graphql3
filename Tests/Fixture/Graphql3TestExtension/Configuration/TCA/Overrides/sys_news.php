<?php

declare(strict_types=1);

$GLOBALS['TCA']['sys_news']['types']['1']['showitem'] .= ',flexform';

$typo3Environment = new \RozbehSharahi\Graphql3\Environment\Typo3Environment();

$filesFlexForm = '
  <files>
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
  </files>
';

if (12 === $typo3Environment->getMainVersion()) {
    $filesFlexForm = '
      <files>
        <label>Files</label>
        <config>
          <type>file</type>
        </config>
      </files>
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
