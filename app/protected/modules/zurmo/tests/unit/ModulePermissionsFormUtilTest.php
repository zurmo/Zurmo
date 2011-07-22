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

    class ModulePermissionsFormUtilTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();

            SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = User::getByUsername('super');

            $everyone = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $saved = $everyone->save();
            assert('$saved'); // Not Coding Standard
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testSetWriteDenyPermission()
        {
            $readWriteBit = Permission::READ |
                    Permission::WRITE |
                    Permission::CHANGE_OWNER |
                    Permission::CHANGE_PERMISSIONS;
            $this->assertEquals(27, $readWriteBit);
            $items = NamedSecurableItem::getAll();
            $this->assertEquals(0, count($items));
            $securableItem1 = new NamedSecurableItem();
            $securableItem1->name = 'TestItem';
            $saved = $securableItem1->save();
            $this->assertTrue($saved);
            $group = new Group();
            $group->name = 'myTestGroup';
            $saved = $group->save();
            $this->assertTrue($saved);
            $this->assertEquals(array(Permission::NONE, Permission::NONE),  $securableItem1->getExplicitActualPermissions($group));
            $securableItem1->addPermissions($group, Permission::WRITE, Permission::DENY);
            $securableItem1->save();
            $this->assertEquals(array(Permission::NONE, Permission::WRITE), $securableItem1->getExplicitActualPermissions($group));
            $securableItem1->addPermissions($group, Permission::READ);
            $securableItem1->save();
            $this->assertEquals(array(Permission::READ, Permission::WRITE), $securableItem1->getExplicitActualPermissions($group));
            $securableItem2 = new NamedSecurableItem();
            $securableItem2->name = 'TestItem2';
            $saved = $securableItem2->save();
            $this->assertTrue($saved);
            $items = NamedSecurableItem::getAll();
            $this->assertEquals(2, count($items));
            $securableItem1->forget();
            $securableItem2->forget();
            $newItem = NamedSecurableItem::getByName('HomeModule');
            $permission = 'WRITE';
            $newItem->addPermissions($group, constant('Permission::' . $permission), Permission::ALLOW);
            $this->assertTrue($newItem->save());
            $newItem->forget();
            $group->forget();
            $group = Group::getByName('myTestGroup');
            $newItem = NamedSecurableItem::getByName('HomeModule');
            $explicitPermissions = $newItem->getExplicitActualPermissions($group);
            $this->assertEquals(array(Permission::WRITE, Permission::NONE), $explicitPermissions);
            $effectivePermissions = $newItem->getEffectivePermissions($group);
            $this->assertEquals(Permission::WRITE, $effectivePermissions);
            $resolvedPermission = PermissionsUtil::resolveExplicitOrInheritedPermission($explicitPermissions, Permission::WRITE);
            $this->assertEquals(PERMISSION::ALLOW, $resolvedPermission);
            $data = PermissionsUtil::getAllModulePermissionsDataByPermitable($group);
            $compareData = array(
                'HomeModule'    => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::WRITE => array(
                        'explicit'    => Permission::ALLOW,
                        'inherited'   => null,
                        'actual'      => Permission::ALLOW
                    ),
                ),
            );
            $this->assertEquals($compareData['HomeModule'], $data['HomeModule']);
            $group->forget();
        }

        /**
         * @depends testSetWriteDenyPermission
         */
        public function testPermissionsUtilGetAllModulePermissionsData()
        {
            $this->assertEquals(User::getByUsername('super'), Yii::app()->user->userModel);
            $securableItem3 = new NamedSecurableItem();
            $securableItem3->name = 'TestItem3';
            $saved = $securableItem3->save();
            $this->assertTrue($saved);
            $group = new Group();
            $group->name = 'modulePermissionsGroup';
            $saved = $group->save();
            $this->assertTrue($saved);
            $data = PermissionsUtil::getAllModulePermissionsDataByPermitable($group);
            $compareData = array(
                'AccountsModule' => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::WRITE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                ),
                'ContactsModule' => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::WRITE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                ),
                'LeadsModule'    => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::WRITE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                ),
                'MeetingsModule' => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::WRITE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                ),
                'NotesModule' => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::WRITE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                ),
                'OpportunitiesModule' => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::WRITE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                ),
                'TasksModule' => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::WRITE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                ),
                'UsersModule' => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::WRITE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                ),
            );
            $this->assertEquals(15, count($data));
            $this->assertEquals($compareData['AccountsModule'], $data['AccountsModule']);
            $this->assertEquals($compareData['ContactsModule'], $data['ContactsModule']);
            $this->assertEquals($compareData['LeadsModule'],    $data['LeadsModule']);
            $this->assertEquals($compareData['OpportunitiesModule'], $data['OpportunitiesModule']);
            $this->assertEquals($compareData['TasksModule'], $data['TasksModule']);
            $this->assertEquals($compareData['NotesModule'], $data['NotesModule']);
            $this->assertEquals($compareData['MeetingsModule'], $data['MeetingsModule']);
            $this->assertEquals($compareData['UsersModule'],    $data['UsersModule']);
            $group->forget();
            $securableItem3->forget();
        }

        /**
         * @depends testPermissionsUtilGetAllModulePermissionsData
         */
        public function testSetModulePermissionsForm()
        {
            $group = Group::getByName('modulePermissionsGroup');
            $group1 = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $accountsItem = NamedSecurableItem::getByName('AccountsModule');
            $contactsItem = NamedSecurableItem::getByName('ContactsModule');
            $accountsItem->addPermissions($group,      Permission::READ);
            $this->assertTrue($accountsItem->save());
            $contactsItem->addPermissions($group1,     Permission::READ);
            $this->assertTrue($contactsItem->save());
            $data = PermissionsUtil::getAllModulePermissionsDataByPermitable($group);
            $form = ModulePermissionsFormUtil::makeFormFromPermissionsData($data);
            $compareData = array(
                'AccountsModule' => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => Permission::ALLOW,
                        'inherited'   => null,
                        'actual'   => Permission::ALLOW,
                    ),
                    Permission::WRITE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                ),
                'ContactsModule' => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => null,
                        'inherited'   => Permission::ALLOW,
                        'actual'   => Permission::ALLOW,
                    ),
                    Permission::WRITE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                ),
                'LeadsModule'    => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::WRITE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                ),
                'MeetingsModule' => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::WRITE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                ),
                'NotesModule' => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::WRITE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                ),
                'OpportunitiesModule' => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::WRITE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                ),
                'TasksModule' => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::WRITE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                ),
                'UsersModule' => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::WRITE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                ),
            );
            $this->assertEquals(15, count($data));
            $this->assertEquals($compareData['AccountsModule'], $form->data['AccountsModule']);
            $this->assertEquals($compareData['ContactsModule'], $form->data['ContactsModule']);
            $this->assertEquals($compareData['LeadsModule'],    $form->data['LeadsModule']);
            $this->assertEquals($compareData['OpportunitiesModule'], $form->data['OpportunitiesModule']);
            $this->assertEquals($compareData['TasksModule'], $data['TasksModule']);
            $this->assertEquals($compareData['NotesModule'], $data['NotesModule']);
            $this->assertEquals($compareData['MeetingsModule'], $data['MeetingsModule']);
            $this->assertEquals($compareData['UsersModule'],    $form->data['UsersModule']);
            $contactsItem->forget();
            $accountsItem->forget();
            $group->forget();
            $group1->forget();
        }

        /**
         * @depends testSetModulePermissionsForm
         */
        public function testSettingChangeOwnerChangePermissionFromPost()
        {
            $group = new Group();
            $group->name = 'newGroup';
            $saved = $group->save();
            $this->assertTrue($saved);
            $group->forget();
            $newItem = NamedSecurableItem::getByName('SomeModule');
            $this->assertEquals(array(Permission::NONE, Permission::NONE),
                    $newItem->getExplicitActualPermissions($group)
            );
            $newItem->forget();
            $fakePost = array(
                'SomeModule__' . Permission::CHANGE_PERMISSIONS    => strval(Permission::ALLOW),
                'SomeModule__' . Permission::CHANGE_OWNER          => strval(Permission::ALLOW),
            );
            $validatedPost = ModulePermissionsFormUtil::typeCastPostData($fakePost);
            $saved = ModulePermissionsFormUtil::setPermissionsFromCastedPost($validatedPost, $group);
            $this->assertTrue($saved);
            $group->forget();
            $group = Group::getByName('newGroup');
            $newItem = NamedSecurableItem::getByName('SomeModule');
            $this->assertEquals(array(
                (   Permission::CHANGE_OWNER |
                    Permission::CHANGE_PERMISSIONS), Permission::NONE),
                    $newItem->getExplicitActualPermissions($group)
            );
            $newItem->forget();
        }

        /**
         * @depends testSettingChangeOwnerChangePermissionFromPost
         */
        public function testModulePermissionsFormUtilSetRightsFromPost()
        {
            $group = Group::getByName('modulePermissionsGroup');
            $data = PermissionsUtil::getAllModulePermissionsDataByPermitable($group);
            $form = ModulePermissionsFormUtil::makeFormFromPermissionsData($data);
            $compareData = array(
                'AccountsModule' => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => Permission::ALLOW,
                        'inherited'   => null,
                        'actual'   => Permission::ALLOW
                    ),
                    Permission::WRITE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                ),
                'ContactsModule' => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => null,
                        'inherited'   => Permission::ALLOW,
                        'actual'   => Permission::ALLOW
                    ),
                    Permission::WRITE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                ),
                'LeadsModule'    => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::WRITE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                ),
                'MeetingsModule' => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::WRITE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                ),
                'NotesModule' => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::WRITE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                ),
                'OpportunitiesModule' => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::WRITE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                ),
                'TasksModule' => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::WRITE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                ),
                'UsersModule' => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::WRITE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                ),
            );
            $this->assertEquals(15, count($data));
            $this->assertEquals($compareData['AccountsModule'], $form->data['AccountsModule']);
            $this->assertEquals($compareData['ContactsModule'], $form->data['ContactsModule']);
            $this->assertEquals($compareData['LeadsModule'],    $form->data['LeadsModule']);
            $this->assertEquals($compareData['OpportunitiesModule'], $form->data['OpportunitiesModule']);
            $this->assertEquals($compareData['TasksModule'], $data['TasksModule']);
            $this->assertEquals($compareData['NotesModule'], $data['NotesModule']);
            $this->assertEquals($compareData['MeetingsModule'], $data['MeetingsModule']);
            $this->assertEquals($compareData['UsersModule'],    $form->data['UsersModule']);
            $fakePost = array(
                'LeadsModule__' . Permission::READ           => strval(Permission::ALLOW),
                'LeadsModule__' . Permission::WRITE          => strval(Permission::ALLOW),
                'AccountsModule__' . Permission::READ        => '',
                'OpportunitiesModule__' . Permission::DELETE => strval(Permission::DENY),
            );
            $validatedPost = ModulePermissionsFormUtil::typeCastPostData($fakePost);
            $readyToSetPostData = ModulePermissionsEditViewUtil::resolveWritePermissionsFromArray($validatedPost);
            $readyToSetPostDataCompare = array(
                'LeadsModule__'         . Permission::READ               => strval(Permission::ALLOW),
                'LeadsModule__'         . Permission::CHANGE_OWNER       => strval(Permission::ALLOW),
                'LeadsModule__'         . Permission::WRITE              => strval(Permission::ALLOW),
                'LeadsModule__'         . Permission::CHANGE_PERMISSIONS => strval(Permission::ALLOW),
                'AccountsModule__'      . Permission::READ               => '',
                'OpportunitiesModule__' . Permission::DELETE             => strval(Permission::DENY),
            );
            $this->assertEquals($readyToSetPostDataCompare, $readyToSetPostData);
            $saved = ModulePermissionsFormUtil::setPermissionsFromCastedPost($readyToSetPostData, $group);
            $this->assertTrue($saved);
            $group->forget();
            $group = Group::getByName('modulePermissionsGroup');
            $newItem = NamedSecurableItem::getByName('LeadsModule');
            $this->assertEquals(array(
                (   Permission::READ |
                    Permission::WRITE |
                    Permission::CHANGE_OWNER |
                    Permission::CHANGE_PERMISSIONS), Permission::NONE),
                    $newItem->getExplicitActualPermissions($group)
            );
            $newItem->forget();
            $compareData = array(
                'AccountsModule' => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::WRITE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                ),
                'ContactsModule' => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => null,
                        'inherited'   => Permission::ALLOW,
                        'actual'   => Permission::ALLOW,
                    ),
                    Permission::WRITE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                ),
                'LeadsModule'    => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => Permission::ALLOW,
                        'inherited'   => null,
                        'actual'   => Permission::ALLOW,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => Permission::ALLOW,
                        'inherited'   => null,
                        'actual'   => Permission::ALLOW,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => Permission::ALLOW,
                        'inherited'   => null,
                        'actual'   => Permission::ALLOW,
                    ),
                    Permission::WRITE => array(
                        'explicit'    => Permission::ALLOW,
                        'inherited'   => null,
                        'actual'   => Permission::ALLOW,
                    ),
                ),
                'MeetingsModule' => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::WRITE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                ),
                'NotesModule' => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::WRITE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                ),
                'OpportunitiesModule' => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => Permission::DENY,
                        'inherited'   => null,
                        'actual'   => Permission::DENY
                    ),
                    Permission::READ => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::WRITE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                ),
                'TasksModule' => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::WRITE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                ),
                'UsersModule' => array(
                    Permission::CHANGE_OWNER => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::CHANGE_PERMISSIONS => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::DELETE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::READ => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                    Permission::WRITE => array(
                        'explicit'    => null,
                        'inherited'   => null,
                        'actual'      => null,
                    ),
                ),
            );
            $data = PermissionsUtil::getAllModulePermissionsDataByPermitable($group);
            $this->assertEquals(15, count($data));
            $this->assertEquals($compareData['AccountsModule'], $data['AccountsModule']);
            $this->assertEquals($compareData['ContactsModule'], $data['ContactsModule']);
            $this->assertEquals($compareData['LeadsModule'],    $data['LeadsModule']);
            $this->assertEquals($compareData['OpportunitiesModule'], $data['OpportunitiesModule']);
            $this->assertEquals($compareData['TasksModule'], $data['TasksModule']);
            $this->assertEquals($compareData['NotesModule'], $data['NotesModule']);
            $this->assertEquals($compareData['MeetingsModule'], $data['MeetingsModule']);
            $this->assertEquals($compareData['UsersModule'],    $data['UsersModule']);
            $group->forget();
        }

       public function testGetDerivedAttributeNameFromTwoStrings()
       {
           $attributeName = FormModelUtil::getDerivedAttributeNameFromTwoStrings('x', 'y');
           $this->assertEquals('x__y', $attributeName);
       }
    }
?>
