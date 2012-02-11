<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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

    class GroupUserMembershipFormUtilTest extends BaseTest
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

        public function testMakeFormFromGroup()
        {
            $user = UserTestHelper::createBasicUser('Billy');
            $billId = $user->id;
            unset($user);
            $user = User::getById($billId);
            $this->assertEquals('billy', $user->username);
            $user = UserTestHelper::createBasicUser('Jimmy');
            $jimId = $user->id;
            unset($user);
            $user = User::getById($jimId);
            $this->assertEquals('jimmy', $user->username);
            $users = User::GetAll();
            $allUsers = array();
            foreach ($users as $user)
            {
                $allUsers[$user->id] = strval($user);
            }
            $this->assertEquals(3, count($allUsers));
            $a = new Group();
            $a->name = 'JJJ';
            $this->assertTrue($a->save());
            $this->assertEquals(0, $a->users ->count());
            $this->assertEquals(0, $a->groups->count());
            $form = GroupUserMembershipFormUtil::makeFormFromGroup($a);
            $this->assertEquals(array(), $form->userMembershipData);
            $this->assertEquals($allUsers, $form->userNonMembershipData);
        }

        /**
         * @depends testMakeFormFromGroup
         */
        public function testSetFormFromPostAndSetMembership()
        {
            $bill = User::getByUsername('billy');
            $jim  = User::getByUsername('jimmy');
            $fakePostData = array(
                'userMembershipData'    => array(0 => $bill->id),
                'userNonMembershipData' => array(0 => $jim->id)
            );
            $form = new GroupUserMembershipForm();
            $this->assertEmpty($form->userMembershipData);
            $this->assertEmpty($form->userNonMembershipData);
            $form = GroupUserMembershipFormUtil::setFormFromCastedPost($form, $fakePostData);
            $compare1 = array(
                $bill->id => strval($bill)
            );
            $this->assertEquals($compare1, $form->userMembershipData);
            $this->assertEquals(null, $form->userNonMembershipData);
            $group = Group::getByName('JJJ');
            $this->assertEquals('JJJ', $group->name);
            $saved = GroupUserMembershipFormUtil::setMembershipFromForm($form, $group);
            $this->assertTrue($saved);
            $group->forget();
            $group = Group::getByName('JJJ');
            $this->assertEquals(1, $group->users ->count());
            $this->assertEquals(0, $group->groups ->count());
            $fakePostData = array(
                'userMembershipData'    => array(0 => $bill->id, 1 => $jim->id),
                'userNonMembershipData' => array(),
            );
            $form = new GroupUserMembershipForm();
            $this->assertEmpty($form->userMembershipData);
            $this->assertEmpty($form->userNonMembershipData);
            $form = GroupUserMembershipFormUtil::setFormFromCastedPost($form, $fakePostData);
            $compare1 = array(
                $bill->id => strval($bill),
                $jim->id => strval($jim)
            );
            $this->assertEquals($compare1, $form->userMembershipData);
            $group = Group::getByName('JJJ');
            $this->assertEquals('JJJ', $group->name);
            $saved = GroupUserMembershipFormUtil::setMembershipFromForm($form, $group);
            $this->assertTrue($saved);
            $group->forget();
            $group = Group::getByName('JJJ');
            $this->assertEquals(2, $group->users ->count());
            $this->assertEquals(0, $group->groups ->count());
        }
    }
?>
