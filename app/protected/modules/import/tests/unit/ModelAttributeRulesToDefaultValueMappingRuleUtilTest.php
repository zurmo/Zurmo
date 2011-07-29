<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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

    class ModelAttributeRulesToDefaultValueMappingRuleUtilTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }
        public function testGetApplicableRulesByModelClassNameAndAttributeName()
        {
            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('ImportModelTestItem', 'boolean');
            $compareData = array(array('defaultValue',  'boolean'));
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('ImportModelTestItem', 'date');
            $compareData = array(array('defaultValue',  'type', 'type' => 'date'));
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('ImportModelTestItem', 'dateTime');
            $compareData = array(array('defaultValue',  'type', 'type' => 'datetime'));
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('ImportModelTestItem', 'float');
            $compareData = array(array('defaultValue',  'type', 'type' => 'float'));
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('ImportModelTestItem', 'integer');
            $compareData = array(array('defaultValue',  'type', 'type' => 'integer'));
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('ImportModelTestItem', 'phone');
            $compareData = array(array('defaultValue',  'type', 'type' => 'string'),
                                 array('defaultValue',  'length',  'min'  => 1, 'max' => 14));
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('ImportModelTestItem', 'string');
            $compareData = array(array('defaultValue',  'type', 'type' => 'string'),
                                 array('defaultValue',  'length',  'min'  => 3, 'max' => 64));
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('ImportModelTestItem', 'textArea');
            $compareData = array(array('defaultValue',  'type', 'type' => 'string'));
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('ImportModelTestItem', 'url');
            $compareData = array(array('defaultValue',  'url'));
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('ImportModelTestItem', 'firstName');
            $compareData = array(array('defaultValue',  'type', 'type' => 'string'),
                                 array('defaultValue',  'length',  'min'  => 1, 'max' => 32));
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('ImportModelTestItem', 'lastName');
            $compareData = array(array('defaultValue',  'type', 'type' => 'string'),
                                 array('defaultValue',  'length',  'min'  => 2, 'max' => 32));
            $this->assertEquals($compareData, $rules);

            //Now test lastName and string with required as applicable.
            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('ImportModelTestItem', 'string', true);
            $compareData = array(array('defaultValue',  'required'),
                                 array('defaultValue',  'type', 'type' => 'string'),
                                 array('defaultValue',  'length',  'min'  => 3, 'max' => 64));
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('ImportModelTestItem', 'lastName', true);
            $compareData = array(array('defaultValue',  'required'),
                                 array('defaultValue',  'type', 'type' => 'string'),
                                 array('defaultValue',  'length',  'min'  => 2, 'max' => 32));
            $this->assertEquals($compareData, $rules);

        }
    }
?>