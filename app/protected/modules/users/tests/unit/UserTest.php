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

    class UserTest extends BaseTest
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

        public function testSetTitleValuesAndRetrieveTitleValuesFromUser()
        {
            $titles = array('Mr', 'Mrs', 'Ms', 'Dr', 'Swami');
            $customFieldData = CustomFieldData::getByName('Titles');
            $customFieldData->serializedData = serialize($titles);
            $this->assertTrue($customFieldData->save());
            $dropDownArray = unserialize($customFieldData->serializedData);
            $this->assertEquals($titles, $dropDownArray);
            $user = new User();
            $dropDownModel = $user->title;
            $dropDownArray = unserialize($dropDownModel->data->serializedData);
            $this->assertEquals($titles, $dropDownArray);
        }

        public function testSaveCurrentUser()
        {
            //some endless loop if you are trying to save yourself
            $user = User::getByUsername('super');
            Yii::app()->user->userModel = $user;
            $user->department = 'somethingNew';
            $this->assertTrue($user->save());
        }

        public function testCreateAndGetUserById()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $user = new User();
            $user->username           = 'bill';
            $user->title->value       = 'Mr';
            $user->firstName          = 'Bill';
            $user->lastName           = 'Billson';
            $user->setPassword('billy');
            $this->assertTrue($user->save());
            $id = $user->id;
            unset($user);
            $user = User::getById($id);
            $this->assertEquals('bill', $user->username);
        }

        /**
         * @depends testCreateAndGetUserById
         */
        public function testCreateUserWithRelatedUser()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $manager = new User();
            $manager->username           = 'bobi';
            $manager->title->value       = 'Mr';
            $manager->firstName          = 'Bob';
            $manager->lastName           = 'Bobson';
            $manager->setPassword('bobii');
            $this->assertTrue($manager->save());

            $user = new User();
            $user->username     = 'dick';
            $user->title->value = 'Mr';
            $user->firstName    = 'Dick';
            $user->lastName     = 'Dickson';
            $user->manager      = $manager;
            $user->setPassword('dickster');
            $this->assertTrue($user->save());
            $id = $user->id;
            $managerId = $user->manager->id;
            unset($user);
            $manager = User::getById($managerId);
            $this->assertEquals('bobi',  $manager->username);
            $user = User::getById($id);
            $this->assertEquals('dick', $user->username);
            $this->assertEquals('bobi',  $user->manager->username);
        }

        /**
         * @depends testCreateAndGetUserById
         * @expectedException NotFoundException
         */
        public function testCreateAndGetUserByIdThatDoesntExist()
        {
            $user = User::getById(123456);
        }

        /**
         * @depends testCreateAndGetUserById
         */
        public function testGetByUsername()
        {
            $user = User::getByUsername('bill');
            $this->assertEquals('bill', $user->username);
        }

        /**
         * @depends testGetByUsername
         */
        public function testGetLabel()
        {
            $user = User::getByUsername('bill');
            $this->assertEquals('User',  $user::getModelLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Users', $user::getModelLabelByTypeAndLanguage('Plural'));
        }

        /**
         * @depends testGetByUsername
         * @expectedException NotFoundException
         */
        public function testGetByUsernameForNonExistentUsername()
        {
            User::getByUsername('noodles');
        }

        /**
         * @depends testCreateAndGetUserById
         */
        public function testSearchByPartialName()
        {
            $user1= User::getByUsername('dick');
            $users = UserModelSearch::getUsersByPartialFullName('di', 5);
            $this->assertEquals(1, count($users));
            $this->assertEquals($user1->id,     $users[0]->id);
            $this->assertEquals('dick',         $users[0]->username);
            $this->assertEquals('Dick Dickson', $users[0]->getFullName());

            $user2 = User::getByUsername('bill');
            $users = UserModelSearch::getUsersByPartialFullName('bi', 5);
            $this->assertEquals(1, count($users));
            $this->assertEquals($user2->id,     $users[0]->id);
            $this->assertEquals('bill',         $users[0]->username);
            $this->assertEquals('Bill Billson', $users[0]->getFullName());

            $user3 = new User();
            $user3->username  = 'dison';
            $user3->title->value = 'Mr';
            $user3->firstName    = 'Dison';
            $user3->lastName     = 'Smith';
            $user3->setPassword('dison');
            $this->assertTrue($user3->save());

            $user4 = new User();
            $user4->username  = 'graham';
            $user4->title->value = 'Mr';
            $user4->firstName    = 'Graham';
            $user4->lastName   = 'Dillon';
            $user4->setPassword('graham');
            $this->assertTrue($user4->save());

            $users = UserModelSearch::getUsersByPartialFullName('di', 5);
            $this->assertEquals(3, count($users));
            $this->assertEquals($user1->id,      $users[0]->id);
            $this->assertEquals('dick',          $users[0]->username);
            $this->assertEquals('Dick Dickson',  $users[0]->getFullName());
            $this->assertEquals($user3->id,      $users[1]->id);
            $this->assertEquals('dison',         $users[1]->username);
            $this->assertEquals('Dison Smith',   $users[1]->getFullName());
            $this->assertEquals($user4->id,      $users[2]->id);
            $this->assertEquals('graham',        $users[2]->username);
            $this->assertEquals('Graham Dillon', $users[2]->getFullName());

            $users = UserModelSearch::getUsersByPartialFullName('g', 5);
            $this->assertEquals(1, count($users));
            $this->assertEquals($user4->id,      $users[0]->id);
            $this->assertEquals('graham',        $users[0]->username);
            $this->assertEquals('Graham Dillon', $users[0]->getFullName());

            $users = UserModelSearch::getUsersByPartialFullName('G', 5);
            $this->assertEquals(1, count($users));
            $this->assertEquals($user4->id,      $users[0]->id);
            $this->assertEquals('graham',        $users[0]->username);
            $this->assertEquals('Graham Dillon', $users[0]->getFullName());

            $users = UserModelSearch::getUsersByPartialFullName('Dil', 5);
            $this->assertEquals(1, count($users));
            $this->assertEquals($user4->id,      $users[0]->id);
            $this->assertEquals('graham',        $users[0]->username);
            $this->assertEquals('Graham Dillon', $users[0]->getFullName());
        }

        /**
         * @depends testSearchByPartialName
         */
        public function testSearchByPartialNameWithFirstNamePlusPartialLastName()
        {
            $user = User::getByUsername('dick');

            $users = UserModelSearch::getUsersByPartialFullName('dick', 5);
            $this->assertEquals(1, count($users));
            $this->assertEquals($user->id,      $users[0]->id);
            $this->assertEquals('dick',         $users[0]->username);
            $this->assertEquals('Dick Dickson', $users[0]->getFullName());

            $users = UserModelSearch::getUsersByPartialFullName('dick ', 5);
            $this->assertEquals(1, count($users));
            $this->assertEquals($user->id,      $users[0]->id);
            $this->assertEquals('dick',         $users[0]->username);
            $this->assertEquals('Dick Dickson', $users[0]->getFullName());

            $users = UserModelSearch::getUsersByPartialFullName('dick d', 5);
            $this->assertEquals(1, count($users));
            $this->assertEquals($user->id,      $users[0]->id);
            $this->assertEquals('dick',         $users[0]->username);
            $this->assertEquals('Dick Dickson', $users[0]->getFullName());

            $user = User::getByUsername('dick');
            $users = UserModelSearch::getUsersByPartialFullName('dick di', 5);
            $this->assertEquals(1, count($users));
            $this->assertEquals($user->id,      $users[0]->id);
            $this->assertEquals('dick',         $users[0]->username);
            $this->assertEquals('Dick Dickson', $users[0]->getFullName());

            $users = UserModelSearch::getUsersByPartialFullName('Dick di', 5);
            $this->assertEquals(1, count($users));
            $this->assertEquals($user->id,      $users[0]->id);
            $this->assertEquals('dick',         $users[0]->username);
            $this->assertEquals('Dick Dickson', $users[0]->getFullName());

            $users = UserModelSearch::getUsersByPartialFullName('dick Di', 5);
            $this->assertEquals(1, count($users));
            $this->assertEquals($user->id,      $users[0]->id);
            $this->assertEquals('dick',         $users[0]->username);
            $this->assertEquals('Dick Dickson', $users[0]->getFullName());
        }

        /**
         * @depends testCreateAndGetUserById
         */
        public function testCreateWithTitleThenClearTitleDirectly()
        {
            $user = new User();
            $user->username     = 'jason';
            $user->title->value = 'Mr';
            $user->firstName    = 'Jason';
            $user->lastName     = 'Jasonson';
            $user->setPassword('jason');
            $this->assertTrue($user->save());
            $id = $user->id;
            unset($user);
            $user = User::getById($id);
            $this->assertEquals('jason', $user->username);
            $this->assertEquals('Mr', strval($user->title));
            $user->title = null;
            $this->assertNotNull($user->title);
            $this->assertTrue($user->save());
        }

        /**
         * @depends testCreateWithTitleThenClearTitleDirectly
         */
        public function testCreateWithTitleThenClearTitleWithSetAttributesWithEmptyId()
        {
            $user = User::getByUsername('jason');
            $user->title->value = 'Mr';
            $this->assertEquals('Mr', strval($user->title));
            $this->assertTrue($user->save());

            $_FAKEPOST = array(
                'User' => array(
                    'title' => array(
                        'value' => '',
                    )
                )
            );

            $user->setAttributes($_FAKEPOST['User']);
            $this->assertEquals('(None)', strval($user->title));
            $this->assertTrue($user->save());
        }

        /**
         * @depends testCreateWithTitleThenClearTitleWithSetAttributesWithEmptyId
         */
        public function testCreateWithTitleThenClearTitleWithSetAttributesWithNullId()
        {
            $user = User::getByUsername('jason');
            $user->title->value = 'Mr';
            $this->assertEquals('Mr', strval($user->title));
            $this->assertTrue($user->save());

            $_FAKEPOST = array(
                'User' => array(
                    'title' => array(
                        'value' => '',
                    )
                )
            );

            $user->setAttributes($_FAKEPOST['User']);
            $this->assertEquals('(None)', strval($user->title));
            $this->assertTrue($user->save());
        }

        /**
         * @depends testCreateWithTitleThenClearTitleWithSetAttributesWithNullId
         */
        public function testCreateWithTitleThenClearTitleWithSetAttributesWithRealId()
        {
            $user = User::getByUsername('jason');
            $user->title->value = 'Mr';
            $this->assertEquals('Mr', strval($user->title));
            $this->assertTrue($user->save());

            $_FAKEPOST = array(
                'User' => array(
                    'title' => array(
                        'value' => 'Sir',
                    )
                )
            );

            $user->setAttributes($_FAKEPOST['User']);
            $this->assertEquals('Sir', strval($user->title));
            $this->assertTrue($user->save());
        }

        public function testSaveUserWithNoManager()
        {
            $user = UserTestHelper::createBasicUser('Steven');
            $_FAKEPOST = array(
                'User' => array(
                    'manager' => array(
                        'id' => '',
                    ),
                ),
            );

            $user->setAttributes($_FAKEPOST['User']);
            $user->validate();
            $this->assertEquals(array(), $user->getErrors());
        }

        /**
         * @depends testCreateWithTitleThenClearTitleWithSetAttributesWithRealId
         * @depends testSaveUserWithNoManager
         */
        public function testSaveExistingUserWithFakePost()
        {
            $user = User::getByUsername('jason');
            $_FAKEPOST = array(
                'User' => array(
                    'title' => array(
                        'value' => '',
                    ),
                    'firstName'   => 'Jason',
                    'lastName'    => 'Jasonson',
                    'username'    => 'jason',
                    'jobTitle'    => '',
                    'officePhone' => '',
                    'manager' => array(
                        'id' => '',
                    ),
                    'mobilePhone' => '',
                    'department'  => '',
                    'primaryEmail' => array(
                        'emailAddress' => '',
                        'optOut' => 0,
                        'isInvalid' => 0,
                    ),
                    'primaryAddress' => array(
                        'street1'    => '',
                        'street2'    => '',
                        'city'       => '',
                        'state'      => '',
                        'postalCode' => '',
                        'country'    => '',
                    )
                )
            );
            $user->setAttributes($_FAKEPOST['User']);
            $user->validate();
            $this->assertEquals(array(), $user->getErrors());
            $this->assertTrue($user->save());
        }

        /**
         * @depends testSaveExistingUserWithFakePost
         */
        public function testSaveExistingUserWithUsersIdAsManagerId()
        {
            $user = User::getByUsername('jason');
            $_FAKEPOST = array(
                'User' => array(
                    'title' => array(
                        'value' => '',
                    ),
                    'firstName'   => 'Jason',
                    'lastName'    => 'Jasonson',
                    'username'    => 'jason',
                    'jobTitle'    => '',
                    'officePhone' => '',
                    'manager' => array(
                        'id' => $user->id,
                    ),
                )
            );
            /*
            $user->setAttributes($_FAKEPOST['User']);
            $this->assertFalse($user->save());
            $errors = $user->getErrors();
            //todo: assert an error is present for manager, assert the error says can't
            //select self or something along those lines.
            */

            //probably should also check if you are picking a manager that is creating recursion,
            //not necessarily yourself, but someone in the chain of yourself already.
        }

        public function testUserMixingInPerson()
        {
            // See comments on User::getDefaultMetadata().

            $user = new User();
            $this->assertTrue($user->isAttribute('username'));
            $this->assertTrue($user->isAttribute('title'));
            $this->assertTrue($user->isAttribute('firstName'));
            $this->assertTrue($user->isAttribute('lastName'));
            $this->assertTrue($user->isAttribute('jobTitle'));

            $user->username     = 'oliver';
            $user->title->value = 'Mr';
            $user->firstName    = 'Oliver';
            $user->lastName     = 'Oliverson';
            $user->jobTitle     = 'Recruiter';
            $this->assertEquals('oliver',           $user->username);
            $this->assertEquals('Oliver Oliverson', strval($user));
            $this->assertEquals('Recruiter',        $user->jobTitle);
            $user->setPassword('oliver');
            $this->assertTrue($user->save());

            $id = $user->id;
            $user->forget();
            unset($user);

            $user = User::getById($id);
            $this->assertEquals('oliver',           $user->username);
            $this->assertEquals('Oliver Oliverson', strval($user));
            $this->assertEquals('Recruiter',        $user->jobTitle);
        }

        public function testCreateNewUserFromPostNoBadValues()
        {
            $_FAKEPOST = array(
                'UserPasswordForm' => array(
                    'title' => array(
                        'value' => '',
                    ),
                    'firstName'   => 'Red',
                    'lastName'    => 'Jiambo',
                    'username'    => 'redjiambo',
                    'newPassword' => '123456',
                    'newPassword_repeat' => '123456',
                    'jobTitle'    => '',
                    'officePhone' => '',
                    'manager' => array(
                        'id' => '',
                    ),
                    'mobilePhone' => '',
                    'department'  => '',
                    'primaryEmail' => array(
                        'emailAddress' => '',
                        'optOut' => 0,
                        'isInvalid' => 0,
                    ),
                    'primaryAddress' => array(
                        'street1'    => '',
                        'street2'    => '',
                        'city'       => '',
                        'state'      => '',
                        'postalCode' => '',
                        'country'    => '',
                    )
                )
            );
            $user = new User();
            $user->setScenario('createUser');
            $userPasswordForm = new UserPasswordForm($user);
            $userPasswordForm->setScenario('createUser');
            $userPasswordForm->setAttributes($_FAKEPOST['UserPasswordForm']);
            $userPasswordForm->validate();
            $this->assertEquals(array(), $userPasswordForm->getErrors());
            $this->assertTrue($userPasswordForm->save());
            $user->forget();
            $user = User::getByUsername('redjiambo');
            $this->assertEquals('Red', $user->firstName);
            $this->assertEquals(null,  $user->officePhone);
            $this->assertEquals(null,  $user->jobTitle);
            $this->assertEquals(null,  $user->mobilePhone);
            $this->assertEquals(null,  $user->department);
        }

        /**
         * @depends testCreateAndGetUserById
         */
        public function testDeleteUserCascadesToDeleteEverythingItShould()
        {
            $group = new Group();
            $group->name = 'Os mais legais do Rio';
            $this->assertTrue($group->save());

            $user = new User();
            $user->username                   = 'carioca';
            $user->title->value               = 'Senhor';
            $user->firstName                  = 'José';
            $user->lastName                   = 'Olivereira';
            $user->jobTitle                   = 'Traficante';
            $user->primaryAddress->street1    = 'R. das Mulheres, 69';
            $user->primaryAddress->street2    = '';
            $user->primaryAddress->city       = 'Centro';
            $user->primaryAddress->state      = 'RJ';
            $user->primaryAddress->postalCode = '';
            $user->primaryAddress->country    = 'Brasil';
            $user->primaryEmail->emailAddress = 'jose@gmail.com';
            $user->primaryEmail->optOut       = 1;
            $user->primaryEmail->isInvalid    = 0;
            $user->manager                    = User::getByUsername('bill');
            $user->setPassword('Senhor');
            $user->groups->add($group);
            $this->assertTrue($user->save());

            $titleId          = $user->title->id;
            $primaryAddressId = $user->primaryAddress->id;
            $primaryEmailId   = $user->primaryEmail  ->id;
            $groupId          = $group->id;

            $user->delete();
            unset($user);
            unset($group);

            Group::getById($groupId);
            User::getByUsername('bill');

            try
            {
                CustomField::getById($titleId);
                $this->fail("Title should have been deleted.");
            }
            catch (NotFoundException $e)
            {
            }

            try
            {
                Address::getById($primaryAddressId);
                $this->fail("Primary address should have been deleted.");
            }
            catch (NotFoundException $e)
            {
            }

            try
            {
                Email::getById($primaryEmailId);
                $this->fail("Primary email should have been deleted.");
            }
            catch (NotFoundException $e)
            {
            }
        }

        /**
         * @depends testCreateAndGetUserById
         */
        public function testCanRemoveRoleFromUser()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $parentRole = new Role();
            $parentRole->name = 'SomeParentRole';
            $saved = $parentRole->save();
            $this->assertTrue($parentRole->id > 0);
            $this->assertTrue($saved);
            $role = new Role();
            $role->name = 'SomeRole';
            $role->role = $parentRole;
            $saved = $role->save();
            $this->assertTrue($parentRole->id > 0);
            $this->assertEquals($parentRole->id, $role->role->id);
            $this->assertTrue($role->id > 0);
            $this->assertTrue($saved);
            $user = User::getByUsername('bill');
            $this->assertTrue($user->id > 0);
            $this->assertFalse($user->role->id > 0);
            $fakePost = array(
                'role' => array(
                    'id' => $role->id,
                )
            );
            $user->setAttributes($fakePost);
            $saved = $user->save();
            $this->assertTrue($saved);
            $user->forget();
            unset($user);
            $user  = User::getByUsername('bill');
            $this->assertTrue($user->id > 0);
            $this->assertTrue($role->id > 0);
            $this->assertEquals($role->id, $user->role->id);
            $fakePost = array(
                'role' => array(
                    'id' => '',
                )
            );
            $user->setAttributes($fakePost);
            $this->assertFalse($user->role->id > 0);
            $saved = $user->save();
            $this->assertTrue($saved);
            $user->forget();
            unset($user);
            $user  = User::getByUsername('bill');
            $this->assertTrue($user->id > 0);
            $this->assertFalse($user->role->id > 0);
        }

        /**
         * @depends testCreateAndGetUserById
         */
        public function testPasswordUserNamePolicyChangesValidationAndLogin()
        {
            $bill  = User::getByUsername('bill');
            $bill->setScenario('changePassword');
            $billPasswordForm = new UserPasswordForm($bill);
            $billPasswordForm->setScenario('changePassword');
            $this->assertEquals(null,       $bill->getEffectivePolicy('UsersModule', UsersModule::POLICY_ENFORCE_STRONG_PASSWORDS));
            $this->assertEquals(5,          $bill->getEffectivePolicy('UsersModule', UsersModule::POLICY_MINIMUM_PASSWORD_LENGTH));
            $this->assertEquals(3,          $bill->getEffectivePolicy('UsersModule', UsersModule::POLICY_MINIMUM_USERNAME_LENGTH));
            $_FAKEPOST = array(
                'UserPasswordForm' => array(
                    'username'           => 'ab',
                    'newPassword'        => 'ab',
                    'newPassword_repeat' => 'ab',
                )
            );
            $billPasswordForm->setAttributes($_FAKEPOST['UserPasswordForm']);
            $this->assertFalse($billPasswordForm->save());
            $errors = array(
                'newPassword' => array(
                    'The password is too short. Minimum length is&#160;5.',
                ),
                'username' => array(
                    'The username is too short. Minimum length is&#160;3.',
                ),
            );
            $this->assertEquals($errors, $billPasswordForm->getErrors());
            $_FAKEPOST = array(
                'UserPasswordForm' => array(
                    'username'           => 'abcdefg',
                    'newPassword'        => 'abcdefg',
                    'newPassword_repeat' => 'abcdefg',
                )
            );
            $billPasswordForm->setAttributes($_FAKEPOST['UserPasswordForm']);
            $this->assertEquals('abcdefg', $billPasswordForm->username);
            $this->assertEquals('abcdefg', $billPasswordForm->newPassword);
            $validated = $billPasswordForm->validate();
            $this->assertTrue($validated);
            $saved = $billPasswordForm->save();
            $this->assertTrue($saved);
            $bill->setPolicy('UsersModule', UsersModule::POLICY_ENFORCE_STRONG_PASSWORDS, Policy::YES);
            // If security is optimized the optimization will see the policy value in the database
            // and so wont use it in validating, so the non-strong password wont be validated as
            // invalid until the next save.
            $this->assertEquals(SECURITY_OPTIMIZED, $billPasswordForm->save());
            $_FAKEPOST = array(
                'UserPasswordForm' => array(
                    'newPassword'        => 'abcdefg',
                    'newPassword_repeat' => 'abcdefg',
                )
            );
            $billPasswordForm->setAttributes($_FAKEPOST['UserPasswordForm']);
            $this->assertFalse($billPasswordForm->save());
            $this->assertEquals(md5('abcdefg'), $bill->hash);
            $errors = array(
                'newPassword' => array(
                    'The password must have at least one uppercase letter',
                    'The password must have at least one number and one letter',
                ),
            );
            $this->assertEquals($errors, $billPasswordForm->getErrors());
            $_FAKEPOST = array(
                'UserPasswordForm' => array(
                    'newPassword'        => 'abcdefgN',
                    'newPassword_repeat' => 'abcdefgN',
                )
            );
            $billPasswordForm->setAttributes($_FAKEPOST['UserPasswordForm']);
            $this->assertFalse($billPasswordForm->save());
            $errors = array(
                'newPassword' => array(
                    'The password must have at least one number and one letter',
                ),
            );
            $this->assertEquals($errors, $billPasswordForm->getErrors());
            $_FAKEPOST = array(
                'UserPasswordForm' => array(
                    'newPassword'        => 'ABCDEFGH',
                    'newPassword_repeat' => 'ABCDEFGH',
                )
            );
            $billPasswordForm->setAttributes($_FAKEPOST['UserPasswordForm']);
            $this->assertFalse($billPasswordForm->save());
            $errors = array(
                'newPassword' => array(
                    'The password must have at least one lowercase letter',
                    'The password must have at least one number and one letter',
                ),
            );
            $this->assertEquals($errors, $billPasswordForm->getErrors());
            $_FAKEPOST = array(
                'UserPasswordForm' => array(
                    'newPassword'        => 'abcdefgN4',
                    'newPassword_repeat' => 'abcdefgN4',
                )
            );
            $billPasswordForm->setAttributes($_FAKEPOST['UserPasswordForm']);
            $this->assertTrue($billPasswordForm->save());
            $bill->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB);
            $this->assertTrue($billPasswordForm->save());
            $this->assertEquals(Right::ALLOW, $bill->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB));
            //Now attempt to login as bill
            $bill->forget();
            $bill       = User::getByUsername('abcdefg');
            $this->assertEquals(md5('abcdefgN4'), $bill->hash);
            $identity = new UserIdentity('abcdefg', 'abcdefgN4');
            $authenticated = $identity->authenticate();
            $this->assertEquals(0, $identity->errorCode);
            $this->assertTrue($authenticated);

            //Now turn off login via web for bill
            Yii::app()->user->userModel = User::getByUsername('super');
            $bill  = User::getByUsername('abcdefg');
            $bill->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB, RIGHT::DENY);
            $this->assertTrue($bill->save());
            $identity = new UserIdentity('abcdefg', 'abcdefgN4');
            $this->assertFalse($identity->authenticate());
            $this->assertEquals(UserIdentity::ERROR_NO_RIGHT_WEB_LOGIN, $identity->errorCode);
        }

        public function testValidatingUserAfterGettingAttributeValuesFromRelatedUsers()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $user = UserTestHelper::createBasicUser('notsuper');
            $this->assertTrue($user->save());
            $this->assertTrue($user->createdByUser ->isSame($super));
            $this->assertTrue($user->modifiedByUser->isSame($super));
            if (!$user->validate())
            {
                $this->assertEquals(array(), $user->getErrors());
            }
            // A regular user has a created by and
            // modified by user so accessing them is no problem.
            $test = $user->createdByUser->id;
            $this->assertTrue($user->validate());
            $this->assertEquals(array(), $user->getErrors());
        }

        public function testValidatingSuperAdministratorAfterGettingAttributeValuesFromRelatedUsers()
        {
            $super = User::getByUsername('super');
            $this->assertTrue($super->validate());
            $this->assertTrue($super->createdByUser->id  < 0);
            $this->assertTrue($super->modifiedByUser->isSame($super));
            $this->assertTrue($super->validate());
            $this->assertEquals(array(), $super->getErrors());
        }

        /**
         * @depends testCreateUserWithRelatedUser
         */
        public function testSavingExistingUserDoesntCreateRelatedBlankUsers()
        {
            $userCount = count(User::getAll());
            $dick = User::getByUsername('dick');
            $this->assertTrue($dick->save());
            $this->assertEquals($userCount, count(User::getAll()));
        }

        public function testMixedInPersonInUser()
        {
            $user = new User();
            $user->username = 'dude';
            $user->lastName = 'Dude';
            $this->assertTrue($user->save());

            $this->assertTrue($user->isAttribute('id'));             // From RedBeanModel.
            $this->assertTrue($user->isAttribute('createdDateTime')); // From Item.
            $this->assertTrue($user->isAttribute('firstName'));      // From Person.
            $this->assertTrue($user->isAttribute('username'));       // From User.

            $this->assertTrue($user->isRelation ('createdByUser'));  // From Item.
            $this->assertTrue($user->isRelation ('rights'));         // From Permitable.
            $this->assertTrue($user->isRelation ('title'));          // From Person.
            $this->assertTrue($user->isRelation ('manager'));        // From User.

            unset($user);

            $user = User::getByUsername('dude');

            $this->assertTrue($user->isAttribute('id'));             // From RedBeanModel.
            $this->assertTrue($user->isAttribute('createdDateTime')); // From Item.
            $this->assertTrue($user->isAttribute('firstName'));      // From Person.
            $this->assertTrue($user->isAttribute('username'));       // From User.

            $this->assertTrue($user->isRelation ('createdByUser'));  // From Item.
            $this->assertTrue($user->isRelation ('rights'));         // From Permitable.
            $this->assertTrue($user->isRelation ('title'));          // From Person.
            $this->assertTrue($user->isRelation ('manager'));        // From User.

            RedBeanModelsCache::cacheModel($user);

            $modelIdentifier = $user->getModelIdentifier();
            unset($user);

            RedBeanModelsCache::forgetAll(true); // Forget it at the php level.

            if (MEMCACHE_ON)
            {
                $user = RedBeanModelsCache::getModel($modelIdentifier);

                $this->assertTrue($user->isAttribute('id'));             // From RedBeanModel.
                $this->assertTrue($user->isAttribute('createdDateTime')); // From Item.
                $this->assertTrue($user->isAttribute('firstName'));      // From Person.
                $this->assertTrue($user->isAttribute('username'));       // From User.

                $this->assertTrue($user->isRelation ('createdByUser'));  // From Item.
                $this->assertTrue($user->isRelation ('rights'));         // From Permitable.
                $this->assertTrue($user->isRelation ('title'));          // From Person.
                $this->assertTrue($user->isRelation ('manager'));        // From User.
            }
        }

        public function testGetModelClassNames()
        {
            $modelClassNames = UsersModule::getModelClassNames();
            $this->assertEquals(2, count($modelClassNames));
            $this->assertEquals('User', $modelClassNames[0]);
            $this->assertEquals('UserModelSearch', $modelClassNames[1]);
        }

        public function testLogAuditEventsListForCreatedAndModifedCreatingFirstUser()
        {
            Yii::app()->user->userModel = null;
            $user = new User();
            $user->username           = 'myuser';
            $user->title->value       = 'Mr';
            $user->firstName          = 'My';
            $user->lastName           = 'Userson';
            $user->setPassword('myuser');
            $saved = $user->save();
            $this->assertTrue($saved);
            $this->assertEquals(Yii::app()->user->userModel, $user);

            //Create a second user and confirm the first user is still the current user.
            $user2 = new User();
            $user2->username           = 'myuser2';
            $user2->title->value       = 'Mr';
            $user2->firstName          = 'My';
            $user2->lastName           = 'Userson2';
            $user2->setPassword('myuser2');
            $this->assertTrue($user2->save());
            $this->assertEquals(Yii::app()->user->userModel, $user);
        }
    }
?>
