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

    class GlobalSearchUtilTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testResolveModuleNamesAndLabelsDataWithAllOption()
        {
            $moduleNamesAndLabels = array('a' => 'b');
            GlobalSearchUtil::resolveModuleNamesAndLabelsDataWithAllOption($moduleNamesAndLabels);
            $this->assertEquals(array('All' => 'All', 'a' => 'b'), $moduleNamesAndLabels);
        }

        /**
         * @depends testResolveModuleNamesAndLabelsDataWithAllOption
         */
        public function testResolveGlobalSearchScopeFromGetData()
        {
            $getData = null;
            $scope   = GlobalSearchUtil::resolveGlobalSearchScopeFromGetData($getData);
            $this->assertNull($scope);

            $getData = null;
            $getData['globalSearchScope'][0] = 'All';
            $scope   = GlobalSearchUtil::resolveGlobalSearchScopeFromGetData($getData);
            $this->assertNull($scope);

            $getData = null;
            $getData['globalSearchScope'][0] = 'accounts';
            $getData['globalSearchScope'][1] = 'contacts';
            $scope   = GlobalSearchUtil::resolveGlobalSearchScopeFromGetData($getData);
            $this->assertEquals(array('accounts', 'contacts'), $scope);
        }

        /**
         * @depends testResolveModuleNamesAndLabelsDataWithAllOption
         */
        public function testGetGlobalSearchScopingModuleNamesAndLabelsDataByUser()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $scopeModulesAndLabels      = GlobalSearchUtil::
                                          getGlobalSearchScopingModuleNamesAndLabelsDataByUser(
                                          Yii::app()->user->userModel);
            $compareData                = array(
                                            'accounts'	    => 'Accounts',
                                            'contacts'      => 'Contacts',
                                            'leads'         => 'Leads',
                                            'opportunities' => 'Opportunities',
                                          );
            $this->assertEquals($compareData, $scopeModulesAndLabels);
        }

        /**
         * @depends testGetGlobalSearchScopingModuleNamesAndLabelsDataByUser
         */
        public function testGetGlobalSearchScopingModuleNamesAndLabelsDataByUserForRegularUser()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $billy                      = UserTestHelper::createBasicUser('Billy');
            $scopeModulesAndLabels      = GlobalSearchUtil::
                                          getGlobalSearchScopingModuleNamesAndLabelsDataByUser($billy);
            $compareData                = array();
            $this->assertEquals($compareData, $scopeModulesAndLabels);
            $scopeModulesAndLabels      = GlobalSearchUtil::
                                          getGlobalSearchScopingModuleNamesAndLabelsDataByUser($billy);

            //Now add rights for billy to the AccountsModule.
            $billy->setRight('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS);
            $this->assertTrue($billy->save());

            //At this point because the data is was cleared after billy saved, it should show Accounts.
            $scopeModulesAndLabels      = GlobalSearchUtil::
                                          getGlobalSearchScopingModuleNamesAndLabelsDataByUser($billy);
            $compareData                = array('accounts'	    => 'Accounts');
            $this->assertEquals($compareData, $scopeModulesAndLabels);
        }
    }
?>