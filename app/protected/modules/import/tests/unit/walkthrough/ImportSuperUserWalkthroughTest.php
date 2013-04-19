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
     * Import Module Super User Walkthrough.
     * Walkthrough for the super user of all possible controller actions.
     * Since this is a super user, he should have access to all controller actions
     * without any exceptions being thrown.
     */
    class ImportSuperUserWalkthroughTest extends ImportWalkthroughBaseTest
    {
        public static $activateDefaultLanguages = true;

        public function testSuperUserMappingRulesEditActionAllAttributeIndexAndDerivedTypes()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            $import = new Import();
            $import->serializedData = serialize(array('importRulesType' => 'ImportModelTestItem'));
            $this->assertTrue($import->save());

            //Test all attributeIndex and Derived types to make sure all types of mapping rules load properly.
            $this->runMappingRulesEditAction($import->id, 'owner');
            $this->runMappingRulesEditAction($import->id, 'hasOne');
            $this->runMappingRulesEditAction($import->id, 'firstName');
            $this->runMappingRulesEditAction($import->id, 'lastName');
            $this->runMappingRulesEditAction($import->id, 'date');
            $this->runMappingRulesEditAction($import->id, 'dateTime');
            $this->runMappingRulesEditAction($import->id, 'float');
            $this->runMappingRulesEditAction($import->id, 'integer');
            $this->runMappingRulesEditAction($import->id, 'phone');
            $this->runMappingRulesEditAction($import->id, 'string');
            $this->runMappingRulesEditAction($import->id, 'textArea');
            $this->runMappingRulesEditAction($import->id, 'string');
            $this->runMappingRulesEditAction($import->id, 'url');

            $this->runMappingRulesEditAction($import->id, 'currencyValue');
            $this->runMappingRulesEditAction($import->id, 'dropDown');
            $this->runMappingRulesEditAction($import->id, 'radioDropDown');
            $this->runMappingRulesEditAction($import->id, 'hasOne');
            $this->runMappingRulesEditAction($import->id, 'primaryEmail__emailAddress');
            $this->runMappingRulesEditAction($import->id, 'primaryAddress__street1');
        }

        public function testSuperUserMappingRulesEditActionOnCustomCreatedTypes()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            $this->createDateCustomFieldByModule                ('AccountsModule', 'datetest');
            $this->createDateTimeCustomFieldByModule            ('AccountsModule', 'datetimetest');
            $this->createCheckBoxCustomFieldByModule            ('AccountsModule', 'checkboxtest');
            $this->createCurrencyValueCustomFieldByModule       ('AccountsModule', 'currencytest');
            $this->createDecimalCustomFieldByModule             ('AccountsModule', 'decimaltest');
            $this->createIntegerCustomFieldByModule             ('AccountsModule', 'integertest');
            $this->createPhoneCustomFieldByModule               ('AccountsModule', 'phonetest');
            $this->createTextCustomFieldByModule                ('AccountsModule', 'stringtest');
            $this->createTextAreaCustomFieldByModule            ('AccountsModule', 'textareatest');
            $this->createUrlCustomFieldByModule                 ('AccountsModule', 'urltest');
            $this->createDropDownCustomFieldByModule            ('AccountsModule', 'dropdowntest');
            $this->createRadioDropDownCustomFieldByModule       ('AccountsModule', 'radiotest');
            //Test all custom created types since their rules could vary
            $import = new Import();
            $import->serializedData = serialize(array('importRulesType' => 'Accounts'));
            $this->assertTrue($import->save());
            $this->runMappingRulesEditAction($import->id, 'datetestCstm');
            $this->runMappingRulesEditAction($import->id, 'datetimetestCstm');
            $this->runMappingRulesEditAction($import->id, 'checkboxtestCstm');
            $this->runMappingRulesEditAction($import->id, 'currencytestCstm');
            $this->runMappingRulesEditAction($import->id, 'decimaltestCstm');
            $this->runMappingRulesEditAction($import->id, 'integertestCstm');
            $this->runMappingRulesEditAction($import->id, 'phonetestCstm');
            $this->runMappingRulesEditAction($import->id, 'stringtestCstm');
            $this->runMappingRulesEditAction($import->id, 'textareatestCstm');
            $this->runMappingRulesEditAction($import->id, 'urltestCstm');
            $this->runMappingRulesEditAction($import->id, 'dropdowntestCstm');
            $this->runMappingRulesEditAction($import->id, 'radiotestCstm');
            //added the rest of the custom field types that are importable
        }
    }
?>