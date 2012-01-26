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

    class ExplicitReadWriteModelPermissionsTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testSettingAndGetting()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $group1 = new Group();
            $group1->name = 'Group1';
            $this->assertTrue($group1->save());

            $group2 = new Group();
            $group2->name = 'Group2';
            $this->assertTrue($group2->save());

            $group3 = new Group();
            $group3->name = 'Group3';
            $this->assertTrue($group3->save());

            $group4 = new Group();
            $group4->name = 'Group4';
            $this->assertTrue($group4->save());

            $group5 = new Group();
            $group5->name = 'Group5';
            $this->assertTrue($group5->save());

            $group6 = new Group();
            $group6->name = 'Group6';
            $this->assertTrue($group6->save());

            $explicitReadWriteModelPermissions = new ExplicitReadWriteModelPermissions();
            $this->assertEquals(0, $explicitReadWriteModelPermissions->getReadOnlyPermitablesCount());
            $this->assertEquals(0, $explicitReadWriteModelPermissions->getReadWritePermitablesCount());

            //Now add permitables and test retrieving them.
            $explicitReadWriteModelPermissions->addReadOnlyPermitable($group1);
            $explicitReadWriteModelPermissions->addReadWritePermitable($group2);
            $explicitReadWriteModelPermissions->addReadWritePermitable($group3);
            $explicitReadWriteModelPermissions->addReadOnlyPermitableToRemove($group4);
            $explicitReadWriteModelPermissions->addReadWritePermitableToRemove($group5);
            $this->assertEquals(1, $explicitReadWriteModelPermissions->getReadOnlyPermitablesCount());
            $this->assertEquals(2, $explicitReadWriteModelPermissions->getReadWritePermitablesCount());
            $this->assertEquals(1, $explicitReadWriteModelPermissions->getReadWritePermitablesToRemoveCount());
            $this->assertEquals(1, $explicitReadWriteModelPermissions->getReadWritePermitablesToRemoveCount());

            $readOnlyPermitables = $explicitReadWriteModelPermissions->getReadOnlyPermitables();
            $readWritePermitables = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $this->assertEquals(1, count($readOnlyPermitables));
            $this->assertEquals(2, count($readWritePermitables));
            $this->assertEquals($group1, $readOnlyPermitables[$group1->id]);
            $this->assertEquals($group2, $readWritePermitables[$group2->id]);
            $this->assertEquals($group3, $readWritePermitables[$group3->id]);
            $readOnlyPermitablesToRemove  = $explicitReadWriteModelPermissions->getReadOnlyPermitablesToRemove();
            $readWritePermitablesToRemove = $explicitReadWriteModelPermissions->getReadWritePermitablesToRemove();
            $this->assertEquals($group4, $readOnlyPermitablesToRemove[$group4->id]);
            $this->assertEquals($group5, $readWritePermitablesToRemove[$group5->id]);

            $this->assertTrue ($explicitReadWriteModelPermissions->isReadOrReadWritePermitable($group1));
            $this->assertTrue ($explicitReadWriteModelPermissions->isReadOrReadWritePermitable($group2));
            $this->assertTrue ($explicitReadWriteModelPermissions->isReadOrReadWritePermitable($group3));
            $this->assertFalse($explicitReadWriteModelPermissions->isReadOrReadWritePermitable($group4));
            $this->assertFalse($explicitReadWriteModelPermissions->isReadOrReadWritePermitable($group5));
            $this->assertFalse($explicitReadWriteModelPermissions->isReadOrReadWritePermitable($group6));
        }
    }
?>
