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

    /**
     * Class to make default data that needs to be created upon an installation.
     */
    class GroupsDefaultDataMaker extends DefaultDataMaker
    {
        public function make()
        {
            $everyone   = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $everyone->setRight('UsersModule',         UsersModule::RIGHT_LOGIN_VIA_WEB);
            $everyone->setRight('UsersModule',         UsersModule::RIGHT_LOGIN_VIA_MOBILE);
            $everyone->setRight('UsersModule',         UsersModule::RIGHT_LOGIN_VIA_WEB_API);
            $everyone->setRight('AccountsModule',      AccountsModule::RIGHT_ACCESS_ACCOUNTS, Right::ALLOW);
            $everyone->setRight('AccountsModule',      AccountsModule::RIGHT_CREATE_ACCOUNTS, Right::ALLOW);
            $everyone->setRight('AccountsModule',      AccountsModule::RIGHT_DELETE_ACCOUNTS, Right::ALLOW);
            $everyone->setRight('ContactsModule',      ContactsModule::RIGHT_ACCESS_CONTACTS, Right::ALLOW);
            $everyone->setRight('ContactsModule',      ContactsModule::RIGHT_CREATE_CONTACTS, Right::ALLOW);
            $everyone->setRight('ContactsModule',      ContactsModule::RIGHT_DELETE_CONTACTS, Right::ALLOW);
            $everyone->setRight('ConversationsModule', ConversationsModule::RIGHT_ACCESS_CONVERSATIONS, Right::ALLOW);
            $everyone->setRight('ConversationsModule', ConversationsModule::RIGHT_CREATE_CONVERSATIONS, Right::ALLOW);
            $everyone->setRight('ConversationsModule', ConversationsModule::RIGHT_DELETE_CONVERSATIONS, Right::ALLOW);
            $everyone->setRight('EmailMessagesModule', EmailMessagesModule::RIGHT_ACCESS_EMAIL_MESSAGES, Right::ALLOW);
            $everyone->setRight('EmailMessagesModule', EmailMessagesModule::RIGHT_CREATE_EMAIL_MESSAGES, Right::ALLOW);
            $everyone->setRight('EmailMessagesModule', EmailMessagesModule::RIGHT_DELETE_EMAIL_MESSAGES, Right::ALLOW);
            $everyone->setRight('EmailTemplatesModule', EmailTemplatesModule::RIGHT_ACCESS_EMAIL_TEMPLATES, Right::ALLOW);
            $everyone->setRight('EmailTemplatesModule', EmailTemplatesModule::RIGHT_CREATE_EMAIL_TEMPLATES, Right::ALLOW);
            $everyone->setRight('EmailTemplatesModule', EmailTemplatesModule::RIGHT_DELETE_EMAIL_TEMPLATES, Right::ALLOW);
            $everyone->setRight('LeadsModule',         LeadsModule::RIGHT_ACCESS_LEADS, Right::ALLOW);
            $everyone->setRight('LeadsModule',         LeadsModule::RIGHT_CREATE_LEADS, Right::ALLOW);
            $everyone->setRight('LeadsModule',         LeadsModule::RIGHT_DELETE_LEADS, Right::ALLOW);
            $everyone->setRight('LeadsModule',         LeadsModule::RIGHT_CONVERT_LEADS, Right::ALLOW);
            $everyone->setRight('OpportunitiesModule', OpportunitiesModule::RIGHT_ACCESS_OPPORTUNITIES, Right::ALLOW);
            $everyone->setRight('OpportunitiesModule', OpportunitiesModule::RIGHT_CREATE_OPPORTUNITIES, Right::ALLOW);
            $everyone->setRight('OpportunitiesModule', OpportunitiesModule::RIGHT_DELETE_OPPORTUNITIES, Right::ALLOW);
            $everyone->setRight('MarketingModule',      MarketingModule::RIGHT_ACCESS_MARKETING, Right::ALLOW);
            $everyone->setRight('MarketingListsModule', MarketingListsModule::RIGHT_ACCESS_MARKETING_LISTS, Right::ALLOW);
            $everyone->setRight('MarketingListsModule', MarketingListsModule::RIGHT_CREATE_MARKETING_LISTS, Right::ALLOW);
            $everyone->setRight('MarketingListsModule', MarketingListsModule::RIGHT_DELETE_MARKETING_LISTS, Right::ALLOW);
            $everyone->setRight('MeetingsModule',      MeetingsModule::RIGHT_ACCESS_MEETINGS, Right::ALLOW);
            $everyone->setRight('MeetingsModule',      MeetingsModule::RIGHT_CREATE_MEETINGS, Right::ALLOW);
            $everyone->setRight('MeetingsModule',      MeetingsModule::RIGHT_DELETE_MEETINGS, Right::ALLOW);
            $everyone->setRight('MissionsModule',      MissionsModule::RIGHT_ACCESS_MISSIONS, Right::ALLOW);
            $everyone->setRight('MissionsModule',      MissionsModule::RIGHT_CREATE_MISSIONS, Right::ALLOW);
            $everyone->setRight('MissionsModule',      MissionsModule::RIGHT_DELETE_MISSIONS, Right::ALLOW);
            $everyone->setRight('NotesModule',         NotesModule::RIGHT_ACCESS_NOTES, Right::ALLOW);
            $everyone->setRight('NotesModule',         NotesModule::RIGHT_CREATE_NOTES, Right::ALLOW);
            $everyone->setRight('NotesModule',         NotesModule::RIGHT_DELETE_NOTES, Right::ALLOW);
            $everyone->setRight('ReportsModule',       ReportsModule::RIGHT_ACCESS_REPORTS, Right::ALLOW);
            $everyone->setRight('ReportsModule',       ReportsModule::RIGHT_CREATE_REPORTS, Right::ALLOW);
            $everyone->setRight('ReportsModule',       ReportsModule::RIGHT_DELETE_REPORTS, Right::ALLOW);
            $everyone->setRight('TasksModule',         TasksModule::RIGHT_ACCESS_TASKS, Right::ALLOW);
            $everyone->setRight('TasksModule',         TasksModule::RIGHT_CREATE_TASKS, Right::ALLOW);
            $everyone->setRight('TasksModule',         TasksModule::RIGHT_DELETE_TASKS, Right::ALLOW);
            $everyone->setRight('HomeModule',          HomeModule::RIGHT_ACCESS_DASHBOARDS, Right::ALLOW);
            $everyone->setRight('HomeModule',          HomeModule::RIGHT_CREATE_DASHBOARDS, Right::ALLOW);
            $everyone->setRight('HomeModule',          HomeModule::RIGHT_DELETE_DASHBOARDS, Right::ALLOW);
            $everyone->setRight('ExportModule',        ExportModule::RIGHT_ACCESS_EXPORT, Right::ALLOW);
            $everyone->setRight('SocialItemsModule',   SocialItemsModule::RIGHT_ACCESS_SOCIAL_ITEMS, Right::ALLOW);
            $saved = $everyone->save();
            assert('$saved');
        }
    }
?>