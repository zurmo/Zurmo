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

    class MenuUtilTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            ZurmoDatabaseCompatibilityUtil::dropStoredFunctionsAndProcedures();
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
            SecurityTestHelper::createGroups();
            SecurityTestHelper::createRoles();
        }

        public function testGetAccessibleShortcutsCreateMenuByCurrentUser()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $menu = MenuUtil::getAccessibleShortcutsCreateMenuByCurrentUser();

            $this->assertEquals(3, count($menu));
            $this->assertEquals(4, count($menu['items']));
            Yii::app()->user->userModel = User::getByUsername('billy');
            $menu = MenuUtil::getAccessibleShortcutsCreateMenuByCurrentUser();
            $this->assertEquals(0, count($menu));
            $bill = User::getByUsername('billy');
            $bill->setRight('OpportunitiesModule', OpportunitiesModule::RIGHT_ACCESS_OPPORTUNITIES);
            $bill->setRight('OpportunitiesModule', OpportunitiesModule::RIGHT_CREATE_OPPORTUNITIES);
            $saved = $bill->save();
            $this->assertTrue($saved);
            $menu = MenuUtil::getAccessibleShortcutsCreateMenuByCurrentUser();
            $this->assertEquals(3, count($menu));
            $this->assertEquals(1, count($menu['items']));
        }

        public function testGetAccessibleConfigureMenuByCurrentUser()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $menu = MenuUtil::getAccessibleConfigureMenuByCurrentUser('GroupsModule');
            $this->assertEquals(1, count($menu));
            Yii::app()->user->userModel = User::getByUsername('billy');
            $menu = MenuUtil::getAccessibleConfigureMenuByCurrentUser('GroupsModule');
            $this->assertEquals(0, count($menu));
            $bill = User::getByUsername('billy');
            $bill->setRight('GroupsModule', GroupsModule::RIGHT_ACCESS_GROUPS);
            $saved = $bill->save();
            $this->assertTrue($saved);
            $menu = MenuUtil::getAccessibleConfigureMenuByCurrentUser('GroupsModule');
            $this->assertEquals(1, count($menu));
        }

        public function testGetVisibleAndOrderedTabMenuByCurrentUser()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $menu = MenuUtil::getVisibleAndOrderedTabMenuByCurrentUser();
            $this->assertEquals(5, count($menu));
            $menu = MenuUtil::getAccessibleModuleTabMenuByUser('AccountsModule', Yii::app()->user->userModel);
            $this->assertEquals(1, count($menu));
            Yii::app()->user->userModel = User::getByUsername('billy');
            $this->assertEquals(Right::NONE,  Yii::app()->user->userModel->getExplicitActualRight ('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS));
            $menu = MenuUtil::getVisibleAndOrderedTabMenuByCurrentUser();
            $this->assertEquals(2, count($menu));
            $menu = MenuUtil::getAccessibleModuleTabMenuByUser('AccountsModule', Yii::app()->user->userModel);
            $this->assertEquals(0, count($menu));
            $bill = User::getByUsername('billy');
            $bill->setRight('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS);
            $bill->setRight('ContactsModule', ContactsModule::RIGHT_ACCESS_CONTACTS);
            $saved = $bill->save();
            $this->assertTrue($saved);
            $this->assertEquals(Right::ALLOW,  $bill->getExplicitActualRight ('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS));
            $this->assertTrue(RightsUtil::canUserAccessModule('AccountsModule', $bill));
            $menu = MenuUtil::getAccessibleModuleTabMenuByUser('AccountsModule', $bill);
            $this->assertEquals(1, count($menu));
            $menu = MenuUtil::getVisibleAndOrderedTabMenuByCurrentUser();
            $this->assertEquals(4, count($menu));
        }

        public function testGetAccessibleHeaderMenuByModuleClassNameForCurrentUser()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $menu = MenuUtil::getOrderedAccessibleHeaderMenuForCurrentUser();
            $this->assertEquals(8, count($menu));
            Yii::app()->user->userModel = User::getByUsername('billy');
            $menu = MenuUtil::getOrderedAccessibleHeaderMenuForCurrentUser();
            $this->assertEquals(3, count($menu));
            $bill = User::getByUsername('billy');
            $bill->setRight('ZurmoModule', ZurmoModule::RIGHT_ACCESS_ADMINISTRATION);
            $saved = $bill->save();
            $this->assertTrue($saved);
            $menu = MenuUtil::getOrderedAccessibleHeaderMenuForCurrentUser();
            $this->assertEquals(4, count($menu));
        }

        public function testGetAccessibleOrderedUserHeaderMenuForCurrentUser()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $menu = MenuUtil::getAccessibleOrderedUserHeaderMenuForCurrentUser();
            $this->assertEquals(3, count($menu));
            Yii::app()->user->userModel = User::getByUsername('billy');
            $menu = MenuUtil::getAccessibleOrderedUserHeaderMenuForCurrentUser();
            $this->assertEquals(3, count($menu));
        }

        public function testResolveMenuItemsForLanguageLocalizationIsRecursive()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $metadata                                 = AccountsModule::getMetadata();
            $backupMetadata                           = $metadata;
            $metadata['global']['shortcutsCreateMenuItems'] = array(
                array(
                    'label' => 'AccountsModuleSingularLabel',
                    'url'   => array('/accounts/default/create'),
                    'right' => AccountsModule::RIGHT_CREATE_ACCOUNTS,
                ),
            );
            AccountsModule::setMetadata($metadata);
            $menuItems = MenuUtil::getAccessibleShortcutsCreateMenuByCurrentUser();
            $compareData = array(
                'label' => 'Create New',
                'url'   => null,
                'items' => array(
                        array(
                            'label' => 'Account',
                            'url'   => array('/accounts/default/create'),
                            'right' => AccountsModule::RIGHT_CREATE_ACCOUNTS,
                        ),
                        array(
                            'label' => 'Contact',
                            'url'   => array('/contacts/default/create'),
                            'right' => ContactsModule::RIGHT_CREATE_CONTACTS,
                        ),
                        array(
                            'label' => 'Lead',
                            'url'   => array('/leads/default/create'),
                            'right' => LeadsModule::RIGHT_CREATE_LEADS,
                        ),
                        array(
                            'label' => 'Opportunity',
                            'url'   => array('/opportunities/default/create'),
                            'right' => OpportunitiesModule::RIGHT_CREATE_OPPORTUNITIES,
                        ),
                ),
            );
            $this->assertEquals($compareData, $menuItems);
            AccountsModule::setMetadata($backupMetadata);
        }
    }
?>
