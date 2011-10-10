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

    /**
     * Walkthrough test for when required attributes are not placed and should be before a view can be accessed.
     */
    class RequiredAttributesViewValidityWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            ContactsModule::loadStartingData();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        public function testRequiredAttributesAreMissingFromLayout()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $account = AccountTestHelper::createAccountByNameForOwner('aTestAccount', $super);
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default/create');
            $this->assertTrue(strpos($content, 'There are required fields missing from the following layout') === false);
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default/list');
            $this->assertTrue(strpos($content, 'There are required fields missing from the following layout') === false);
            $this->setGetArray (array('id' => $account->id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default/edit');
            $this->assertTrue(strpos($content, 'There are required fields missing from the following layout') === false);

            //Now create an attribute that is required.
            $this->createTextCustomFieldByModule('AccountsModule', 'text');

            $content = $this->runControllerWithExitExceptionAndGetContent('accounts/default/create');
            $this->assertFalse(strpos($content, 'There are required fields missing from the following layout') === false);
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default/list');
            $this->assertTrue(strpos($content, 'There are required fields missing from the following layout') === false);
            $this->setGetArray (array('id' => $account->id));
            $content = $this->runControllerWithExitExceptionAndGetContent('accounts/default/edit');
            $this->assertFalse(strpos($content, 'There are required fields missing from the following layout') === false);
        }

        public function testRequiredContactAttributesProperlyAreRequiredToBePlacedInLeadLayouts()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $contact = ContactTestHelper::createContactByNameForOwner('aTestContact', $super);
            $lead    = LeadTestHelper::createLeadByNameForOwner('aTestLead', $super);
            $content = $this->runControllerWithNoExceptionsAndGetContent('contacts/default/create');
            $this->assertTrue(strpos($content, 'There are required fields missing from the following layout') === false);
            $content = $this->runControllerWithNoExceptionsAndGetContent('contacts/default/list');
            $this->assertTrue(strpos($content, 'There are required fields missing from the following layout') === false);
            $this->setGetArray (array('id' => $contact->id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('contacts/default/edit');
            $this->assertTrue(strpos($content, 'There are required fields missing from the following layout') === false);
            //Now check lead layouts.
            $content = $this->runControllerWithNoExceptionsAndGetContent('leads/default/create');
            $this->assertTrue(strpos($content, 'There are required fields missing from the following layout') === false);
            $content = $this->runControllerWithNoExceptionsAndGetContent('leads/default/list');
            $this->assertTrue(strpos($content, 'There are required fields missing from the following layout') === false);
            $this->setGetArray (array('id' => $lead->id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('leads/default/edit');
            $this->assertTrue(strpos($content, 'There are required fields missing from the following layout') === false);

            //Now create an attribute that is required.
            $this->createTextCustomFieldByModule('ContactsModule', 'text');

            $content = $this->runControllerWithExitExceptionAndGetContent('contacts/default/create');
            $this->assertFalse(strpos($content, 'There are required fields missing from the following layout') === false);
            $content = $this->runControllerWithNoExceptionsAndGetContent('contacts/default/list');
            $this->assertTrue(strpos($content, 'There are required fields missing from the following layout') === false);
            $this->setGetArray (array('id' => $contact->id));
            $content = $this->runControllerWithExitExceptionAndGetContent('contacts/default/edit');
            $this->assertFalse(strpos($content, 'There are required fields missing from the following layout') === false);
            //Now check lead layouts. They should follow the same pattern as contacts.
            $content = $this->runControllerWithExitExceptionAndGetContent('leads/default/create');
            $this->assertFalse(strpos($content, 'There are required fields missing from the following layout') === false);
            $content = $this->runControllerWithNoExceptionsAndGetContent('leads/default/list');
            $this->assertTrue(strpos($content, 'There are required fields missing from the following layout') === false);
            $this->setGetArray (array('id' => $lead->id));
            $content = $this->runControllerWithExitExceptionAndGetContent('leads/default/edit');
            $this->assertFalse(strpos($content, 'There are required fields missing from the following layout') === false);
        }

        //todo: test switching existing attribute to required.
        //todo: test switching existing attribute to not required from required.
        //todo: test note inlineEditSave
        //todo: test out multiple custom fields not placed, make sure array of config for RequiredAttributesValidViewUtil is working ok.
    }
?>