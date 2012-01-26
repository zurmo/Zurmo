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

    class ZurmoPaginationHelperTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            ZurmoDatabaseCompatibilityUtil::dropStoredFunctionsAndProcedures();
            SecurityTestHelper::createSuperAdmin();
            UserTestHelper::createBasicUser('billy');
            UserTestHelper::createBasicUser('sally');
        }

        public function testZurmoPaginationHelper()
        {
            $pager = new ZurmoPaginationHelper();
            $pager->setListPageSize(11);
            $pager->setSubListPageSize(12);
            $pager->setModalListPageSize(13);
            $pager->setMassEditProgressPageSize(14);
            $pager->setAutoCompleteListPageSize(15);
            $pager->setImportPageSize(16);
            $pager->setDashboardListPageSize(17);

            //Retrieve settings for different current users.
            Yii::app()->user->userModel =  User::getByUsername('super');
            Yii::app()->user->clearStates();
            $this->assertEquals(11, $pager->resolveActiveForCurrentUserByType('listPageSize'));
            Yii::app()->user->userModel =  User::getByUsername('billy');
            Yii::app()->user->clearStates();
            $this->assertEquals(11, $pager->resolveActiveForCurrentUserByType('listPageSize'));
            $this->assertEquals(12, $pager->resolveActiveForCurrentUserByType('subListPageSize'));
            $this->assertEquals(13, $pager->resolveActiveForCurrentUserByType('modalListPageSize'));
            $this->assertEquals(14, $pager->resolveActiveForCurrentUserByType('massEditProgressPageSize'));
            $this->assertEquals(15, $pager->resolveActiveForCurrentUserByType('autoCompleteListPageSize'));
            $this->assertEquals(16, $pager->resolveActiveForCurrentUserByType('importPageSize'));
            $this->assertEquals(17, $pager->resolveActiveForCurrentUserByType('dashboardListPageSize'));

            //Retrieve settings for different specific users
            $sally = User::getByUsername('sally');
            $billy = User::getByUsername('billy');
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $sally;
            Yii::app()->user->clearStates();
            $this->assertEquals(11, $pager->getByUserAndType($super, 'listPageSize'));
            $this->assertEquals(11, $pager->getByUserAndType($billy, 'listPageSize'));
            $this->assertEquals(12, $pager->getByUserAndType($super, 'subListPageSize'));
            $this->assertEquals(12, $pager->getByUserAndType($billy, 'subListPageSize'));
            $this->assertEquals(13, $pager->getByUserAndType($super, 'modalListPageSize'));
            $this->assertEquals(13, $pager->getByUserAndType($billy, 'modalListPageSize'));
            $this->assertEquals(14, $pager->getByUserAndType($super, 'massEditProgressPageSize'));
            $this->assertEquals(14, $pager->getByUserAndType($billy, 'massEditProgressPageSize'));
            $this->assertEquals(15, $pager->getByUserAndType($super, 'autoCompleteListPageSize'));
            $this->assertEquals(15, $pager->getByUserAndType($billy, 'autoCompleteListPageSize'));
            $this->assertEquals(16, $pager->getByUserAndType($super, 'importPageSize'));
            $this->assertEquals(16, $pager->getByUserAndType($billy, 'importPageSize'));
            $this->assertEquals(17, $pager->getByUserAndType($super, 'dashboardListPageSize'));
            $this->assertEquals(17, $pager->getByUserAndType($billy, 'dashboardListPageSize'));

            $pager->setByUserAndType($billy, 'listPageSize',             88);
            $pager->setByUserAndType($billy, 'subListPageSize',          89);
            $pager->setByUserAndType($billy, 'modalListPageSize',        90);
            $pager->setByUserAndType($billy, 'massEditProgressPageSize', 91);
            $pager->setByUserAndType($billy, 'autoCompleteListPageSize', 92);
            $pager->setByUserAndType($billy, 'importPageSize',           93);
            $pager->setByUserAndType($billy, 'dashboardListPageSize',    94);

            $this->assertEquals(88, $pager->getByUserAndType($billy, 'listPageSize'));
            $this->assertEquals(89, $pager->getByUserAndType($billy, 'subListPageSize'));
            $this->assertEquals(90, $pager->getByUserAndType($billy, 'modalListPageSize'));
            $this->assertEquals(91, $pager->getByUserAndType($billy, 'massEditProgressPageSize'));
            $this->assertEquals(92, $pager->getByUserAndType($billy, 'autoCompleteListPageSize'));
            $this->assertEquals(93, $pager->getByUserAndType($billy, 'importPageSize'));
            $this->assertEquals(94, $pager->getByUserAndType($billy, 'dashboardListPageSize'));
        }

        public function testSetGetGlobalValueByType()
        {
            $pager = new ZurmoPaginationHelper();
            $pager->setListPageSize(11);
            $pager->setSubListPageSize(12);
            $pager->setModalListPageSize(13);
            $pager->setMassEditProgressPageSize(14);
            $pager->setAutoCompleteListPageSize(15);
            $pager->setImportPageSize(16);
            $this->assertEquals         (11, $pager->getGlobalValueByType('listPageSize'));
            $this->assertEquals         (12, $pager->getGlobalValueByType('subListPageSize'));
            $this->assertEquals         (13, $pager->getGlobalValueByType('modalListPageSize'));
            $this->assertEquals         (14, $pager->getGlobalValueByType('massEditProgressPageSize'));
            $this->assertEquals         (15, $pager->getGlobalValueByType('autoCompleteListPageSize'));
            $this->assertEquals         (16, $pager->getGlobalValueByType('importPageSize'));
            $pager->setGlobalValueByType('listPageSize',             88);
            $pager->setGlobalValueByType('subListPageSize',          89);
            $pager->setGlobalValueByType('modalListPageSize',        90);
            $pager->setGlobalValueByType('massEditProgressPageSize', 91);
            $pager->setGlobalValueByType('autoCompleteListPageSize', 92);
            $pager->setGlobalValueByType('importPageSize',           93);
            $this->assertEquals         (88, $pager->getGlobalValueByType('listPageSize'));
            $this->assertEquals         (89, $pager->getGlobalValueByType('subListPageSize'));
            $this->assertEquals         (90, $pager->getGlobalValueByType('modalListPageSize'));
            $this->assertEquals         (91, $pager->getGlobalValueByType('massEditProgressPageSize'));
            $this->assertEquals         (92, $pager->getGlobalValueByType('autoCompleteListPageSize'));
            $this->assertEquals         (93, $pager->getGlobalValueByType('importPageSize'));
        }

        public function testSetForCurrentUserByType()
        {
            $sally = User::getByUsername('sally');
            Yii::app()->user->userModel = $sally;
            $pager = new ZurmoPaginationHelper();
            $pager->setForCurrentUserByType('subListPageSize', 44);
            Yii::app()->user->getState('subListPageSizeZurmoModule', 44);
            $this->assertEquals(44, $pager->getByUserAndType($sally, 'subListPageSize'));
        }
    }
?>