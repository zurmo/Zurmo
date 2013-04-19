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