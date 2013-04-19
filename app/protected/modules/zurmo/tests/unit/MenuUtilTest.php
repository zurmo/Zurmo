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

    class MenuUtilTest extends ZurmoBaseTest
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
            $this->assertEquals(7, count($menu['items']));
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

        /**
         * @depends testGetAccessibleShortcutsCreateMenuByCurrentUser
         */
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

        /**
         * @depends testGetAccessibleConfigureMenuByCurrentUser
         */
        public function testGetVisibleAndOrderedTabMenuByCurrentUser()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $menu = MenuUtil::getVisibleAndOrderedTabMenuByCurrentUser();
            $this->assertEquals(8, count($menu));
            $menu = MenuUtil::getAccessibleModuleTabMenuByUser('AccountsModule', Yii::app()->user->userModel);
            $this->assertEquals(1, count($menu));
            Yii::app()->user->userModel = User::getByUsername('billy');
            $this->assertEquals(Right::NONE,  Yii::app()->user->userModel->getExplicitActualRight ('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS));
            $menu = MenuUtil::getVisibleAndOrderedTabMenuByCurrentUser();
            $this->assertEquals(3, count($menu));
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
            $this->assertEquals(5, count($menu));
        }

        public function testGetAccessibleHeaderMenuByModuleClassNameForCurrentUser()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $menu = MenuUtil::getOrderedAccessibleHeaderMenuForCurrentUser();
            $this->assertEquals(6, count($menu));
            Yii::app()->user->userModel = User::getByUsername('billy');
            $menu = MenuUtil::getOrderedAccessibleHeaderMenuForCurrentUser();
            $this->assertEquals(2, count($menu));
            $bill = User::getByUsername('billy');
            $bill->setRight('ZurmoModule', ZurmoModule::RIGHT_ACCESS_ADMINISTRATION);
            $saved = $bill->save();
            $this->assertTrue($saved);
            $menu = MenuUtil::getOrderedAccessibleHeaderMenuForCurrentUser();
            $this->assertEquals(3, count($menu));
        }

        public function testGetAccessibleOrderedUserHeaderMenuForCurrentUser()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $menu = MenuUtil::getAccessibleOrderedUserHeaderMenuForCurrentUser();
            $this->assertEquals(4, count($menu));
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
                    'label'  => "eval:Zurmo::t('AccountsModule', 'AccountsModulePluralLabel', \$translationParams)",
                    'url'    => array('/accounts/default/create'),
                    'right'  => AccountsModule::RIGHT_CREATE_ACCOUNTS,
                    'mobile' => true,
                ),
            );
            AccountsModule::setMetadata($metadata);
            $menuItems = MenuUtil::getAccessibleShortcutsCreateMenuByCurrentUser();
            $compareData = array(
                'label' => 'Create',
                'url'   => null,
                'items' => array(
                        array(
                            'label'  => 'Accounts',
                            'url'    => array('/accounts/default/create'),
                            'right'  => AccountsModule::RIGHT_CREATE_ACCOUNTS,
                            'mobile' => true,
                        ),
                        array(
                            'label'  => 'Contact',
                            'url'    => array('/contacts/default/create'),
                            'right'  => ContactsModule::RIGHT_CREATE_CONTACTS,
                            'mobile' => true,
                        ),
                        array(
                            'label'  => 'Conversation',
                            'url'    => array('/conversations/default/create'),
                            'right'  => ConversationsModule::RIGHT_CREATE_CONVERSATIONS,
                            'mobile' => true,
                        ),
                        array(
                            'label'  => 'Lead',
                            'url'    => array('/leads/default/create'),
                            'right'  => LeadsModule::RIGHT_CREATE_LEADS,
                            'mobile' => true,
                        ),
                        array(
                            'label'  => 'Mission',
                            'url'    => array('/missions/default/create'),
                            'right'  => MissionsModule::RIGHT_CREATE_MISSIONS,
                            'mobile' => true,
                        ),
                        array(
                            'label'  => 'Opportunity',
                            'url'    => array('/opportunities/default/create'),
                            'right'  => OpportunitiesModule::RIGHT_CREATE_OPPORTUNITIES,
                            'mobile' => true,
                        ),
                        array(
                            'label'  => 'Report',
                            'url'    => array('/reports/default/selectType'),
                            'right'  => ReportsModule::RIGHT_CREATE_REPORTS,
                            'mobile' => false,
                        ),
                ),
            );
            $this->assertEquals($compareData, $menuItems);
            AccountsModule::setMetadata($backupMetadata);
        }
    }
?>
