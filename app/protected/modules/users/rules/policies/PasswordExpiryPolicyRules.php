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

    class PasswordExpiryPolicyRules extends PolicyRules
    {
        public function __construct($moduleName, $policy, $explicit, $inherited)
        {
            assert('$moduleName == "UsersModule"');
            assert('$policy == "POLICY_PASSWORD_EXPIRY_DAYS"');
            parent::__construct($moduleName, $policy, $explicit, $inherited);
        }

        public function getElementAttributeType()
        {
            return 'PolicyPasswordExpiry';
        }

        public function getEffectiveElementAttributeType()
        {
            return 'PolicyEffectivePasswordExpiry';
        }

        /**
         * This policy rule ontroles PasswordExpiry
         * and PasswordExpires.
         */
        public function isElementTypeDerived()
        {
            return true;
        }

        /**
         * PasswordExpiry handles both
         * POLICY_PASSWORD_EXPIRES and POLICY_PASSWORD_EXPIRY_DAYS
         */
        public function getFormRules()
        {
            $attributeName1 = FormModelUtil::getDerivedAttributeNameFromTwoStrings($this->moduleName, 'POLICY_PASSWORD_EXPIRES');
            $attributeName2 = FormModelUtil::getDerivedAttributeNameFromTwoStrings($this->moduleName, $this->policy);
            $rules = array(
                array($attributeName1, 'type', 'type' => 'string'),
                array($attributeName2, 'type',    'type' => 'integer'),
                array($attributeName2, 'length',  'max'  => 3),
                array($attributeName2, 'validateIsRequiredByComparingHelper',
                    'compareAttributeName' => 'UsersModule__POLICY_PASSWORD_EXPIRES'),
            );
            if (!empty($this->inherited))
            {
                $rules[] = array($attributeName2, 'numerical', 'max'  => $this->inherited);
            }
            return $rules;
        }
    }
?>