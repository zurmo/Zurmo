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
     *Tests that the ZeroModel user interface shows up correctly.  This occurs when no models are available to the
     * logged in user. @see ZeroModelsCheckControllerFilter
     */
    class ContactsSuperUserZeroModelsWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            ContactsModule::loadStartingData();
        }

        public function testSuperUserThatContactsAndLeadsShowZeroModelUserInterfaceCorrectly()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            $this->assertEquals(0, count(Contact::getAll()));
            //At this point the zero model ui should show up for contacts and leads
            $content = $this->runControllerWithNoExceptionsAndGetContent('contacts/default/list');
            $this->assertFalse(strpos($content, 'Arthur Conan') === false);
            $content = $this->runControllerWithNoExceptionsAndGetContent('leads/default/list');
            $this->assertFalse(strpos($content, 'Thomas Paine') === false);

            $contact = ContactTestHelper::createContactByNameForOwner('Jimmy', $super);

            //At this point leads should still show the zero model message
            $content = $this->runControllerWithNoExceptionsAndGetContent('contacts/default/list');
            $this->assertTrue(strpos($content, 'Arthur Conan') === false);
            $content = $this->runControllerWithNoExceptionsAndGetContent('leads/default/list');
            $this->assertFalse(strpos($content, 'Thomas Paine') === false);

            $this->assertTrue($contact->delete());
            $this->assertEquals(0, count(Contact::getAll()));

            //Create lead.
            $lead = LeadTestHelper::createLeadByNameForOwner('Jammy', $super);

            //At this point contacts should still show the zero model message
            $content = $this->runControllerWithNoExceptionsAndGetContent('contacts/default/list');
            $this->assertFalse(strpos($content, 'Arthur Conan') === false);
            $content = $this->runControllerWithNoExceptionsAndGetContent('leads/default/list');
            $this->assertTrue(strpos($content, 'Thomas Paine') === false);
        }
    }
?>