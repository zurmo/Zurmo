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

    class ImportWizardFormPostUtilTest extends ZurmoBaseTest
    {
        public function testSanitizePostByTypeForSavingMappingData()
        {
            $language = Yii::app()->getLanguage();
            $this->assertEquals($language, 'en');
            $postData = array(
                'column_0' => array('attributeIndexOrDerivedType' => 'date',
                                    'mappingRulesData' => array(
                                        'DefaultValueModelAttributeMappingRuleForm' =>
                                            array('defaultValue' => '5/4/11'))),
                'column_1' => array('attributeIndexOrDerivedType' => 'dateTime',
                                    'mappingRulesData' => array(
                                        'DefaultValueModelAttributeMappingRuleForm' =>
                                            array('defaultValue' => '5/4/11 5:45 PM'))),
            );
            $sanitizedPostData = ImportWizardFormPostUtil::
                                 sanitizePostByTypeForSavingMappingData('ImportModelTestItem', $postData);
            $compareDateTime   = DateTimeUtil::
                                 convertDateTimeLocaleFormattedDisplayToDbFormattedDateTimeWithSecondsAsZero('5/4/11 5:45 PM');
            $compareData = array(
                'column_0' => array('attributeIndexOrDerivedType' => 'date',
                                    'mappingRulesData' => array(
                                        'DefaultValueModelAttributeMappingRuleForm' =>
                                            array('defaultValue' => '2011-05-04'))),
                'column_1' => array('attributeIndexOrDerivedType' => 'dateTime',
                                    'mappingRulesData' => array(
                                        'DefaultValueModelAttributeMappingRuleForm' =>
                                            array('defaultValue' => $compareDateTime))),
            );
            $this->assertEquals($compareData, $sanitizedPostData);

            //now do German (de) to check a different locale.
            Yii::app()->setLanguage('de');
            $postData = array(
                'column_0' => array('attributeIndexOrDerivedType' => 'date',
                                    'mappingRulesData' => array(
                                        'DefaultValueModelAttributeMappingRuleForm' =>
                                            array('defaultValue' => '04.05.11'))),
                'column_1' => array('attributeIndexOrDerivedType' => 'dateTime',
                                    'mappingRulesData' => array(
                                        'DefaultValueModelAttributeMappingRuleForm' =>
                                            array('defaultValue' => '04.05.11 17:45'))),
            );
            $sanitizedPostData = ImportWizardFormPostUtil::
                                 sanitizePostByTypeForSavingMappingData('ImportModelTestItem', $postData);
            $compareDateTime   = DateTimeUtil::
                                 convertDateTimeLocaleFormattedDisplayToDbFormattedDateTimeWithSecondsAsZero('04.05.11 17:45');
            $compareData = array(
                'column_0' => array('attributeIndexOrDerivedType' => 'date',
                                    'mappingRulesData' => array(
                                        'DefaultValueModelAttributeMappingRuleForm' =>
                                            array('defaultValue' => '2011-05-04'))),
                'column_1' => array('attributeIndexOrDerivedType' => 'dateTime',
                                    'mappingRulesData' => array(
                                        'DefaultValueModelAttributeMappingRuleForm' =>
                                            array('defaultValue' => $compareDateTime))),
            );
            $this->assertEquals($compareData, $sanitizedPostData);

            //reset language back to english
            Yii::app()->setLanguage('en');

            //test sanitizing a bad datetime
            $postData = array(
                'column_0' => array('attributeIndexOrDerivedType' => 'dateTime',
                                    'mappingRulesData' => array(
                                        'DefaultValueModelAttributeMappingRuleForm' =>
                                            array('defaultValue' => 'wang chung'))),
            );
            $sanitizedPostData = ImportWizardFormPostUtil::
                                 sanitizePostByTypeForSavingMappingData('ImportModelTestItem', $postData);
            $this->assertNull($sanitizedPostData['column_0']['mappingRulesData']
                                                ['DefaultValueModelAttributeMappingRuleForm']['defaultValue']);
            //sanitize an empty datetime
            $postData = array(
                'column_0' => array('attributeIndexOrDerivedType' => 'dateTime',
                                    'mappingRulesData' => array(
                                        'DefaultValueModelAttributeMappingRuleForm' =>
                                            array('defaultValue' => ''))),
            );
            $sanitizedPostData = ImportWizardFormPostUtil::
                                 sanitizePostByTypeForSavingMappingData('ImportModelTestItem', $postData);
            $this->assertEmpty($sanitizedPostData['column_0']['mappingRulesData']
                                                ['DefaultValueModelAttributeMappingRuleForm']['defaultValue']);
        }
    }
?>
