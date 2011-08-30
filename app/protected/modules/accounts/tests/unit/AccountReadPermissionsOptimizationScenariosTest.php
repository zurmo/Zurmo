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

    global $freeze;

    if (!$freeze)
    {
        // These tests rely on the model ids being certain values, which relies
        // on it running on new tables, which is not the case when freezing.

        class AccountReadPermissionsOptimizationScenariosTest extends AccountReadPermissionsOptimizationBaseTest
        {
            public static function setUpBeforeClass()
            {
                parent::setUpBeforeClass();
                // This is setting up users and groups to match Jason's
                // powerpoint workings out of how the munge should look
                // after each operation. Things are set up in the order
                // that will give them the right ids to have munge ids
                // that match the document. The names are adjusted
                // to conform to the minimum lengths and casing in
                // the models. This the basic set up that is almost
                // right for many of the tests, and each test does
                // whatever it needs to to make the exactly what it
                // needs AND puts it back how it found it.
                $u1 = new User();
                $u1->username = 'u1.';
                $u1->lastName = 'U1';
                $saved = $u1->save();
                assert('$saved');       // Not Coding Standard
                assert('$u1->id == 1'); // Not Coding Standard

                $u2 = new User();
                $u2->username = 'u2.';
                $u2->lastName = 'U2';
                $saved = $u2->save();
                assert('$saved');       // Not Coding Standard
                assert('$u2->id == 2'); // Not Coding Standard

                $u3 = new User();
                $u3->username = 'u3.';
                $u3->lastName = 'U3';
                $saved = $u3->save();
                assert('$saved');       // Not Coding Standard
                assert('$u3->id == 3'); // Not Coding Standard

                $u4 = new User();
                $u4->username = 'u4.';
                $u4->lastName = 'U4';
                $saved = $u4->save();
                assert('$saved');       // Not Coding Standard
                assert('$u4->id == 4'); // Not Coding Standard

                $u5 = new User();
                $u5->username = 'u5.';
                $u5->lastName = 'U5';
                $saved = $u5->save();
                assert('$saved');       // Not Coding Standard
                assert('$u5->id == 5'); // Not Coding Standard

                $u6 = new User();
                $u6->username = 'u6.';
                $u6->lastName = 'U6';
                $saved = $u6->save();
                assert('$saved');       // Not Coding Standard
                assert('$u6->id == 6'); // Not Coding Standard

                $u99 = new User();      // A user with no roles
                $u99->username = 'u99.';// that can create accounts
                $u99->lastName = 'U99'; // without having any
                $saved = $u99->save();  // effect on the munge.
                assert('$saved');       // Not Coding Standard

                $g1 = new Group();
                $g1->name = 'G1.';
                $saved = $g1->save();
                assert('$saved');       // Not Coding Standard
                assert('$g1->id == 1'); // Not Coding Standard

                $g2 = new Group();
                $g2->name = 'G2.';
                $saved = $g2->save();
                assert('$saved');       // Not Coding Standard
                assert('$g2->id == 2'); // Not Coding Standard

                $g3 = new Group();
                $g3->name = 'G3.';
                $saved = $g3->save();
                assert('$saved');       // Not Coding Standard
                assert('$g3->id == 3'); // Not Coding Standard

                $r1 = new Role();
                $r1->name = 'R1.';
                $saved = $r1->save();
                assert('$saved');       // Not Coding Standard
                assert('$r1->id == 1'); // Not Coding Standard

                $r2 = new Role();
                $r2->name = 'R2.';
                $saved = $r2->save();
                assert('$saved');       // Not Coding Standard
                assert('$r2->id == 2'); // Not Coding Standard

                $r3 = new Role();
                $r3->name = 'R3.';
                $saved = $r3->save();
                assert('$saved');       // Not Coding Standard
                assert('$r3->id == 3'); // Not Coding Standard

                $r4 = new Role();
                $r4->name = 'R4.';
                $saved = $r4->save();
                assert('$saved');       // Not Coding Standard
                assert('$r4->id == 4'); // Not Coding Standard

                $r5 = new Role();
                $r5->name = 'R5.';
                $saved = $r5->save();
                assert('$saved');       // Not Coding Standard
                assert('$r5->id == 5'); // Not Coding Standard

                $r6 = new Role();
                $r6->name = 'R6.';
                $saved = $r6->save();
                assert('$saved');       // Not Coding Standard
                assert('$r6->id == 6'); // Not Coding Standard

                $r3->roles->add($r2);
                $r2->roles->add($r1);
                $r6->roles->add($r5);
                $r5->roles->add($r4);
                $u1->role = $r1;
                $u2->role = $r4;
                $u3->role = $r4;
                $u4->role = $r4;

                $saved = $r3->save();
                assert('$saved');       // Not Coding Standard

                $saved = $r2->save();
                assert('$saved');       // Not Coding Standard

                $saved = $r6->save();
                assert('$saved');       // Not Coding Standard

                $saved = $r5->save();
                assert('$saved');       // Not Coding Standard

                $saved = $u1->save();
                assert('$saved');       // Not Coding Standard

                $saved = $u2->save();
                assert('$saved');       // Not Coding Standard

                $saved = $u3->save();
                assert('$saved');       // Not Coding Standard

                $saved = $u4->save();
                assert('$saved');       // Not Coding Standard

                ReadPermissionsOptimizationUtil::rebuild();
                assert('self::getAccountMungeRowCount() == 0'); // Not Coding Standard
                RedBeanModel::forgetAll();
                self::assertEverythingHasBeenSetBackToHowItStarted();
            }

            public function tearDown()
            {
                ReadPermissionsOptimizationUtil::rebuild();
                assert('self::getAccountMungeRowCount() == 0'); // Not Coding Standard
                RedBeanModel::forgetAll();
                self::assertEverythingHasBeenSetBackToHowItStarted();
                //Teardown comes after so that the Yii::app()->user->userModel is still in tact since the rebuild
                //requires it.
                parent::tearDown();
            }

            protected static function assertEverythingHasBeenSetBackToHowItStarted()
            {
                RedBeanModel::forgetAll();

                $u1 = User::getByUsername('u1.');
                $u2 = User::getByUsername('u2.');
                $u3 = User::getByUsername('u3.');
                $u4 = User::getByUsername('u4.');
                $u5 = User::getByUsername('u5.');
                $u6 = User::getByUsername('u6.');

                $g1 = Group::getByName('G1.');
                $g2 = Group::getByName('G2.');
                $g3 = Group::getByName('G3.');

                $r1 = Role::getByName('R1.');
                $r2 = Role::getByName('R2.');
                $r3 = Role::getByName('R3.');
                $r4 = Role::getByName('R4.');
                $r5 = Role::getByName('R5.');
                $r6 = Role::getByName('R6.');

                assert('$g1->users->count() == 0'); // Not Coding Standard
                assert('$g2->users->count() == 0'); // Not Coding Standard
                assert('$g3->users->count() == 0'); // Not Coding Standard

                assert('$g1->groups->count() == 0'); // Not Coding Standard
                assert('$g2->groups->count() == 0'); // Not Coding Standard
                assert('$g3->groups->count() == 0'); // Not Coding Standard

                assert('$r1->role->isSame($r2)');   // Not Coding Standard
                assert('$r2->role->isSame($r3)');   // Not Coding Standard
                assert('$r3->role->id < 0');        // Not Coding Standard
                assert('$r4->role->isSame($r5)');   // Not Coding Standard
                assert('$r5->role->isSame($r6)');   // Not Coding Standard
                assert('$r6->role->id < 0');        // Not Coding Standard

                assert('$u1->role->isSame($r1)');   // Not Coding Standard
                assert('$u2->role->isSame($r4)');   // Not Coding Standard
    //            assert('$u3->role->isSame($r4)'); // Not Coding Standard
                assert('$u4->role->isSame($r4)');   // Not Coding Standard
                assert('$u5->role->id < 0');        // Not Coding Standard
                assert('$u6->role->id < 0');        // Not Coding Standard
            }

            public function testOwnedSecurableItemCreated_Slide2()
            {
                $u1 = User::getByUsername('u1.');

                Yii::app()->user->userModel = $u1;

                $a1 = new Account();
                $a1->name = 'A1.';
                $this->assertTrue($a1->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a1);

                $this->assertEquals(array(
                                        array('R2', 1),
                                        array('R3', 1),
                                    ),
                                    self::getAccountMungeRows($a1));

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());

                $a1->delete();
            }

            public function testOwnedSecurableItemOwnerChanged_Slide3()
            {
                $u1 = User::getByUsername('u1.');
                $u2 = User::getByUsername('u2.');

                Yii::app()->user->userModel = $u1;

                $a1 = new Account();
                $a1->name = 'A1.';
                $this->assertTrue($a1->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a1);

                $this->assertEquals(array(
                                        array('R2', 1),
                                        array('R3', 1),
                                    ),
                                    self::getAccountMungeRows($a1));

                $a1->owner = $u2;
                $this->assertTrue($a1->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemOwnerChanged($a1, $u1);

                $this->assertEquals(array(
                                        array('R5', 1),
                                        array('R6', 1),
                                    ),
                                    self::getAccountMungeRows($a1));

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());

                Yii::app()->user->userModel = $u2;
                $a1->delete();

                $this->assertEverythingHasBeenSetBackToHowItStarted();
            }

            public function testOwnedSecurableItemBeingDeleted_Slide4()
            {
                $u1 = User::getByUsername('u1.');

                Yii::app()->user->userModel = $u1;

                $a1 = new Account();
                $a1->name = 'A1.';
                $this->assertTrue($a1->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a1);

                $this->assertEquals(array(
                                        array('R2', 1),
                                        array('R3', 1),
                                    ),
                                    self::getAccountMungeRows($a1));
                //Called in OwnedSecurableItem::beforeDelete();
                //ReadPermissionsOptimizationUtil::securableItemBeingDeleted($a1);
                $a1->delete();

                $this->assertEquals(array(),
                                    self::getAccountMungeRows($a1));
            }

            public function testUserGivenReadOnOwnedSecurableItem_Slide5()
            {
                $u1 = User::getByUsername('u1.');
                $u2 = User::getByUsername('u2.');
                $u3 = User::getByUsername('u3.');

                Yii::app()->user->userModel = $u1;

                $a1 = new Account();
                $a1->name = 'A1.';
                $this->assertTrue($a1->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a1);

                $this->assertEquals(array(
                                        array('R2', 1),
                                        array('R3', 1),
                                    ),
                                    self::getAccountMungeRows($a1));

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());

                $a1->addPermissions($u2, Permission::READ);
                $this->assertTrue($a1->save());
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForUser($a1, $u2);

                $this->assertEquals(array(
                                        array('R2', 1),
                                        array('R3', 1),
                                        array('R5', 1),
                                        array('R6', 1),
                                        array('U2', 1),
                                    ),
                                    self::getAccountMungeRows($a1));

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());

                $a1->addPermissions($u3, Permission::READ);
                $this->assertTrue($a1->save());
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForUser($a1, $u3);

                $this->assertEquals(array(
                                        array('R2', 1),
                                        array('R3', 1),
                                        array('R5', 2),
                                        array('R6', 2),
                                        array('U2', 1),
                                        array('U3', 1),
                                    ),
                                    self::getAccountMungeRows($a1));

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());

                $a1->delete();
            }

            public function testGroupGivenReadOnOwnedSecurableItem_Slide6()
            {
                $u1  = User::getByUsername('u1.');
                $u2  = User::getByUsername('u2.');
                $u3  = User::getByUsername('u3.');
                $u99 = User::getByUsername('u99.');

                Yii::app()->user->userModel = $u99;

                $g1 = Group::getByName('G1.');
                $g1->users->add($u2);
                $g1->users->add($u3);
                $this->assertTrue($g1->save());

                Yii::app()->user->userModel = $u1;

                $a1 = new Account();
                $a1->name = 'A1.';
                $this->assertTrue($a1->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a1);

                $this->assertEquals(array(
                                        array('R2', 1),
                                        array('R3', 1),
                                    ),
                                    self::getAccountMungeRows($a1));

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());

                $a1->addPermissions($g1, Permission::READ);
                $this->assertTrue($a1->save());
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForGroup($a1, $g1);

                $this->assertEquals(array(
                                        array('G1', 1),
                                        array('R2', 1),
                                        array('R3', 1),
                                        array('R5', 2),
                                        array('R6', 2),
                                    ),
                                    self::getAccountMungeRows($a1));

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());

                $a1->delete();

                $g1->users->removeAll();
                $this->assertTrue($g1->save());
            }

            public function testUserLosesReadOnOwnedSecurableItem_Slide7()
            {
                $u1 = User::getByUsername('u1.');
                $u2 = User::getByUsername('u2.');
                $u3 = User::getByUsername('u3.');

                Yii::app()->user->userModel = $u1;

                $a1 = new Account();
                $a1->name = 'A1.';
                $a1->addPermissions($u2, Permission::READ);
                $a1->addPermissions($u3, Permission::READ);
                $this->assertTrue($a1->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a1);
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForUser($a1, $u2);
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForUser($a1, $u3);

                $this->assertEquals(array(
                                        array('R2', 1),
                                        array('R3', 1),
                                        array('R5', 2),
                                        array('R6', 2),
                                        array('U2', 1),
                                        array('U3', 1),
                                    ),
                                    self::getAccountMungeRows($a1));

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());

                $a1->removePermissions($u2, Permission::READ);
                $this->assertTrue($a1->save());
                ReadPermissionsOptimizationUtil::securableItemLostPermissionsForUser($a1, $u2);

                $this->assertEquals(array(
                                        array('R2', 1),
                                        array('R3', 1),
                                        array('R5', 1),
                                        array('R6', 1),
                                        array('U3', 1),
                                    ),
                                    self::getAccountMungeRows($a1));

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());

                $a1->removePermissions($u3, Permission::READ);
                $this->assertTrue($a1->save());
                ReadPermissionsOptimizationUtil::securableItemLostPermissionsForUser($a1, $u3);

                $this->assertEquals(array(
                                        array('R2', 1),
                                        array('R3', 1),

                                    ),
                                    self::getAccountMungeRows($a1));

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());

                $a1->delete();
            }

            public function testGroupLosesReadOnOwnedSecurableItem_Slide8()
            {
                $u1  = User::getByUsername('u1.');
                $u2  = User::getByUsername('u2.');
                $u3  = User::getByUsername('u3.');
                $u99 = User::getByUsername('u99.');

                Yii::app()->user->userModel = $u99;

                $g1 = Group::getByName('G1.');
                $g1->users->add($u2);
                $g1->users->add($u3);
                $this->assertTrue($g1->save());

                Yii::app()->user->userModel = $u1;

                $a1 = new Account();
                $a1->name = 'A1.';
                $a1->addPermissions($g1, Permission::READ);
                $this->assertTrue($a1->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a1);
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForGroup($a1, $g1);

                $this->assertEquals(array(
                                        array('G1', 1),
                                        array('R2', 1),
                                        array('R3', 1),
                                        array('R5', 2),
                                        array('R6', 2),
                                    ),
                                    self::getAccountMungeRows($a1));

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());

                $a1->removePermissions($g1, Permission::READ);
                $this->assertTrue($a1->save());
                ReadPermissionsOptimizationUtil::securableItemLostPermissionsForGroup($a1, $g1);

                $this->assertEquals(array(
                                        array('R2', 1),
                                        array('R3', 1),
                                    ),
                                    self::getAccountMungeRows($a1));

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());

                $a1->delete();

                $g1->users->removeAll();
                $this->assertTrue($g1->save());
            }

            public function testUserAddedToRole_Slide9()
            {
                $u1  = User::getByUsername('u1.');
                $u2  = User::getByUsername('u2.');
                $u3  = User::getByUsername('u3.');
                $u99 = User::getByUsername('u99.');

                Yii::app()->user->userModel = $u99;

                $u1->role = null;
                $this->assertTrue($u1->save());
                $u2->role = null;
                $this->assertTrue($u2->save());

                //at this point U2 has no role.

                $g1 = Group::getByName('G1.');
                $g1->users->add($u2);
                $this->assertTrue($g1->save());
                $u2->forget();
                $u2  = User::getByUsername('u2.');

                $r1 = Role::getByName('R1.');
                $r4 = Role::getByName('R4.');

                Yii::app()->user->userModel = $u1;

                $a1 = new Account();
                $a1->name = 'A1.';
                $this->assertTrue($a1->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a1);

                $a2 = new Account();
                $a2->name = 'A2.';
                $this->assertTrue($a2->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a2);

                Yii::app()->user->userModel = $u99;

                $a3 = new Account();
                $a3->name = 'A3.';
                $a3->addPermissions($g1, Permission::READ);
                $this->assertTrue($a3->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a3);
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForGroup($a3, $g1);

                $this->assertEquals(array(
                                        array('A3', 'G1', 1),
                                    ),
                                    self::getAccountMungeRows());

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());

                $u1->role = $r1;
                $this->assertTrue($u1->save());
                //Called in $u1->afterSave();
                //ReadPermissionsOptimizationUtil::userAddedToRole($u1);

                $this->assertEquals(array(
                                        array('A1', 'R2', 1),
                                        array('A1', 'R3', 1),
                                        array('A2', 'R2', 1),
                                        array('A2', 'R3', 1),
                                        array('A3', 'G1', 1),
                                    ),
                                    self::getAccountMungeRows());
                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());

                $u2->role = $r4;
                $this->assertTrue($u2->save());

                //Called in $u2->afterSave();
                //ReadPermissionsOptimizationUtil::userAddedToRole($u2);

                $this->assertEquals(array(
                                        array('A1', 'R2', 1),
                                        array('A1', 'R3', 1),
                                        array('A2', 'R2', 1),
                                        array('A2', 'R3', 1),
                                        array('A3', 'G1', 1),
                                        array('A3', 'R5', 1),
                                        array('A3', 'R6', 1),
                                    ),
                                    self::getAccountMungeRows());

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());

                Yii::app()->user->userModel = $u1;
                $a1->delete();
                $a2->delete();

                Yii::app()->user->userModel = $u99;
                $a3->delete();

                $g1->forget();
                $g1 = Group::getByName('G1.');
                $g1->users->removeAll();
                $this->assertTrue($g1->save());
            }

            public function testUserRemovedFromRole_Slide10()
            {
                $u1  = User::getByUsername('u1.');
                $u2  = User::getByUsername('u2.');
                $u3  = User::getByUsername('u3.');
                $u99 = User::getByUsername('u99.');

                Yii::app()->user->userModel = $u99;

                $g1 = Group::getByName('G1.');
                $g1->users->add($u2);
                $this->assertTrue($g1->save());

                $r1 = Role::getByName('R1.');
                $r4 = Role::getByName('R4.');

                Yii::app()->user->userModel = $u1;

                $a1 = new Account();
                $a1->name = 'A1.';
                $this->assertTrue($a1->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a1);

                $a2 = new Account();
                $a2->name = 'A2.';
                $this->assertTrue($a2->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a2);

                Yii::app()->user->userModel = $u99;

                $a3 = new Account();
                $a3->name = 'A3.';
                $a3->addPermissions($g1, Permission::READ);
                $this->assertTrue($a3->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a3);
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForGroup($a3, $g1);

                $this->assertEquals(array(
                                        array('A1', 'R2', 1),
                                        array('A1', 'R3', 1),
                                        array('A2', 'R2', 1),
                                        array('A2', 'R3', 1),
                                        array('A3', 'G1', 1),
                                        array('A3', 'R5', 1),
                                        array('A3', 'R6', 1),
                                    ),
                                    self::getAccountMungeRows());

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());
                //Called in User->beforeSave();
                //ReadPermissionsOptimizationUtil::userBeingRemovedFromRole($u2, $u2->role);
                $u2->role = null;
                $this->assertTrue($u2->save());
                $this->assertEquals(array(
                                        array('A1', 'R2', 1),
                                        array('A1', 'R3', 1),
                                        array('A2', 'R2', 1),
                                        array('A2', 'R3', 1),
                                        array('A3', 'G1', 1),
                                    ),
                                    self::getAccountMungeRows());

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());
                //Called in User->beforeSave();
                //ReadPermissionsOptimizationUtil::userBeingRemovedFromRole($u1, $u1->role);
                $u1->role = null;
                $this->assertTrue($u1->save());

                $this->assertEquals(array(
                                        array('A3', 'G1', 1),
                                    ),
                                    self::getAccountMungeRows());

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());

                Yii::app()->user->userModel = $u1;
                $a1->delete();
                $a2->delete();

                Yii::app()->user->userModel = $u99;
                $a3->delete();

                $g1->users->removeAll();
                $this->assertTrue($g1->save());

                $u1->role = $r1;
                $this->assertTrue($u1->save());

                $u2->role = $r4;
                $this->assertTrue($u2->save());
            }

            public function testUserAddedToGroup_Slide11()
            {
                $u2  = User::getByUsername('u2.');
                $u99 = User::getByUsername('u99.');

                Yii::app()->user->userModel = $u99;

                $g1 = Group::getByName('G1.');

                Yii::app()->user->userModel = $u99;

                $a3 = new Account();
                $a3->name = 'A3.';
                $a3->addPermissions($g1, Permission::READ);
                $this->assertTrue($a3->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a3);
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForGroup($a3, $g1);

                $this->assertEquals(array(
                                        array('A3', 'G1', 1),
                                    ),
                                    self::getAccountMungeRows());

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());
                //Utilize method that is used by user interface to handle removing users from a group.
                $form = new GroupUserMembershipForm();
                $fakePostData = array(
                    'userMembershipData'    => array(0 => $u2->id),
                    'userNonMembershipData' => array()
                );
                $form = GroupUserMembershipFormUtil::setFormFromCastedPost($form, $fakePostData);
                $saved = GroupUserMembershipFormUtil::setMembershipFromForm($form, $g1);
                //This is completed above in GroupUserMembershipFormUtil::setMembershipFromForm
                //$g1->users->add($u2);
                //$this->assertTrue($g1->save());
                //ReadPermissionsOptimizationUtil::userAddedToGroup($g1, $u2);

                $this->assertEquals(array(
                                        array('A3', 'G1', 1),
                                        array('A3', 'R5', 1),
                                        array('A3', 'R6', 1),
                                    ),
                                    self::getAccountMungeRows());

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());

                $a3->delete();

                $g1->users->removeAll();
                $this->assertTrue($g1->save());
            }

            public function testUserRemovedFromGroup_Slide12()
            {
                $u2  = User::getByUsername('u2.');
                $u99 = User::getByUsername('u99.');

                Yii::app()->user->userModel = $u99;

                $g1 = Group::getByName('G1.');
                $g1->users->add($u2);
                $this->assertTrue($g1->save());

                Yii::app()->user->userModel = $u99;

                $a3 = new Account();
                $a3->name = 'A3.';
                $a3->addPermissions($g1, Permission::READ);
                $this->assertTrue($a3->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a3);
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForGroup($a3, $g1);

                $this->assertEquals(array(
                                        array('A3', 'G1', 1),
                                        array('A3', 'R5', 1),
                                        array('A3', 'R6', 1),
                                    ),
                                    self::getAccountMungeRows());

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());

                //Utilize method that is used by user interface to handle removing users from a group.
                $form = new GroupUserMembershipForm();
                $fakePostData = array(
                    'userMembershipData'    => array(),
                    'userNonMembershipData' => array()
                );
                $form = GroupUserMembershipFormUtil::setFormFromCastedPost($form, $fakePostData);
                $saved = GroupUserMembershipFormUtil::setMembershipFromForm($form, $g1);
                //This is completed above in GroupUserMembershipFormUtil::setMembershipFromForm
                //$g1->users->remove($u2);
                //$this->assertTrue($g1->save());
                //ReadPermissionsOptimizationUtil::userRemovedFromGroup($g1, $u2);

                $this->assertEquals(array(
                                        array('A3', 'G1', 1),
                                    ),
                                    self::getAccountMungeRows());

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());

                $a3->delete();
            }

            public function testRoleDeleted_Slide13()
            {
                $u1  = User::getByUsername('u1.');
                $u2  = User::getByUsername('u2.');
                $u99 = User::getByUsername('u99.');

                Yii::app()->user->userModel = $u99;

                $g1 = Group::getByName('G1.');
                $g1->users->add($u2);
                $this->assertTrue($g1->save());

                $r1 = Role::getByName('R1.');
                $r3 = Role::getByName('R3.');
                $r4 = Role::getByName('R4.');

                $u2->role = $r1;
                $this->assertTrue($u2->save());

                Yii::app()->user->userModel = $u1;

                $a1 = new Account();
                $a1->name = 'A1.';
                $this->assertTrue($a1->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a1);

                $a2 = new Account();
                $a2->name = 'A2.';
                $this->assertTrue($a2->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a2);

                Yii::app()->user->userModel = $u99;

                $a3 = new Account();
                $a3->name = 'A3.';
                $a3->addPermissions($g1, Permission::READ);
                $this->assertTrue($a3->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a3);
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForGroup($a3, $g1);

                $this->assertEquals(array(
                                        array('A1', 'R2', 1),
                                        array('A1', 'R3', 1),
                                        array('A2', 'R2', 1),
                                        array('A2', 'R3', 1),
                                        array('A3', 'G1', 1),
                                        array('A3', 'R2', 1),
                                        array('A3', 'R3', 1),
                                    ),
                                    self::getAccountMungeRows());

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());

                $r3->testBeforeDelete();
                //Called in Role->beforeDelete();
                //ReadPermissionsOptimizationUtil::roleBeingDeleted($r3);
                //$r3->delete(); // Not really deleting it, to avoid messing up the ids.

                $this->assertEquals(array(
                                        array('A1', 'R2', 1),
                                        array('A2', 'R2', 1),
                                        array('A3', 'G1', 1),
                                        array('A3', 'R2', 1),
                                    ),
                                    self::getAccountMungeRows());

                //$this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt()); // Can't do this because
                                                                                  // of not really deleting
                Yii::app()->user->userModel = $u1;                                // the role.
                $a1->delete();
                $a2->delete();

                Yii::app()->user->userModel = $u99;
                $a3->delete();

                $u2->role = $r4;
                $this->assertTrue($u2->save());

                $g1->users->removeAll();
                $this->assertTrue($g1->save());
            }

            public function testParentRoleRemovedFromRole_Slide14()
            {
                $u1  = User::getByUsername('u1.');
                $u2  = User::getByUsername('u2.');
                $u99 = User::getByUsername('u99.');

                Yii::app()->user->userModel = $u99;

                $g1 = Group::getByName('G1.');
                $g1->users->add($u2);
                $this->assertTrue($g1->save());

                $r1 = Role::getByName('R1.');
                $r2 = Role::getByName('R2.');
                $r3 = Role::getByName('R3.');
                $r4 = Role::getByName('R4.');

                $u2->role = $r1;
                $this->assertTrue($u2->save());

                Yii::app()->user->userModel = $u1;

                $a1 = new Account();
                $a1->name = 'A1.';
                $this->assertTrue($a1->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a1);

                $a2 = new Account();
                $a2->name = 'A2.';
                $this->assertTrue($a2->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a2);

                Yii::app()->user->userModel = $u99;

                $a3 = new Account();
                $a3->name = 'A3.';
                $a3->addPermissions($u1, Permission::READ);
                $this->assertTrue($a3->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a3);
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForUser($a3, $u1);

                $a4 = new Account();
                $a4->name = 'A4.';
                $a4->addPermissions($g1, Permission::READ);
                $this->assertTrue($a4->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a4);
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForGroup($a4, $g1);

                $this->assertEquals(array(
                                        array('A1', 'R2', 1),
                                        array('A1', 'R3', 1),
                                        array('A2', 'R2', 1),
                                        array('A2', 'R3', 1),
                                        array('A3', 'R2', 1),
                                        array('A3', 'R3', 1),
                                        array('A3', 'U1', 1),
                                        array('A4', 'G1', 1),
                                        array('A4', 'R2', 1),
                                        array('A4', 'R3', 1),
                                    ),
                                    self::getAccountMungeRows());

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());
                //This is being called in Role->afterSave().
                //ReadPermissionsOptimizationUtil::roleParentBeingRemoved($r2);
                //Reversing the way $r2 and $r3 are detached from each other in order to accomodate how it is processed
                //in Role->afterSave();
                //$r3->roles->remove($r2);
                //$this->assertTrue($r3->save());
                $r2->role = null;
                $this->assertTrue($r2->save());
                RedBeanModelsCache::forgetAll();
                $r2 = Role::getByName('R2.');
                $this->assertTrue($r2->role->id < 0);

                $this->assertEquals(array(
                                        array('A1', 'R2', 1),
                                        array('A2', 'R2', 1),
                                        array('A3', 'R2', 1),
                                        array('A3', 'U1', 1),
                                        array('A4', 'G1', 1),
                                        array('A4', 'R2', 1),
                                    ),
                                    self::getAccountMungeRows());

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());

                Yii::app()->user->userModel = $u1;
                $a1->delete();
                $a2->delete();

                Yii::app()->user->userModel = $u99;
                $a3->delete();
                $a4->delete();

                $r3->roles->add($r2);
                $this->assertTrue($r3->save());

                $u2->role = $r4;
                $this->assertTrue($u2->save());

                $g1->users->removeAll();
                $this->assertTrue($g1->save());
            }

            public function testParentRoleSetOnRole_SlideNone()
            {
                $u1  = User::getByUsername('u1.');
                $u2  = User::getByUsername('u2.');
                $u99 = User::getByUsername('u99.');

                Yii::app()->user->userModel = $u99;

                $g1 = Group::getByName('G1.');
                $g1->users->add($u2);
                $this->assertTrue($g1->save());

                $r1 = Role::getByName('R1.');
                $r2 = Role::getByName('R2.');
                $r3 = Role::getByName('R3.');
                $r4 = Role::getByName('R4.');

                $u2->role = $r1;
                $this->assertTrue($u2->save());

                $r2->role = null;
                $this->assertTrue($r2->save());

                Yii::app()->user->userModel = $u1;

                $a1 = new Account();
                $a1->name = 'A1.';
                $this->assertTrue($a1->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a1);

                $a2 = new Account();
                $a2->name = 'A2.';
                $this->assertTrue($a2->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a2);

                Yii::app()->user->userModel = $u99;

                $a3 = new Account();
                $a3->name = 'A3.';
                $a3->addPermissions($u1, Permission::READ);
                $this->assertTrue($a3->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a3);
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForUser($a3, $u1);

                $a4 = new Account();
                $a4->name = 'A4.';
                $a4->addPermissions($g1, Permission::READ);
                $this->assertTrue($a4->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a4);
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForGroup($a4, $g1);

                $this->assertEquals(array(
                                        array('A1', 'R2', 1),
                                        array('A2', 'R2', 1),
                                        array('A3', 'R2', 1),
                                        array('A3', 'U1', 1),
                                        array('A4', 'G1', 1),
                                        array('A4', 'R2', 1),
                                    ),
                                    self::getAccountMungeRows());

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());
                //Reversing how $r2 and $r3 get connected in order to support how the Role->afterSave() manages this with
                //the read optimization.
                //$r3->roles->add($r2);
                //$this->assertTrue($r3->save());
                $r2->role = $r3;
                $this->assertTrue($r2->save());
                RedBeanModelsCache::forgetAll();
                $r2 = Role::getByName('R2.');
                $this->assertTrue($r2->role->isSame($r3));
                //Role->afterSave() is where this is being called from.
                //ReadPermissionsOptimizationUtil::roleParentSet($r2);

                $this->assertEquals(array(
                                        array('A1', 'R2', 1),
                                        array('A1', 'R3', 1),
                                        array('A2', 'R2', 1),
                                        array('A2', 'R3', 1),
                                        array('A3', 'R2', 1),
                                        array('A3', 'R3', 1),
                                        array('A3', 'U1', 1),
                                        array('A4', 'G1', 1),
                                        array('A4', 'R2', 1),
                                        array('A4', 'R3', 1),
                                    ),
                                    self::getAccountMungeRows());

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());

                Yii::app()->user->userModel = $u1;
                $a1->delete();
                $a2->delete();

                Yii::app()->user->userModel = $u99;
                $a3->delete();
                $a4->delete();

                $r2->role = $r3;
                $this->assertTrue($r2->save());

                $u2->role = $r4;
                $this->assertTrue($u2->save());

                $g1->users->removeAll();
                $this->assertTrue($g1->save());
            }

            public function testGroupDeleted_Slide15()
            {
                $u1  = User::getByUsername('u1.');
                $u2  = User::getByUsername('u2.');
                $u3  = User::getByUsername('u3.');
                $u99 = User::getByUsername('u99.');

                Yii::app()->user->userModel = $u99;

                $g1 = Group::getByName('G1.');
                $g2 = Group::getByName('G2.');

                $g1->users->add($u1);
                $g1->users->add($u2);
                $g1->users->add($u3);
                $this->assertTrue($g1->save());

                $g1->groups->add($g2);
                $this->assertTrue($g1->save());

                $r1 = Role::getByName('R1.');
                $r4 = Role::getByName('R4.');
                $r5 = Role::getByName('R5.');
                $r6 = Role::getByName('R6.');

                $u2->role = $r1;
                $this->assertTrue($u2->save());

                $r5->role = null;
                $this->assertTrue($r5->save());

                Yii::app()->user->userModel = $u99;

                $a1 = new Account();
                $a1->name = 'A1.';
                $a1->addPermissions($g1, Permission::READ);
                $this->assertTrue($a1->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a1);
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForGroup($a1, $g1);

                $a2 = new Account();
                $a2->name = 'A2.';
                $a2->addPermissions($g1, Permission::READ);
                $this->assertTrue($a2->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a2);
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForGroup($a2, $g1);

                $a3 = new Account();
                $a3->name = 'A3.';
                $a3->addPermissions($g1, Permission::READ);
                $a3->addPermissions($g2, Permission::READ);
                $this->assertTrue($a3->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a3);
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForGroup($a3, $g1);
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForGroup($a3, $g2);

                $this->assertEquals(array(
                                        array('A1', 'G1', 1),
                                        array('A1', 'G2', 1),
                                        array('A1', 'R2', 2),
                                        array('A1', 'R3', 2),
                                        array('A1', 'R5', 1),
                                        array('A2', 'G1', 1),
                                        array('A2', 'G2', 1),
                                        array('A2', 'R2', 2),
                                        array('A2', 'R3', 2),
                                        array('A2', 'R5', 1),
                                        array('A3', 'G1', 1),
                                        array('A3', 'G2', 2),
                                        array('A3', 'R2', 2),
                                        array('A3', 'R3', 2),
                                        array('A3', 'R5', 1),
                                    ),
                                    self::getAccountMungeRows());

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());

                $g1->testBeforeDelete();
                //Called in Group->beforeDelete();
                //ReadPermissionsOptimizationUtil::groupBeingDeleted($g1);
                // $g1->delete(); // Not really deleting it, to avoid messing up the ids.

                $this->assertEquals(array(
                                        array('A3', 'G2', 1),
                                    ),
                                    self::getAccountMungeRows());

                //$this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt()); // Can't do this because
                                                                                  // of not really deleting
                Yii::app()->user->userModel = $u99;                               // the group.
                $a1->delete();
                $a2->delete();
                $a3->delete();

                $r5->role = $r6;
                $this->assertTrue($r5->save());

                $u2->role = $r4;
                $this->assertTrue($u2->save());

                $g1->groups->removeAll();
                $this->assertTrue($g1->save());

                $g1->users->removeAll();
                $this->assertTrue($g1->save());
            }

            public function testUserDeleted_Slide16()
            {
                $u1  = User::getByUsername('u1.');
                $u99 = User::getByUsername('u99.');

                Yii::app()->user->userModel = $u99;

                $g1 = Group::getByName('G1.');
                $g2 = Group::getByName('G2.');
                $g3 = Group::getByName('G3.');

                $g1->users->add($u1);
                $this->assertTrue($g1->save());

                $g2->groups->add($g1);
                $this->assertTrue($g2->save());

                $g1->groups->add($g3);
                $this->assertTrue($g1->save());

                Yii::app()->user->userModel = $u1;

                $a1 = new Account();
                $a1->name = 'A1.';
                $this->assertTrue($a1->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a1);

                Yii::app()->user->userModel = $u99;

                $a2 = new Account();
                $a2->name = 'A2.';
                $a2->addPermissions($u1, Permission::READ);
                $this->assertTrue($a2->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a2);
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForUser($a2, $u1);

                $a3 = new Account();
                $a3->name = 'A3.';
                $a3->addPermissions($g1, Permission::READ);
                $this->assertTrue($a3->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a3);
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForGroup($a3, $g1);

                $this->assertEquals(array(
                                        array('A1', 'R2', 1),
                                        array('A1', 'R3', 1),
                                        array('A2', 'R2', 1),
                                        array('A2', 'R3', 1),
                                        array('A2', 'U1', 1),
                                        array('A3', 'G1', 1),
                                        array('A3', 'G3', 1),
                                        array('A3', 'R2', 1),
                                        array('A3', 'R3', 1),
                                    ),
                                    self::getAccountMungeRows());

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());
                $u1->testBeforeDelete();
                //Called in User->beforeDelete();
                //ReadPermissionsOptimizationUtil::userBeingDeleted($u1);
                // $u1->delete(); // Not really deleting it, to avoid messing up the ids.

                $this->assertEquals(array(
                                        array('A3', 'G1', 1),
                                        array('A3', 'G3', 1),
                                    ),
                                    self::getAccountMungeRows());

                //$this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt()); // Can't do this because
                                                                                  // of not really deleting
                Yii::app()->user->userModel = $u1;                                // the group.
                $a1->delete();

                Yii::app()->user->userModel = $u99;
                $a2->delete();
                $a3->delete();

                $g1->users->removeAll();
                $g1->groups->removeAll();
                $this->assertTrue($g1->save());

                $g2->groups->removeall();
                $this->assertTrue($g2->save());
            }

            public function testGroupAddedToGroup_Slide17()
            {
                $u2  = User::getByUsername('u2.');
                $u3  = User::getByUsername('u3.');
                $u4  = User::getByUsername('u4.');
                $u6  = User::getByUsername('u6.');
                $u99 = User::getByUsername('u99.');

                Yii::app()->user->userModel = $u99;

                $g1 = Group::getByName('G1.');
                $g2 = Group::getByName('G2.');

                $g1->users->add($u3);
                $g1->users->add($u6);
                $this->assertTrue($g1->save());

                $g2->users->add($u4);
                $this->assertTrue($g2->save());

                $r1 = Role::getByName('R1.');
                $r2 = Role::getByName('R2.');
                $r3 = Role::getByName('R3.');
                $r4 = Role::getByName('R4.');
                $r5 = Role::getByName('R5.');
                $r6 = Role::getByName('R6.');

                $u2->role = $r2;
                $this->assertTrue($u2->save());

                $u3->role = $r1;
                $this->assertTrue($u3->save());

                $r5->role = $r3;
                $this->assertTrue($r5->save());

                Yii::app()->user->userModel = $u99;

                $a1 = new Account();
                $a1->name = 'A1.';
                $a1->addPermissions($g1, Permission::READ);
                $this->assertTrue($a1->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a1);
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForGroup($a1, $g1);

                $a2 = new Account();
                $a2->name = 'A2.';
                $a2->addPermissions($g2, Permission::READ);
                $this->assertTrue($a2->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a2);
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForGroup($a2, $g2);

                $this->assertEquals(array(
                                        array('A1', 'G1', 1),
                                        array('A1', 'R2', 1),
                                        array('A1', 'R3', 1),
                                        array('A2', 'G2', 1),
                                        array('A2', 'R3', 1),
                                        array('A2', 'R5', 1),
                                    ),
                                    self::getAccountMungeRows());

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());

                //Reverse how $g1 is added to $g2 to accomodate how groupAddedToGroup is implemented in afterSave
                //$g2->groups->add($g1);
                //$this->assertTrue($g2->save());
                $g1->group = $g2;
                $this->assertTrue($g1->save());
                RedBeanModelsCache::forgetAll();
                $g1 = Group::getByName('G1.');
                $g2 = Group::getByName('G2.');
                $this->assertTrue($g1->group->isSame($g2));
                $this->assertTrue($g2->groups->contains($g1));
                //Called in $g1->afterSave();
                //ReadPermissionsOptimizationUtil::groupAddedToGroup($g1);

                $this->assertEquals(array(
                                        array('A1', 'G1', 1),
                                        array('A1', 'R2', 1),
                                        array('A1', 'R3', 1),
                                        array('A2', 'G1', 1),
                                        array('A2', 'G2', 1),
                                        array('A2', 'R2', 1),
                                        array('A2', 'R3', 2),
                                        array('A2', 'R5', 1),
                                    ),
                                    self::getAccountMungeRows());

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());

                Yii::app()->user->userModel = $u99;
                $a1->delete();
                $a2->delete();

                $r5->role = $r6;
                $this->assertTrue($r5->save());

                $u3->role = $r4;
                $this->assertTrue($u3->save());

                $u2->role = $r4;
                $this->assertTrue($u2->save());

                $this->assertTrue($g2->groups->contains($g1)); //Testing it contains G1.
                $this->assertTrue($g1->group->isSame($g2));
                $g2->users->removeAll();
                $g2->groups->removeAll();
                $this->assertTrue($g2->save());
                $this->assertFalse($g2->groups->contains($g1));
                $this->assertTrue($g1->group->isSame($g2)); //BUG - This should not be true, but something with caching
                                                            //is causing it to be.  Need to ->forget to then show the
                                                            //right value. This needs to be fixed eventually.

                $g1->forget();    //Doing this properly clears out $g1->group->isSame($g2), but we shouldn't have to do that.
                $g1 = Group::getByName('G1.');
                $this->assertFalse($g1->group->isSame($g2));
                $this->assertEquals(0, $g2->groups->count());

                $g1->users->removeAll();
                $this->assertTrue($g1->save());
                $this->assertFalse($g2->groups->contains($g1)); //G1 should still no longer be contained/
                $this->assertFalse($g1->group->isSame($g2));
                $this->assertEquals(0, $g2->groups->count());

                $g2->forget();
                $g2 = Group::getByName('G2.');
                $this->assertFalse($g2->groups->contains($g1));
                $this->assertEquals(0, $g2->groups->count());
            }

            public function testGroupRemovedFromGroup_Slide18()
            {
                $u2  = User::getByUsername('u2.');
                $u3  = User::getByUsername('u3.');
                $u4  = User::getByUsername('u4.');
                $u6  = User::getByUsername('u6.');
                $u99 = User::getByUsername('u99.');

                Yii::app()->user->userModel = $u99;

                $g1 = Group::getByName('G1.');
                $g2 = Group::getByName('G2.');

                $g1->users->add($u3);
                $g1->users->add($u6);
                $this->assertTrue($g1->save());

                $g2->users->add($u4);
                $g2->groups->add($g1);
                $this->assertTrue($g2->save());

                $r1 = Role::getByName('R1.');
                $r2 = Role::getByName('R2.');
                $r3 = Role::getByName('R3.');
                $r4 = Role::getByName('R4.');
                $r5 = Role::getByName('R5.');
                $r6 = Role::getByName('R6.');

                $u2->role = $r2;
                $this->assertTrue($u2->save());

                $u3->role = $r1;
                $this->assertTrue($u3->save());

                $r5->role = $r3;
                $this->assertTrue($r5->save());

                Yii::app()->user->userModel = $u99;

                $a1 = new Account();
                $a1->name = 'A1.';
                $a1->addPermissions($g1, Permission::READ);
                $this->assertTrue($a1->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a1);
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForGroup($a1, $g1);

                $a2 = new Account();
                $a2->name = 'A2.';
                $a2->addPermissions($g2, Permission::READ);
                $this->assertTrue($a2->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a2);
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForGroup($a2, $g2);

                $this->assertEquals(array(
                                        array('A1', 'G1', 1),
                                        array('A1', 'R2', 1),
                                        array('A1', 'R3', 1),
                                        array('A2', 'G1', 1),
                                        array('A2', 'G2', 1),
                                        array('A2', 'R2', 1),
                                        array('A2', 'R3', 2),
                                        array('A2', 'R5', 1),
                                    ),
                                    self::getAccountMungeRows());

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());

                //Called in $g1::beforeSave();
                //ReadPermissionsOptimizationUtil::groupBeingRemovedFromGroup($g1, $g2);
                //Reversing the way in which we remove $g1 from $g2 in order to accomodate the API
                //$g2->groups->remove($g1);
                //$this->assertTrue($g2->save());
                $this->assertEquals($g2, $g1->group);
                $g1->group = null;
                $this->assertTrue($g1->save());
                $this->assertEquals(array(
                                        array('A1', 'G1', 1),
                                        array('A1', 'R2', 1),
                                        array('A1', 'R3', 1),
                                        array('A2', 'G2', 1),
                                        array('A2', 'R3', 1),
                                        array('A2', 'R5', 1),
                                    ),
                                    self::getAccountMungeRows());

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());

                Yii::app()->user->userModel = $u99;
                $a1->delete();
                $a2->delete();

                $r5->role = $r6;
                $this->assertTrue($r5->save());

                $u3->role = $r4;
                $this->assertTrue($u3->save());

                $u2->role = $r4;
                $this->assertTrue($u2->save());

                $g2->users->removeAll();
                $this->assertTrue($g2->save());

                $g1->users->removeAll();
                $this->assertTrue($g1->save());
            }

            public function testUserAddedToRoleWhereUserIsMemberOfGroupWithChildrenGroups_Slide19()
            {
                $u1  = User::getByUsername('u1.');
                $u99 = User::getByUsername('u99.');

                Yii::app()->user->userModel = $u99;

                $u1->role = null;
                $this->assertTrue($u1->save());

                $g1 = Group::getByName('G1.');
                $g2 = Group::getByName('G2.');
                $g3 = Group::getByName('G3.');
                $g1->groups->add($g2);
                $this->assertTrue($g1->save());
                $g2->groups->add($g3);
                $this->assertTrue($g2->save());
                $g3->users->add($u1);
                $this->assertTrue($g3->save());

                $u1->forget(); //Forget the user, so the user knows what groups it is part of.
                $u1  = User::getByUsername('u1.');

                $r1 = Role::getByName('R1.');
                $r2 = Role::getByName('R2.');
                $r3 = Role::getByName('R3.');

                $a1 = new Account();
                $a1->name = 'A1.';
                $a1->addPermissions($g1, Permission::READ);
                $this->assertTrue($a1->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a1);

                $a2 = new Account();
                $a2->name = 'A2.';
                $a2->addPermissions($g2, Permission::READ);
                $this->assertTrue($a2->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a2);

                $a3 = new Account();
                $a3->name = 'A3.';
                $a3->addPermissions($g3, Permission::READ);
                $this->assertTrue($a3->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a3);

                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForGroup($a1, $g1);
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForGroup($a2, $g2);
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForGroup($a3, $g3);

                $this->assertEquals(array(
                                        array('A1', 'G1', 1),
                                        array('A1', 'G2', 1),
                                        array('A1', 'G3', 1),
                                        array('A2', 'G2', 1),
                                        array('A2', 'G3', 1),
                                        array('A3', 'G3', 1),
                                        ),
                                    self::getAccountMungeRows());

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());

                $u1->role = $r1;
                $this->assertTrue($u1->save());
                //Called in $u1->afterSave();
                //ReadPermissionsOptimizationUtil::userAddedToRole($u1);

                $r1->forget(); //Forget R1 so when it is utilized below, it will know that u1 is a member.

                $this->assertEquals(array(
                                        array('A1', 'G1', 1),
                                        array('A1', 'G2', 1),
                                        array('A1', 'G3', 1),
                                        array('A1', 'R2', 1),
                                        array('A1', 'R3', 1),
                                        array('A2', 'G2', 1),
                                        array('A2', 'G3', 1),
                                        array('A2', 'R2', 1),
                                        array('A2', 'R3', 1),
                                        array('A3', 'G3', 1),
                                        array('A3', 'R2', 1),
                                        array('A3', 'R3', 1),
                                    ),
                                    self::getAccountMungeRows());

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());

                $a1->delete();
                $a2->delete();
                $a3->delete();


                $g1->group = null;
                $this->assertTrue($g1->save());

                $g2->group = null;
                $this->assertTrue($g2->save());

                $g3->forget();
                $g3 = Group::getByName('G3.');
                $g3->group = null;
                $g3->users->removeAll();
                $this->assertTrue($g3->save());
            }

            public function testUserAddedToRoleWhereUserIsMemberOfGroupWithChildrenGroups_Slide20()
            {
                $u1  = User::getByUsername('u1.');
                $u99 = User::getByUsername('u99.');

                Yii::app()->user->userModel = $u99;

                $u1->role = null;
                $this->assertTrue($u1->save());

                $g1 = Group::getByName('G1.');
                $g2 = Group::getByName('G2.');
                $g3 = Group::getByName('G3.');
                $g1->groups->add($g2);
                $this->assertTrue($g1->save());
                $g2->groups->add($g3);
                $this->assertTrue($g2->save());
                $g3->users->add($u1);
                $this->assertTrue($g3->save());

                $u1->forget(); //Forget the user, so the user knows what groups it is part of.
                $u1  = User::getByUsername('u1.');

                $r1 = Role::getByName('R1.');
                $r2 = Role::getByName('R2.');
                $r3 = Role::getByName('R3.');

                $a1 = new Account();
                $a1->name = 'A1.';
                $a1->addPermissions($g1, Permission::READ);
                $this->assertTrue($a1->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a1);

                $a2 = new Account();
                $a2->name = 'A2.';
                $a2->addPermissions($g2, Permission::READ);
                $this->assertTrue($a2->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a2);

                $a3 = new Account();
                $a3->name = 'A3.';
                $a3->addPermissions($g3, Permission::READ);
                $this->assertTrue($a3->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a3);

                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForGroup($a1, $g1);
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForGroup($a2, $g2);
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForGroup($a3, $g3);

                $u1->role = $r1;
                $this->assertTrue($u1->save());
                //Called in $u1->afterSave();
                //ReadPermissionsOptimizationUtil::userAddedToRole($u1);

                $r1->forget(); //Forget R1 so when it is utilized below, it will know that u1 is a member.

                $this->assertEquals(array(
                                        array('A1', 'G1', 1),
                                        array('A1', 'G2', 1),
                                        array('A1', 'G3', 1),
                                        array('A1', 'R2', 1),
                                        array('A1', 'R3', 1),
                                        array('A2', 'G2', 1),
                                        array('A2', 'G3', 1),
                                        array('A2', 'R2', 1),
                                        array('A2', 'R3', 1),
                                        array('A3', 'G3', 1),
                                        array('A3', 'R2', 1),
                                        array('A3', 'R3', 1),
                                    ),
                                    self::getAccountMungeRows());

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());

                $u1->forget(); //Forget the user, so the user knows what groups it is part of.
                $u1  = User::getByUsername('u1.');
                $u1->role = null;
                $this->assertTrue($u1->save());

                RedBeanModelsCache::forgetAll();

                $this->assertEquals(array(
                                        array('A1', 'G1', 1),
                                        array('A1', 'G2', 1),
                                        array('A1', 'G3', 1),
                                        array('A2', 'G2', 1),
                                        array('A2', 'G3', 1),
                                        array('A3', 'G3', 1),
                                        ),
                                    self::getAccountMungeRows());

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());

                $a1->delete();
                $a2->delete();
                $a3->delete();


                $g1->group = null;
                $this->assertTrue($g1->save());

                $g2->group = null;
                $this->assertTrue($g2->save());

                $g3->forget();
                $g3 = Group::getByName('G3.');
                $g3->group = null;
                $g3->users->removeAll();
                $this->assertTrue($g3->save());

                $r1 = Role::getByName('R1.');
                $u1->role = $r1;
                $this->assertTrue($u1->save());
            }

            public function testUserAddedToGroup_Slide21()
            {
                $u2  = User::getByUsername('u2.');
                $u99 = User::getByUsername('u99.');

                Yii::app()->user->userModel = $u99;

                $g1 = Group::getByName('G1.');
                $g2 = Group::getByName('G2.');
                $g3 = Group::getByName('G3.');
                $g3->groups->add($g2);
                $this->assertTrue($g3->save());
                $g2->groups->add($g1);
                $this->assertTrue($g2->save());

                Yii::app()->user->userModel = $u99;

                $a3 = new Account();
                $a3->name = 'A3.';
                $a3->addPermissions($g1, Permission::READ);
                $this->assertTrue($a3->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a3);

                $a2 = new Account();
                $a2->name = 'A2.';
                $a2->addPermissions($g2, Permission::READ);
                $this->assertTrue($a2->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a2);

                $a1 = new Account();
                $a1->name = 'A1.';
                $a1->addPermissions($g3, Permission::READ);
                $this->assertTrue($a1->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a1);

                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForGroup($a3, $g1);
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForGroup($a2, $g2);
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForGroup($a1, $g3);

                $this->assertEquals(array(
                                        array('A1', 'G1', 1),
                                        array('A1', 'G2', 1),
                                        array('A1', 'G3', 1),
                                        array('A2', 'G1', 1),
                                        array('A2', 'G2', 1),
                                        array('A3', 'G1', 1),
                                    ),
                                    self::getAccountMungeRows());

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());
                //Utilize method that is used by user interface to handle removing users from a group.
                $form = new GroupUserMembershipForm();
                $fakePostData = array(
                    'userMembershipData'    => array(0 => $u2->id),
                    'userNonMembershipData' => array()
                );
                $form = GroupUserMembershipFormUtil::setFormFromCastedPost($form, $fakePostData);
                $saved = GroupUserMembershipFormUtil::setMembershipFromForm($form, $g1);
                //This is completed above in GroupUserMembershipFormUtil::setMembershipFromForm
                //$g1->users->add($u2);
                //$this->assertTrue($g1->save());
                //ReadPermissionsOptimizationUtil::userAddedToGroup($g1, $u2);

                $this->assertEquals(array(
                                        array('A1', 'G1', 1),
                                        array('A1', 'G2', 1),
                                        array('A1', 'G3', 1),
                                        array('A1', 'R5', 1),
                                        array('A1', 'R6', 1),
                                        array('A2', 'G1', 1),
                                        array('A2', 'G2', 1),
                                        array('A2', 'R5', 1),
                                        array('A2', 'R6', 1),
                                        array('A3', 'G1', 1),
                                        array('A3', 'R5', 1),
                                        array('A3', 'R6', 1),
                                    ),
                                    self::getAccountMungeRows());

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());

                $a1->delete();
                $a2->delete();
                $a3->delete();

                $g1->group = null;
                $g1->users->removeAll();
                $this->assertTrue($g1->save());

                $g2->group = null;
                $this->assertTrue($g2->save());

                $g3->group = null;
                $this->assertTrue($g3->save());
            }

            public function testUserAddedToGroup_Slide22()
            {
                $u2  = User::getByUsername('u2.');
                $u99 = User::getByUsername('u99.');

                Yii::app()->user->userModel = $u99;

                $g1 = Group::getByName('G1.');
                $g2 = Group::getByName('G2.');
                $g3 = Group::getByName('G3.');
                $g3->groups->add($g2);
                $this->assertTrue($g3->save());
                $g2->groups->add($g1);
                $this->assertTrue($g2->save());

                Yii::app()->user->userModel = $u99;

                $a3 = new Account();
                $a3->name = 'A3.';
                $a3->addPermissions($g1, Permission::READ);
                $this->assertTrue($a3->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a3);

                $a2 = new Account();
                $a2->name = 'A2.';
                $a2->addPermissions($g2, Permission::READ);
                $this->assertTrue($a2->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a2);

                $a1 = new Account();
                $a1->name = 'A1.';
                $a1->addPermissions($g3, Permission::READ);
                $this->assertTrue($a1->save());
                //Called in OwnedSecurableItem::afterSave();
                //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($a1);

                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForGroup($a3, $g1);
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForGroup($a2, $g2);
                ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForGroup($a1, $g3);

                //Utilize method that is used by user interface to handle removing users from a group.
                $form = new GroupUserMembershipForm();
                $fakePostData = array(
                    'userMembershipData'    => array(0 => $u2->id),
                    'userNonMembershipData' => array()
                );
                $form = GroupUserMembershipFormUtil::setFormFromCastedPost($form, $fakePostData);
                $saved = GroupUserMembershipFormUtil::setMembershipFromForm($form, $g1);
                //This is completed above in GroupUserMembershipFormUtil::setMembershipFromForm
                //$g1->users->add($u2);
                //$this->assertTrue($g1->save());
                //ReadPermissionsOptimizationUtil::userAddedToGroup($g1, $u2);

                $this->assertEquals(array(
                                        array('A1', 'G1', 1),
                                        array('A1', 'G2', 1),
                                        array('A1', 'G3', 1),
                                        array('A1', 'R5', 1),
                                        array('A1', 'R6', 1),
                                        array('A2', 'G1', 1),
                                        array('A2', 'G2', 1),
                                        array('A2', 'R5', 1),
                                        array('A2', 'R6', 1),
                                        array('A3', 'G1', 1),
                                        array('A3', 'R5', 1),
                                        array('A3', 'R6', 1),
                                    ),
                                    self::getAccountMungeRows());

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());


                //Utilize method that is used by user interface to handle removing users from a group.
                $form = new GroupUserMembershipForm();
                $fakePostData = array(
                    'userMembershipData'    => array(),
                    'userNonMembershipData' => array()
                );
                $form = GroupUserMembershipFormUtil::setFormFromCastedPost($form, $fakePostData);
                $saved = GroupUserMembershipFormUtil::setMembershipFromForm($form, $g1);
                //This is completed above in GroupUserMembershipFormUtil::setMembershipFromForm
                //$g1->users->remove($u2);
                //$this->assertTrue($g1->save());
                //ReadPermissionsOptimizationUtil::userRemovedFromGroup($g1, $u2);

                $this->assertEquals(array(
                                        array('A1', 'G1', 1),
                                        array('A1', 'G2', 1),
                                        array('A1', 'G3', 1),
                                        array('A2', 'G1', 1),
                                        array('A2', 'G2', 1),
                                        array('A3', 'G1', 1),
                                    ),
                                    self::getAccountMungeRows());

                $this->assertTrue(self::accountMungeDoesntChangeWhenRebuilt());

                $a1->delete();
                $a2->delete();
                $a3->delete();

                $g1->group = null;
                $this->assertTrue($g1->save());

                $g2->group = null;
                $this->assertTrue($g2->save());

                $g3->group = null;
                $this->assertTrue($g3->save());
            }
        }
    }
?>
