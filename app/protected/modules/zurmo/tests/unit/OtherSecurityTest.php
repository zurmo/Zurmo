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

    // Tests that don't seem to have a home.
    class OtherSecurityTest extends ZurmoBaseTest
    {
        public function testStrongerIntegerNotSavingAsInteger()
        {
            SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = User::getByUsername('super');
            $user = UserTestHelper::createBasicUser('arrry');
            $userId = $user->id;

            $user2 = UserTestHelper::createBasicUser('brrry');
            $user2Id = $user2->id;

            $a = new Group();
            $a->name = 'RRRRRA';
            $this->assertTrue($a->save());
            $a->users ->add($user);
            $a->users ->add($user2);
            $a->save();
            $user->forget();
            $user2->forget();
            $a->forget();
            unset($a);
            unset($user);
            unset($user2);
            $a = Group::getByName('RRRRRA');
            $data = PoliciesUtil::getAllModulePoliciesDataByPermitable($a);
            $policiesForm = PoliciesFormUtil::makeFormFromPoliciesData($data);
            $fakePost = array(
                'UsersModule__POLICY_ENFORCE_STRONG_PASSWORDS'        => '',
                'UsersModule__POLICY_MINIMUM_PASSWORD_LENGTH__helper' => '1',
                'UsersModule__POLICY_MINIMUM_PASSWORD_LENGTH'         => '5',
                'UsersModule__POLICY_MINIMUM_USERNAME_LENGTH__helper' => '1',
                'UsersModule__POLICY_MINIMUM_USERNAME_LENGTH'         => '5',
                'UsersModule__POLICY_PASSWORD_EXPIRES'                => '',
            );
            $validatedAndCastedPostData = PoliciesFormUtil::typeCastPostData($fakePost);
            $policiesForm = PoliciesFormUtil::loadFormFromCastedPost($policiesForm,
                $validatedAndCastedPostData);
            $this->assertTrue($policiesForm->validate());
            $saved = PoliciesFormUtil::setPoliciesFromCastedPost(
                $validatedAndCastedPostData, $a);
            $this->assertTrue($saved);
            $a->forget();
            $user = User::getById($userId);
            $user2 = User::getById($user2Id);
            $data = PoliciesUtil::getAllModulePoliciesDataByPermitable($user);
            $data = PoliciesUtil::getAllModulePoliciesDataByPermitable($user2);
            $user->forget();
            $user2->forget();
        }

        /**
         * @depends testStrongerIntegerNotSavingAsInteger
         */
        public function testRemovingGroupUserIsInAndRetrievingUserModulePermissions()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $group = Group::getByName('RRRRRA');
            $item = NamedSecurableItem::getByName('AccountsModule');
            $item->addPermissions($group, Permission::READ, Permission::ALLOW);
            $item->addPermissions($group, Permission::WRITE, Permission::DENY);
            $item->addPermissions($group, Permission::WRITE, Permission::DENY);
            $item->save();
            $item = NamedSecurableItem::getByName('LeadsModule');
            $item->addPermissions($group, Permission::READ, Permission::ALLOW);
            $item->save();
            $group->forget();
            $item->forget();
            unset($item);
            unset($group);
            $group = Group::getByName('RRRRRA');
            $group->users->removeAll();
            $group->groups->removeAll();
            $group->save();
            $group->delete();
            $group->forget();
            unset($group);
            $user = User::getByUsername('arrry');
            $modulePermissionsData =  PermissionsUtil::getAllModulePermissionsDataByPermitable($user);
            $user->forget();
            unset($user);
        }

        public function testUserCanReadEmptyModelWithoutPermissionAndNoDefaultsSetOnModelButCantSaveItUntilTheySetAnOwner()
        {
            $user = UserTestHelper::createBasicUser('atester');
            $this->assertTrue($user->id > 0);
            $item = NamedSecurableItem::getByName('AccountsModule');
            $this->assertEquals(Permission::NONE, $item->getEffectivePermissions($user));
            Yii::app()->user->userModel = $user;
            $account = new Account(false);
            $this->assertEquals('', $account->name);
            $account->name = 'Something Corp';
            $account->validate();
            $this->assertFalse($account->save());
            $this->assertEquals(
                array('owner' =>
                    array('username' =>
                        array('Username cannot be blank.'),
                          'lastName' =>
                        array('Last Name cannot be blank.'),
                    )
                ),
                $account->getErrors());
        }

        /**
         * @depends testUserCanReadEmptyModelWithoutPermissionAndNoDefaultsSetOnModelButCantSaveItUntilTheySetAnOwner
         */
        public function testUserWhoCreatesModelAndGivesItAwayLosesAccessAndCantGetItBack()
        {
            $user  = User::getByUsername('atester');
            $user2 = UserTestHelper::createBasicUser('atester2');
            $item = NamedSecurableItem::getByName('AccountsModule');
            $this->assertEquals(Permission::NONE, $item->getEffectivePermissions($user));
            Yii::app()->user->userModel = $user;
            $account = new Account(false);
            // When an account has no owner (meaning the unsaved user
            // automatically) associated with it can be manipulated
            // but whoever created.
            $this->assertEquals('', $account->name);
            // If it is given away...
            $account->owner = $user2;
            $account->save();
            try
            {
                // They lose access to it.
                $name = $account->name;
                $this->fail();
            }
            catch (AccessDeniedSecurityException $e)
            {
            }
            try
            {
                // Make sure they can't get it back by setting the
                // owner to an unsaved user.
                $account->owner = new User();
                $this->fail();
            }
            catch (AccessDeniedSecurityException $e)
            {
            }
        }

        public function testUserCannotSeeRelatedModelInformationWithLimitedPermissions()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            ContactsModule::loadStartingData();
            $user = UserTestHelper::createBasicUser('dtester');
            $this->assertTrue($user->id > 0);

            $user = UserTestHelper::createBasicUser('etester');
            $this->assertTrue($user->id > 0);

            $account = new Account();
            $account->name  = 'DAccount';
            $account->owner = User::getByUsername('dtester');
            $saved = $account->save();
            assert('$saved'); // Not Coding Standard

            $states = ContactState::GetAll();
            $contact = new Contact();
            $contact->owner        = User::getByUsername('etester');
            $contact->account      = $account;
            $contact->title->value = 'Mr.';
            $contact->firstName    = 'Super';
            $contact->lastName     = 'Man';
            $contact->state        = $states[0];
            $this->assertTrue($contact->save());
            $id = $contact->id;
            $this->assertNotEmpty($id);
            $contact->forget();

            Yii::app()->user->userModel = User::getByUsername('etester');
            $contact = Contact::getById($id);
            $this->assertNotEmpty($contact->id);
            $this->assertEquals(Permission::NONE, $contact->account->getEffectivePermissions      (Yii::app()->user->userModel));
        }

        public function testLimitedUserCanCreateAccountHeDoesNotOwnAndThenCannotReadIt()
        {
            $super = User::getByUsername('super');
            $limitedUser = UserTestHelper::createBasicUser('limitedMan');
            Yii::app()->user->userModel = $limitedUser;
            $account = new Account();
            $account->name  = 'limited';
            $account->owner = $super;
            $saved = $account->save();
            $this->assertTrue($saved);
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($limitedUser));
        }
    }
?>
