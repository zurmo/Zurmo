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

    class RedBeanRelatedModelsTest extends ZurmoBaseTest
    {
        const CONTACTS = 10;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testCreateAddAndSaveAndRemoveByIndexRelatedModels()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $user = UserTestHelper::createBasicUser('Billy');

            $account = new Account();
            $account->owner = $user;
            $account->name = 'Wibble Corp';
            $this->assertTrue($account->save());
            for ($i = 0; $i < self::CONTACTS; $i++)
            {
                $contact = ContactTestHelper::createContactByNameForOwner('sampleContact' . $i,
                                                                                Yii::app()->user->userModel);
                $account->contacts->add($contact);
            }
            $this->assertTrue($account->save());
            $contact = $account->contacts[0];
            $this->assertFalse ($account->isModified());
            $this->assertFalse ($contact->isModified());
            $this->assertTrue  ($account->save());
            $this->assertFalse ($account->isModified());
            $this->assertFalse ($contact->isModified());
            $accountId = $account->id;
            unset($account);

            $account = Account::getById($accountId);
            $this->assertEquals('Wibble Corp', $account->name);
            $this->assertEquals(self::CONTACTS, $account->contacts->count());
            $this->assertEquals("{$account->contacts->count()} records.", strval($account->contacts));
            $contact = $account->contacts[0];
            $description  = $contact->description;
            $contact->description  = "this is a contact";
            $this->assertTrue ($account->isModified());
            $this->assertTrue ($contact   ->isModified());
            $this->assertTrue ($account->save());
            $this->assertFalse($account->isModified());
            $this->assertFalse($contact   ->isModified());
        }

        /**
         * @depends testCreateAddAndSaveAndRemoveByIndexRelatedModels
         */
        public function testRemoveRelatedModels()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $accounts = Account::getByName('Wibble Corp');
            $this->assertEquals(1, count($accounts));
            $account = $accounts[0];

            $this->assertEquals(self::CONTACTS, $account->contacts->count());
            $account->contacts->removeByIndex(0);
            $this->assertEquals(self::CONTACTS - 1, $account->contacts->count());
            $accountId = $account->id;
            $account->forget();
            unset($account); // Removes are now deferred. The account
                             // wasn't saved so no removed happened.

            $account = Account::getById($accountId);
            $this->assertEquals(self::CONTACTS, $account->contacts->count());
            $account->contacts->removeByIndex(0);
            $this->assertEquals(self::CONTACTS - 1, $account->contacts->count());
            $this->assertTrue($account->save());
            $account->forget();
            unset($account);

            $account = Account::getById($accountId);
            $this->assertEquals(self::CONTACTS - 1, $account->contacts->count());
        }

        /**
         * @depends testRemoveRelatedModels
         */
        public function testRemoveDoesntDeleteRelatedModels()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $accounts = Account::getByName('Wibble Corp');
            $this->assertEquals(1, count($accounts));
            $account = $accounts[0];

            $this->assertEquals(self::CONTACTS - 1, $account->contacts->count());
            $contactId = $account->contacts[0]->id;
            $account->contacts->removeByIndex(0);
            $this->assertEquals(self::CONTACTS - 2, $account->contacts->count());
            $account->forget();
            unset($account);

            // Removes now don't delete the thing being removed
            // (until the save - which wasn't done) so this now
            // doesn't throw.
            Contact::getById($contactId);
        }

        /**
         * @depends testRemoveDoesntDeleteRelatedModels
         */
        public function testRemoveAll()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $accounts = Account::getByName('Wibble Corp');
            $this->assertEquals(1, count($accounts));
            $account = $accounts[0];

            $this->assertEquals(self::CONTACTS, count(Contact::getAll()));

            $this->assertEquals(self::CONTACTS - 1, $account->contacts->count());
            $account->contacts->removeAll();
            $this->assertEquals(0, $account->contacts->count());
            $account->forget();
            unset($account);

            // None of the contacts should be removed since they are not owned by the account.
            $this->assertEquals(self::CONTACTS, count(Contact::getAll()));
        }

        public function testSomethingOpaqueThatICantThinkHowToDescribe()
        {
            $model1 = new TestSimplestModel();
            $model1->save();
            $model2 = new TestSimplestManyRelationModel();
            $model2->relation->add($model1);
        }
    }
?>
