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

    class UserPolicyRulesTest extends ZurmoBaseTest
    {
        public function testPasswordExpiresPolicyRules()
        {
            $rules = new PasswordExpiresPolicyRules('UsersModule', 'POLICY_PASSWORD_EXPIRES', Policy::NONE, Policy::NONE);
            $this->assertFalse($rules->showInView());
            $this->assertEquals(null, $rules->getElementAttributeType());
            $this->assertEquals(null, $rules->getEffectiveElementAttributeType());
            $this->assertFalse($rules->isElementTypeDerived());
        }

        public function testPasswordExpiryPolicyRules()
        {
            $rules = new PasswordExpiryPolicyRules('UsersModule', 'POLICY_PASSWORD_EXPIRY_DAYS', Policy::NONE, Policy::NONE);
            $this->assertTrue($rules->showInView());
            $this->assertEquals('PolicyPasswordExpiry', $rules->getElementAttributeType());
            $this->assertEquals('PolicyEffectivePasswordExpiry', $rules->getEffectiveElementAttributeType());
            $this->assertTrue($rules->isElementTypeDerived());
            $compareValidationRules = array(
                array('UsersModule__POLICY_PASSWORD_EXPIRES', 'type', 'type' => 'string'),
                array('UsersModule__POLICY_PASSWORD_EXPIRY_DAYS', 'type', 'type' => 'integer'),
                array('UsersModule__POLICY_PASSWORD_EXPIRY_DAYS', 'length',  'max'  => 3),
                array('UsersModule__POLICY_PASSWORD_EXPIRY_DAYS', 'validateIsRequiredByComparingHelper',
                    'compareAttributeName' => 'UsersModule__POLICY_PASSWORD_EXPIRES'),
            );
            $validationRules = $rules->getFormRules();
            $this->assertEquals($compareValidationRules, $validationRules);
            $rules = new PasswordExpiryPolicyRules('UsersModule', 'POLICY_PASSWORD_EXPIRY_DAYS', Policy::NONE, 10);
            $this->assertTrue($rules->showInView());
            $this->assertEquals('PolicyPasswordExpiry', $rules->getElementAttributeType());
            $this->assertEquals('PolicyEffectivePasswordExpiry', $rules->getEffectiveElementAttributeType());
            $this->assertTrue($rules->isElementTypeDerived());
            $compareValidationRules = array(
                array('UsersModule__POLICY_PASSWORD_EXPIRES', 'type', 'type' => 'string'),
                array('UsersModule__POLICY_PASSWORD_EXPIRY_DAYS', 'type', 'type' => 'integer'),
                array('UsersModule__POLICY_PASSWORD_EXPIRY_DAYS', 'length',  'max'  => 3),
                array('UsersModule__POLICY_PASSWORD_EXPIRY_DAYS', 'validateIsRequiredByComparingHelper',
                    'compareAttributeName' => 'UsersModule__POLICY_PASSWORD_EXPIRES'),
                array('UsersModule__POLICY_PASSWORD_EXPIRY_DAYS', 'numerical', 'max'  => 10),
            );
            $validationRules = $rules->getFormRules();
            $this->assertEquals($compareValidationRules, $validationRules);
        }
    }
?>