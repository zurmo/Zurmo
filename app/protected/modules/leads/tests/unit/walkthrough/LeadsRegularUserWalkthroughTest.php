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
     * Leads Module Walkthrough.
     * Walkthrough for a peon user.  The peon user at first will have no granted
     * rights or permissions.  Most attempted actions will result in an ExitException
     * and a access failure view.  After this, we elevate the user with added tab rights
     * so that some of the actions will result in success and no exceptions being thrown.
     * There will still be some actions they cannot get too though because of the lack of
     * elevated permissions.  Then we will elevate permissions to allow the user to access
     * other owner's records.
     */
    class LeadsRegularUserWalkthroughTest extends ZurmoRegularUserWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = Yii::app()->user->userModel;
            //Setup test data owned by the super user.
            LeadTestHelper::createLeadbyNameForOwner                 ('superLead',  $super);
            LeadTestHelper::createLeadbyNameForOwner                 ('superLead2', $super);
            LeadTestHelper::createLeadbyNameForOwner                 ('superLead3', $super);
            LeadTestHelper::createLeadbyNameForOwner                 ('superLead4', $super);
            //Setup default dashboard.
            Dashboard::getByLayoutIdAndUser                          (Dashboard::DEFAULT_USER_LAYOUT_ID, $super);
        }

        public function testRegularUserAllControllerActions()
        {
            //Now test all portlet controller actions

            //Now test peon with elevated rights to tabs /other available rights
            //such as convert lead

            //Now test peon with elevated permissions to models.
        }

        //todo: test lead conversion.

        public function testUserHasNoAccessToAccountsAndTriesToConvertWhenAccountIsOptional()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $belina = UserTestHelper::createBasicUser('belina');
            $lead = LeadTestHelper::createLeadbyNameForOwner('BelinaLead1', $belina);
            $belina->setRight   ('LeadsModule', LeadsModule::RIGHT_CONVERT_LEADS, Right::ALLOW);
            $belina->setRight   ('LeadsModule', LeadsModule::RIGHT_ACCESS_LEADS, Right::ALLOW);
            $belina->setRight   ('ContactsModule', ContactsModule::RIGHT_CREATE_CONTACTS, Right::ALLOW);
            $belina->setRight   ('ContactsModule', ContactsModule::RIGHT_ACCESS_CONTACTS, Right::ALLOW);
            $this->assertTrue($belina->save());
            $this->assertEquals(Right::DENY, $belina->getEffectiveRight('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS));
            $belina = $this->logoutCurrentUserLoginNewUserAndGetByUsername('belina');

            //Now check that when belina tries to convert a lead, it will automatically make it an account.
            $convertToAccountSetting = LeadsModule::getConvertToAccountSetting();
            $this->assertTrue($convertToAccountSetting == LeadsModule::CONVERT_NO_ACCOUNT ||
                              $convertToAccountSetting == LeadsModule::CONVERT_ACCOUNT_NOT_REQUIRED);

            $oldStateValue = $lead->state->name;
            $this->setGetArray (array('id' => $lead->id));
            $this->runControllerWithRedirectExceptionAndGetContent('leads/default/convert');

            $contact = Contact::getById($lead->id);
            $this->assertNotEquals($oldStateValue, $contact->state->name);
        }

        /**
         * @depends testUserHasNoAccessToAccountsAndTriesToConvertWhenAccountIsOptional
         */
        public function testUserCanAccessAccountsButCannotCreateAccountShowConvertAction()
        {
            $super  = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $belina = User::getByUserName('belina');
            $lead   = LeadTestHelper::createLeadbyNameForOwner('BelinaLead1', $belina);
            $belina->setRight('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS, Right::ALLOW);
            $this->assertTrue($belina->save());
            $belina = $this->logoutCurrentUserLoginNewUserAndGetByUsername('belina');
            $convertToAccountSetting = LeadsModule::getConvertToAccountSetting();
            $this->assertEquals(Right::DENY, $belina->getEffectiveRight('AccountsModule', AccountsModule::RIGHT_CREATE_ACCOUNTS));

            //The convert view should load up normally, except the option to create an account will not be pressent.
            //This tests that the view does in fact come up.
            $this->setGetArray (array('id' => $lead->id));
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/convert');
        }

        /**
         * @depends testUserCanAccessAccountsButCannotCreateAccountShowConvertAction
         */
        public function testLeadConversionMisconfigurationScenarios()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            $bubby = UserTestHelper::createBasicUser('bubby');
            $lead  = LeadTestHelper::createLeadbyNameForOwner('BelinaLead1', $bubby);
            $bubby->setRight   ('LeadsModule', LeadsModule::RIGHT_CONVERT_LEADS, Right::ALLOW);
            $bubby->setRight   ('LeadsModule', LeadsModule::RIGHT_ACCESS_LEADS, Right::ALLOW);
            $this->assertTrue($bubby->save());

            //Scenario #1 - User does not have access to contacts
            $this->assertEquals(Right::DENY, $bubby->getEffectiveRight('ContactsModule', ContactsModule::RIGHT_ACCESS_CONTACTS));
            $bubby = $this->logoutCurrentUserLoginNewUserAndGetByUsername('bubby');
            //View will not show up properly.
            $this->setGetArray (array('id' => $lead->id));
            $this->runControllerWithExitExceptionAndGetContent('leads/default/convert');

            //Scenario #2 - User cannot access accounts and an account is required for conversion
            $bubby->setRight   ('ContactsModule', ContactsModule::RIGHT_CREATE_CONTACTS, Right::ALLOW);
            $bubby->setRight   ('ContactsModule', ContactsModule::RIGHT_ACCESS_CONTACTS, Right::ALLOW);
            $this->assertTrue($bubby->save());
            $metadata = LeadsModule::getMetadata();
            $metadata['global']['convertToAccountSetting'] = LeadsModule::CONVERT_ACCOUNT_REQUIRED;
            LeadsModule::setMetadata($metadata);

            //At this point because the account is required, the view will not come up properly.
            $this->setGetArray (array('id' => $lead->id));
            $content = $this->runControllerWithExitExceptionAndGetContent('leads/default/convert');
            $this->assertFalse(strpos($content, 'Conversion is set to require an account.  Currently you do not have access to the accounts module.') === false);
        }
    }
?>