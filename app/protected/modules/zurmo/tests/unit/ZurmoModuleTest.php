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

    class ZurmoModuleTest extends ZurmoBaseTest
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

        public function testGetModelClassNames()
        {
            $modelClassNames = ZurmoModule::getModelClassNames();
            $this->assertEquals(23, count($modelClassNames));
            $this->assertEquals('Address', $modelClassNames[0]);
            $this->assertEquals('AuditEvent', $modelClassNames[1]);
            $this->assertEquals('Currency', $modelClassNames[2]);
            $this->assertEquals('CurrencyValue', $modelClassNames[3]);
            $this->assertEquals('Email', $modelClassNames[4]);
            $this->assertEquals('ExplicitReadWriteModelPermissions', $modelClassNames[5]);
            $this->assertEquals('FileModel', $modelClassNames[6]);
            $this->assertEquals('FilteredList', $modelClassNames[7]);
            $this->assertEquals('Group', $modelClassNames[8]);
            $this->assertEquals('Item', $modelClassNames[9]);
            $this->assertEquals('NamedSecurableItem', $modelClassNames[10]);
            $this->assertEquals('OwnedCustomField', $modelClassNames[11]);
            $this->assertEquals('OwnedModel', $modelClassNames[12]);
            $this->assertEquals('OwnedMultipleValuesCustomField', $modelClassNames[13]);
            $this->assertEquals('OwnedSecurableItem', $modelClassNames[14]);
            $this->assertEquals('Permission', $modelClassNames[15]);
            $this->assertEquals('Permitable', $modelClassNames[16]);
            $this->assertEquals('Person', $modelClassNames[17]);
            $this->assertEquals('Policy', $modelClassNames[18]);
            $this->assertEquals('Right', $modelClassNames[19]);
            $this->assertEquals('Role', $modelClassNames[20]);
            $this->assertEquals('SecurableItem', $modelClassNames[21]);
            $this->assertEquals('ZurmoModelSearch', $modelClassNames[22]);
        }
    }
?>
