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

    /**
     * Helper class to assist with security checks in the leads module specific controllers
     */
    class LeadsControllerSecurityUtil extends ControllerSecurityUtil
    {
        /**
         * There are several scenarios that can occur where a user has the right to convert, but is missing other
         * rights in order to properly utilize the convert mechanism.  This method checks for those conditions, and
         * if present, will alert the user that there is a misconfiguration and they should contact their administrator.
         * Scenario #1 - User does not have access to contacts
         * Scenario #2 - User cannot access accounts and an account is required for conversion
         */
        public static function resolveCanUserProperlyConvertLead($userCanAccessContacts, $userCanAccessAccounts,
                                                                 $convertToAccountSetting)
        {
            assert('is_bool($userCanAccessContacts)');
            assert('is_bool($userCanAccessAccounts)');
            assert('is_int($convertToAccountSetting)');
            $userCanConvertProperly = true;
            //Scenario #1 - User does not have access to contacts
            if (!$userCanAccessContacts)
            {
                $scenarioSpecificContent = // Not Coding Standard
                Yii::t('Default', 'Conversion requires access to the ContactsModulePluralLowerCaseLabel' .
                                  ' module which you do not have. Please contact your administrator.',
                       LabelUtil::getTranslationParamsForAllModules());
                $userCanConvertProperly  = false;
            }
            //Scenario #2 - User cannot access accounts and an account is required for conversion
            elseif ( !$userCanAccessAccounts && $convertToAccountSetting == LeadsModule::CONVERT_ACCOUNT_REQUIRED)
            {
                $scenarioSpecificContent = // Not Coding Standard
                Yii::t('Default', 'Conversion is set to require an AccountsModuleSingularLowerCaseLabel.  Currently' .
                                  ' you do not have access to the AccountsModulePluralLowerCaseLabel module.' .
                                  ' Please contact your administrator.',
                       LabelUtil::getTranslationParamsForAllModules());
                $userCanConvertProperly  = false;
            }
            if ($userCanConvertProperly)
            {
                return;
            }
            static::processAccessFailure(false, $scenarioSpecificContent);
            Yii::app()->end(0, false);
        }
    }
?>