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
     * Walkthrough for the super user of dynamic search actions
     */
    class ZurmoDynamicSearchSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static $activateDefaultLanguages = true;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            $this->setGetArray(array(   'viewClassName'      => 'AccountsSearchView',
                                        'modelClassName'     => 'Account',
                                        'formModelClassName' => 'AccountsSearchForm',
                                        'rowNumber'          => 5));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/dynamicSearchAddExtraRow');
            $this->assertNotNull($content);

            //Test not passing validation post var
            $this->setGetArray(array(   'viewClassName'               => 'AccountsSearchView',
                                        'modelClassName'              => 'Account',
                                        'formModelClassName'          => 'AccountsSearchForm'));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/validateDynamicSearch', true);
            $this->assertEmpty($content);

            //Test form that validates
            $this->setGetArray(array(   'viewClassName'               => 'AccountsSearchView',
                                        'modelClassName'              => 'Account',
                                        'formModelClassName'          => 'AccountsSearchForm'));
            $this->setPostArray(array('ajax'                          => 'search-form',
                                      'AccountsSearchForm'            => array()));
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/validateDynamicSearch', true);
            $this->assertEmpty($content);

            //Test a form that does not validate because it is missing a field selection
            $this->setGetArray(array(   'viewClassName'               => 'AccountsSearchView',
                                        'modelClassName'              => 'Account',
                                        'formModelClassName'          => 'AccountsSearchForm'));
            $this->setPostArray(array('ajax'               => 'search-form',
                                        'AccountsSearchForm' => array(
                                            'dynamicStructure' => '1',
                                            'dynamicClauses'   => array(
                                                array('structurePosition'           => '1',
                                                      'attributeIndexOrDerivedType' => '')))));
            $content = $this->runControllerWithExitExceptionAndGetContent('zurmo/default/validateDynamicSearch');
            $this->assertEquals('{"AccountsSearchForm_dynamicClauses":["You must select a field for row 1"]}', $content);

            //Test a form that does not validate because it is missing a field selection
            $this->setGetArray(array(   'viewClassName'               => 'AccountsSearchView',
                                        'modelClassName'              => 'Account',
                                        'formModelClassName'          => 'AccountsSearchForm'));
            $this->setPostArray(array('ajax'               => 'search-form',
                                        'AccountsSearchForm' => array(
                                            'dynamicStructure' => '1',
                                            'dynamicClauses'   => array(
                                                array('structurePosition'           => '1',
                                                      'attributeIndexOrDerivedType' => 'name',
                                                      'name' => '')))));
            $content = $this->runControllerWithExitExceptionAndGetContent('zurmo/default/validateDynamicSearch');
            $this->assertEquals('{"AccountsSearchForm_dynamicClauses":["You must select a value for row 1"]}', $content);

            //Test a form that validates a dynamic clause
            $this->setGetArray(array(   'viewClassName'               => 'AccountsSearchView',
                                        'modelClassName'              => 'Account',
                                        'formModelClassName'          => 'AccountsSearchForm'));
            $this->setPostArray(array('ajax'               => 'search-form',
                                        'AccountsSearchForm' => array(
                                            'dynamicStructure' => '1',
                                            'dynamicClauses'   => array(
                                                array('structurePosition'           => '1',
                                                      'attributeIndexOrDerivedType' => 'name',
                                                      'name' => 'someValue')))));
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/validateDynamicSearch', true);
            $this->assertEmpty($content);

            //Test a form that does not validate recursive dynamic clause attributes
            $this->setGetArray(array(   'viewClassName'               => 'AccountsSearchView',
                                        'modelClassName'              => 'Account',
                                        'formModelClassName'          => 'AccountsSearchForm'));
            $this->setPostArray(array('ajax'               => 'search-form',
                                        'AccountsSearchForm' => array(
                                            'dynamicStructure' => '1',
                                            'dynamicClauses'   => array(
                                                array('structurePosition'           => '1',
                                                      'attributeIndexOrDerivedType' => 'name',
                                                      'contacts' => array(
                                                        'relatedData'   => true,
                                                        'firstName'     => '',
                                                      ))))));
            $content = $this->runControllerWithExitExceptionAndGetContent('zurmo/default/validateDynamicSearch');
            $this->assertEquals('{"AccountsSearchForm_dynamicClauses":["You must select a value for row 1"]}', $content);

            //Test a form that does validate recursive dynamic clause attributes
            $this->setGetArray(array(   'viewClassName'               => 'AccountsSearchView',
                                        'modelClassName'              => 'Account',
                                        'formModelClassName'          => 'AccountsSearchForm'));
            $this->setPostArray(array('ajax'               => 'search-form',
                                        'AccountsSearchForm' => array(
                                            'dynamicStructure' => '1',
                                            'dynamicClauses'   => array(
                                                array('structurePosition'           => '1',
                                                      'attributeIndexOrDerivedType' => 'name',
                                                      'contacts' => array(
                                                        'relatedData'   => true,
                                                        'firstName'     => 'Jason',
                                                      ))))));
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/validateDynamicSearch', true);
            $this->assertEmpty($content);
        }

        public function testDynamicSearchAttributeInputTypes()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            //Test null attribute
            $this->setGetArray(array(   'viewClassName'               => 'AccountsSearchView',
                                        'modelClassName'              => 'Account',
                                        'formModelClassName'          => 'AccountsSearchForm',
                                        'rowNumber'                   => 5,
                                        'attributeIndexOrDerivedType' => ''));
            $this->resetPostArray();
            $this->runControllerWithExitExceptionAndGetContent('zurmo/default/dynamicSearchAttributeInput');

            //Test Account attribute
            $this->setGetArray(array(   'viewClassName'               => 'AccountsSearchView',
                                        'modelClassName'              => 'Account',
                                        'formModelClassName'          => 'AccountsSearchForm',
                                        'rowNumber'                   => 5,
                                        'attributeIndexOrDerivedType' => 'name'));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/dynamicSearchAttributeInput');
            $this->assertNotNull($content);

            //Test AccountsSearchForm attribute
            $this->setGetArray(array(   'viewClassName'               => 'AccountsSearchView',
                                        'modelClassName'              => 'Account',
                                        'formModelClassName'          => 'AccountsSearchForm',
                                        'rowNumber'                   => 5,
                                        'attributeIndexOrDerivedType' => 'anyCountry'));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/dynamicSearchAttributeInput');
            $this->assertNotNull($content);
        }

        public function testDynamicSearchAttributeInputCheckBox()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->createCheckBoxCustomFieldByModule('AccountsModule', 'checkbox');
            $content = $this->insertSearchAttributeAndGetContent('checkboxCstm');
            $this->assertNotNull($content);
        }

        public function testDynamicSearchAttributeInputCurrency()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->createCurrencyValueCustomFieldByModule('AccountsModule', 'currency');
            $content = $this->insertSearchAttributeAndGetContent('currencyCstm');
            $this->assertNotNull($content);
        }

        public function testDynamicSearchAttributeInputDate()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->createDateCustomFieldByModule('AccountsModule', 'date');
            $content = $this->insertSearchAttributeAndGetContent('dateCstm__Date');
            $this->assertNotNull($content);
        }

        public function testDynamicSearchAttributeInputDateTime()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->createDateTimeCustomFieldByModule('AccountsModule', 'datetime');
            $content = $this->insertSearchAttributeAndGetContent('datetimeCstm__DateTime');
            $this->assertNotNull($content);
        }

        public function testDynamicSearchAttributeInputDecimal()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->createDecimalCustomFieldByModule('AccountsModule', 'decimal');
            $content = $this->insertSearchAttributeAndGetContent('decimalCstm');
            $this->assertNotNull($content);
        }

        public function testDynamicSearchAttributeInputPicklist()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->createDropDownCustomFieldByModule('AccountsModule', 'picklist');
            $content = $this->insertSearchAttributeAndGetContent('picklistCstm');
            $this->assertNotNull($content);
        }

        public function testDynamicSearchAttributeInputCountrylist()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->createDependentDropDownCustomFieldByModule('AccountsModule', 'countrylist');
            $content = $this->insertSearchAttributeAndGetContent('countrylistCstm');
            $this->assertNotNull($content);
        }

        public function testDynamicSearchAttributeInputMultiselect()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->createMultiSelectDropDownCustomFieldByModule('AccountsModule', 'multiselect');
            $content = $this->insertSearchAttributeAndGetContent('multiselectCstm');
            $this->assertNotNull($content);
        }

        public function testDynamicSearchAttributeInputTagcloud()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->createTagCloudCustomFieldByModule('AccountsModule', 'tagcloud');
            $content = $this->insertSearchAttributeAndGetContent('tagcloudCstm');
            $this->assertNotNull($content);
        }

        public function testDynamicSearchAttributeInputCalculatednumber()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->createCalculatedNumberCustomFieldByModule('AccountsModule', 'calcnumber');
            $this->setGetArray(array(   'viewClassName'               => 'AccountsSearchView',
                                        'modelClassName'              => 'Account',
                                        'formModelClassName'          => 'AccountsSearchForm',
                                        'rowNumber'                   => 5,
                                        'attributeIndexOrDerivedType' => 'calcnumber'));
            $this->resetPostArray();
            $content = $this->runControllerWithNotSupportedExceptionAndGetContent('zurmo/default/dynamicSearchAttributeInput');
            $this->assertNotNull($content);
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testDynamicSearchAttributeInputDropdowndependency()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->createDropDownDependencyCustomFieldByModule('AccountsModule', 'dropdowndep');
            $this->setGetArray(array(   'viewClassName'               => 'AccountsSearchView',
                                        'modelClassName'              => 'Account',
                                        'formModelClassName'          => 'AccountsSearchForm',
                                        'rowNumber'                   => 5,
                                        'attributeIndexOrDerivedType' => 'dropdowndep'));
            $this->resetPostArray();
            $content = $this->runControllerWithNotSupportedExceptionAndGetContent('zurmo/default/dynamicSearchAttributeInput');
            $this->assertNotNull($content);
        }

        public function testDynamicSearchAttributeInputInteger()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->createIntegerCustomFieldByModule('AccountsModule', 'integer');
            $content = $this->insertSearchAttributeAndGetContent('integerCstm');
            $this->assertNotNull($content);
        }

        public function testDynamicSearchAttributeInputPhone()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->createPhoneCustomFieldByModule('AccountsModule', 'phone');
            $content = $this->insertSearchAttributeAndGetContent('phoneCstm');
            $this->assertNotNull($content);
        }

        public function testDynamicSearchAttributeInputRadio()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->createRadioDropDownCustomFieldByModule('AccountsModule', 'radio');
            $content = $this->insertSearchAttributeAndGetContent('radioCstm');
            $this->assertNotNull($content);
        }

        public function testDynamicSearchAttributeInputText()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->createTextCustomFieldByModule('AccountsModule', 'text');
            $content = $this->insertSearchAttributeAndGetContent('textCstm');
            $this->assertNotNull($content);
        }

        public function testDynamicSearchAttributeInputTextarea()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->createTextAreaCustomFieldByModule('AccountsModule', 'textarea');
            $this->setGetArray(array(   'viewClassName'               => 'AccountsSearchView',
                                        'modelClassName'              => 'Account',
                                        'formModelClassName'          => 'AccountsSearchForm',
                                        'rowNumber'                   => 5,
                                        'attributeIndexOrDerivedType' => 'textarea'));
            $this->resetPostArray();
            $content = $this->runControllerWithNotSupportedExceptionAndGetContent('zurmo/default/dynamicSearchAttributeInput');
            $this->assertNotNull($content);
        }

        public function testDynamicSearchAttributeInputUrl()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->createUrlCustomFieldByModule('AccountsModule', 'url');
            $content = $this->insertSearchAttributeAndGetContent('urlCstm');
            $this->assertNotNull($content);
        }

        /**
         * Auxiliar function to return content when a new search attribute is inserted
         * @param   string    name of attibute to insert
         * @return  string    content to be inserted in the page that make the inserted attribute searcheble
         */
        private function insertSearchAttributeAndGetContent($name)
        {
            $this->setGetArray(array(   'viewClassName'               => 'AccountsSearchView',
                                        'modelClassName'              => 'Account',
                                        'formModelClassName'          => 'AccountsSearchForm',
                                        'rowNumber'                   => 5,
                                        'attributeIndexOrDerivedType' => $name));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/dynamicSearchAttributeInput');
            return $content;
        }
    }
?>