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

    class ZurmoConfigurationUtilTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            ZurmoDatabaseCompatibilityUtil::dropStoredFunctionsAndProcedures();
            SecurityTestHelper::createSuperAdmin();
            UserTestHelper::createBasicUser('billy');
            UserTestHelper::createBasicUser('sally');
        }

        public function testGetAndSetByCurrentUserByModuleName()
        {
            Yii::app()->user->userModel =  User::getByUsername('super');
            $this->assertNull(ZurmoConfigurationUtil::getForCurrentUserByModuleName('ZurmoModule', 'aKey'));
            ZurmoConfigurationUtil::setForCurrentUserByModuleName('ZurmoModule', 'aKey', 'aValue');
            Yii::app()->user->userModel =  User::getByUsername('billy');
            $this->assertNull(ZurmoConfigurationUtil::getForCurrentUserByModuleName('ZurmoModule', 'aKey'));
            ZurmoConfigurationUtil::setForCurrentUserByModuleName('ZurmoModule', 'aKey', 'bValue');
            Yii::app()->user->userModel =  User::getByUsername('sally');
            $this->assertNull(ZurmoConfigurationUtil::getForCurrentUserByModuleName('ZurmoModule', 'aKey'));
            ZurmoConfigurationUtil::setForCurrentUserByModuleName('ZurmoModule', 'aKey', 'cValue');

            //now retrieve again.
            Yii::app()->user->userModel =  User::getByUsername('super');
            $this->assertEquals('aValue', ZurmoConfigurationUtil::getForCurrentUserByModuleName('ZurmoModule', 'aKey'));
            Yii::app()->user->userModel =  User::getByUsername('billy');
            $this->assertEquals('bValue', ZurmoConfigurationUtil::getForCurrentUserByModuleName('ZurmoModule', 'aKey'));
            Yii::app()->user->userModel =  User::getByUsername('sally');
            $this->assertEquals('cValue', ZurmoConfigurationUtil::getForCurrentUserByModuleName('ZurmoModule', 'aKey'));

            //Test retrieving a generic value that is set globally on ZurmoModule. The value returned should be the
            //same for all users.
            $metadata = ZurmoModule::getMetadata();
            $this->assertTrue(!isset($metadata['global']['bKey']));
            $metadata['global']['bKey'] = 'GlobalValue';
            ZurmoModule::setMetadata($metadata);
            Yii::app()->user->userModel =  User::getByUsername('super');
            $this->assertEquals('GlobalValue',
                ZurmoConfigurationUtil::getForCurrentUserByModuleName('ZurmoModule', 'bKey'));
            Yii::app()->user->userModel =  User::getByUsername('billy');
            $this->assertEquals('GlobalValue',
                ZurmoConfigurationUtil::getForCurrentUserByModuleName('ZurmoModule', 'bKey'));
            Yii::app()->user->userModel =  User::getByUsername('sally');
            $this->assertEquals('GlobalValue',
                ZurmoConfigurationUtil::getForCurrentUserByModuleName('ZurmoModule', 'bKey'));

            //Now change the bKey value, just for billy and retrieve again for all users. Only billy's bKey value
            //should be different.
            ZurmoConfigurationUtil::setByUserAndModuleName(
                User::getByUsername('billy'), 'ZurmoModule', 'bKey', 'BillyBKey');
            Yii::app()->user->userModel =  User::getByUsername('super');
            $this->assertEquals('GlobalValue',
                ZurmoConfigurationUtil::getForCurrentUserByModuleName('ZurmoModule', 'bKey'));
            Yii::app()->user->userModel =  User::getByUsername('billy');
            $this->assertEquals('BillyBKey',
                ZurmoConfigurationUtil::getForCurrentUserByModuleName('ZurmoModule', 'bKey'));
            Yii::app()->user->userModel =  User::getByUsername('sally');
            $this->assertEquals('GlobalValue',
                ZurmoConfigurationUtil::getForCurrentUserByModuleName('ZurmoModule', 'bKey'));
        }

        public function testSetGetByModuleName()
        {
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'testSetGetByModuleName', 'someValue');
            $this->assertEquals('someValue',
                ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'testSetGetByModuleName'));
        }
    }
?>