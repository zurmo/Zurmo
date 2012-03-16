<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
     * details.
     *
     * You should have received a copy of the GNU General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/
    class MappingFormLayoutUtilTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super                      = SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = $super;
        }

        /*
         * test function renderAttributeAndColumnTypeContent with columnType = importColumn
         */
        public function testRenderAttributeAndColumnTypeContentWithImportColumn()
        {
            $super = SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = $super;
            $data                       = ImportModelTestItemImportRules::getMappableAttributeIndicesAndDerivedTypes();
            $modelName                  = 'ImportModelTestItem';
            $mappingFormLayoutUtil      = ImportToMappingFormLayoutUtil::
                    make($modelName, new ZurmoActiveForm(), 'ImportModelTestItem', $data);
            $attributeForImportColumn   = $mappingFormLayoutUtil->getMappableAttributeIndicesAndDerivedTypesForImportColumns();
            $columnName                 = 'SampleColumn';
            $content                    = $mappingFormLayoutUtil->renderAttributeAndColumnTypeContent($columnName, 'importColumn', 'sampleAttribute', 'sampleAjax');
            $name                       = $modelName . '[' . $columnName . '][attributeIndexOrDerivedType]';
            $id                         = $modelName . '_' . $columnName . '_attributeIndexOrDerivedType';
            $scriptExist                = Yii::app()->clientScript->isScriptRegistered('AttributeDropDown' . $id);
            $this->assertTrue($scriptExist);
            $this->assertTrue(stripos($content, $name) !== false);
            $hiddenInputName            = 'ImportModelTestItem' . '[' . $columnName . '][type]';
            $hiddenIdName               = 'ImportModelTestItem' . '_' . $columnName . '_type';
            $this->assertTrue(stripos($content, $hiddenInputName) !== false);
            $this->assertTrue(stripos($content, $hiddenIdName) !== false);
            $this->assertTrue(stripos($content, end($attributeForImportColumn)) !== false);
        }

        /*
         * test function renderAttributeAndColumnTypeContent with columnType = importColumn
         */
        public function testRenderAttributeAndColumnTypeContentWithExtraColumn()
        {
            $super                      = SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = $super;
            $data                       = ImportModelTestItemImportRules::getMappableAttributeIndicesAndDerivedTypes();
            $mappingFormLayoutUtil      = ImportToMappingFormLayoutUtil::
                    make('ImportModelTestItem', new ZurmoActiveForm(), 'ImportModelTestItem', $data);
            $attributeForExtraColumn    = $mappingFormLayoutUtil->getMappableAttributeIndicesAndDerivedTypesForExtraColumns();
            $columnName                 = 'SampleColumn';
            $content                    = $mappingFormLayoutUtil->renderAttributeAndColumnTypeContent($columnName, 'extraColumn', 'sampleAttribute', 'sampleAjax');
            $this->assertTrue(stripos($content, Yii::t('Default', 'Remove Field')) !== false);
            $this->assertTrue(stripos($content, end($attributeForExtraColumn)) !== false);
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testRenderMappingRulesElements()
        {
            $super                      = SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = $super;
            $data                       = ImportModelTestItemImportRules::getMappableAttributeIndicesAndDerivedTypes();
            $mappingFormLayoutUtil = ImportToMappingFormLayoutUtil::
                    make('ImportModelTestItem', new ZurmoActiveForm(), 'ImportModelTestItem', $data);
            $columnName                 = 'SampleColumn';
            $content                    = $mappingFormLayoutUtil->renderMappingRulesElements($columnName, null, 'Accounts', 'importColumn', array());
            $this->assertTrue(stripos($content, $columnName) !== false);
            $this->assertTrue(stripos($content, '<table><tbody><tr>') === false);
            $this->assertTrue(stripos($content, $columnName . '-mapping-rules') !== false);
            $content                    = $mappingFormLayoutUtil->renderMappingRulesElements($columnName, 'officePhone', 'Accounts', 'importColumn', array());
            $this->assertTrue(stripos($content, '<table><tbody><tr>') !== false);

            $mappingFormLayoutUtil->renderMappingRulesElements($columnName, 'DummyAttribute', 'Accounts', 'importColumn', array());
        }

        public function testRenderMappingDataMetadataWithRenderedElements()
        {
            $testData                       = array(
                                                'rows' => array(
                                                    array(
                                                        'cells' => array('Hello')
                                                    )
                                                 )
                                            );
            $super                      = SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = $super;
            $data                       = ImportModelTestItemImportRules::getMappableAttributeIndicesAndDerivedTypes();
            $mappingFormLayoutUtil      = ImportToMappingFormLayoutUtil::
                    make('ImportModelTestItem', new ZurmoActiveForm(), 'ImportModelTestItem', $data);
            $content                    = $mappingFormLayoutUtil->renderMappingDataMetadataWithRenderedElements($testData);
            $this->assertEquals($content, '<tr><td>Hello</td></tr>');
        }

        public function testRetMappingRulesDivIdByColumnName()
        {
            $content                    = MappingFormLayoutUtil::resolveSampleColumnIdByColumnName('SampleColumn');
            $this->assertEquals($content, 'SampleColumn-import-data');
        }

        public function testRenderChoppedStringContent()
        {
            $text                       = 'a';
            $content                    = MappingFormLayoutUtil::renderChoppedStringContent($text);
            $this->assertEquals($content, $text);
            $text                       = str_repeat('a', 24);
            $content                    = MappingFormLayoutUtil::renderChoppedStringContent($text);
            $this->assertEquals($content, CHtml::tag('div', array('title' => $text), str_repeat('a', 22) . '...'));
        }

        public function testRenderHeaderColumnContent()
        {
            $super                      = SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = $super;
            $data                       = ImportModelTestItemImportRules::getMappableAttributeIndicesAndDerivedTypes();
            $mappingFormLayoutUtil      = ImportToMappingFormLayoutUtil::
                    make('ImportModelTestItem', new ZurmoActiveForm(), 'ImportModelTestItem', $data);
            $text                       = 'a';
            $content                    = $mappingFormLayoutUtil->renderHeaderColumnContent('SampleColumn', $text);
            $this->assertEquals($content, $text);

            $text                       = str_repeat('a', 24);
            $content                    = $mappingFormLayoutUtil->renderHeaderColumnContent('SampleColumn', $text);
            $this->assertEquals($content, CHtml::tag('div', array('title' => $text), str_repeat('a', 22) . '...'));
        }

        public function testRenderImportColumnContent()
        {
            $super                      = SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = $super;
            $data                       = ImportModelTestItemImportRules::getMappableAttributeIndicesAndDerivedTypes();
            $mappingFormLayoutUtil      = ImportToMappingFormLayoutUtil::
                    make('ImportModelTestItem', new ZurmoActiveForm(), 'ImportModelTestItem', $data);
            $sampleValue                = 'a';
            $columnName                 = 'SampleColumnName';
            $content                    = $mappingFormLayoutUtil->renderImportColumnContent($columnName, $sampleValue);
            $this->assertEquals($content, '<div id="' . $columnName . '-import-data">' . $sampleValue . '</div>');

            $sampleValue                = str_repeat('a', 24);
            $content                    = $mappingFormLayoutUtil->renderImportColumnContent($columnName, $sampleValue);
            $this->assertEquals($content, '<div id="' . $columnName . '-import-data">' . CHtml::tag('div', array('title' => $sampleValue), str_repeat('a', 22) . '...') . '</div>');
        }

        public function testGetSampleColumnHeaderId()
        {
            $content                    = MappingFormLayoutUtil::getSampleColumnHeaderId();
            $this->assertEquals($content, 'sample-column-header');
        }
    }
?>