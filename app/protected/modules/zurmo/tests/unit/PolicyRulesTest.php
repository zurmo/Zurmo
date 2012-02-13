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

    class PolicyRulesTest extends BaseTest
    {
        public function testYesNoPolicyRules()
        {
            $rules = new YesNoPolicyRules('SomeModule', 'B_POLICY', Policy::NONE, Policy::NONE);
            $this->assertTrue($rules->showInView());
            $this->assertEquals('PolicyStaticDropDown', $rules->getElementAttributeType());
            $this->assertEquals('PolicyEffectiveYesNo', $rules->getEffectiveElementAttributeType());
            $this->assertFalse($rules->isElementTypeDerived());
            $compareValidationRules = array(
                array('SomeModule__B_POLICY', 'type', 'type' => 'string'),
            );
            $validationRules = $rules->getFormRules();
            $this->assertEquals($compareValidationRules, $validationRules);
            $rules = new YesNoPolicyRules('SomeModule', 'B_POLICY', Policy::NONE, Policy::YES);
            $this->assertEquals('PolicyInheritedYesNoText', $rules->getElementAttributeType());
            $this->assertEquals('PolicyEffectiveYesNo', $rules->getEffectiveElementAttributeType());
            $rules = new YesNoPolicyRules('SomeModule', 'B_POLICY', Policy::NONE, Policy::NO);
            $this->assertEquals('PolicyStaticDropDown', $rules->getElementAttributeType());
            $this->assertEquals('PolicyEffectiveYesNo', $rules->getEffectiveElementAttributeType());
        }

        /**
         * @depends testYesNoPolicyRules
         */
        public function testIntegerPolicyRules()
        {
            $rules = new IntegerPolicyRules('SomeModule', 'B_POLICY', Policy::NONE, Policy::NONE);
            $this->assertTrue($rules->showInView());
            $this->assertEquals('PolicyIntegerAndStaticDropDown', $rules->getElementAttributeType());
            $this->assertEquals('PolicyEffectiveInteger', $rules->getEffectiveElementAttributeType());
            $this->assertFalse($rules->isElementTypeDerived());
            $compareValidationRules = array(
                array('SomeModule__B_POLICY', 'type', 'type' => 'integer'),
                array('SomeModule__B_POLICY',   'length',  'max'  => 3),
                array('SomeModule__B_POLICY', 'validateIsRequiredByComparingHelper',
                    'compareAttributeName' => 'SomeModule__B_POLICY__helper'),
            );
            $validationRules = $rules->getFormRules();
            $this->assertEquals($compareValidationRules, $validationRules);
            $rules = new IntegerPolicyRules('SomeModule', 'B_POLICY', Policy::NONE, 4);
            $this->assertTrue($rules->showInView());
            $this->assertEquals('PolicyIntegerAndStaticDropDown', $rules->getElementAttributeType());
            $this->assertEquals('PolicyEffectiveInteger', $rules->getEffectiveElementAttributeType());
            $this->assertFalse($rules->isElementTypeDerived());
            $compareValidationRules = array(
                array('SomeModule__B_POLICY', 'type', 'type' => 'integer'),
                array('SomeModule__B_POLICY', 'length',  'max'  => 3),
                array('SomeModule__B_POLICY', 'validateIsRequiredByComparingHelper',
                    'compareAttributeName' => 'SomeModule__B_POLICY__helper'),
                array('SomeModule__B_POLICY', 'numerical', 'min'  => 4),
            );
            $validationRules = $rules->getFormRules();
            $this->assertEquals($compareValidationRules, $validationRules);
            $rules = new IntegerPolicyRules('SomeModule', 'B_POLICY', 2, Policy::NONE);
            $this->assertTrue($rules->showInView());
            $this->assertEquals('PolicyIntegerAndStaticDropDown', $rules->getElementAttributeType());
            $this->assertEquals('PolicyEffectiveInteger', $rules->getEffectiveElementAttributeType());
            $this->assertFalse($rules->isElementTypeDerived());
            $compareValidationRules = array(
                array('SomeModule__B_POLICY', 'type', 'type' => 'integer'),
                array('SomeModule__B_POLICY', 'length',  'max'  => 3),
                array('SomeModule__B_POLICY', 'validateIsRequiredByComparingHelper',
                    'compareAttributeName' => 'SomeModule__B_POLICY__helper'),
            );
            $validationRules = $rules->getFormRules();
            $this->assertEquals($compareValidationRules, $validationRules);
        }
    }
?>