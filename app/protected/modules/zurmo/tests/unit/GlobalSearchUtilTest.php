<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class GlobalSearchUtilTest extends ZurmoBaseTest
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
                                            'accounts'      => 'Accounts',
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
            $compareData                = array('accounts'      => 'Accounts');
            $this->assertEquals($compareData, $scopeModulesAndLabels);
        }
    }
?>