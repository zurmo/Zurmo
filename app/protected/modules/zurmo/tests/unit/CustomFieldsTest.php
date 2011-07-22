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

    class CustomFieldsTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testAccountAndContactIndustries()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $values = array(
                'Automotive',
                'Adult Entertainment',
                'Financial Services',
                'Mercenaries & Armaments',
            );
            $industryFieldData = CustomFieldData::getByName('Industries');
            $industryFieldData->defaultValue = $values[0];
            $industryFieldData->serializedData = serialize($values);
            $this->assertTrue($industryFieldData->save());
            unset($industryFieldData);

            $user = UserTestHelper::createBasicUser('Billy');

            $account = new Account();
            $account->name  = 'Consoladores-R-Us';
            $account->owner = $user;
            $data = unserialize($account->industry->data->serializedData);
            $this->assertEquals('Automotive', $account->industry->value);
            $account->industry->value = $values[1];
            $this->assertTrue($account->save());
            unset($account);

            ContactsModule::loadStartingData();
            $states = ContactState::GetAll();
            $contact = new Contact();
            $contact->firstName = 'John';
            $contact->lastName  = 'Johnson';
            $contact->owner     = $user;
            $contact->state     = $states[0];
            $values = unserialize($contact->industry->data->serializedData);
            $this->assertEquals(4, count($values));
            $contact->industry->value = $values[3];
            $this->assertTrue($contact->save());
            unset($contact);

            $accounts = Account::getByName('Consoladores-R-Us');
            $account  = $accounts[0];
            $this->assertEquals('Adult Entertainment', $account->industry->value);

            $contacts = Contact::getAll();
            $contact  = $contacts[0];
            $this->assertEquals('Mercenaries & Armaments', $contact->industry->value);
        }
    }
?>
