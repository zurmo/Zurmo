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

    class DataUtilTest extends BaseTest
    {
        public function testPurifyHtml()
        {
            $text = '<b>This</b> is <a href="http://www.zurmo.com">valid text</a>';
            $purifiedText = DataUtil::purifyHtml($text);
            $this->assertEquals($text, $purifiedText);

            $text = "<IMG SRC=JaVaScRiPt:alert('XSS')>"; // Not Coding Standard
            $purifiedText = DataUtil::purifyHtml($text);
            $this->assertEquals('', $purifiedText);

            $text = "Valid text.<SCRIPT>alert('XSS')</SCRIPT>";
            $purifiedText = DataUtil::purifyHtml($text);
            $this->assertEquals('Valid text.', $purifiedText);

            $text = "<SCRIPT>alert('XSS')</SCRIPT>Valid text.";
            $purifiedText = DataUtil::purifyHtml($text);
            $this->assertEquals('Valid text.', $purifiedText);
        }

        /**
        * @depends testPurifyHtml
        */
        public function testPurifyHtmlAndModifyInput()
        {
            $text = '<b>This</b> is <a href="http://www.zurmo.com">valid text</a>';
            DataUtil::purifyHtmlAndModifyInput($text);
            $this->assertEquals('<b>This</b> is <a href="http://www.zurmo.com">valid text</a>', $text);

            $text = "<IMG SRC=JaVaScRiPt:alert('XSS')>"; // Not Coding Standard
            DataUtil::purifyHtmlAndModifyInput($text);
            $this->assertEquals('', $text);

            $text = "Valid text.<SCRIPT>alert('XSS')</SCRIPT>";
            DataUtil::purifyHtmlAndModifyInput($text);
            $this->assertEquals('Valid text.', $text);

            $text = "<SCRIPT>alert('XSS')</SCRIPT>Valid text.";
            DataUtil::purifyHtmlAndModifyInput($text);
            $this->assertEquals('Valid text.', $text);
        }

        /**
        * @depends testPurifyHtmlAndModifyInput
        */
        public function testPurifyHtmlAndModifyInputUsingArrayWalkRecursive()
        {
            $data = array(
                "Valid text.",
                "<SCRIPT>alert('XSS')</SCRIPT>Valid text 2.",
                "<SCRIPT>alert('XSS')</SCRIPT>",
                "<INPUT TYPE=\"IMAGE\" SRC=\"javascript:alert('XSS');\">",
                "Valid text 3.<INPUT TYPE=\"IMAGE\" SRC=\"javascript:alert('XSS');\">",
                "inner" => array(
                    "<SCRIPT>alert('XSS')</SCRIPT>Valid text 4.",
                    "<SCRIPT>alert('XSS')</SCRIPT>",
                ),
            );
            array_walk_recursive($data, array('DataUtil', 'purifyHtmlAndModifyInput'));
            $compareData = array(
                "Valid text.",
                "Valid text 2.",
                "",
                "",
                "Valid text 3.",
                "inner" => array(
                    "Valid text 4.",
                    "",
                )
            );
            $this->assertEquals($compareData, $data);
        }

        /**
        * @depends testPurifyHtmlAndModifyInputUsingArrayWalkRecursive
        */
        public function testSanitizeDataByDesignerTypeForSavingModel()
        {
            $data = array(
                'firstName' => 'Steve',
                'lastName' => 'Thunder<SCRIPT>alert(\'XSS\')</SCRIPT>',
                'boolean' => '0',
                'date' => '3/25/11',
                'dateTime' => '04/05/11 5:00 AM',
                'float' => '3.68',
                'integer' => '10',
                'phone' => '435655',
                'string' => 'some string<SCRIPT>alert(\'XSS\')</SCRIPT>',
                'textArea' => 'more text here<SCRIPT>alert(\'XSS\')</SCRIPT>',
                'url' => 'http://www.zurmo.org',
                'dropDown' => array('value' => 'test value<SCRIPT>alert(\'XSS\')</SCRIPT>'),
                'radioDropDown' => array('value' => 'my value'),
                'multiDropDown' => array('values' => array('multi1', 'multi2')),                      // Not Coding Standard
                'tagCloud' => array('values' => 'tag1,tag2<SCRIPT>alert(\'XSS\')</SCRIPT>') // Not Coding Standard
            );
            $model = new TestDataUtilModel;
            $sanitizedData = DataUtil::sanitizeDataByDesignerTypeForSavingModel($model, $data);
            $compareData = array(
                'firstName' => 'Steve',
                'lastName' => 'Thunder',
                'boolean' => '0',
                'date' => DateTimeUtil::resolveValueForDateDBFormatted('3/25/11'),
                'dateTime' => DateTimeUtil::convertDateTimeLocaleFormattedDisplayToDbFormattedDateTimeWithSecondsAsZero('04/05/11 5:00 AM'),
                'float' => '3.68',
                'integer' => '10',
                'phone' => '435655',
                'string' => 'some string',
                'textArea' => 'more text here',
                'url' => 'http://www.zurmo.org',
                'dropDown' => array('value' => 'test value'),
                'radioDropDown' => array('value' => 'my value'),
                'multiDropDown' => array('values' => array('multi1', 'multi2')),
                'tagCloud' => array('values' => array('tag1', 'tag2'))
            );
            $this->assertEquals($compareData, $sanitizedData);
        }

        public function testSanitizeDataToJustHavingElementForSavingModel()
        {
            $sanitizedData = array(
                'name'  => 'Global Inc.',
                'phone' => '3432432'
            );
            $elementName = 'phone';
            $data = DataUtil::sanitizeDataToJustHavingElementForSavingModel($sanitizedData, $elementName);
            $this->assertEquals(array($elementName => '3432432'), $data);

            $elementName = "annualRavenue";
            $data = DataUtil::sanitizeDataToJustHavingElementForSavingModel($sanitizedData, $elementName);
            $this->assertNull($data);
        }

        public function testRemoveElementFromDataForSavingModel()
        {
            $sanitizedData = array(
                'name'  => 'Global Inc.',
                'phone' => '3432432'
            );
            $elementName = "annualRavenue";
            $data = DataUtil::removeElementFromDataForSavingModel($sanitizedData, $elementName);
            $this->assertEquals($sanitizedData, $data);

            $elementName = 'phone';
            $data = DataUtil::removeElementFromDataForSavingModel($sanitizedData, $elementName);
            $this->assertEquals(array('name' => 'Global Inc.'), $data);
        }
    }
?>
