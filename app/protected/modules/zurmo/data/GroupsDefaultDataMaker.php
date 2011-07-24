<?php
    /**
     * Class to make default data that needs to be created upon an installation.
     */
    class GroupsDefaultDataMaker extends DefaultDataMaker
    {
        public function make()
        {
            $everyone   = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $everyone->setRight('UsersModule',         UsersModule::RIGHT_LOGIN_VIA_WEB);
            $everyone->setRight('AccountsModule',      AccountsModule::RIGHT_ACCESS_ACCOUNTS, Right::ALLOW);
            $everyone->setRight('ContactsModule',      ContactsModule::RIGHT_ACCESS_CONTACTS, Right::ALLOW);
            $everyone->setRight('LeadsModule',         LeadsModule::RIGHT_ACCESS_LEADS, Right::ALLOW);
            $everyone->setRight('OpportunitiesModule', OpportunitiesModule::RIGHT_ACCESS_OPPORTUNITIES, Right::ALLOW);
            $everyone->setRight('MeetingsModule',      MeetingsModule::RIGHT_ACCESS_MEETINGS, Right::ALLOW);
            $everyone->setRight('NotesModule',         NotesModule::RIGHT_ACCESS_NOTES, Right::ALLOW);
            $everyone->setRight('TasksModule',         TasksModule::RIGHT_ACCESS_TASKS, Right::ALLOW);
            $everyone->setRight('HomeModule',          HomeModule::RIGHT_ACCESS_DASHBOARDS, Right::ALLOW);
            $saved = $everyone->save();
            assert('$saved');
        }
    }
?>