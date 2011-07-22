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

    class ModelAutoCompleteUtilTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            ZurmoDatabaseCompatibilityUtil::dropStoredFunctionsAndProcedures();
            SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = User::getByUsername('super');
            $loaded = ContactsModule::loadStartingData();
            assert('$loaded'); // Not Coding Standard
        }

        public function testGetByPartialName()
        {
            $userData = array(
                'Billy',
                'Jimmy',
                'Sunny',
                'Jinny',
                'Johnny',
                'Appay'
            );
            foreach ($userData as $firstName)
            {
                $user = UserTestHelper::createBasicUser($firstName);
            }

            $user = User::getByUsername('billy');
            $contactData = array(
                'Sam',
                'Sally',
                'Sarah',
                'Jason',
                'James',
                'Roger'
            );
            $contactStates = ContactState::getAll();
            foreach ($contactData as $firstName)
            {
                $contact = new Contact();
                $contact->title->value = 'Mr';
                $contact->firstName    = $firstName;
                $contact->lastName     = $firstName . 'son';
                $contact->owner        = $user;
                $contact->state        = $contactStates[count($contactStates) - 1];
                $this->assertTrue($contact->save());
            }
            $accountData = array(
                'ABC Company',
                'Rabbit Systems',
                'Rabid Technology',
                'Super Company',
                'Legalland',
                'Toycity'
            );
            foreach ($accountData as $name)
            {
                $account = new Account();
                $account->name    = $name;
                $account->owner     = $user;
                $this->assertTrue($account->save());
            }
            $data = ModelAutoCompleteUtil::getByPartialName('User', 'j', 5);
            $compareData = array(
                array(
                    'id'    => $data[0]['id'],
                    'value' => 'Jimmy Jimmyson',
                    'label' => 'Jimmy Jimmyson (jimmy)',
                ),
                array(
                    'id'    => $data[1]['id'],
                    'value' => 'Jinny Jinnyson',
                    'label' => 'Jinny Jinnyson (jinny)',
                ),
                array(
                    'id'    => $data[2]['id'],
                    'value' => 'Johnny Johnnyson',
                    'label' => 'Johnny Johnnyson (johnny)',
                ),
            );
            $this->assertEquals($compareData, $data);
            $data = ContactAutoCompleteUtil::getByPartialName('sa', 5);
            $compareData = array(
                array(
                    'id'    => $data[0]['id'],
                    'value' => 'Sally Sallyson',
                    'label' => 'Sally Sallyson',
                ),
                array(
                    'id'    => $data[1]['id'],
                    'value' => 'Sam Samson',
                    'label' => 'Sam Samson',
                ),
                array(
                    'id'    => $data[2]['id'],
                    'value' => 'Sarah Sarahson',
                    'label' => 'Sarah Sarahson',
                ),
            );
            $this->assertEquals($compareData, $data);
            $data = ContactAutoCompleteUtil::getByPartialName('xa', 5);
            $compareData = array();
            $this->assertEquals($compareData, $data);
            $data = ContactAutoCompleteUtil::getByPartialName('s', 1);
            $compareData = array(
                array(
                    'id'    => $data[0]['id'],
                    'value' => 'Sally Sallyson',
                    'label' => 'Sally Sallyson',
                ),
            );
            $this->assertEquals($compareData, $data);
            $data = ModelAutoCompleteUtil::getByPartialName('Account', 'rab', 5);
            $compareData = array(
                array(
                    'id'    => $data[0]['id'],
                    'value' => 'Rabbit Systems',
                    'label' => 'Rabbit Systems',
                ),
                array(
                    'id'    => $data[1]['id'],
                    'value' => 'Rabid Technology',
                    'label' => 'Rabid Technology',
                ),
            );
            $this->assertEquals($compareData, $data);
        }
    }
?>
