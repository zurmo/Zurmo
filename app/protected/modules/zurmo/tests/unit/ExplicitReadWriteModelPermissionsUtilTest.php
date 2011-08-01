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

    class ExplicitReadWriteModelPermissionsUtilTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $everyoneGroup        = Group::getByName(Group::EVERYONE_GROUP_NAME);
            assert($everyoneGroup->save());
            $group1 = new Group();
            $group1->name = 'Group1';
            assert($group1->save());

            $group2 = new Group();
            $group2->name = 'Group2';
            assert($group2->save());

            $group3 = new Group();
            $group3->name = 'Group3';
            assert($group3->save());
        }

        public function testMakeByMixedPermitablesData()
        {
            Yii::app()->user->userModel          = User::getByUsername('super');
            $group1                              = Group::getByName('Group1');
            $group2                              = Group::getByName('Group2');
            $group3                              = Group::getByName('Group3');
            $mixedPermitablesData['readOnly'] [] = array('Group' => $group1->id);
            $mixedPermitablesData['readWrite'][] = array('Group' => $group2->id);
            $mixedPermitablesData['readWrite'][] = array('Group' => $group3->id);
            $explicitReadWriteModelPermissions   = ExplicitReadWriteModelPermissionsUtil::
                                                   makeByMixedPermitablesData($mixedPermitablesData);
            $this->assertEquals(1, $explicitReadWriteModelPermissions->getReadOnlyPermitablesCount());
            $this->assertEquals(2, $explicitReadWriteModelPermissions->getReadWritePermitablesCount());
            $readOnlyPermitables  = $explicitReadWriteModelPermissions->getReadOnlyPermitables();
            $this->assertEquals($group1, $readOnlyPermitables[$group1->id]);
            $readWritePermitables = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $this->assertEquals($group2, $readWritePermitables[$group2->id]);
            $this->assertEquals($group3, $readWritePermitables[$group3->id]);
        }

        public function testMakeMixedPermitablesDataByExplicitReadWriteModelPermissions()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $group1                            = Group::getByName('Group1');
            $group2                            = Group::getByName('Group2');
            $group3                            = Group::getByName('Group3');
            $explicitReadWriteModelPermissions = new ExplicitReadWriteModelPermissions();
            $this->assertEquals(0, $explicitReadWriteModelPermissions->getReadOnlyPermitablesCount());
            $this->assertEquals(0, $explicitReadWriteModelPermissions->getReadWritePermitablesCount());

            //Now add permitables
            $explicitReadWriteModelPermissions->addReadOnlyPermitable($group1);
            $explicitReadWriteModelPermissions->addReadWritePermitable($group2);
            $explicitReadWriteModelPermissions->addReadWritePermitable($group3);

            $mixedPermitablesData = ExplicitReadWriteModelPermissionsUtil::
                                    makeMixedPermitablesDataByExplicitReadWriteModelPermissions(
                                    $explicitReadWriteModelPermissions);
            $compareData          = array(
                'readOnly'  => array(array('Group' => $group1->id)),
                'readWrite' => array(array('Group' => $group2->id), array('Group' => $group3->id)),
            );
            $this->assertEquals($compareData, $mixedPermitablesData);
        }

        public function testMakeByPostData()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            //Test selecting owner only.
            $postData = array('type' => null);
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::makeByPostData($postData);
            $this->assertEquals(0, $explicitReadWriteModelPermissions->getReadOnlyPermitablesCount());
            $this->assertEquals(0, $explicitReadWriteModelPermissions->getReadWritePermitablesCount());

            //Test selecting the everyone group.
            $postData = array('type' => ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP);
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::makeByPostData($postData);
            $this->assertEquals(0, $explicitReadWriteModelPermissions->getReadOnlyPermitablesCount());
            $this->assertEquals(1, $explicitReadWriteModelPermissions->getReadWritePermitablesCount());
            $readWritePermitables = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $everyoneGroup        = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $this->assertEquals($everyoneGroup, $readWritePermitables[$everyoneGroup->id]);

            //Test selecting a group that is not the everyone group.
            $group2 = Group::getByName('group2');
            $postData = array('type' => ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_NONEVERYONE_GROUP,
                              'nonEveryoneGroup' => $group2->id);
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::makeByPostData($postData);
            $this->assertEquals(0, $explicitReadWriteModelPermissions->getReadOnlyPermitablesCount());
            $this->assertEquals(1, $explicitReadWriteModelPermissions->getReadWritePermitablesCount());
            $readWritePermitables = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $this->assertEquals($group2, $readWritePermitables[$group2->id]);
        }
    }
?>
