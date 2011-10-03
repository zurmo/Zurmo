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

        /**
         * @depends testGetByPartialName
         */
        public function testGetGlobalSearchResultsByPartialTerm()
        {
            //Unfrozen, there are too many attributes that have to be columns in the database at this point, so
            //now this is just a frozen test.
            if (RedBeanDatabase::isFrozen())
            {
                $super = User::getByUsername('super');
                Yii::app()->user->userModel = $super;

                //Add an account with an email address.
                $account = new Account();
                $account->name        = 'The Zoo';
                $account->owner       = $super;
                $email = new Email();
                $email->optOut = 0;
                $email->emailAddress   = 'animal@zoo.com';
                $account->primaryEmail = $email;
                $this->assertTrue($account->save());

                //Create a contact with a similar e-mail address
                $contactStates = ContactState::getAll();
                $contact = new Contact();
                $contact->title->value = 'Mr';
                $contact->firstName    = 'Big';
                $contact->lastName     = 'Elephant';
                $contact->owner        = $super;
                $contact->state        = $contactStates[count($contactStates) - 1];
                $email = new Email();
                $email->optOut = 0;
                $email->emailAddress   = 'animal@africa.com';
                $contact->primaryEmail = $email;
                $this->assertTrue($contact->save());

                //Add an opportunity
                $currencies    = Currency::getAll();
                $currencyValue = new CurrencyValue();
                $currencyValue->value = 500.54;
                $currencyValue->currency = $currencies[0];
                $opportunity = new Opportunity();
                $opportunity->owner        = $super;
                $opportunity->name         = 'Animal Crackers';
                $opportunity->amount       = $currencyValue;
                $opportunity->closeDate    = '2011-01-01'; //eventually fix to make correct format
                $opportunity->stage->value = 'Negotiating';
                $this->assertTrue($opportunity->save());

                //Test where no results are expected.
                $data = ModelAutoCompleteUtil::getGlobalSearchResultsByPartialTerm('weqqw', 5, $super);
                $this->assertEquals(array(array('href' => '', 'label' => 'No Results Found')), $data);

                //Test where one account is expected searching by account name.
                $data = ModelAutoCompleteUtil::getGlobalSearchResultsByPartialTerm('Rabbit', 5, $super);
                $this->assertEquals(1, count($data));
                $this->assertEquals('Rabbit Systems - Account', $data[0]['label']);

                //test anyEmail where results are across more than one module. This will also pick up an opportunity that
                //has the name 'animal' in it.
                $data = ModelAutoCompleteUtil::getGlobalSearchResultsByPartialTerm('animal', 5, $super);
                $this->assertEquals(3, count($data));
                $this->assertEquals('The Zoo - Account', $data[0]['label']);
                $this->assertEquals('Big Elephant - Contact', $data[1]['label']);
                $this->assertEquals('Animal Crackers - Opportunity', $data[2]['label']);


            }
        }

        /**
         * @depends testGetGlobalSearchResultsByPartialTerm
         */
        public function testGetGlobalSearchResultsByPartialTermWithRegularUserAndElevationStepsForRegularUser()
        {
            //Unfrozen, there are too many attributes that have to be columns in the database at this point, so
            //now this is just a frozen test.
            if (RedBeanDatabase::isFrozen())
            {
                $super = User::getByUsername('super');
                $jimmy = User::getByUsername('jimmy');
                Yii::app()->user->userModel = $super;

                //Jimmy does not have read access, so he should not be able to see any results.
                $this->assertEquals(Right::DENY, $jimmy->getEffectiveRight('AccountsModule',      AccountsModule::RIGHT_ACCESS_ACCOUNTS));
                $this->assertEquals(Right::DENY, $jimmy->getEffectiveRight('ContactsModule',      ContactsModule::RIGHT_ACCESS_CONTACTS));
                $this->assertEquals(Right::DENY, $jimmy->getEffectiveRight('OpportunitiesModule', OpportunitiesModule::RIGHT_ACCESS_OPPORTUNITIES));
                Yii::app()->user->userModel = $jimmy;
                $data = ModelAutoCompleteUtil::getGlobalSearchResultsByPartialTerm('animal', 5, Yii::app()->user->userModel);
                $this->assertEquals(array(array('href' => '', 'label' => 'No Results Found')), $data);

                //Give Jimmy access to the module, he still will not be able to see results.
                Yii::app()->user->userModel = $super;
                $jimmy->setRight   ('AccountsModule',      AccountsModule::RIGHT_ACCESS_ACCOUNTS);
                $jimmy->setRight   ('ContactsModule',      ContactsModule::RIGHT_ACCESS_CONTACTS);
                $jimmy->setRight   ('LeadsModule',         LeadsModule::RIGHT_ACCESS_LEADS);
                $jimmy->setRight   ('OpportunitiesModule', OpportunitiesModule::RIGHT_ACCESS_OPPORTUNITIES);
                $this->assertTrue  ($jimmy->save());
                Yii::app()->user->userModel = $jimmy;
                $data = ModelAutoCompleteUtil::getGlobalSearchResultsByPartialTerm('animal', 5, Yii::app()->user->userModel);
                $this->assertEquals(array(array('href' => '', 'label' => 'No Results Found')), $data);

                //Give Jimmy read on 1 model.  The search then should pick up this model.
                Yii::app()->user->userModel = $super;
                $accounts = Account::getByName('The Zoo');
                $this->assertEquals(1, count($accounts));
                $account = $accounts[0];
                $this->assertEquals(Permission::NONE, $account->getEffectivePermissions      ($jimmy));
                $account->addPermissions($jimmy, Permission::READ);
                $this->assertTrue  ($account->save());
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForUser($account, $jimmy);
                Yii::app()->user->userModel = $jimmy;
                $data = ModelAutoCompleteUtil::getGlobalSearchResultsByPartialTerm('animal', 5, Yii::app()->user->userModel);
                $this->assertEquals(1, count($data));
                $this->assertEquals('The Zoo - Account',             $data[0]['label']);

                //Give Jimmy read on 2 more models.  The search then should pick up these models.
                Yii::app()->user->userModel = $super;
                $contacts = Contact::getByName('Big Elephant');
                $this->assertEquals(1, count($contacts));
                $contact = $contacts[0];
                $this->assertEquals(Permission::NONE, $contact->getEffectivePermissions      ($jimmy));
                $contact->addPermissions($jimmy, Permission::READ);
                $this->assertTrue  ($contact->save());
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForUser        ($contact, $jimmy);
                $opportunities = Opportunity::getByName('Animal Crackers');
                $this->assertEquals(1, count($opportunities));
                $opportunity = $opportunities[0];
                $this->assertEquals(Permission::NONE, $opportunity->getEffectivePermissions  ($jimmy));
                $opportunity->addPermissions($jimmy, Permission::READ);
                $this->assertTrue  ($opportunity->save());
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForUser        ($opportunity, $jimmy);
                Yii::app()->user->userModel = $jimmy;
                $data = ModelAutoCompleteUtil::getGlobalSearchResultsByPartialTerm('animal', 5, Yii::app()->user->userModel);
                $this->assertEquals(3, count($data));
                $this->assertEquals('The Zoo - Account',             $data[0]['label']);
                $this->assertEquals('Big Elephant - Contact',        $data[1]['label']);
                $this->assertEquals('Animal Crackers - Opportunity', $data[2]['label']);
            }
        }
    }
?>
