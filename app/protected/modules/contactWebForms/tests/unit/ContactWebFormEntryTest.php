<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    class ContactWebFormEntryTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testCreateAndGetContactWebFormEntryById()
        {
            $allAttributes                      = ContactWebFormsUtil::getAllAttributes();
            $placedAttributes                   = array('firstName', 'lastName', 'companyName', 'jobTitle');
            $contactFormAttributes              = ContactWebFormsUtil::getAllPlacedAttributes($allAttributes,
                                                                                              $placedAttributes);
            $attributes                         = array_keys($contactFormAttributes);
            $this->assertTrue(ContactsModule::loadStartingData());
            $contactStates                      = ContactState::getByName('New');
            $contactWebForm                     = new ContactWebForm();
            $contactWebForm->name               = 'Test Form';
            $contactWebForm->redirectUrl        = 'http://google.com';
            $contactWebForm->submitButtonLabel  = 'Save';
            $contactWebForm->defaultState       = $contactStates[0];
            $contactWebForm->defaultOwner       = Yii::app()->user->userModel;
            $contactWebForm->serializedData     = serialize($attributes);
            $contactWebForm->save();

            $contact                = new Contact();
            $contact->owner         = $contactWebForm->defaultOwner;
            $contact->state         = $contactWebForm->defaultState;
            $contact->firstName     = 'Super';
            $contact->lastName      = 'Man';
            $contact->jobTitle      = 'Superhero';
            $contact->companyName   = 'Test Inc.';
            if ($contact->validate())
            {
                $contactWebFormEntryStatus  = ContactWebFormEntry::STATUS_SUCCESS;
                $contactWebFormEntryMessage = ContactWebFormEntry::STATUS_SUCCESS_MESSAGE;
            }
            else
            {
                $contactWebFormEntryStatus  = ContactWebFormEntry::STATUS_ERROR;
                $contactWebFormEntryMessage = ContactWebFormEntry::STATUS_ERROR_MESSAGE;
            }
            $contact->save();

            foreach ($contactFormAttributes as $attributeName => $attributeValue)
            {
                $contactFormAttributes[$attributeName] = $contact->$attributeName;
            }
            $contactFormAttributes['owner']      = $contactWebForm->defaultOwner->id;
            $contactFormAttributes['state']      = $contactWebForm->defaultState->id;

            $contactWebFormEntry = new ContactWebFormEntry();
            $contactWebFormEntry->serializedData = serialize($contactFormAttributes);
            $contactWebFormEntry->status         = $contactWebFormEntryStatus;
            $contactWebFormEntry->message        = $contactWebFormEntryMessage;
            $contactWebFormEntry->contactWebForm = $contactWebForm;
            $contactWebFormEntry->contact        = $contact;
            $this->assertTrue($contactWebFormEntry->save());
            $contactWebFormEntryId               = $contactWebFormEntry->id;
            unset($contactWebFormEntry);

            $contactWebFormEntry = ContactWebFormEntry::getById($contactWebFormEntryId);
            $this->assertEquals('Test Form'     , $contactWebFormEntry->contactWebForm->name);
            $this->assertEquals('Super'         , $contactWebFormEntry->contact->firstName);
            $this->assertEquals('Man'           , $contactWebFormEntry->contact->lastName);
            $contactFormAttributes = unserialize($contactWebFormEntry->serializedData);
            $this->assertEquals('Super'         , $contactFormAttributes['firstName']);
            $this->assertEquals('Man'           , $contactFormAttributes['lastName']);
            $this->assertEquals('Superhero'     , $contactFormAttributes['jobTitle']);
            $this->assertEquals('Test Inc.'     , $contactFormAttributes['companyName']);
        }
    }
?>