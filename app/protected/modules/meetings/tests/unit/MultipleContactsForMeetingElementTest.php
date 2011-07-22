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

    class MultipleContactsForMeetingElementTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $loaded = ContactsModule::loadStartingData();
        }

        public function testRenderHtmlContentLabelFromContactAndKeyword()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $contact  = new Contact();
            $contact->firstName = 'johnny';
            $contact->lastName  = 'five';
            $contact->owner     = $super;
            $contact->state        = ContactState::getById(5);
            $contact->primaryEmail = new Email();
            $contact->primaryEmail->emailAddress = 'a@a.com';
            $this->assertTrue($contact->save());

            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $contact2  = new Contact();
            $contact2->firstName = 'johnny';
            $contact2->lastName  = 'six';
            $contact2->owner     = $super;
            $contact2->state        = ContactState::getById(5);
            $contact2->primaryEmail = new Email();
            $contact2->primaryEmail->emailAddress = 'a@a.com';
            $contact2->secondaryEmail = new Email();
            $contact2->secondaryEmail->emailAddress = 'b@b.com';
            $this->assertTrue($contact2->save());

            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $contact3  = new Contact();
            $contact3->firstName = 'johnny';
            $contact3->lastName  = 'seven';
            $contact3->owner     = $super;
            $contact3->state        = ContactState::getById(5);
            $this->assertTrue($contact3->save());

            $content = MultipleContactsForMeetingElement::renderHtmlContentLabelFromContactAndKeyword($contact, 'asdad');
            $this->assertEquals('johnny five&#160&#160<b>a@a.com</b>', $content);

            $content = MultipleContactsForMeetingElement::renderHtmlContentLabelFromContactAndKeyword($contact2, 'b@b');
            $this->assertEquals('johnny six&#160&#160<b>b@b.com</b>', $content);

            $content = MultipleContactsForMeetingElement::renderHtmlContentLabelFromContactAndKeyword($contact2, 'cc');
            $this->assertEquals('johnny six&#160&#160<b>a@a.com</b>', $content);

            $content = MultipleContactsForMeetingElement::renderHtmlContentLabelFromContactAndKeyword($contact3, 'cx');
            $this->assertEquals('johnny seven', $content);
        }
    }
?>
