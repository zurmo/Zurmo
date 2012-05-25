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
     * Export module walkthrough tests.
     */
    class ContactsRegularUserExportWalkthroughTest extends ZurmoRegularUserWalkthroughBaseTest
    {
        protected static $asynchronusTreshold;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();

            self::$asynchronusTreshold = ExportModule::$asynchronusTreshold;
            ExportModule::$asynchronusTreshold = 3;
            ReadPermissionsOptimizationUtil::rebuild();
        }

        public static function tearDownAfterClass()
        {
            ExportModule::$asynchronusTreshold = self::$asynchronusTreshold;
            parent::tearDownAfterClass();
        }

        /**
         * Walkthrough test for synchronous download
         */
        public function testDownloadDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);

            $contacts = array();
            for ($i = 0; $i < 2; $i++)
            {
                $contacts[] = ContactTestHelper::createContactWithAccountByNameForOwner('superContact' . $i, $super, $account);
            }

            // Check if access is denied if user doesn't have access privileges at all to export actions
            Yii::app()->user->userModel = User::getByUsername('nobody');
            $nobody = $this->logoutCurrentUserLoginNewUserAndGetByUsername('nobody');

            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/list');

            // Check if user have access to module action, but not to export action
            // Now test peon with elevated rights to accounts
            $nobody->setRight('ContactsModule', ContactsModule::RIGHT_ACCESS_CONTACTS);
            $nobody->setRight('ContactsModule', ContactsModule::RIGHT_CREATE_CONTACTS);
            $nobody->setRight('ContactsModule', ContactsModule::RIGHT_DELETE_CONTACTS);
            $nobody->setRight('ExportModule', ExportModule::RIGHT_ACCESS_EXPORT);
            $this->assertTrue($nobody->save());

            // Check if access is denied if user doesn't have access privileges at all to export actions
            $nobody = $this->logoutCurrentUserLoginNewUserAndGetByUsername('nobody');
            Yii::app()->user->userModel = User::getByUsername('nobody');

            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/list');

            $this->setGetArray(array(
                'Contact_page' => '1',
                'export' => '',
                'ajax' => '',
                'selectAll' => '',
                'selectedIds' => '')
            );
            $response = $this->runControllerWithRedirectExceptionAndGetUrl('contacts/default/export');
            $this->assertTrue(strstr($response, 'contacts/default/index') !== false);

            $this->setGetArray(array(
                'ContactsSearchForm' => array(
                    'anyMixedAttributesScope' => array(0 => 'All'),
                    'anyMixedAttributes'      => '',
                    'fullName'                => 'superContact',
                    'officePhone'             => ''
                ),
                'multiselect_ContactsSearchForm_anyMixedAttributesScope' => 'All',
                'Contact_page'   => '1',
                'export'         => '',
                'ajax'           => '',
                'selectAll' => '1',
                'selectedIds' => '')
            );
            $response = $this->runControllerWithRedirectExceptionAndGetUrl('contacts/default/export');
            $this->assertTrue(strstr($response, 'contacts/default/index') !== false);

            $this->setGetArray(array(
                'ContactsSearchForm' => array(
                    'anyMixedAttributesScope' => array(0 => 'All'),
                    'anyMixedAttributes'      => '',
                    'fullName'                => 'superContact',
                    'officePhone'             => ''
                ),
                'multiselect_ContactsSearchForm_anyMixedAttributesScope' => 'All',
                'Contact_page'   => '1',
                'export'         => '',
                'ajax'           => '',
                'selectAll' => '',
                'selectedIds' => "{$contacts[0]->id}, {$contacts[1]->id}")
            );
            $response = $this->runControllerWithRedirectExceptionAndGetUrl('contacts/default/export');
            $this->assertTrue(strstr($response, 'contacts/default/index') !== false);
            $this->assertContains('There is no data to export.',
                Yii::app()->user->getFlash('notification'));

            //give nobody access to read and write
            Yii::app()->user->userModel = $super;
            foreach ($contacts as $contact)
            {
                $contact->addPermissions($nobody, Permission::READ_WRITE_CHANGE_PERMISSIONS);
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForUser($contact, $nobody);
                $this->assertTrue($contact->save());
            }
            //Now the nobody user should be able to access the edit view and still the details view.
            Yii::app()->user->userModel = $nobody;

            $this->setGetArray(array(
                'ContactsSearchForm' => array(
                    'anyMixedAttributesScope' => array(0 => 'All'),
                    'anyMixedAttributes'      => '',
                    'fullName'                => 'superContact',
                    'officePhone'             => ''
                ),
                'multiselect_ContactsSearchForm_anyMixedAttributesScope' => 'All',
                'Contact_page'   => '1',
                'export'         => '',
                'ajax'           => '',
                'selectAll' => '1',
                'selectedIds' => '')
            );
            $response = $this->runControllerWithExitExceptionAndGetContent('contacts/default/export');
            $this->assertEquals('Testing download.', $response);

            $this->setGetArray(array(
                'ContactsSearchForm' => array(
                    'anyMixedAttributesScope' => array(0 => 'All'),
                    'anyMixedAttributes'      => '',
                    'fullName'                => 'superContact',
                    'officePhone'             => ''
                ),
                'multiselect_ContactsSearchForm_anyMixedAttributesScope' => 'All',
                'Contact_page'   => '1',
                'export'         => '',
                'ajax'           => '',
                'selectAll' => '',
                'selectedIds' => "{$contacts[0]->id}, {$contacts[1]->id}")
            );
            $response = $this->runControllerWithExitExceptionAndGetContent('contacts/default/export');
            $this->assertEquals('Testing download.', $response);

            // No mathces
            $this->setGetArray(array(
                'ContactsSearchForm' => array(
                    'anyMixedAttributesScope' => array(0 => 'All'),
                    'anyMixedAttributes'      => '',
                    'fullName'                => 'missingName',
                    'officePhone'             => ''
                ),
                'multiselect_ContactsSearchForm_anyMixedAttributesScope' => 'All',
                'Contact_page' => '1',
                'export'       => '',
                'ajax'         => '',
                'selectAll' => '1',
                'selectedIds' => '')
            );
            $response = $this->runControllerWithRedirectExceptionAndGetUrl('contacts/default/export');
            $this->assertTrue(strstr($response, 'contacts/default/index') !== false);
        }
    }
?>