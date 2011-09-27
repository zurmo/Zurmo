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
     * Note module walkthrough tests for a regular user.
     */
    class NotesRegularUserWalkthroughTest extends ZurmoRegularUserWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = Yii::app()->user->userModel;

            //Setup test data owned by the super user.
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
            AccountTestHelper::createAccountByNameForOwner('superAccount2', $super);
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact', $super, $account);
        }

        public function testRegularUserAllControllerActions()
        {
            //Now test all portlet controller actions

            //Now test peon with elevated rights to tabs /other available rights
            //such as convert lead

            //Now test peon with elevated permissions to models.
        }
        
         public function testRegularUserAllControllerActionsNoElevation()
        {
            //Now Logout and Login as a Super User
            //Get the superAccountId using the account module
            //Assign the nobody to userModel       
            
            //Now test all portlet controller actions 
            //Check that the nobody user does not have access to the following controllers
            //notes/default/createFromRelation
            //notes/default/edit
            //notes/default/inlineEditSave
            //notes/default/details
            //notes/default/delete                  
        }
        
         /**
         * @depends testRegularUserAllControllerActionsNoElevation
         */
        public function testRegularUserControllerActionsWithElevationToAccessAndCreate()
        {        
            //Now Logout and Login as a Nobody User            
                       
            //Now test peon with elevated rights to accounts
            //Now give Nobody User Access Right to accounts Module
            //Now give Nobody User Create Right to accounts Module
            //Now assertTrue save the nobody user           

            //create the account with nobody user as the owner           
                       
            //Now test peon with elevated rights to notes
            //Now give Nobody User Access Right to notes Module
            //Now give Nobody User Create Right to notes Module
            //Now assertTrue save the nobody user            
            
            //Test nobody with elevated rights.
            //Now assign userModel to nobody user
            //Now craete note using createNoteWithOwnerAndRelatedAccount with the nobdy user and account created by nobody user
                       
            //Test whether the nobody user is able to view the note details that he created               

            //Test nobody can delete an existing note he craeted and it redirects to index.                     
        }
        
         /**
         * @depends testRegularUserControllerActionsWithElevationToAccessAndCreate
         */
        public function testRegularUserControllerActionsWithElevationToModels()
        {
            //Create superAccount owned by user super.
            //Now Logout and Login as the Super User
            //Now create account with super user as owner            
            
            //Test nobody, access to edit and details of superAccount should fail.
            //Now Logout and Login as a nobody User
            //Now access to account details should fail for nobody user            
            
            //give nobody access to read the superAccount
                        
            //Now the nobody user can access the details view.
                                 
            //create note for an superAccount using the nobody user             
            
            //Test nobody, access to edit and details of notes should fail.
            
            //give nobody access to read and edit the note created by super user for superAccount
           
            //Now nobody should have access to read and edit note
            
            //revoke the permission from the nobody user to access the note
            
            //Now the nobodys access to read and edit note should fail
            
            //create some roles
            //create parentRole
            //create childRole
            
            //create userInParentRole  
            //create userInChildRole

            //create account2 with Super user as the owner

            //Test userInParentRole, access to account2 details should fail.
            
            //give userInChildRole access to account2 READ
            
            //Test userInChildRole, access to account2 details should not fail.
            
            //Test userInParentRole, access to account2 details should not fail.
            
            //create note2 for account2 with super user as the owner
            
            //Test userInParentRole, access to note2 details and edit should fail.
            
            //give userInChildRole access to note2 READ
            
            //Test userInChildRole, access to note2 edit should fail.
            
            //Test userInParentRole, access to note2 edit should fail.
            
            //give userInChildRole access to note2 READ_WRITE permission
            
            //Test userInChildRole, access to note2 detail and edit should not fail.
            
            //Test userInParentRole, access to note2 detail and edit should not fail.
            
            //revoke note2 read and write permission from userInChildRole
            
            //Test userInChildRole, access to note2 details and edit should fail.
            
            //Test userInParentRole, access to note2 details and edit should fail.
            
            //clear up the role relationships between users so not to effect next assertions
            
            //create groups and assign users to groups
            //create parentGroup
            //create childGroup
            
            //create userInParentGroup  
            //create userInChildGroup

            //create account3 with Super user as the owner

            //Test userInParentGroup, access to account3 details should fail.
            
            //give userInChildGroup access to account3 READ
            
            //Test userInChildGroup, access to account3 details should not fail.
            
            //Test userInParentGroup, access to account3 details should not fail.
            
            //create note3 for account3 with super user as the owner
            
            //Test userInParentGroup, access to note3 details and edit should fail.
            
            //give userInChildGroup access to note3 READ
            
            //Test userInChildGroup, access to note3 edit should fail.
            
            //Test userInParentGroup, access to note3 edit should fail.
            
            //give userInChildGroup access to note3 READ_WRITE permission
            
            //Test userInChildGroup, access to note3 detail and edit should not fail.
            
            //Test userInParentGroup, access to note3 detail and edit should not fail.
            
            //revoke note3 read and write permission from userInChildGroup
            
            //Test userInChildGroup, access to note3 details and edit should fail.
            
            //Test userInParentGroup, access to note3 details and edit should fail.            
        }
    }
?>