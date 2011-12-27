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

    class RightsFormUtilTest extends BaseTest
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

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testModuleRightsUtilGetAllModuleRightsData()
        {
            $group = new Group();
            $group->name = 'viewGroup';
            $group->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE);
            $saved = $group->save();
            $this->assertTrue($saved);
            $data = RightsUtil::getAllModuleRightsDataByPermitable($group);
            $compareData = array(
                'AccountsModule' => array(
                    'RIGHT_CREATE_ACCOUNTS'   => array(
                        'displayName' => AccountsModule::RIGHT_CREATE_ACCOUNTS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_ACCOUNTS'   => array(
                        'displayName' => AccountsModule::RIGHT_DELETE_ACCOUNTS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_ACCOUNTS'   => array(
                        'displayName' => AccountsModule::RIGHT_ACCESS_ACCOUNTS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'ContactsModule' => array(
                    'RIGHT_CREATE_CONTACTS'   => array(
                        'displayName' => ContactsModule::RIGHT_CREATE_CONTACTS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_CONTACTS'   => array(
                        'displayName' => ContactsModule::RIGHT_DELETE_CONTACTS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_CONTACTS'   => array(
                        'displayName' => ContactsModule::RIGHT_ACCESS_CONTACTS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'DesignerModule' => array(
                    'RIGHT_ACCESS_DESIGNER'   => array(
                        'displayName' => DesignerModule::RIGHT_ACCESS_DESIGNER,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'HomeModule' => array(
                    'RIGHT_CREATE_DASHBOARDS'   => array(
                        'displayName' => HomeModule::RIGHT_CREATE_DASHBOARDS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_DASHBOARDS'   => array(
                        'displayName' => HomeModule::RIGHT_DELETE_DASHBOARDS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_DASHBOARDS'   => array(
                        'displayName' => HomeModule::RIGHT_ACCESS_DASHBOARDS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'JobsManagerModule' => array(
                    'RIGHT_ACCESS_JOBSMANAGER'   => array(
                        'displayName' => JobsManagerModule::RIGHT_ACCESS_JOBSMANAGER,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'LeadsModule' => array(
                    'RIGHT_CREATE_LEADS'   => array(
                        'displayName' => LeadsModule::RIGHT_CREATE_LEADS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_LEADS'   => array(
                        'displayName' => LeadsModule::RIGHT_DELETE_LEADS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_LEADS'   => array(
                        'displayName' => LeadsModule::RIGHT_ACCESS_LEADS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_CONVERT_LEADS'   => array(
                        'displayName' => LeadsModule::RIGHT_CONVERT_LEADS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'MapsModule' => array(
                    'RIGHT_ACCESS_MAPS_ADMINISTRATION'   => array(
                        'displayName' => MapsModule::RIGHT_ACCESS_MAPS_ADMINISTRATION,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'NotesModule' => array(
                    'RIGHT_CREATE_NOTES'   => array(
                        'displayName' => NotesModule::RIGHT_CREATE_NOTES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_NOTES'   => array(
                        'displayName' => NotesModule::RIGHT_DELETE_NOTES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_NOTES'   => array(
                        'displayName' => NotesModule::RIGHT_ACCESS_NOTES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'OpportunitiesModule' => array(
                    'RIGHT_CREATE_OPPORTUNITIES'   => array(
                        'displayName' => OpportunitiesModule::RIGHT_CREATE_OPPORTUNITIES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_OPPORTUNITIES'   => array(
                        'displayName' => OpportunitiesModule::RIGHT_DELETE_OPPORTUNITIES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_OPPORTUNITIES'   => array(
                        'displayName' => OpportunitiesModule::RIGHT_ACCESS_OPPORTUNITIES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'GroupsModule'  => array(
                    'RIGHT_CREATE_GROUPS'   => array(
                        'displayName' => GroupsModule::RIGHT_CREATE_GROUPS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_GROUPS'   => array(
                        'displayName' => GroupsModule::RIGHT_DELETE_GROUPS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_GROUPS'   => array(
                        'displayName' => GroupsModule::RIGHT_ACCESS_GROUPS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'ImportModule'  => array(
                    'RIGHT_ACCESS_IMPORT'   => array(
                        'displayName' => ImportModule::RIGHT_ACCESS_IMPORT,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'MeetingsModule'  => array(
                    'RIGHT_CREATE_MEETINGS'   => array(
                        'displayName' => MeetingsModule::RIGHT_CREATE_MEETINGS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_MEETINGS'   => array(
                        'displayName' => MeetingsModule::RIGHT_DELETE_MEETINGS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_MEETINGS'   => array(
                        'displayName' => MeetingsModule::RIGHT_ACCESS_MEETINGS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'RolesModule'  => array(
                    'RIGHT_CREATE_ROLES'   => array(
                        'displayName' => RolesModule::RIGHT_CREATE_ROLES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_ROLES'   => array(
                        'displayName' => RolesModule::RIGHT_DELETE_ROLES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_ROLES'   => array(
                        'displayName' => RolesModule::RIGHT_ACCESS_ROLES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'TasksModule'  => array(
                    'RIGHT_CREATE_TASKS'   => array(
                        'displayName' => TasksModule::RIGHT_CREATE_TASKS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_TASKS'   => array(
                        'displayName' => TasksModule::RIGHT_DELETE_TASKS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_TASKS'   => array(
                        'displayName' => TasksModule::RIGHT_ACCESS_TASKS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'ZurmoModule'  => array(
                    'RIGHT_ACCESS_ADMINISTRATION'   => array(
                        'displayName' => ZurmoModule::RIGHT_ACCESS_ADMINISTRATION,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_BULK_WRITE'   => array(
                        'displayName' => ZurmoModule::RIGHT_BULK_WRITE,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_GLOBAL_CONFIGURATION'   => array(
                        'displayName' => ZurmoModule::RIGHT_ACCESS_GLOBAL_CONFIGURATION,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_CURRENCY_CONFIGURATION'   => array(
                        'displayName' => ZurmoModule::RIGHT_ACCESS_CURRENCY_CONFIGURATION,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'UsersModule' => array(
                    'RIGHT_CHANGE_USER_PASSWORDS'   => array(
                        'displayName' => UsersModule::RIGHT_CHANGE_USER_PASSWORDS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_LOGIN_VIA_WEB'   => array(
                        'displayName' => UsersModule::RIGHT_LOGIN_VIA_WEB,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_LOGIN_VIA_MOBILE'   => array(
                        'displayName' => UsersModule::RIGHT_LOGIN_VIA_MOBILE,
                        'explicit'    => Right::ALLOW,
                        'inherited'   => null,
                        'effective'   => Right::ALLOW,
                    ),
                    'RIGHT_LOGIN_VIA_WEB_API'   => array(
                        'displayName' => UsersModule::RIGHT_LOGIN_VIA_WEB_API,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_CREATE_USERS'   => array(
                        'displayName' => UsersModule::RIGHT_CREATE_USERS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_USERS'   => array(
                        'displayName' => UsersModule::RIGHT_ACCESS_USERS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
            );
            $this->assertEquals($compareData, $data);
            $group->forget();
        }

        /**
         * @depends testModuleRightsUtilGetAllModuleRightsData
         */
        public function testRightsFormUtil()
        {
            $group = Group::getByName('viewGroup');
            $group1 = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $group1->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API);
            $saved = $group1->save();
            $this->assertTrue($saved);
            $data = RightsUtil::getAllModuleRightsDataByPermitable($group);
            $this->assertTrue(is_array($data));
            $form = RightsFormUtil::makeFormFromRightsData($data);
            $compareData = array(
                'AccountsModule' => array(
                    'RIGHT_CREATE_ACCOUNTS'   => array(
                        'displayName' => AccountsModule::RIGHT_CREATE_ACCOUNTS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_ACCOUNTS'   => array(
                        'displayName' => AccountsModule::RIGHT_DELETE_ACCOUNTS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_ACCOUNTS'   => array(
                        'displayName' => AccountsModule::RIGHT_ACCESS_ACCOUNTS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'ContactsModule' => array(
                    'RIGHT_CREATE_CONTACTS'   => array(
                        'displayName' => ContactsModule::RIGHT_CREATE_CONTACTS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_CONTACTS'   => array(
                        'displayName' => ContactsModule::RIGHT_DELETE_CONTACTS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_CONTACTS'   => array(
                        'displayName' => ContactsModule::RIGHT_ACCESS_CONTACTS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'DesignerModule' => array(
                    'RIGHT_ACCESS_DESIGNER'   => array(
                        'displayName' => DesignerModule::RIGHT_ACCESS_DESIGNER,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'HomeModule' => array(
                    'RIGHT_CREATE_DASHBOARDS'   => array(
                        'displayName' => HomeModule::RIGHT_CREATE_DASHBOARDS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_DASHBOARDS'   => array(
                        'displayName' => HomeModule::RIGHT_DELETE_DASHBOARDS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_DASHBOARDS'   => array(
                        'displayName' => HomeModule::RIGHT_ACCESS_DASHBOARDS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'JobsManagerModule' => array(
                    'RIGHT_ACCESS_JOBSMANAGER'   => array(
                        'displayName' => JobsManagerModule::RIGHT_ACCESS_JOBSMANAGER,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'LeadsModule' => array(
                    'RIGHT_CREATE_LEADS'   => array(
                        'displayName' => LeadsModule::RIGHT_CREATE_LEADS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_LEADS'   => array(
                        'displayName' => LeadsModule::RIGHT_DELETE_LEADS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_LEADS'   => array(
                        'displayName' => LeadsModule::RIGHT_ACCESS_LEADS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_CONVERT_LEADS'   => array(
                        'displayName' => LeadsModule::RIGHT_CONVERT_LEADS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'MapsModule' => array(
                    'RIGHT_ACCESS_MAPS_ADMINISTRATION'   => array(
                        'displayName' => MapsModule::RIGHT_ACCESS_MAPS_ADMINISTRATION,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'NotesModule' => array(
                    'RIGHT_CREATE_NOTES'   => array(
                        'displayName' => NotesModule::RIGHT_CREATE_NOTES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_NOTES'   => array(
                        'displayName' => NotesModule::RIGHT_DELETE_NOTES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_NOTES'   => array(
                        'displayName' => NotesModule::RIGHT_ACCESS_NOTES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'OpportunitiesModule' => array(
                    'RIGHT_CREATE_OPPORTUNITIES'   => array(
                        'displayName' => OpportunitiesModule::RIGHT_CREATE_OPPORTUNITIES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_OPPORTUNITIES'   => array(
                        'displayName' => OpportunitiesModule::RIGHT_DELETE_OPPORTUNITIES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_OPPORTUNITIES'   => array(
                        'displayName' => OpportunitiesModule::RIGHT_ACCESS_OPPORTUNITIES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'GroupsModule'  => array(
                    'RIGHT_CREATE_GROUPS'   => array(
                        'displayName' => GroupsModule::RIGHT_CREATE_GROUPS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_GROUPS'   => array(
                        'displayName' => GroupsModule::RIGHT_DELETE_GROUPS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_GROUPS'   => array(
                        'displayName' => GroupsModule::RIGHT_ACCESS_GROUPS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'ImportModule'  => array(
                    'RIGHT_ACCESS_IMPORT'   => array(
                        'displayName' => ImportModule::RIGHT_ACCESS_IMPORT,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'MeetingsModule'  => array(
                    'RIGHT_CREATE_MEETINGS'   => array(
                        'displayName' => MeetingsModule::RIGHT_CREATE_MEETINGS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_MEETINGS'   => array(
                        'displayName' => MeetingsModule::RIGHT_DELETE_MEETINGS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_MEETINGS'   => array(
                        'displayName' => MeetingsModule::RIGHT_ACCESS_MEETINGS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'RolesModule'  => array(
                    'RIGHT_CREATE_ROLES'   => array(
                        'displayName' => RolesModule::RIGHT_CREATE_ROLES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_ROLES'   => array(
                        'displayName' => RolesModule::RIGHT_DELETE_ROLES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_ROLES'   => array(
                        'displayName' => RolesModule::RIGHT_ACCESS_ROLES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'TasksModule'  => array(
                    'RIGHT_CREATE_TASKS'   => array(
                        'displayName' => TasksModule::RIGHT_CREATE_TASKS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_TASKS'   => array(
                        'displayName' => TasksModule::RIGHT_DELETE_TASKS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_TASKS'   => array(
                        'displayName' => TasksModule::RIGHT_ACCESS_TASKS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'ZurmoModule'  => array(
                    'RIGHT_ACCESS_ADMINISTRATION'   => array(
                        'displayName' => ZurmoModule::RIGHT_ACCESS_ADMINISTRATION,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_BULK_WRITE'   => array(
                        'displayName' => ZurmoModule::RIGHT_BULK_WRITE,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_GLOBAL_CONFIGURATION'   => array(
                        'displayName' => ZurmoModule::RIGHT_ACCESS_GLOBAL_CONFIGURATION,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_CURRENCY_CONFIGURATION'   => array(
                        'displayName' => ZurmoModule::RIGHT_ACCESS_CURRENCY_CONFIGURATION,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'UsersModule' => array(
                    'RIGHT_CHANGE_USER_PASSWORDS'   => array(
                        'displayName' => UsersModule::RIGHT_CHANGE_USER_PASSWORDS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_LOGIN_VIA_WEB'   => array(
                        'displayName' => UsersModule::RIGHT_LOGIN_VIA_WEB,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_LOGIN_VIA_MOBILE'   => array(
                        'displayName' => UsersModule::RIGHT_LOGIN_VIA_MOBILE,
                        'explicit'    => Right::ALLOW,
                        'inherited'   => null,
                        'effective'   => Right::ALLOW,
                    ),
                    'RIGHT_LOGIN_VIA_WEB_API'   => array(
                        'displayName' => UsersModule::RIGHT_LOGIN_VIA_WEB_API,
                        'explicit'    => null,
                        'inherited'   => Right::ALLOW,
                        'effective'   => Right::ALLOW,
                    ),
                    'RIGHT_CREATE_USERS'   => array(
                        'displayName' => UsersModule::RIGHT_CREATE_USERS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_USERS'   => array(
                        'displayName' => UsersModule::RIGHT_ACCESS_USERS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
            );
            $this->assertEquals($compareData, $form->data);
            $group->forget();
            $group1->forget();
        }

        /**
         * @depends testRightsFormUtil
         */
        public function testRightsFormUtilSetRightsFromPost()
        {
            $group = Group::getByName('viewGroup');
            $data = RightsUtil::getAllModuleRightsDataByPermitable($group);
            $form = RightsFormUtil::makeFormFromRightsData($data);
            $compareData = array(
                'AccountsModule' => array(
                    'RIGHT_CREATE_ACCOUNTS'   => array(
                        'displayName' => AccountsModule::RIGHT_CREATE_ACCOUNTS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_ACCOUNTS'   => array(
                        'displayName' => AccountsModule::RIGHT_DELETE_ACCOUNTS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_ACCOUNTS'   => array(
                        'displayName' => AccountsModule::RIGHT_ACCESS_ACCOUNTS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'ContactsModule' => array(
                    'RIGHT_CREATE_CONTACTS'   => array(
                        'displayName' => ContactsModule::RIGHT_CREATE_CONTACTS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_CONTACTS'   => array(
                        'displayName' => ContactsModule::RIGHT_DELETE_CONTACTS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_CONTACTS'   => array(
                        'displayName' => ContactsModule::RIGHT_ACCESS_CONTACTS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'DesignerModule' => array(
                    'RIGHT_ACCESS_DESIGNER'   => array(
                        'displayName' => DesignerModule::RIGHT_ACCESS_DESIGNER,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'HomeModule' => array(
                    'RIGHT_CREATE_DASHBOARDS'   => array(
                        'displayName' => HomeModule::RIGHT_CREATE_DASHBOARDS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_DASHBOARDS'   => array(
                        'displayName' => HomeModule::RIGHT_DELETE_DASHBOARDS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_DASHBOARDS'   => array(
                        'displayName' => HomeModule::RIGHT_ACCESS_DASHBOARDS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'JobsManagerModule' => array(
                    'RIGHT_ACCESS_JOBSMANAGER'   => array(
                        'displayName' => JobsManagerModule::RIGHT_ACCESS_JOBSMANAGER,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'LeadsModule' => array(
                    'RIGHT_CREATE_LEADS'   => array(
                        'displayName' => LeadsModule::RIGHT_CREATE_LEADS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_LEADS'   => array(
                        'displayName' => LeadsModule::RIGHT_DELETE_LEADS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_LEADS'   => array(
                        'displayName' => LeadsModule::RIGHT_ACCESS_LEADS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_CONVERT_LEADS'   => array(
                        'displayName' => LeadsModule::RIGHT_CONVERT_LEADS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'MapsModule' => array(
                    'RIGHT_ACCESS_MAPS_ADMINISTRATION'   => array(
                        'displayName' => MapsModule::RIGHT_ACCESS_MAPS_ADMINISTRATION,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'NotesModule' => array(
                    'RIGHT_CREATE_NOTES'   => array(
                        'displayName' => NotesModule::RIGHT_CREATE_NOTES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_NOTES'   => array(
                        'displayName' => NotesModule::RIGHT_DELETE_NOTES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_NOTES'   => array(
                        'displayName' => NotesModule::RIGHT_ACCESS_NOTES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'OpportunitiesModule' => array(
                    'RIGHT_CREATE_OPPORTUNITIES'   => array(
                        'displayName' => OpportunitiesModule::RIGHT_CREATE_OPPORTUNITIES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_OPPORTUNITIES'   => array(
                        'displayName' => OpportunitiesModule::RIGHT_DELETE_OPPORTUNITIES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_OPPORTUNITIES'   => array(
                        'displayName' => OpportunitiesModule::RIGHT_ACCESS_OPPORTUNITIES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'GroupsModule'  => array(
                    'RIGHT_CREATE_GROUPS'   => array(
                        'displayName' => GroupsModule::RIGHT_CREATE_GROUPS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_GROUPS'   => array(
                        'displayName' => GroupsModule::RIGHT_DELETE_GROUPS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_GROUPS'   => array(
                        'displayName' => GroupsModule::RIGHT_ACCESS_GROUPS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'ImportModule'  => array(
                    'RIGHT_ACCESS_IMPORT'   => array(
                        'displayName' => ImportModule::RIGHT_ACCESS_IMPORT,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'MeetingsModule'  => array(
                    'RIGHT_CREATE_MEETINGS'   => array(
                        'displayName' => MeetingsModule::RIGHT_CREATE_MEETINGS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_MEETINGS'   => array(
                        'displayName' => MeetingsModule::RIGHT_DELETE_MEETINGS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_MEETINGS'   => array(
                        'displayName' => MeetingsModule::RIGHT_ACCESS_MEETINGS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'RolesModule'  => array(
                    'RIGHT_CREATE_ROLES'   => array(
                        'displayName' => RolesModule::RIGHT_CREATE_ROLES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_ROLES'   => array(
                        'displayName' => RolesModule::RIGHT_DELETE_ROLES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_ROLES'   => array(
                        'displayName' => RolesModule::RIGHT_ACCESS_ROLES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'TasksModule'  => array(
                    'RIGHT_CREATE_TASKS'   => array(
                        'displayName' => TasksModule::RIGHT_CREATE_TASKS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_TASKS'   => array(
                        'displayName' => TasksModule::RIGHT_DELETE_TASKS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_TASKS'   => array(
                        'displayName' => TasksModule::RIGHT_ACCESS_TASKS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'ZurmoModule'  => array(
                    'RIGHT_ACCESS_ADMINISTRATION'   => array(
                        'displayName' => ZurmoModule::RIGHT_ACCESS_ADMINISTRATION,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_BULK_WRITE'   => array(
                        'displayName' => ZurmoModule::RIGHT_BULK_WRITE,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_GLOBAL_CONFIGURATION'   => array(
                        'displayName' => ZurmoModule::RIGHT_ACCESS_GLOBAL_CONFIGURATION,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_CURRENCY_CONFIGURATION'   => array(
                        'displayName' => ZurmoModule::RIGHT_ACCESS_CURRENCY_CONFIGURATION,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'UsersModule' => array(
                    'RIGHT_CHANGE_USER_PASSWORDS'   => array(
                        'displayName' => UsersModule::RIGHT_CHANGE_USER_PASSWORDS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_LOGIN_VIA_WEB'   => array(
                        'displayName' => UsersModule::RIGHT_LOGIN_VIA_WEB,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_LOGIN_VIA_MOBILE'   => array(
                        'displayName' => UsersModule::RIGHT_LOGIN_VIA_MOBILE,
                        'explicit'    => Right::ALLOW,
                        'inherited'   => null,
                        'effective'   => Right::ALLOW,
                    ),
                    'RIGHT_LOGIN_VIA_WEB_API'   => array(
                        'displayName' => UsersModule::RIGHT_LOGIN_VIA_WEB_API,
                        'explicit'    => null,
                        'inherited'   => Right::ALLOW,
                        'effective'   => Right::ALLOW,
                    ),
                    'RIGHT_CREATE_USERS'   => array(
                        'displayName' => UsersModule::RIGHT_CREATE_USERS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_USERS'   => array(
                        'displayName' => UsersModule::RIGHT_ACCESS_USERS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
            );
            $this->assertEquals($compareData, $form->data);
            $fakePost = array(
                'UsersModule__RIGHT_LOGIN_VIA_WEB_API' => strval(Right::ALLOW),
                'UsersModule__RIGHT_LOGIN_VIA_MOBILE'  => '',
                'UsersModule__RIGHT_LOGIN_VIA_WEB'     => strval(Right::DENY),

            );
            $fakePost = RightsFormUtil::typeCastPostData($fakePost);
            $saved = RightsFormUtil::setRightsFromCastedPost($fakePost, $group);
            $this->assertTrue($saved);
            $group->forget();
            $group = Group::getByName('viewGroup');
            $compareData = array(
                'AccountsModule' => array(
                    'RIGHT_CREATE_ACCOUNTS'   => array(
                        'displayName' => AccountsModule::RIGHT_CREATE_ACCOUNTS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_ACCOUNTS'   => array(
                        'displayName' => AccountsModule::RIGHT_DELETE_ACCOUNTS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_ACCOUNTS'   => array(
                        'displayName' => AccountsModule::RIGHT_ACCESS_ACCOUNTS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'ContactsModule' => array(
                    'RIGHT_CREATE_CONTACTS'   => array(
                        'displayName' => ContactsModule::RIGHT_CREATE_CONTACTS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_CONTACTS'   => array(
                        'displayName' => ContactsModule::RIGHT_DELETE_CONTACTS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_CONTACTS'   => array(
                        'displayName' => ContactsModule::RIGHT_ACCESS_CONTACTS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'DesignerModule' => array(
                    'RIGHT_ACCESS_DESIGNER'   => array(
                        'displayName' => DesignerModule::RIGHT_ACCESS_DESIGNER,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'HomeModule' => array(
                    'RIGHT_CREATE_DASHBOARDS'   => array(
                        'displayName' => HomeModule::RIGHT_CREATE_DASHBOARDS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_DASHBOARDS'   => array(
                        'displayName' => HomeModule::RIGHT_DELETE_DASHBOARDS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_DASHBOARDS'   => array(
                        'displayName' => HomeModule::RIGHT_ACCESS_DASHBOARDS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'JobsManagerModule' => array(
                    'RIGHT_ACCESS_JOBSMANAGER'   => array(
                        'displayName' => JobsManagerModule::RIGHT_ACCESS_JOBSMANAGER,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'LeadsModule' => array(
                    'RIGHT_CREATE_LEADS'   => array(
                        'displayName' => LeadsModule::RIGHT_CREATE_LEADS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_LEADS'   => array(
                        'displayName' => LeadsModule::RIGHT_DELETE_LEADS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_LEADS'   => array(
                        'displayName' => LeadsModule::RIGHT_ACCESS_LEADS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_CONVERT_LEADS'   => array(
                        'displayName' => LeadsModule::RIGHT_CONVERT_LEADS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'MapsModule' => array(
                    'RIGHT_ACCESS_MAPS_ADMINISTRATION'   => array(
                        'displayName' => MapsModule::RIGHT_ACCESS_MAPS_ADMINISTRATION,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'NotesModule' => array(
                    'RIGHT_CREATE_NOTES'   => array(
                        'displayName' => NotesModule::RIGHT_CREATE_NOTES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_NOTES'   => array(
                        'displayName' => NotesModule::RIGHT_DELETE_NOTES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_NOTES'   => array(
                        'displayName' => NotesModule::RIGHT_ACCESS_NOTES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'OpportunitiesModule' => array(
                    'RIGHT_CREATE_OPPORTUNITIES'   => array(
                        'displayName' => OpportunitiesModule::RIGHT_CREATE_OPPORTUNITIES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_OPPORTUNITIES'   => array(
                        'displayName' => OpportunitiesModule::RIGHT_DELETE_OPPORTUNITIES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_OPPORTUNITIES'   => array(
                        'displayName' => OpportunitiesModule::RIGHT_ACCESS_OPPORTUNITIES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'GroupsModule'  => array(
                    'RIGHT_CREATE_GROUPS'   => array(
                        'displayName' => GroupsModule::RIGHT_CREATE_GROUPS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_GROUPS'   => array(
                        'displayName' => GroupsModule::RIGHT_DELETE_GROUPS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_GROUPS'   => array(
                        'displayName' => GroupsModule::RIGHT_ACCESS_GROUPS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'ImportModule'  => array(
                    'RIGHT_ACCESS_IMPORT'   => array(
                        'displayName' => ImportModule::RIGHT_ACCESS_IMPORT,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'MeetingsModule'  => array(
                    'RIGHT_CREATE_MEETINGS'   => array(
                        'displayName' => MeetingsModule::RIGHT_CREATE_MEETINGS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_MEETINGS'   => array(
                        'displayName' => MeetingsModule::RIGHT_DELETE_MEETINGS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_MEETINGS'   => array(
                        'displayName' => MeetingsModule::RIGHT_ACCESS_MEETINGS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'RolesModule'  => array(
                    'RIGHT_CREATE_ROLES'   => array(
                        'displayName' => RolesModule::RIGHT_CREATE_ROLES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_ROLES'   => array(
                        'displayName' => RolesModule::RIGHT_DELETE_ROLES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_ROLES'   => array(
                        'displayName' => RolesModule::RIGHT_ACCESS_ROLES,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'TasksModule'  => array(
                    'RIGHT_CREATE_TASKS'   => array(
                        'displayName' => TasksModule::RIGHT_CREATE_TASKS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_DELETE_TASKS'   => array(
                        'displayName' => TasksModule::RIGHT_DELETE_TASKS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_TASKS'   => array(
                        'displayName' => TasksModule::RIGHT_ACCESS_TASKS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'ZurmoModule'  => array(
                    'RIGHT_ACCESS_ADMINISTRATION'   => array(
                        'displayName' => ZurmoModule::RIGHT_ACCESS_ADMINISTRATION,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_BULK_WRITE'   => array(
                        'displayName' => ZurmoModule::RIGHT_BULK_WRITE,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_GLOBAL_CONFIGURATION'   => array(
                        'displayName' => ZurmoModule::RIGHT_ACCESS_GLOBAL_CONFIGURATION,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_CURRENCY_CONFIGURATION'   => array(
                        'displayName' => ZurmoModule::RIGHT_ACCESS_CURRENCY_CONFIGURATION,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
                'UsersModule' => array(
                    'RIGHT_CHANGE_USER_PASSWORDS'   => array(
                        'displayName' => UsersModule::RIGHT_CHANGE_USER_PASSWORDS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_LOGIN_VIA_WEB'   => array(
                        'displayName' => UsersModule::RIGHT_LOGIN_VIA_WEB,
                        'explicit'   => Right::DENY,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_LOGIN_VIA_MOBILE'   => array(
                        'displayName' => UsersModule::RIGHT_LOGIN_VIA_MOBILE,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_LOGIN_VIA_WEB_API'   => array(
                        'displayName' => UsersModule::RIGHT_LOGIN_VIA_WEB_API,
                        'explicit'    => Right::ALLOW,
                        'inherited'   => Right::ALLOW,
                        'effective'   => Right::ALLOW,
                    ),
                    'RIGHT_CREATE_USERS'   => array(
                        'displayName' => UsersModule::RIGHT_CREATE_USERS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_USERS'   => array(
                        'displayName' => UsersModule::RIGHT_ACCESS_USERS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
            );
            $data = RightsUtil::getAllModuleRightsDataByPermitable($group);
            $this->assertEquals($compareData, $data);
            $group->forget();
        }

        public function testGetDerivedAttributeNameFromTwoStrings()
        {
            $attributeName = FormModelUtil::getDerivedAttributeNameFromTwoStrings('x', 'y');
            $this->assertEquals('x__y', $attributeName);
        }

        /**
         * @depends testRightsFormUtilSetRightsFromPost
         */
        public function testGiveUserAccessToModule()
        {
            $user = User::getByUsername('billy');
            $this->assertFalse(RightsUtil::canUserAccessModule('AccountsModule', $user));
            $group = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $fakePost = array(
                'AccountsModule__RIGHT_ACCESS_ACCOUNTS' => strval(Right::ALLOW),
            );
            $fakePost = RightsFormUtil::typeCastPostData($fakePost);
            $saved = RightsFormUtil::setRightsFromCastedPost($fakePost, $group);
            $this->assertTrue($saved);
            $this->assertTrue(RightsUtil::canUserAccessModule('AccountsModule', $user));
        }
    }
?>
