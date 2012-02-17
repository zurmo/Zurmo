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

    class PostUtilTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        public function testSanitizePostByDesignerTypeForSavingModel()
        {
            $language = Yii::app()->getLanguage();
            $this->assertEquals($language, 'en');
            $postData = array(
                'aDate' => '5/4/11',
                'aDateTime' => '5/4/11 5:45 PM'
            );
            $sanitizedPostData = PostUtil::sanitizePostByDesignerTypeForSavingModel(new DateDateTime(), $postData);
            $compareData = array(
                'aDate' => '2011-05-04',
                'aDateTime' => DateTimeUtil::convertDateTimeLocaleFormattedDisplayToDbFormattedDateTimeWithSecondsAsZero('5/4/11 5:45 PM'),
            );
            $this->assertEquals($compareData, $sanitizedPostData);
            $this->assertTrue(is_string($compareData['aDateTime']));

            //now do German (de) to check a different locale.
            Yii::app()->setLanguage('de');
            $postData = array(
                'aDate' => '04.05.11',
                'aDateTime' => '04.05.11 17:45'
            );
            $sanitizedPostData = PostUtil::sanitizePostByDesignerTypeForSavingModel(new DateDateTime(), $postData);
            $compareData = array(
                'aDate' => '2011-05-04',
                'aDateTime' => DateTimeUtil::convertDateTimeLocaleFormattedDisplayToDbFormattedDateTimeWithSecondsAsZero('04.05.11 17:45'),
            );
            $this->assertEquals($compareData, $sanitizedPostData);
            $this->assertTrue(is_string($compareData['aDateTime']));

            //reset language back to english
            Yii::app()->setLanguage('en');

            //test sanitizing a bad datetime
            $sanitizedPostData = PostUtil::sanitizePostByDesignerTypeForSavingModel(new DateDateTime(),
                                                                                    array('aDateTime' => 'wang chung'));
            $this->assertNull($sanitizedPostData['aDateTime']);
            //sanitize an empty datetime
            $sanitizedPostData = PostUtil::sanitizePostByDesignerTypeForSavingModel(new DateDateTime(),
                                                                                    array('aDateTime' => ''));
            $this->assertEmpty($sanitizedPostData['aDateTime']);
        }

        /**
         * @depends testSanitizePostByDesignerTypeForSavingModel
         */
        public function testSanitizePostByDesignerTypeForSavingModelForTagCloud()
        {
            $sanitizedPostData = PostUtil::sanitizePostByDesignerTypeForSavingModel(new TestCustomFieldsModel(),
                                                                                    array('tagCloud' =>
                                                                                    array('values' => 'abc,def'))); // Not Coding Standard
            $this->assertEquals(array('abc', 'def'), $sanitizedPostData['tagCloud']['values']);

            $sanitizedPostData = PostUtil::sanitizePostByDesignerTypeForSavingModel(new TestCustomFieldsModel(),
                                                                                    array('tagCloud' =>
                                                                                    array('values' => '')));
            $this->assertEquals(array(), $sanitizedPostData['tagCloud']['values']);

            $sanitizedPostData = PostUtil::sanitizePostByDesignerTypeForSavingModel(new TestCustomFieldsModel(),
                                                                                    array('tagCloud' =>
                                                                                    array('values' => array('gg', 'hh'))));
            $this->assertEquals(array('gg', 'hh'), $sanitizedPostData['tagCloud']['values']);
        }

        /**
         * @depends testSanitizePostByDesignerTypeForSavingModelForTagCloud
         */
        public function testSanitizeSearchFormAttributes()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $language                   = Yii::app()->getLanguage();
            $this->assertEquals($language, 'en');

            //test sanitizing a SearchForm date attribute and a SearchForm dateTime attribute
            $searchForm        = new ASearchFormTestModel(new MixedRelationsModel());
            $postData          = array( 'date__Date'  =>
                                    array('type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER,
                                          'firstDate' => '3/25/11'),
                                         'date2__Date'  =>
                                    array('type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_BETWEEN,
                                          'firstDate' =>  '5/25/11',
                                          'secondDate' => '6/25/11'),
                                'dateTime__DateTime'  =>
                                   array('type'       => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER,
                                          'firstDate' => '3/26/11'));
            $sanitizedPostData = PostUtil::sanitizePostByDesignerTypeForSavingModel($searchForm, $postData);
            $compareData = array( 'date__Date'  =>
                                    array('type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER,
                                          'firstDate' => '2011-03-25'),
                                  'date2__Date'  =>
                                    array('type'       => MixedDateTypesSearchFormAttributeMappingRules::TYPE_BETWEEN,
                                          'firstDate'  => '2011-05-25',
                                          'secondDate' => '2011-06-25'),
                                'dateTime__DateTime'  =>
                                   array('type'       => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER,
                                          'firstDate' => '2011-03-26'));
            $this->assertEquals($compareData, $sanitizedPostData);
        }

        public function testSanitizePostDataToJustHavingElementForSavingModel()
        {
            $data = array('a' => 'aaa', 'b' => 'bbb', 'c' => 'ccc');
            $newData = PostUtil::sanitizePostDataToJustHavingElementForSavingModel($data, 'nothere');
            $this->assertNull($newData);
            $newData = PostUtil::sanitizePostDataToJustHavingElementForSavingModel($data, 'b');
            $this->assertEquals(array('b' => 'bbb'), $newData);
        }

        public function testRemoveElementFromPostDataForSavingModel()
        {
            $data = array('a' => 'aaa', 'b' => 'bbb', 'c' => 'ccc');
            $newData = PostUtil::removeElementFromPostDataForSavingModel($data, 'doesntexist');
            $this->assertEquals($data, $newData);
            $newData = PostUtil::removeElementFromPostDataForSavingModel($data, 'b');
            $this->assertEquals(array('a' => 'aaa', 'c' => 'ccc'), $newData);
        }
    }
?>
