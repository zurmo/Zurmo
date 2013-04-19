<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
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