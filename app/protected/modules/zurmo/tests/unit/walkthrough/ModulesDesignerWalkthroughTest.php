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

    /**
     * Designer Module Walkthrough of accounts, contacts, leads, and opportunities.
     * Walkthrough for the super user of all possible controller actions.
     * Since this is a super user, he should have access to all controller actions
     * without any exceptions being thrown.
     *
     * Walkthrough for a peon user should show failure to access pages.
     */
    class ModulesDesignerWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            ContactsModule::loadStartingData();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            //Create models for testing.
            $account = AccountTestHelper::createAccountByNameForOwner        ('superAccount', $super);
            ContactTestHelper::createContactWithAccountByNameForOwner        ('superContact', $super, $account);
            LeadTestHelper::createLeadbyNameForOwner                         ('superLead',    $super);
            OpportunityTestHelper::createOpportunityStagesIfDoesNotExist     ();
            OpportunityTestHelper::createOpportunityWithAccountByNameForOwner('superOpp',     $super, $account);
            TaskTestHelper::createTaskWithOwnerAndRelatedAccount             ('superTask', $super, $account);
            NoteTestHelper::createNoteWithOwnerAndRelatedAccount             ('superNote', $super, $account);
            MeetingTestHelper::createMeetingWithOwnerAndRelatedAccount          ('superMeeting', $super, $account);
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Default Controller actions requiring some sort of parameter via POST or GET
            //Load Modules Menu for each applicable module.
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'AccountsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/modulesMenu');
            $this->setGetArray(array('moduleClassName' => 'ContactsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/modulesMenu');
            $this->setGetArray(array('moduleClassName' => 'LeadsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/modulesMenu');
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/modulesMenu');
            $this->setGetArray(array('moduleClassName' => 'UsersModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/modulesMenu');

            //Load AttributesList for each applicable module.
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'AccountsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributesList');
            $this->setGetArray(array('moduleClassName' => 'ContactsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributesList');
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributesList');
            $this->setGetArray(array('moduleClassName' => 'TasksModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributesList');
            $this->setGetArray(array('moduleClassName' => 'NotesModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributesList');
            $this->setGetArray(array('moduleClassName' => 'MeetingsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributesList');

            //Load ModuleLayoutsList for each applicable module.
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'AccountsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleLayoutsList');
            $this->setGetArray(array('moduleClassName' => 'ContactsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleLayoutsList');
            $this->setGetArray(array('moduleClassName' => 'LeadsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleLayoutsList');
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleLayoutsList');
            $this->setGetArray(array('moduleClassName' => 'TasksModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleLayoutsList');
            $this->setGetArray(array('moduleClassName' => 'NotesModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleLayoutsList');
            $this->setGetArray(array('moduleClassName' => 'MeetingsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleLayoutsList');
            $this->setGetArray(array('moduleClassName' => 'UsersModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleLayoutsList');

            //Load ModuleEdit view for each applicable module.
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'AccountsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleEdit');
            $this->setGetArray(array('moduleClassName' => 'ContactsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleEdit');
            $this->setGetArray(array('moduleClassName' => 'LeadsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleEdit');
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleEdit');
            $this->setGetArray(array('moduleClassName' => 'TasksModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleEdit');
            $this->setGetArray(array('moduleClassName' => 'NotesModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleEdit');
            $this->setGetArray(array('moduleClassName' => 'MeetingsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleEdit');

            //Now validate save with failed validation.
            $this->setGetArray(array('moduleClassName' => 'AccountsModule'));
            $this->setPostArray(array('ajax' => 'edit-form',
                'AccountsModuleForm' => $this->createModuleEditBadValidationPostData()));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/moduleEdit');
            $this->assertTrue(strlen($content) > 50); //approximate, but should definetely be larger than 50.
            $this->setGetArray(array('moduleClassName' => 'ContactsModule'));
            $this->setPostArray(array('ajax' => 'edit-form',
                'ContactsModuleForm' => $this->createModuleEditBadValidationPostData()));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/moduleEdit');
            $this->assertTrue(strlen($content) > 50); //approximate, but should definetely be larger than 50.
            $this->setGetArray(array('moduleClassName' => 'LeadsModule'));
            $this->setPostArray(array('ajax' => 'edit-form',
                'LeadsModuleForm' => $this->createModuleEditBadValidationPostData()));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/moduleEdit');
            $this->assertTrue(strlen($content) > 50); //approximate, but should definetely be larger than 50.
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule'));
            $this->setPostArray(array('ajax' => 'edit-form',
                'OpportunitiesModuleForm' => $this->createModuleEditBadValidationPostData()));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/moduleEdit');
            $this->assertTrue(strlen($content) > 50); //approximate, but should definetely be larger than 50.
            $this->setGetArray(array('moduleClassName' => 'TasksModule'));
            $this->setPostArray(array('ajax' => 'edit-form',
                'TasksModuleForm' => $this->createModuleEditBadValidationPostData()));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/moduleEdit');
            $this->assertTrue(strlen($content) > 50); //approximate, but should definetely be larger than 50.
            $this->setGetArray(array('moduleClassName' => 'NotesModule'));
            $this->setPostArray(array('ajax' => 'edit-form',
                'NotesModuleForm' => $this->createModuleEditBadValidationPostData()));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/moduleEdit');
            $this->assertTrue(strlen($content) > 50); //approximate, but should definetely be larger than 50.
            $this->setGetArray(array('moduleClassName' => 'MeetingsModule'));
            $this->setPostArray(array('ajax' => 'edit-form',
                'MeetingsModuleForm' => $this->createModuleEditBadValidationPostData()));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/moduleEdit');
            $this->assertTrue(strlen($content) > 50); //approximate, but should definetely be larger than 50.

            //Now validate save with successful validation.
            $this->setGetArray(array('moduleClassName' => 'AccountsModule'));
            $this->setPostArray(array('ajax' => 'edit-form',
                'AccountsModuleForm' => $this->createModuleEditGoodValidationPostData('acc new name')));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/moduleEdit');
            $this->assertEquals('[]', $content);
            $this->setGetArray(array('moduleClassName' => 'ContactsModule'));
            $this->setPostArray(array('ajax' => 'edit-form',
                'ContactsModuleForm' => $this->createModuleEditGoodValidationPostData('con new name')));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/moduleEdit');
            $this->assertEquals('[]', $content);
            $this->setGetArray(array('moduleClassName' => 'LeadsModule'));
            $this->setPostArray(array('ajax' => 'edit-form',
                'LeadsModuleForm' => $this->createModuleEditGoodValidationPostData('lea new name')));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/moduleEdit');
            $this->assertEquals('[]', $content);
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule'));
            $this->setPostArray(array('ajax' => 'edit-form',
                'OpportunitiesModuleForm' => $this->createModuleEditGoodValidationPostData('opp new name')));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/moduleEdit');
            $this->assertEquals('[]', $content);
            $this->setGetArray(array('moduleClassName' => 'TasksModule'));
            $this->setPostArray(array('ajax' => 'edit-form',
                'TasksModuleForm' => $this->createModuleEditGoodValidationPostData('task new name')));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/moduleEdit');
            $this->assertEquals('[]', $content);
            $this->setGetArray(array('moduleClassName' => 'NotesModule'));
            $this->setPostArray(array('ajax' => 'edit-form',
                'NotesModuleForm' => $this->createModuleEditGoodValidationPostData('note new name')));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/moduleEdit');
            $this->assertEquals('[]', $content);
            $this->setGetArray(array('moduleClassName' => 'MeetingsModule'));
            $this->setPostArray(array('ajax' => 'edit-form',
                'MeetingsModuleForm' => $this->createModuleEditGoodValidationPostData('meeting new name')));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/moduleEdit');
            $this->assertEquals('[]', $content);

            //Now save successfully.
            $this->setGetArray(array('moduleClassName' => 'AccountsModule'));
            $this->setPostArray(array('save' => 'Save',
                'AccountsModuleForm' => $this->createModuleEditGoodValidationPostData('acc new name')));
            $this->runControllerWithRedirectExceptionAndGetContent('designer/default/moduleEdit');
            $this->setGetArray(array('moduleClassName' => 'ContactsModule'));
            $this->setPostArray(array('save' => 'Save',
                'ContactsModuleForm' => $this->createModuleEditGoodValidationPostData('con new name')));
            $this->runControllerWithRedirectExceptionAndGetContent('designer/default/moduleEdit');
            $this->setGetArray(array('moduleClassName' => 'LeadsModule'));
            $this->setPostArray(array('save' => 'Save',
                'LeadsModuleForm' => $this->createModuleEditGoodValidationPostData('lea new name')));
            $this->runControllerWithRedirectExceptionAndGetContent('designer/default/moduleEdit');
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule'));
            $this->setPostArray(array('save' => 'Save',
                'OpportunitiesModuleForm' => $this->createModuleEditGoodValidationPostData('opp new name')));
            $this->runControllerWithRedirectExceptionAndGetContent('designer/default/moduleEdit');
            $this->setGetArray(array('moduleClassName' => 'TasksModule'));
            $this->setPostArray(array('save' => 'Save',
                'TasksModuleForm' => $this->createModuleEditGoodValidationPostData('task new name')));
            $this->runControllerWithRedirectExceptionAndGetContent('designer/default/moduleEdit');
            $this->setGetArray(array('moduleClassName' => 'NotesModule'));
            $this->setPostArray(array('save' => 'Save',
                'NotesModuleForm' => $this->createModuleEditGoodValidationPostData('note new name')));
            $this->runControllerWithRedirectExceptionAndGetContent('designer/default/moduleEdit');
            $this->setGetArray(array('moduleClassName' => 'MeetingsModule'));
            $this->setPostArray(array('save' => 'Save',
                'MeetingsModuleForm' => $this->createModuleEditGoodValidationPostData('meeting new name')));
            $this->runControllerWithRedirectExceptionAndGetContent('designer/default/moduleEdit');

            //Now confirm everything did in fact save correctly.
            $this->assertEquals('Acc New Name',  AccountsModule::getModuleLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Acc New Names', AccountsModule::getModuleLabelByTypeAndLanguage('Plural'));
            $this->assertEquals('acc new name',  AccountsModule::getModuleLabelByTypeAndLanguage('SingularLowerCase'));
            $this->assertEquals('acc new names', AccountsModule::getModuleLabelByTypeAndLanguage('PluralLowerCase'));

            $this->assertEquals('Con New Name',  ContactsModule::getModuleLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Con New Names', ContactsModule::getModuleLabelByTypeAndLanguage('Plural'));
            $this->assertEquals('con new name',  ContactsModule::getModuleLabelByTypeAndLanguage('SingularLowerCase'));
            $this->assertEquals('con new names', ContactsModule::getModuleLabelByTypeAndLanguage('PluralLowerCase'));

            $this->assertEquals('Lea New Name',  LeadsModule::getModuleLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Lea New Names', LeadsModule::getModuleLabelByTypeAndLanguage('Plural'));
            $this->assertEquals('lea new name',  LeadsModule::getModuleLabelByTypeAndLanguage('SingularLowerCase'));
            $this->assertEquals('lea new names', LeadsModule::getModuleLabelByTypeAndLanguage('PluralLowerCase'));

            $this->assertEquals('Opp New Name',  OpportunitiesModule::getModuleLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Opp New Names', OpportunitiesModule::getModuleLabelByTypeAndLanguage('Plural'));
            $this->assertEquals('opp new name',  OpportunitiesModule::getModuleLabelByTypeAndLanguage('SingularLowerCase'));
            $this->assertEquals('opp new names', OpportunitiesModule::getModuleLabelByTypeAndLanguage('PluralLowerCase'));

            $this->assertEquals('Task New Name',  TasksModule::getModuleLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Task New Names', TasksModule::getModuleLabelByTypeAndLanguage('Plural'));
            $this->assertEquals('task new name',  TasksModule::getModuleLabelByTypeAndLanguage('SingularLowerCase'));
            $this->assertEquals('task new names', TasksModule::getModuleLabelByTypeAndLanguage('PluralLowerCase'));

            $this->assertEquals('Note New Name',  NotesModule::getModuleLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Note New Names', NotesModule::getModuleLabelByTypeAndLanguage('Plural'));
            $this->assertEquals('note new name',  NotesModule::getModuleLabelByTypeAndLanguage('SingularLowerCase'));
            $this->assertEquals('note new names', NotesModule::getModuleLabelByTypeAndLanguage('PluralLowerCase'));

            $this->assertEquals('Meeting New Name',  MeetingsModule::getModuleLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Meeting New Names', MeetingsModule::getModuleLabelByTypeAndLanguage('Plural'));
            $this->assertEquals('meeting new name',  MeetingsModule::getModuleLabelByTypeAndLanguage('SingularLowerCase'));
            $this->assertEquals('meeting new names', MeetingsModule::getModuleLabelByTypeAndLanguage('PluralLowerCase'));

            //Load LayoutEdit for each applicable module and applicable layout
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'AccountsModule',
                                     'viewClassName'   => 'AccountEditAndDetailsView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'AccountsModule',
                                     'viewClassName'   => 'AccountsListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'AccountsModule',
                                     'viewClassName'   => 'AccountsMassEditView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'AccountsModule',
                                     'viewClassName'   => 'AccountsModalListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'AccountsModule',
                                     'viewClassName'   => 'AccountsModalSearchView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'AccountsModule',
                                     'viewClassName'   => 'AccountsRelatedListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'AccountsModule',
                                     'viewClassName'   => 'AccountsSearchView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');

            $this->setGetArray(array('moduleClassName' => 'ContactsModule',
                                     'viewClassName'   => 'ContactEditAndDetailsView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'ContactsModule',
                                     'viewClassName'   => 'ContactsListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'ContactsModule',
                                     'viewClassName'   => 'ContactsModalListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'ContactsModule',
                                     'viewClassName'   => 'ContactsModalSearchView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'ContactsModule',
                                     'viewClassName'   => 'ContactsMassEditView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'ContactsModule',
                                     'viewClassName'   => 'ContactsRelatedListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'ContactsModule',
                                     'viewClassName'   => 'ContactsSearchView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');

            $this->setGetArray(array('moduleClassName' => 'LeadsModule',
                                     'viewClassName'   => 'LeadEditAndDetailsView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'LeadsModule',
                                     'viewClassName'   => 'LeadsListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'LeadsModule',
                                     'viewClassName'   => 'LeadsMassEditView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'LeadsModule',
                                     'viewClassName'   => 'LeadsModalListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'LeadsModule',
                                     'viewClassName'   => 'LeadsModalSearchView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'LeadsModule',
                                     'viewClassName'   => 'LeadsSearchView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');

            //todo: save changes to leads layouts

            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule',
                                     'viewClassName'   => 'OpportunitiesListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule',
                                     'viewClassName'   => 'OpportunitiesModalListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule',
                                     'viewClassName'   => 'OpportunitiesModalSearchView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule',
                                     'viewClassName'   => 'OpportunitiesMassEditView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule',
                                     'viewClassName'   => 'OpportunitiesRelatedListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule',
                                     'viewClassName'   => 'OpportunitiesSearchView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule',
                                     'viewClassName'   => 'OpportunityEditAndDetailsView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');

            $this->setGetArray(array('moduleClassName' => 'TasksModule',
                                     'viewClassName'   => 'OpenTasksForAccountRelatedListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'TasksModule',
                                     'viewClassName'   => 'OpenTasksForContactRelatedListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'TasksModule',
                                     'viewClassName'   => 'OpenTasksForOpportunityRelatedListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'TasksModule',
                                     'viewClassName'   => 'TaskEditAndDetailsView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');

            $this->setGetArray(array('moduleClassName' => 'NotesModule',
                                     'viewClassName'   => 'NoteEditAndDetailsView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'NotesModule',
                                     'viewClassName'   => 'NoteInlineEditView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');

            $this->setGetArray(array('moduleClassName' => 'MeetingsModule',
                                     'viewClassName'   => 'UpcomingMeetingsForAccountRelatedListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'MeetingsModule',
                                     'viewClassName'   => 'UpcomingMeetingsForContactRelatedListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'MeetingsModule',
                                     'viewClassName'   => 'UpcomingMeetingsForOpportunityRelatedListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'MeetingsModule',
                                     'viewClassName'   => 'MeetingEditAndDetailsView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');

            $this->setGetArray(array('moduleClassName' => 'UsersModule',
                                     'viewClassName'   => 'UsersListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'UsersModule',
                                     'viewClassName'   => 'UsersMassEditView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'UsersModule',
                                     'viewClassName'   => 'UsersModalListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'UsersModule',
                                     'viewClassName'   => 'UsersModalSearchView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'UsersModule',
                                     'viewClassName'   => 'UsersSearchView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            //todo: save changes to User layouts
        }

        /**
         * @depends testSuperUserAllDefaultControllerActions
         */
        public function testSuperUserCustomFieldsWalkthroughForAccountsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test create field list.
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'AccountsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributeCreate');

            //View creation screen, then create custom field for each custom field type.
            $this->createCheckBoxCustomFieldByModule            ('AccountsModule', 'checkbox');
            $this->createCurrencyValueCustomFieldByModule       ('AccountsModule', 'currency');
            $this->createDateCustomFieldByModule                ('AccountsModule', 'date');
            $this->createDateTimeCustomFieldByModule            ('AccountsModule', 'datetime');
            $this->createDecimalCustomFieldByModule             ('AccountsModule', 'decimal');
            $this->createDropDownCustomFieldByModule            ('AccountsModule', 'picklist');
            $this->createIntegerCustomFieldByModule             ('AccountsModule', 'integer');
            $this->createMultiSelectDropDownCustomFieldByModule ('AccountsModule', 'multiselect');
            $this->createPhoneCustomFieldByModule               ('AccountsModule', 'phone');
            $this->createRadioDropDownCustomFieldByModule       ('AccountsModule', 'radio');
            $this->createTextCustomFieldByModule                ('AccountsModule', 'text');
            $this->createTextAreaCustomFieldByModule            ('AccountsModule', 'textarea');
            $this->createUrlCustomFieldByModule                 ('AccountsModule', 'url');
        }

        /**
         * @depends testSuperUserCustomFieldsWalkthroughForAccountsModule
         */
        public function testSuperUserCustomFieldsWalkthroughForContactsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test create field list.
            $this->setGetArray(array('moduleClassName' => 'ContactsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributeCreate');

            //View creation screen, then create custom field for each custom field type.
            $this->createCheckBoxCustomFieldByModule            ('ContactsModule', 'checkbox');
            $this->createCurrencyValueCustomFieldByModule       ('ContactsModule', 'currency');
            $this->createDateCustomFieldByModule                ('ContactsModule', 'date');
            $this->createDateTimeCustomFieldByModule            ('ContactsModule', 'datetime');
            $this->createDecimalCustomFieldByModule             ('ContactsModule', 'decimal');
            $this->createDropDownCustomFieldByModule            ('ContactsModule', 'picklist');
            $this->createIntegerCustomFieldByModule             ('ContactsModule', 'integer');
            $this->createMultiSelectDropDownCustomFieldByModule ('ContactsModule', 'multiselect');
            $this->createPhoneCustomFieldByModule               ('ContactsModule', 'phone');
            $this->createRadioDropDownCustomFieldByModule       ('ContactsModule', 'radio');
            $this->createTextCustomFieldByModule                ('ContactsModule', 'text');
            $this->createTextAreaCustomFieldByModule            ('ContactsModule', 'textarea');
            $this->createUrlCustomFieldByModule                 ('ContactsModule', 'url');
        }

        /**
         * @depends testSuperUserCustomFieldsWalkthroughForContactsModule
         */
        public function testSuperUserModifyContactStatesDefaultValueItemsInDropDown()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //test existing ContactState changes to labels.
            $extraPostData = array( 'startingStateOrder'  => '4',
                                    'isAudited'           => '1',
                                    'isRequired'          => '1',
                                    'contactStatesData' => array(
                                                'New', 'In ProgressD', 'RecycledC', 'QualifiedA', 'CustomerF', 'YRE'
                                    ),
                                    'contactStatesDataExistingValues' => array(
                                                'New', 'In Progress', 'Recycled', 'Qualified', 'Customer', 'YRE'
                                    )
                                    );
            $this->createCustomAttributeWalkthroughSequence('ContactsModule', 'state', 'ContactState',
                $extraPostData, 'state');
            $compareData = array(
                'New',
                'In ProgressD',
                'RecycledC',
                'QualifiedA',
                'CustomerF',
                'YRE'
            );
            $this->assertEquals($compareData, ContactsUtil::getContactStateDataKeyedByOrder());

            //todo: test that the changed labels, updated the existing data if any existed.

            //Removing ContactStates items
            $extraPostData = array( 'startingStateOrder'  => '2',
                                    'isAudited'           => '1',
                                    'isRequired'          => '1',
                                    'contactStatesData' => array(
                                                'New', 'RecycledC', 'QualifiedA'
                                    ));
            $this->createCustomAttributeWalkthroughSequence('ContactsModule', 'state', 'ContactState',
                $extraPostData, 'state');
            $compareData = array(
                'New',
                'RecycledC',
                'QualifiedA',
            );
            $this->assertEquals($compareData, ContactsUtil::getContactStateDataKeyedByOrder());

            //Adding ContactStates items
            $extraPostData = array( 'startingStateOrder'  => '2',
                                    'isAudited'           => '1',
                                    'isRequired'          => '1',
                                    'contactStatesData' => array(
                                                'New', 'RecycledC', 'QualifiedA', 'NewItem', 'NewItem2'
                                    ));
            $this->createCustomAttributeWalkthroughSequence('ContactsModule', 'state', 'ContactState',
                $extraPostData, 'state');
            $compareData = array(
                'New',
                'RecycledC',
                'QualifiedA',
                'NewItem',
                'NewItem2'
            );
            $this->assertEquals($compareData, ContactsUtil::getContactStateDataKeyedByOrder());

            //Changing order of ContactStates items
            $extraPostData = array( 'startingStateOrder'  => '2',
                                    'isAudited'           => '1',
                                    'isRequired'          => '1',
                                    'contactStatesData' => array(
                                                'New', 'NewItem2', 'RecycledC', 'QualifiedA', 'NewItem'
                                    ));
            $this->createCustomAttributeWalkthroughSequence('ContactsModule', 'state', 'ContactState',
                $extraPostData, 'state');
            $compareData = array(
                'New',
                'NewItem2',
                'RecycledC',
                'QualifiedA',
                'NewItem',
            );
            $this->assertEquals($compareData, ContactsUtil::getContactStateDataKeyedByOrder());

            //test trying to save 2 ContactStates with the same name (QualifiedA is twice)
            $extraPostData = array( 'startingStateOrder'  => '2',
                                    'isAudited'           => '1',
                                    'isRequired'          => '1',
                                    'contactStatesData' => array(
                                                'New', 'NewItem2', 'QualifiedA', 'QualifiedA', 'NewItem'
                                    ));
            $this->setGetArray(array(   'moduleClassName'       => 'ContactsModule',
                                        'attributeTypeName'     => 'ContactState',
                                        'attributeName'         => 'state'));
            $this->setPostArray(array(   'ajax'                     => 'edit-form',
                                        'ContactStateAttributeForm' => array_merge(array(
                                            'attributeLabels'       => $this->createAttributeLabelGoodValidationPostData('state'),
                                            'attributeName'         => 'state',
                                        ), $extraPostData)));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/attributeEdit');
            $this->assertTrue(strlen($content) > 50); //approximate, but should definetely be larger than 50.

            //test trying to save 0 ContactStates
            $extraPostData = array( 'startingStateOrder'  => '2',
                                    'isAudited'           => '1',
                                    'isRequired'          => '1',
                                    'contactStatesData' => array());
            $this->setPostArray(array(   'ajax'                 => 'edit-form',
                                        'ContactStateAttributeForm' => array_merge(array(
                                            'attributeLabels' => $this->createAttributeLabelGoodValidationPostData('state'),
                                            'attributeName'     => 'state',
                                        ), $extraPostData)));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/attributeEdit');
            $this->assertTrue(strlen($content) > 50); //approximate, but should definetely be larger than 50.

            //test trying to save contact states that are shorter than the minimum length.
            $extraPostData = array( 'startingStateOrder'  => '2',
                                    'isAudited'           => '1',
                                    'isRequired'          => '1',
                                    'contactStatesData' => array('NA', ' NB', 'NC'));
            $this->setPostArray(array(   'ajax'                 => 'edit-form',
                                        'ContactStateAttributeForm' => array_merge(array(
                                            'attributeLabels' => $this->createAttributeLabelGoodValidationPostData('state'),
                                            'attributeName'     => 'state',
                                        ), $extraPostData)));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/attributeEdit');
            $this->assertTrue(strlen($content) > 50); //approximate, but should definetely be larger than 50.
        }

        /**
         * @depends testSuperUserCustomFieldsWalkthroughForContactsModule
         */
        public function testSuperUserCustomFieldsWalkthroughForOpportunitiesModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test create field list.
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributeCreate');

            //View creation screen, then create custom field for each custom field type.
            $this->createCheckBoxCustomFieldByModule            ('OpportunitiesModule', 'checkbox');
            $this->createCurrencyValueCustomFieldByModule       ('OpportunitiesModule', 'currency');
            $this->createDateCustomFieldByModule                ('OpportunitiesModule', 'date');
            $this->createDateTimeCustomFieldByModule            ('OpportunitiesModule', 'datetime');
            $this->createDecimalCustomFieldByModule             ('OpportunitiesModule', 'decimal');
            $this->createDropDownCustomFieldByModule            ('OpportunitiesModule', 'picklist');
            $this->createIntegerCustomFieldByModule             ('OpportunitiesModule', 'integer');
            $this->createMultiSelectDropDownCustomFieldByModule ('OpportunitiesModule', 'multiselect');
            $this->createPhoneCustomFieldByModule               ('OpportunitiesModule', 'phone');
            $this->createRadioDropDownCustomFieldByModule       ('OpportunitiesModule', 'radio');
            $this->createTextCustomFieldByModule                ('OpportunitiesModule', 'text');
            $this->createTextAreaCustomFieldByModule            ('OpportunitiesModule', 'textarea');
            $this->createUrlCustomFieldByModule                 ('OpportunitiesModule', 'url');
        }

        /**
         * @depends testSuperUserCustomFieldsWalkthroughForOpportunitiesModule
         */
        public function testSuperUserCustomFieldsWalkthroughForTasksModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test create field list.
            $this->setGetArray(array('moduleClassName' => 'TasksModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributeCreate');

            //View creation screen, then create custom field for each custom field type.
            $this->createCheckBoxCustomFieldByModule            ('TasksModule', 'checkbox');
            $this->createCurrencyValueCustomFieldByModule       ('TasksModule', 'currency');
            $this->createDateCustomFieldByModule                ('TasksModule', 'date');
            $this->createDateTimeCustomFieldByModule            ('TasksModule', 'datetime');
            $this->createDecimalCustomFieldByModule             ('TasksModule', 'decimal');
            $this->createDropDownCustomFieldByModule            ('TasksModule', 'picklist');
            $this->createIntegerCustomFieldByModule             ('TasksModule', 'integer');
            $this->createMultiSelectDropDownCustomFieldByModule ('TasksModule', 'multiselect');
            $this->createPhoneCustomFieldByModule               ('TasksModule', 'phone');
            $this->createRadioDropDownCustomFieldByModule       ('TasksModule', 'radio');
            $this->createTextCustomFieldByModule                ('TasksModule', 'text');
            $this->createTextAreaCustomFieldByModule            ('TasksModule', 'textarea');
            $this->createUrlCustomFieldByModule                 ('TasksModule', 'url');
        }

        /**
         * @depends testSuperUserCustomFieldsWalkthroughForTasksModule
         */
        public function testSuperUserCustomFieldsWalkthroughForNotesModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test create field list.
            $this->setGetArray(array('moduleClassName' => 'NotesModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributeCreate');

            //View creation screen, then create custom field for each custom field type.
            $this->createCheckBoxCustomFieldByModule            ('NotesModule', 'checkbox');
            $this->createCurrencyValueCustomFieldByModule       ('NotesModule', 'currency');
            $this->createDateCustomFieldByModule                ('NotesModule', 'date');
            $this->createDateTimeCustomFieldByModule            ('NotesModule', 'datetime');
            $this->createDecimalCustomFieldByModule             ('NotesModule', 'decimal');
            $this->createDropDownCustomFieldByModule            ('NotesModule', 'picklist');
            $this->createIntegerCustomFieldByModule             ('NotesModule', 'integer');
            $this->createMultiSelectDropDownCustomFieldByModule ('NotesModule', 'multiselect');
            $this->createPhoneCustomFieldByModule               ('NotesModule', 'phone');
            $this->createRadioDropDownCustomFieldByModule       ('NotesModule', 'radio');
            $this->createTextCustomFieldByModule                ('NotesModule', 'text');
            $this->createTextAreaCustomFieldByModule            ('NotesModule', 'textarea');
            $this->createUrlCustomFieldByModule                 ('NotesModule', 'url');
        }

        /**
         * @depends testSuperUserCustomFieldsWalkthroughForNotesModule
         */
        public function testSuperUserCustomFieldsWalkthroughForMeetingsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test create field list.
            $this->setGetArray(array('moduleClassName' => 'MeetingsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributeCreate');

            //View creation screen, then create custom field for each custom field type.
            $this->createCheckBoxCustomFieldByModule            ('MeetingsModule', 'checkbox');
            $this->createCurrencyValueCustomFieldByModule       ('MeetingsModule', 'currency');
            $this->createDateCustomFieldByModule                ('MeetingsModule', 'date');
            $this->createDateTimeCustomFieldByModule            ('MeetingsModule', 'datetime');
            $this->createDecimalCustomFieldByModule             ('MeetingsModule', 'decimal');
            $this->createDropDownCustomFieldByModule            ('MeetingsModule', 'picklist');
            $this->createIntegerCustomFieldByModule             ('MeetingsModule', 'integer');
            $this->createMultiSelectDropDownCustomFieldByModule ('MeetingsModule', 'multiselect');
            $this->createPhoneCustomFieldByModule               ('MeetingsModule', 'phone');
            $this->createRadioDropDownCustomFieldByModule       ('MeetingsModule', 'radio');
            $this->createTextCustomFieldByModule                ('MeetingsModule', 'text');
            $this->createTextAreaCustomFieldByModule            ('MeetingsModule', 'textarea');
            $this->createUrlCustomFieldByModule                 ('MeetingsModule', 'url');
        }

        /**
         * @depends testSuperUserCustomFieldsWalkthroughForAccountsModule
         */
        public function testSuperUserAddCustomFieldsToLayoutsForAccountsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Add custom fields to AccountEditAndDetailsView.
            $this->setGetArray(array('moduleClassName' => 'AccountsModule',
                                     'viewClassName'   => 'AccountEditAndDetailsView'));
            $layout = AccountsDesignerWalkthroughHelperUtil::getAccountEditAndDetailsViewLayoutWithAllCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout,
                                      'LayoutPanelsTypeForm' => array('type' => FormLayout::PANELS_DISPLAY_TYPE_ALL)));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to AccountsSearchView.
            $this->setGetArray(array('moduleClassName' => 'AccountsModule',
                                     'viewClassName'   => 'AccountsSearchView'));
            $layout = AccountsDesignerWalkthroughHelperUtil::getAccountsSearchViewLayoutWithAllCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to AccountsModalSearchView.
            $this->setGetArray(array('moduleClassName' => 'AccountsModule',
                                     'viewClassName'   => 'AccountsModalSearchView'));
            $layout = AccountsDesignerWalkthroughHelperUtil::getAccountsSearchViewLayoutWithAllCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to AccountsListView.
            $this->setGetArray(array('moduleClassName' => 'AccountsModule',
                                     'viewClassName'   => 'AccountsListView'));
            $layout = AccountsDesignerWalkthroughHelperUtil::getAccountsListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to AccountsRelatedListView.
            $this->setGetArray(array('moduleClassName' => 'AccountsModule',
                                     'viewClassName'   => 'AccountsRelatedListView'));
            $layout = AccountsDesignerWalkthroughHelperUtil::getAccountsListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to AccountsModalListView.
            $this->setGetArray(array('moduleClassName' => 'AccountsModule',
                                     'viewClassName'   => 'AccountsModalListView'));
            $layout = AccountsDesignerWalkthroughHelperUtil::getAccountsListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to AccountsMassEditView.
            $this->setGetArray(array('moduleClassName' => 'AccountsModule',
                                     'viewClassName'   => 'AccountsMassEditView'));
            $layout = AccountsDesignerWalkthroughHelperUtil::getAccountsMassEditViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);
        }

        /**
         * @depends testSuperUserAddCustomFieldsToLayoutsForAccountsModule
         */
        public function testLayoutsLoadOkAfterCustomFieldsPlacedForAccountsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superAccountId = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            //Load create, edit, and details views.
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/create');
            $this->setGetArray(array('id' => $superAccountId));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/edit');
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/list');
            $this->setGetArray(array(
                'modalTransferInformation' => array('sourceIdFieldId' => 'x', 'sourceNameFieldId' => 'y')
            ));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/modalList');
            $this->setGetArray(array('selectAll' => '1'));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/massEdit');
            //todo: test related list once the related list is available in a sub view.
        }

        /**
         * @depends testLayoutsLoadOkAfterCustomFieldsPlacedForAccountsModule
         */
        public function testSuperUserAddCustomFieldsToLayoutsForContactsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Add custom fields to ContactEditAndDetailsView.
            $this->setGetArray(array('moduleClassName' => 'ContactsModule',
                                     'viewClassName'   => 'ContactEditAndDetailsView'));
            $layout = ContactsDesignerWalkthroughHelperUtil::getContactEditAndDetailsViewLayoutWithAllCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout,
                                      'LayoutPanelsTypeForm' => array('type' => FormLayout::PANELS_DISPLAY_TYPE_ALL)));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to ContactsSearchView.
            $this->setGetArray(array('moduleClassName' => 'ContactsModule',
                                     'viewClassName'   => 'ContactsSearchView'));
            $layout = ContactsDesignerWalkthroughHelperUtil::getContactsSearchViewLayoutWithAllCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to ContactsListView.
            $this->setGetArray(array('moduleClassName' => 'ContactsModule',
                                     'viewClassName'   => 'ContactsListView'));
            $layout = ContactsDesignerWalkthroughHelperUtil::getContactsListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to ContactsRelatedListView.
            $this->setGetArray(array('moduleClassName' => 'ContactsModule',
                                     'viewClassName'   => 'ContactsRelatedListView'));
            $layout = ContactsDesignerWalkthroughHelperUtil::getContactsListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to ContactsMassEditView.
            $this->setGetArray(array('moduleClassName' => 'ContactsModule',
                                     'viewClassName'   => 'ContactsMassEditView'));
            $layout = ContactsDesignerWalkthroughHelperUtil::getContactsMassEditViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);
        }

       /**
         * @depends testSuperUserAddCustomFieldsToLayoutsForContactsModule
         */
        public function testLayoutsLoadOkAfterCustomFieldsPlacedForContactsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superAccountId = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $superContactId = self::getModelIdByModelNameAndName ('Contact', 'superContact superContactson');
            //Load create, edit, and details views.
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/create');
            $this->setGetArray(array('id' => $superContactId));
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/edit');
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/details');
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/list');
            $this->setGetArray(array(
                'modalTransferInformation' => array('sourceIdFieldId' => 'x', 'sourceNameFieldId' => 'y')
            ));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/modalList');
            $this->setGetArray(array('id' => $superAccountId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');
            $this->setGetArray(array('selectAll' => '1'));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/massEdit');
        }

       /**
         * @depends testLayoutsLoadOkAfterCustomFieldsPlacedForContactsModule
         */
        public function testSuperUserAddCustomFieldsToLayoutsForLeadsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Add custom fields to LeadEditAndDetailsView.
            $this->setGetArray(array('moduleClassName' => 'LeadsModule',
                                     'viewClassName'   => 'LeadEditAndDetailsView'));
            $layout = ContactsDesignerWalkthroughHelperUtil::getContactEditAndDetailsViewLayoutWithAllCustomFieldsPlaced(
                        'LeadStateDropDown');
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout,
                                      'LayoutPanelsTypeForm' => array('type' => FormLayout::PANELS_DISPLAY_TYPE_ALL)));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to LeadsSearchView.
            $this->setGetArray(array('moduleClassName' => 'LeadsModule',
                                     'viewClassName'   => 'LeadsSearchView'));
            $layout = ContactsDesignerWalkthroughHelperUtil::getContactsSearchViewLayoutWithAllCustomFieldsPlaced(
                        'LeadStateDropDown');
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to LeadsListView.
            $this->setGetArray(array('moduleClassName' => 'LeadsModule',
                                     'viewClassName'   => 'LeadsListView'));
            $layout = ContactsDesignerWalkthroughHelperUtil::getContactsListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to LeadsMassEditView.
            $this->setGetArray(array('moduleClassName' => 'LeadsModule',
                                     'viewClassName'   => 'LeadsMassEditView'));
            $layout = ContactsDesignerWalkthroughHelperUtil::getContactsMassEditViewLayoutWithAllStandardAndCustomFieldsPlaced(
                        'LeadStateDropDown');
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to LeadsModalListView.
            $this->setGetArray(array('moduleClassName' => 'LeadsModule',
                                     'viewClassName'   => 'LeadsModalListView'));
            $layout = ContactsDesignerWalkthroughHelperUtil::getContactsListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);
        }

       /**
         * @depends testSuperUserAddCustomFieldsToLayoutsForLeadsModule
         */
        public function testLayoutsLoadOkAfterCustomFieldsPlacedForLeadsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superLeadId  = self::getModelIdByModelNameAndName('Contact', 'superLead superLeadson');
            //Load create, edit, and details views.
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/create');
            $this->setGetArray(array('id' => $superLeadId));
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/edit');
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/details');
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/list');
            $this->setGetArray(array(
                'modalTransferInformation' => array('sourceIdFieldId' => 'x', 'sourceNameFieldId' => 'y')
            ));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/modalList');
            $this->setGetArray(array('selectAll' => '1'));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/massEdit');
            //todo: test related list once the related list is available in a sub view.
        }

        /**
         * @depends testLayoutsLoadOkAfterCustomFieldsPlacedForLeadsModule
         */
        public function testSuperUserAddCustomFieldsToLayoutsForOpportunitiesModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Add custom fields to OpportunityEditAndDetailsView.
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule',
                                     'viewClassName'   => 'OpportunityEditAndDetailsView'));
            $layout = OpportunitiesDesignerWalkthroughHelperUtil::getOpportunityEditAndDetailsViewLayoutWithAllCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout,
                                      'LayoutPanelsTypeForm' => array('type' => FormLayout::PANELS_DISPLAY_TYPE_ALL)));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to OpportunitiesSearchView.
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule',
                                     'viewClassName'   => 'OpportunitiesSearchView'));
            $layout = OpportunitiesDesignerWalkthroughHelperUtil::getOpportunitiesSearchViewLayoutWithAllCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to OpportunitiesListView.
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule',
                                     'viewClassName'   => 'OpportunitiesListView'));
            $layout = OpportunitiesDesignerWalkthroughHelperUtil::getOpportunitiesListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to OpportunitiesRelatedListView.
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule',
                                     'viewClassName'   => 'OpportunitiesRelatedListView'));
            $layout = OpportunitiesDesignerWalkthroughHelperUtil::getOpportunitiesListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to OpportunitiesMassEditView.
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule',
                                     'viewClassName'   => 'OpportunitiesMassEditView'));
            $layout = OpportunitiesDesignerWalkthroughHelperUtil::getOpportunitiesMassEditViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);
        }

       /**
         * @depends testSuperUserAddCustomFieldsToLayoutsForOpportunitiesModule
         */
        public function testLayoutsLoadOkAfterCustomFieldsPlacedForOpportunitiesModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superAccountId = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $superOpportunityId = self::getModelIdByModelNameAndName ('Opportunity', 'superOpp');
            //Load create, edit, and details views.
            $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/create');
            $this->setGetArray(array('id' => $superOpportunityId));
            $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/edit');
            $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/details');
            $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/list');
            $this->setGetArray(array(
                'modalTransferInformation' => array('sourceIdFieldId' => 'x', 'sourceNameFieldId' => 'y')
            ));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/modalList');
            $this->setGetArray(array('id' => $superAccountId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');
            $this->setGetArray(array('selectAll' => '1'));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/massEdit');
        }

        /**
         * @depends testLayoutsLoadOkAfterCustomFieldsPlacedForOpportunitiesModule
         */
        public function testSuperUserAddCustomFieldsToLayoutsForTasksModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Add custom fields to TaskEditAndDetailsView.
            $this->setGetArray(array('moduleClassName' => 'TasksModule',
                                     'viewClassName'   => 'TaskEditAndDetailsView'));
            $layout = TasksDesignerWalkthroughHelperUtil::getTaskEditAndDetailsViewLayoutWithAllCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout,
                                      'LayoutPanelsTypeForm' => array('type' => FormLayout::PANELS_DISPLAY_TYPE_ALL)));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to OpenTasksForAccountRelatedListView.
            $this->setGetArray(array('moduleClassName' => 'TasksModule',
                                     'viewClassName'   => 'OpenTasksForAccountRelatedListView'));
            $layout = TasksDesignerWalkthroughHelperUtil::getTasksRelatedListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            $this->setGetArray(array('moduleClassName' => 'TasksModule',
                                     'viewClassName'   => 'OpenTasksForContactRelatedListView'));
            $layout = TasksDesignerWalkthroughHelperUtil::getTasksRelatedListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            $this->setGetArray(array('moduleClassName' => 'TasksModule',
                                     'viewClassName'   => 'OpenTasksForOpportunityRelatedListView'));
            $layout = TasksDesignerWalkthroughHelperUtil::getTasksRelatedListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);
        }

        /**
         * @depends testSuperUserAddCustomFieldsToLayoutsForTasksModule
         */
        public function testSuperUserAddCustomFieldsToLayoutsForNotesModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Add custom fields to NoteEditAndDetailsView.
            $this->setGetArray(array('moduleClassName' => 'NotesModule',
                                     'viewClassName'   => 'NoteEditAndDetailsView'));
            $layout = NotesDesignerWalkthroughHelperUtil::getNoteEditAndDetailsViewLayoutWithAllCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout,
                                      'LayoutPanelsTypeForm' => array('type' => FormLayout::PANELS_DISPLAY_TYPE_ALL)));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add custom fields to NoteInlineEditView.
            $this->setGetArray(array('moduleClassName' => 'NotesModule',
                                     'viewClassName'   => 'NoteInlineEditView'));
            $layout = NotesDesignerWalkthroughHelperUtil::getNoteInlineEditViewLayoutWithAllCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout,
                                      'LayoutPanelsTypeForm' => array('type' => FormLayout::PANELS_DISPLAY_TYPE_ALL)));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);
        }

       /**
         * @depends testSuperUserAddCustomFieldsToLayoutsForNotesModule
         */
        public function testLayoutsLoadOkAfterCustomFieldsPlacedForTasksModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superAccountId = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $superTaskId = self::getModelIdByModelNameAndName ('Task', 'superTask');
            //Load create, edit, and details views.
            $this->setGetArray(array('id' => $superTaskId));
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/edit');
            $this->setGetArray(array('id' => $superAccountId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');
            $this->setGetArray(array(   'relationAttributeName'  => 'Account',
                                        'relationModelId'        => $superAccountId,
                                        'relationModuleId'       => 'account',
                                        'redirectUrl'            => 'someRedirection'));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/createFromRelation');
            //todo: more permutations from different relations.
        }

        /**
         * @depends testSuperUserAddCustomFieldsToLayoutsForTasksModule
         */
        public function testLayoutsLoadOkAfterCustomFieldsPlacedForNotesModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superAccountId = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $superNoteId = self::getModelIdByModelNameAndName ('Note', 'superNote');
            //Load create, edit, and details views.
            $this->setGetArray(array('id' => $superNoteId));
            $this->runControllerWithNoExceptionsAndGetContent('notes/default/edit');
            $this->setGetArray(array('id' => $superAccountId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');
            $this->setGetArray(array(   'relationAttributeName'  => 'Account',
                                        'relationModelId'        => $superAccountId,
                                        'relationModuleId'       => 'account',
                                        'redirectUrl'            => 'someRedirection'));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('notes/default/createFromRelation');
            //todo: more permutations from different relations.
        }

            /**
         * @depends testLayoutsLoadOkAfterCustomFieldsPlacedForNotesModule
         */
        public function testSuperUserAddCustomFieldsToLayoutsForMeetingsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Add custom fields to MeetingEditAndDetailsView.
            $this->setGetArray(array('moduleClassName' => 'MeetingsModule',
                                     'viewClassName'   => 'MeetingEditAndDetailsView'));
            $layout = MeetingsDesignerWalkthroughHelperUtil::getMeetingEditAndDetailsViewLayoutWithAllCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout,
                                      'LayoutPanelsTypeForm' => array('type' => FormLayout::PANELS_DISPLAY_TYPE_ALL)));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to UpcomingMeetingsForAccountRelatedListView.
            $this->setGetArray(array('moduleClassName' => 'MeetingsModule',
                                     'viewClassName'   => 'UpcomingMeetingsForAccountRelatedListView'));
            $layout = MeetingsDesignerWalkthroughHelperUtil::getMeetingsRelatedListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            $this->setGetArray(array('moduleClassName' => 'MeetingsModule',
                                     'viewClassName'   => 'UpcomingMeetingsForContactRelatedListView'));
            $layout = MeetingsDesignerWalkthroughHelperUtil::getMeetingsRelatedListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            $this->setGetArray(array('moduleClassName' => 'MeetingsModule',
                                     'viewClassName'   => 'UpcomingMeetingsForOpportunityRelatedListView'));
            $layout = MeetingsDesignerWalkthroughHelperUtil::getMeetingsRelatedListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);
        }

       /**
         * @depends testSuperUserAddCustomFieldsToLayoutsForMeetingsModule
         */
        public function testLayoutsLoadOkAfterCustomFieldsPlacedForMeetingsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superAccountId = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $superMeetingId = self::getModelIdByModelNameAndName ('Meeting', 'superMeeting');
            //Load create, edit, and details views.
            $this->setGetArray(array('id' => $superMeetingId));
            $this->runControllerWithNoExceptionsAndGetContent('meetings/default/edit');
            $this->setGetArray(array('id' => $superAccountId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');
            $this->setGetArray(array(   'relationAttributeName'  => 'Account',
                                        'relationModelId'        => $superAccountId,
                                        'relationModuleId'       => 'account',
                                        'redirectUrl'            => 'someRedirection'));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('meetings/default/createFromRelation');
            //todo: more permutations from different relations.
        }

        public function testRegularUserAllControllerActions()
        {
            //Peon should be restricted to all, always.
        }

        protected function createCheckBoxCustomFieldByModule($moduleClassName, $name)
        {
            $extraPostData = array( 'defaultValue' => '1', 'isAudited' => '1');
            $this->createCustomAttributeWalkthroughSequence($moduleClassName, $name, 'CheckBox', $extraPostData);
        }

        protected function createCurrencyValueCustomFieldByModule($moduleClassName, $name)
        {
            $extraPostData = array( 'defaultValue' => '45', 'isAudited' => '1', 'isRequired' => '1');
            $this->createCustomAttributeWalkthroughSequence($moduleClassName, $name, 'CurrencyValue', $extraPostData);
        }

        protected function createDateCustomFieldByModule($moduleClassName, $name)
        {
            $extraPostData = array( 'defaultValueCalculationType' => '', 'isAudited' => '1', 'isRequired' => '1');
            $this->createCustomAttributeWalkthroughSequence($moduleClassName, $name, 'Date', $extraPostData);
        }

        protected function createDateTimeCustomFieldByModule($moduleClassName, $name)
        {
            $extraPostData = array( 'defaultValueCalculationType' => '', 'isAudited' => '1', 'isRequired' => '1');
            $this->createCustomAttributeWalkthroughSequence($moduleClassName, $name, 'DateTime', $extraPostData);
        }

        protected function createDecimalCustomFieldByModule($moduleClassName, $name)
        {
            $extraPostData = array( 'defaultValue' => '123', 'isAudited' => '1', 'isRequired' => '1',
                                    'maxLength' => '18', 'precisionLength' => '2');
            $this->createCustomAttributeWalkthroughSequence($moduleClassName, $name, 'Decimal', $extraPostData);
        }

        protected function createIntegerCustomFieldByModule($moduleClassName, $name)
        {
            $extraPostData = array( 'defaultValue' => '123', 'isAudited' => '1', 'isRequired' => '1',
                                    'maxLength' => '11', 'minValue' => '2', 'maxValue' => '400');
            $this->createCustomAttributeWalkthroughSequence($moduleClassName, $name, 'Integer', $extraPostData);
        }

        protected function createPhoneCustomFieldByModule($moduleClassName, $name)
        {
            $extraPostData = array( 'defaultValue' => '5423', 'isAudited' => '1', 'isRequired' => '1',
                                    'maxLength' => '20');
            $this->createCustomAttributeWalkthroughSequence($moduleClassName, $name, 'Phone', $extraPostData);
        }

        protected function createTextCustomFieldByModule($moduleClassName, $name)
        {
            $extraPostData = array( 'defaultValue' => 'aText', 'isAudited' => '1', 'isRequired' => '1',
                                    'maxLength' => '255');
            $this->createCustomAttributeWalkthroughSequence($moduleClassName, $name, 'Text', $extraPostData);
        }

        protected function createTextAreaCustomFieldByModule($moduleClassName, $name)
        {
            $extraPostData = array( 'defaultValue' => 'aTextDesc', 'isAudited' => '1', 'isRequired' => '1');
            $this->createCustomAttributeWalkthroughSequence($moduleClassName, $name, 'TextArea', $extraPostData);
        }

        protected function createUrlCustomFieldByModule($moduleClassName, $name)
        {
            $extraPostData = array( 'defaultValue' => 'http://www.zurmo.com', 'isAudited' => '1', 'isRequired' => '1',
                                    'maxLength' => '200');
            $this->createCustomAttributeWalkthroughSequence($moduleClassName, $name, 'Url', $extraPostData);
        }

        protected function createDropDownCustomFieldByModule($moduleClassName, $name)
        {
            $extraPostData = array( 'defaultValueOrder'   => '2',
                                    'isAudited'           => '1',
                                    'isRequired'          => '1',
                                    'customFieldDataData' => array(
                                                'a', 'b', 'c'
                                    ));
            $this->createCustomAttributeWalkthroughSequence($moduleClassName, $name, 'DropDown', $extraPostData);
        }

        protected function createRadioDropDownCustomFieldByModule($moduleClassName, $name)
        {
            $extraPostData = array( 'defaultValueOrder'   => '2',
                                    'isAudited'           => '1',
                                    'isRequired'          => '1',
                                    'customFieldDataData' => array(
                                                'd', 'e', 'f'
                                    ));
            $this->createCustomAttributeWalkthroughSequence($moduleClassName, $name, 'RadioDropDown', $extraPostData);
        }

        protected function createMultiSelectDropDownCustomFieldByModule($moduleClassName, $name)
        {
            $extraPostData = array( 'defaultValueOrder'   => '1',
                                    'isAudited'           => '1',
                                    'isRequired'          => '1',
                                    'customFieldDataData' => array(
                                                'gg', 'hh', 'rr'
                                    ));
            $this->createCustomAttributeWalkthroughSequence($moduleClassName, $name, 'MultiSelectDropDown', $extraPostData);
        }

        protected function createModuleEditBadValidationPostData()
        {
            return array('singularModuleLabels' =>
                            array('de' => '', 'it' => 'forget everything but this', 'es' => '', 'en' => '', 'fr' => ''),
                         'pluralModuleLabels' =>
                            array('de' => '', 'it' => '', 'es' => '', 'en' => '', 'fr' => '')
                        );
        }

        protected function createModuleEditGoodValidationPostData($singularName)
        {
            assert('strtolower($singularName) == $singularName'); // Not Coding Standard
            $pluralName = $singularName .'s';
            return array('singularModuleLabels' =>
                            array('de' => $singularName, 'it' => $singularName, 'es' => $singularName,
                                    'en' => $singularName, 'fr' => $singularName),
                         'pluralModuleLabels' =>
                            array(  'de' => $pluralName, 'it' => $pluralName, 'es' => $pluralName,
                                    'en' => $pluralName, 'fr' => $pluralName)
                        );
        }

        protected function createAttributeLabelBadValidationPostData()
        {
            return array('de' => '', 'it' => 'forget everything but this', 'es' => '', 'en' => '', 'fr' => ''
                        );
        }

        protected function createAttributeLabelGoodValidationPostData($name)
        {
            assert('strtolower($name) == $name'); // Not Coding Standard
            return array('de' => $name . ' de', 'it' => $name . ' it', 'es' => $name . ' es',
                                    'en' => $name . ' en', 'fr' => $name . ' fr'
                        );
        }

        protected function createCustomAttributeWalkthroughSequence($moduleClassName,
                                                                    $name,
                                                                    $attributeTypeName,
                                                                    $extraPostData,
                                                                    $attributeName = null)
        {
            assert('$name[0] == strtolower($name[0])'); // Not Coding Standard
            assert('is_array($extraPostData)'); // Not Coding Standard
            $formName = $attributeTypeName . 'AttributeForm';
            $this->setGetArray(array(   'moduleClassName'       => $moduleClassName,
                                        'attributeTypeName'     => $attributeTypeName,
                                        'attributeName'         => $attributeName));
            $this->resetPostArray();
            //Now test going to the user interface edit view.
            $content = $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributeEdit');

            //Now validate save with failed validation.
            $this->setPostArray(array(   'ajax'                 => 'edit-form',
                                        $formName => array_merge(array(
                                            'attributeLabels' => $this->createAttributeLabelBadValidationPostData($name),
                                            'attributeName'     => $name,
                                        ), $extraPostData)));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/attributeEdit');
            $this->assertTrue(strlen($content) > 50); //approximate, but should definetely be larger than 50.
            //Now validate save with successful validation.
            $this->setPostArray(array(   'ajax'                 => 'edit-form',
                                        $formName => array_merge(array(
                                            'attributeLabels' => $this->createAttributeLabelGoodValidationPostData($name),
                                            'attributeName'     => $name,
                                        ), $extraPostData)));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/attributeEdit');
            $this->assertEquals('[]', $content);

            //Now save successfully.
            $this->setPostArray(array(   'save'                 => 'Save',
                                        $formName => array_merge(array(
                                            'attributeLabels' => $this->createAttributeLabelGoodValidationPostData($name),
                                            'attributeName'     => $name,
                                        ), $extraPostData)));
            $this->runControllerWithRedirectExceptionAndGetContent('designer/default/attributeEdit');
            //Now confirm everything did in fact save correctly.
            $modelClassName = $moduleClassName::getPrimaryModelName();
            $newModel       = new $modelClassName(false);
            $compareData = array(
                'de' => $name . ' de',
                'it' => $name . ' it',
                'es' => $name . ' es',
                'en' => $name . ' en',
                'fr' => $name . ' fr',
            );
            $this->assertEquals(
                $compareData, $newModel->getAttributeLabelsForAllSupportedLanguagesByAttributeName($name));

            //Now go to the detail viwe of the attribute.
            $this->setGetArray(array(   'moduleClassName'       => $moduleClassName,
                                        'attributeTypeName'     => $attributeTypeName,
                                        'attributeName'         => $name));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributeDetails');

            //Now test going to the user interface edit view for the existing attribute.
            $content = $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributeEdit');
        }
    }
?>