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

    // THIS IS BODGY, TEMPORARY DATA. THE REAL SAMPLE DATA WILL
    // BE SUPPLIED BY THE MODULES THEMSELVES, USING THE MODULE
    // DEPENDENCY MECHANISM, NOT LIKE THIS.
    class BodgySampleDataUtil
    {
        public static function makeBodgyTestDataIfItDoesntLookLikeItIsAlreadyThere()
        {
            if (!Yii::app()->user->userModel instanceof User)
            {
                self::makeBodgyUsersIfTheyDontSeemToExist();
            }
            else
            {
                self::makeBodgyNonUserDataIfTheyDontSeemToExist();
            }
        }

        public static function makeBodgyUsersIfTheyDontSeemToExist()
        {
            $titles = array('Mr', 'Mrs', 'Ms', 'Dr', 'Swami');
            $customFieldData = CustomFieldData::getByName('Titles');
            if (count(unserialize($customFieldData->serializedData)) == 0)
            {
                $customFieldData->serializedData = serialize($titles);
                $saved = $customFieldData->save();
                assert('$saved');
            }
            try
            {
                Yii::app()->user->userModel = User::getByUsername('super');
            }
            catch (NotFoundException $e)
            {
                foreach (User::getAll() as $user)
                {
                    $user->delete();
                }

                $user = new User();
                $user->username           = 'super';
                $user->title->value       = 'Mr';
                $user->firstName          = 'Clark';
                $user->lastName           = 'Kent';
                $user->setPassword('super');
                $saved = $user->save();
                assert('$saved');

                Yii::app()->user->userModel = $user;

                $group = Group::getByName(Group::EVERYONE_GROUP_NAME);
                $saved = $group->save();
                assert('$saved');
                $group->forget();

                $group = Group::getByName('Super Administrators');
                $group->users->add($user);
                $saved = $group->save();
                assert('$saved');

                $user = new User();
                $user->username           = 'admin';
                $user->title->value       = 'Sir';
                $user->firstName          = 'Jason';
                $user->lastName           = 'Blue';
                $user->setPassword('admin');
                $saved = $user->save();
                assert('$saved');

                foreach (array('jim'   => 'Mr',
                               'john'  => 'Swami',
                               'sally' => 'Dr',
                               'mary'  => 'Mrs',
                               'katie' => 'Miss',
                               'jill'  => 'Ms',
                               'sam'   => 'Mr') as $username => $title)
                {
                    $user = new User();
                    $user->username           = $username;
                    $user->title->value       = $title;
                    $user->firstName          = ucfirst($username);
                    $user->lastName           = 'Smith';
                    $user->setPassword($username);
                    $saved = $user->save();
                    assert('$saved');
                }
            }
        }

        // Must be called after login.
        public static function makeBodgyNonUserDataIfTheyDontSeemToExist()
        {
            if (count(Group::getAll()) < 3)
            {
                $groupA = new Group();
                $groupA->name = 'Sales People';
                $saved = $groupA->save();
                assert('$saved');
                $groupB = new Group();
                $groupB->name = 'Team East';
                $groupB->group = $groupA;
                $saved = $groupB->save();
                assert('$saved');
                $groupC = new Group();
                $groupC->name = 'Team West';
                $groupC->group = $groupA;
                $saved = $groupC->save();
                assert('$saved');
                $groupD = new Group();
                $groupD->name = 'Team California';
                $groupD->group = $groupC;
                $saved = $groupD->save();
                assert('$saved');
                $groupE = new Group();
                $groupE->name = 'Team Washington';
                $groupE->group = $groupC;
                $saved = $groupE->save();
                assert('$saved');
            }
            else
            {
                //Temporary, always assume if we are here that bodgy is fully created.
                return;
            }

            if (count(Role::getAll()) == 0)
            {
                $roleA = new Role();
                $roleA->name = 'Vp of Sales';
                $saved = $roleA->save();
                assert('$saved');
                $roleB = new Role();
                $roleB->name = 'Europe Sales Manager';
                $roleB->role = $roleA;
                $saved = $roleB->save();
                assert('$saved');
                $roleC = new Role();
                $roleC->name = 'Asia Sales Manager';
                $roleC->role = $roleA;
                $saved = $roleC->save();
                assert('$saved');
                $roleD = new Role();
                $roleD->name = 'Sales Engineer';
                $roleD->role = $roleC;
                $saved = $roleD->save();
                assert('$saved');
                $roleE = new Role();
                $roleE->name = 'Account Representative';
                $roleE->role = $roleC;
                $saved = $roleE->save();
                assert('$saved');
            }

            $industryFieldData = CustomFieldData::getByName('Industries');
            if (count(unserialize($industryFieldData->serializedData)) == 0)
            {
                $values = array(
                    'Automotive',
                    'Adult Entertainment',
                    'Financial Services',
                    'Mercenaries & Armaments',
                );
                $industryFieldData->defaultValue = $values[0];
                $industryFieldData->serializedData = serialize($values);
                $saved = $industryFieldData->save();
                assert('$saved');
            }
            $accountTypeFieldData = CustomFieldData::getByName('AccountTypes');
            if (count(unserialize($accountTypeFieldData->serializedData)) == 0)
            {
                $values = array('Prospect',
                    'Customer',
                    'Vendor',
                );
                $accountTypeFieldData = CustomFieldData::getByName('AccountTypes');
                $accountTypeFieldData->defaultValue = $values[0];
                $accountTypeFieldData->serializedData = serialize($values);
                $saved = $accountTypeFieldData->save();
                assert('$saved');
            }
            if (count(Account::getByName('Foodland')) == 0)
            {
                foreach (Account::getAll() as $account)
                {
                    $account->delete();
                }
                $companies = array('Big John\'s Toilet Supplies' => 'Financial Services',
                                   'Foodland'                   => 'Financial Services',
                                   'Tienda Betty'                => 'Financial Services');
                for ($i = ord('A'); $i <= ord('Z'); $i++)
                {
                    $companies['Company ' . chr($i)] = null;
                }
                foreach ($companies as $name => $industry)
                {
                    $account = new Account();
                    $account->name  = $name;
                    assert('$account->industry->value == "Automotive"');
                    assert('count(unserialize($account->industry->data->serializedData)) > 1');
                    if ($industry !== null)
                    {
                        $account->industry->value = $industry;
                    }
                    $account->type->value = 'Customer';
                    $saved = $account->save();
                    assert('$saved');
                }
            }
            $salesStagesFieldData = CustomFieldData::getByName('SalesStages');
            $stageValues = array(
                'Prospecting',
                'Qualification',
                'Negotiating',
                'Verbal',
                'Closed Won',
                'Closed Lost',
            );
            if (count(unserialize($salesStagesFieldData->serializedData)) == 0)
            {
                $salesStagesFieldData = CustomFieldData::getByName('SalesStages');
                $salesStagesFieldData->defaultValue = $stageValues[0];
                $salesStagesFieldData->serializedData = serialize($stageValues);
                $saved = $salesStagesFieldData->save();
                assert('$saved');
            }

            $leadSourcesFieldData = CustomFieldData::getByName('LeadSources');
            $sourceValues = array(
                'Self-Generated',
                'Inbound Call',
                'Tradeshow',
                'Word of Mouth',
            );
            if (count(unserialize($leadSourcesFieldData->serializedData)) == 0)
            {
                $leadSourcesFieldData = CustomFieldData::getByName('LeadSources');
                $leadSourcesFieldData->defaultValue = $sourceValues[0];
                $leadSourcesFieldData->serializedData = serialize($sourceValues);
                $saved = $leadSourcesFieldData->save();
                assert('$saved');
            }
            if (count(Opportunity::getAll()) == 0)
            {
                $currencies = Currency::getAll();
                foreach (Account::getAll() as $account)
                {
                    $currencyValue = new CurrencyValue();
                    $currencyValue->value = mt_rand(1000, 5000);
                    $currencyValue->currency = $currencies[0];
                    $opportunity   = new Opportunity();
                    $opportunity->name          = $account->name . ' - 1000 Widgets';
                    $opportunity->account       = $account;
                    $opportunity->stage->value  = $stageValues[mt_rand(0, count($stageValues)-1)];
                    $opportunity->source->value = $sourceValues[mt_rand(0, count($sourceValues)-1)];
                    $opportunity->amount        = $currencyValue;
                    $opportunity->closeDate     = '2012-01-01';
                    $saved = $opportunity->save();
                    assert('$saved');
                }
            }

            //attempt to load starting state data.
            ContactsModule::loadStartingData();
            if (count(Contact::getAll()) == 0)
            {
                $firstNames = array(
                    'Jason',
                    'Jim',
                    'Susan',
                    'Laura',
                    'Donna',
                    'Stafford',
                    'Nev',
                    'Alex',
                    'Ross',
                    'Anne',
                    'Ray',
                    'Fatmah',

                );
                $lastNames = array(
                    'Green',
                    'Blue',
                    'White',
                    'Smith',
                    'Lane',
                    'Anderson',
                    'Miller',
                    'Sandberg',
                    'Patel',
                    'Klein'

                );
                foreach (Account::getAll() as $account)
                {
                    $contact = new Contact();
                    $contact->firstName     = $firstNames[array_rand($firstNames)];
                    $contact->lastName      = $lastNames[array_rand($lastNames)];
                    $contact->account       = $account;
                    $contact->source->value = $sourceValues[1];
                    $contact->state         = ContactState::getById(5);
                    $contact->primaryEmail  = new Email();
                    $contact->primaryEmail->emailAddress = $contact->firstName . '@zurmocompany.com';
                    $saved = $contact->save();
                    assert('$saved');
                }
                $contactStates = ContactState::getAll();
                for ($i = 1; $i <= 21; $i++)
                {
                    $contact = new Contact();
                    $contact->firstName     = 'John' . $i;
                    $contact->lastName      = 'Smith' . $i;
                    $contact->companyName   = 'ABC Company' . $i;
                    $contact->source->value = $sourceValues[1];
                    $contact->state         = $contactStates[mt_rand(0, count($contactStates) - 1)];
                    $contact->primaryEmail  = new Email();
                    $contact->primaryEmail->emailAddress = $contact->firstName . '@zurmocompany.com';
                    $saved = $contact->save();
                    assert('$saved');
                }
            }
            //Add meeting categories
            $meetingCategoriesFieldData = CustomFieldData::getByName('MeetingCategories');
            if (count(unserialize($meetingCategoriesFieldData->serializedData)) == 0)
            {
                $values = array(
                    'Meeting',
                    'Call',
                );
                $meetingCategoriesFieldData->defaultValue = $values[0];
                $meetingCategoriesFieldData->serializedData = serialize($values);
                $saved = $meetingCategoriesFieldData->save();
                assert('$saved');
            }
        }
    }
?>
