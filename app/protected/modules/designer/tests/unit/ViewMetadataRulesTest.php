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

    class ViewMetadataRulesTest extends ZurmoBaseTest
    {
        public function testAddBlankForDropDownViewMetadataRules()
        {
            $elementMetadata = array();
            $elementInformation = array();
            $elementInformation['type'] = 'DropDown';
            AddBlankForDropDownViewMetadataRules::resolveElementMetadata($elementInformation, $elementMetadata);
            $this->assertTrue($elementMetadata['addBlank']);

            $elementMetadata = array();
            $elementInformation = array();
            $elementInformation['type'] = 'MultiSelectDropDown';
            AddBlankForDropDownViewMetadataRules::resolveElementMetadata($elementInformation, $elementMetadata);
            $this->assertTrue($elementMetadata['addBlank']);

            $elementMetadata = array();
            $elementInformation = array();
            $elementInformation['type'] = 'RadioDropDown';
            AddBlankForDropDownViewMetadataRules::resolveElementMetadata($elementInformation, $elementMetadata);
            $this->assertTrue($elementMetadata['addBlank']);

            $elementMetadata = array();
            $elementInformation = array();
            $elementInformation['type'] = 'Phone';
            AddBlankForDropDownViewMetadataRules::resolveElementMetadata($elementInformation, $elementMetadata);
            $this->assertTrue(!isset($elementMetadata['addBlank']));
        }

        public function testAddLinkViewMetadataRules()
        {
            $elementMetadata = array();
            $elementInformation = array();
            $elementInformation['attributeName'] = 'name';
            $elementInformation['type'] = 'Text';
            AddLinkViewMetadataRules::resolveElementMetadata($elementInformation, $elementMetadata);
            $this->assertTrue($elementMetadata['isLink']);

            $elementMetadata = array();
            $elementInformation = array();
            $elementInformation['attributeName'] = 'null';
            $elementInformation['type'] = 'FullName';
            AddLinkViewMetadataRules::resolveElementMetadata($elementInformation, $elementMetadata);
            $this->assertTrue($elementMetadata['isLink']);

            $elementMetadata = array();
            $elementInformation = array();
            $elementInformation['attributeName'] = 'randomAttribute';
            $elementInformation['type'] = 'Text';
            AddLinkViewMetadataRules::resolveElementMetadata($elementInformation, $elementMetadata);
            $this->assertTrue(!isset($elementMetadata['isLink']));
        }

        public function testBooleanAsDropDownViewMetadataRules()
        {
            $elementMetadata = array();
            $elementInformation = array();
            $elementInformation['type'] = 'CheckBox';
            BooleanAsDropDownViewMetadataRules::resolveElementMetadata($elementInformation, $elementMetadata);
            $this->assertTrue($elementMetadata['addBlank']);
            $this->assertEquals('BooleanStaticDropDown', $elementMetadata['type']);

            $elementMetadata = array();
            $elementInformation = array();
            $elementInformation['type'] = 'Phone';
            BooleanAsDropDownViewMetadataRules::resolveElementMetadata($elementInformation, $elementMetadata);
            $this->assertTrue(!isset($elementMetadata['addBlank']));
            $this->assertTrue(!isset($elementMetadata['type']));
        }

        public function testRadioAsDropDownViewMetadataRules()
        {
            $elementMetadata = array();
            $elementInformation = array();
            $elementInformation['type'] = 'RadioDropDown';
            RadioAsDropDownViewMetadataRules::resolveElementMetadata($elementInformation, $elementMetadata);
            $this->assertTrue($elementMetadata['addBlank']);
            $this->assertEquals('DropDown', $elementMetadata['type']);

            $elementMetadata = array();
            $elementInformation = array();
            $elementInformation['type'] = 'Phone';
            RadioAsDropDownViewMetadataRules::resolveElementMetadata($elementInformation, $elementMetadata);
            $this->assertTrue(!isset($elementMetadata['addBlank']));
            $this->assertTrue(!isset($elementMetadata['type']));
        }

        public function testDropDownAsMultiSelectViewMetadataRules()
        {
            $elementMetadata = array();
            $elementInformation = array();
            $elementInformation['type'] = 'RadioDropDown';
            DropDownAsMultiSelectViewMetadataRules::resolveElementMetadata($elementInformation, $elementMetadata);
            $this->assertTrue($elementMetadata['addBlank']);
            $this->assertEquals('DropDownAsMultiSelect', $elementMetadata['type']);

            $elementMetadata = array();
            $elementInformation = array();
            $elementInformation['type'] = 'DropDown';
            DropDownAsMultiSelectViewMetadataRules::resolveElementMetadata($elementInformation, $elementMetadata);
            $this->assertTrue($elementMetadata['addBlank']);
            $this->assertEquals('DropDownAsMultiSelect', $elementMetadata['type']);

            $elementMetadata = array();
            $elementInformation = array();
            $elementInformation['type'] = 'Phone';
            DropDownAsMultiSelectViewMetadataRules::resolveElementMetadata($elementInformation, $elementMetadata);
            $this->assertTrue(!isset($elementMetadata['addBlank']));
            $this->assertTrue(!isset($elementMetadata['type']));
        }

        public function testTextAreaAsTextViewMetadataRules()
        {
            $elementMetadata = array();
            $elementInformation = array();
            $elementInformation['type'] = 'TextArea';
            TextAreaAsTextViewMetadataRules::resolveElementMetadata($elementInformation, $elementMetadata);
            $this->assertEquals('Text', $elementMetadata['type']);

            $elementMetadata = array();
            $elementInformation = array();
            $elementInformation['type'] = 'Phone';
            TextAreaAsTextViewMetadataRules::resolveElementMetadata($elementInformation, $elementMetadata);
            $this->assertTrue(!isset($elementMetadata['type']));
        }
    }
?>