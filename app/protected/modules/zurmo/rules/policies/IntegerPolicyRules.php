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

    /**
     * Policy Rules for displaying an integer input box
     * type policy in the user interface.
     */
    class IntegerPolicyRules extends PolicyRules
    {
        public function getElementAttributeType()
        {
            return 'PolicyIntegerAndStaticDropDown';
        }

        public function getEffectiveElementAttributeType()
        {
            return 'PolicyEffectiveInteger';
        }

        public function getFormRules()
        {
            $attributeName = FormModelUtil::getDerivedAttributeNameFromTwoStrings($this->moduleName, $this->policy);
            $rules = array(
                array($attributeName, 'type',    'type' => 'integer'),
                array($attributeName, 'length',  'max'  => 3),
                array($attributeName, 'validateIsRequiredByComparingHelper',
                    'compareAttributeName' => $attributeName . FormModelUtil::DELIMITER . 'helper'),
            );
            if (!empty($this->inherited))
            {
                if ($this->isMinimumValueTheInheritedValue())
                {
                    $rules[] = array($attributeName, 'numerical', 'max'  => $this->inherited);
                }
                else
                {
                    $rules[] = array($attributeName, 'numerical', 'min'  => $this->inherited);
                }
            }
            return $rules;
        }

        /**
         * Returns true if the smaller inherited value is the strong value.
         * @return boolean
         */
        protected function isMinimumValueTheInheritedValue()
        {
            return false;
        }
    }
?>