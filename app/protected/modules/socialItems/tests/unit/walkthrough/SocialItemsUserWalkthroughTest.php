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
     * SocialItems Module User Walkthrough.
     * Walkthrough for the users of all possible controller actions.
     */
    class SocialItemsUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            ReadPermissionsOptimizationUtil::rebuild();

            //create everyone group
            $everyoneGroup = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $everyoneGroup->save();

            //Create test users
            $steven                             = UserTestHelper::createBasicUser('steven');
            $sally                              = UserTestHelper::createBasicUser('sally');
            $mary                               = UserTestHelper::createBasicUser('mary');

            //give 3 users access to social items
            $steven->setRight('SocialItemsModule',   SocialItemsModule::RIGHT_ACCESS_SOCIAL_ITEMS, Right::ALLOW);
            $saved = $steven->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
            $sally->setRight('SocialItemsModule',   SocialItemsModule::RIGHT_ACCESS_SOCIAL_ITEMS, Right::ALLOW);
            $saved = $sally->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
            $mary->setRight('SocialItemsModule',   SocialItemsModule::RIGHT_ACCESS_SOCIAL_ITEMS, Right::ALLOW);
            $saved = $mary->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }

            //Setup test data owned by the super user.
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);

            //Setup default dashboard.
            Dashboard::getByLayoutIdAndUser(Dashboard::DEFAULT_USER_LAYOUT_ID, $super);
            Dashboard::getByLayoutIdAndUser(Dashboard::DEFAULT_USER_LAYOUT_ID, $mary);
            ContactsModule::loadStartingData();
        }

        public function testSuperUserAllSimpleControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            //Test all default controller actions that do not require any POST/GET variables to be passed.
            //Currently there are no simple actions.
        }

        /**
         * @depends testSuperUserAllSimpleControllerActions
         */
        public function testSuperUserCreateSocialItem()
        {
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $mary           = User::getByUsername('mary');

            $socialItems = SocialItem::getAll();
            $this->assertEquals(0, count($socialItems));
            $this->setGetArray(array('redirectUrl'                => 'someRedirect'));
            $this->setPostArray(array('ajax' => 'social-item-inline-edit-form',
                                      'SocialItem' => array('description' => 'a validSocialItem')));

            //Validate
            $content = $this->runControllerWithExitExceptionAndGetContent('socialItems/default/inlineCreateSave');
            $this->assertEquals('[]', $content);

            $this->assertEquals(0, count($socialItems));
            $this->setGetArray(array('redirectUrl' => 'someRedirect'));
            $this->setPostArray(array('SocialItem' => array('description' => 'a validSocialItem')));
            $this->runControllerWithRedirectExceptionAndGetContent('socialItems/default/inlineCreateSave');

            //Confirm socialItem saved.
            $socialItems = SocialItem::getAll();
            $this->assertEquals(1, count($socialItems));
            $this->assertEquals('a validSocialItem', $socialItems[0]->description);
            $this->assertSame($super, $socialItems[0]->owner);
            $this->assertTrue($socialItems[0]->toUser->id < 0);

            //Confirm everyone has read/write
            $everyoneGroup                     = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem($socialItems[0]);
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $this->assertEquals(1, count($readWritePermitables));
            $this->assertTrue(isset($readWritePermitables[$everyoneGroup->id]));
        }

        /**
         * @depends testSuperUserCreateSocialItem
         */
        public function testSuperUserCreateSocialItemOnAnotherUsersProfile()
        {
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $mary           = User::getByUsername('mary');

            $socialItems = SocialItem::getAll();
            $this->assertEquals(1, count($socialItems));
            $this->setGetArray(array('redirectUrl'                => 'someRedirect'));
            $this->setPostArray(array('ajax' => 'social-item-inline-edit-form', 'relatedUserId' => $mary->id,
                                      'SocialItem' => array('description' => 'a validSocialItem 2')));
            //Validate
            $content = $this->runControllerWithExitExceptionAndGetContent('socialItems/default/inlineCreateSave');
            $this->assertEquals('[]', $content);

            $this->assertEquals(1, count($socialItems));
            $this->setGetArray(array('redirectUrl'    => 'someRedirect', 'relatedUserId' => $mary->id));
            $this->setPostArray(array('SocialItem' => array('description' => 'a validSocialItem 2')));
            $this->runControllerWithRedirectExceptionAndGetContent('socialItems/default/inlineCreateSave');

            //Confirm socialItem saved.
            $socialItems = SocialItem::getAll();
            $this->assertEquals(2, count($socialItems));
            $this->assertEquals('a validSocialItem 2', $socialItems[1]->description);
            $this->assertSame($super, $socialItems[1]->owner);
            $this->assertSame($mary,  $socialItems[1]->toUser);

            //Confirm everyone has read/write
            $everyoneGroup                     = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem($socialItems[1]);
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $this->assertEquals(1, count($readWritePermitables));
            $this->assertTrue(isset($readWritePermitables[$everyoneGroup->id]));
        }

        /**
         * @depends testSuperUserCreateSocialItemOnAnotherUsersProfile
         */
        public function testAddingCommentsAndUpdatingActivityStampsOnSocialItem()
        {
            if (!SECURITY_OPTIMIZED) //bug prevents this from running correctly
            {
                return;
            }
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $steven         = User::getByUsername('steven');
            $sally          = User::getByUsername('sally');
            $mary           = User::getByUsername('mary');
            $socialItems  = SocialItem::getAll();
            $this->assertEquals(2, count($socialItems));
            $this->assertEquals(0, $socialItems[0]->comments->count());
            $oldStamp        = $socialItems[0]->latestDateTime;

            //Validate comment
            $this->setGetArray(array('relatedModelId'             => $socialItems[0]->id,
                                     'relatedModelClassName'      => 'SocialItem',
                                     'relatedModelRelationName'   => 'comments',
                                     'redirectUrl'                => 'someRedirect'));
            $this->setPostArray(array('ajax' => 'comment-inline-edit-form',
                                      'Comment' => array('description' => 'a ValidComment Name')));

            $content = $this->runControllerWithExitExceptionAndGetContent('comments/default/inlineCreateSave');
            $this->assertEquals('[]', $content);

            sleep(2); // Sleeps are bad in tests, but I need some time to pass

            //Now save that comment.
            $this->setGetArray(array('relatedModelId'             => $socialItems[0]->id,
                                     'relatedModelClassName'      => 'SocialItem',
                                     'relatedModelRelationName'   => 'comments',
                                     'redirectUrl'                => 'someRedirect'));
            $this->setPostArray(array('Comment'          => array('description' => 'a ValidComment Name')));
            $content = $this->runControllerWithRedirectExceptionAndGetContent('comments/default/inlineCreateSave');
            $id = $socialItems[0]->id;
            $socialItems[0]->forget();
            $socialItem = SocialItem::getById($id);
            $this->assertEquals(1, $socialItem->comments->count());

            //should update latest activity stamp
            $this->assertNotEquals($oldStamp, $socialItems[0]->latestDateTime);
            $newStamp = $socialItems[0]->latestDateTime;
            sleep(2); // Sleeps are bad in tests, but I need some time to pass

            //Mary should be able to add a comment because everyone can do this on a mission
            $mary = $this->logoutCurrentUserLoginNewUserAndGetByUsername('mary');
            $this->setGetArray(array('relatedModelId'             => $socialItems[0]->id,
                                     'relatedModelClassName'      => 'SocialItem',
                                     'relatedModelRelationName'   => 'comments',
                                     'redirectUrl'                => 'someRedirect'));
            $this->setPostArray(array('Comment'          => array('description' => 'a ValidComment Name 2')));
            $content = $this->runControllerWithRedirectExceptionAndGetContent('comments/default/inlineCreateSave');
            $id = $socialItems[0]->id;
            $socialItems[0]->forget();
            $socialItem = SocialItem::getById($id);
            $this->assertEquals(2, $socialItem->comments->count());
            $this->assertNotEquals($newStamp, $socialItem->latestDateTime);
        }

        /**
         * @depends testAddingCommentsAndUpdatingActivityStampsOnSocialItem
         */
        public function testUsersCanReadAndWriteSocialItemsOkThatAreNotOwner()
        {
            if (!SECURITY_OPTIMIZED) //bug prevents this from running correctly
            {
                return;
            }
            //todo; we stll need to test that other users can get to the missions.
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $mary           = User::getByUsername('mary');
            $socialItems  = SocialItem::getAll();
            $this->assertEquals(2, count($socialItems));
            $this->assertEquals(2, $socialItems[0]->comments->count());

            //Mary should not be able to edit the mission
            $mary           = $this->logoutCurrentUserLoginNewUserAndGetByUsername('mary');
            $this->setGetArray(array('id' => $socialItems[0]->id));
            $this->runControllerWithExitExceptionAndGetContent('missions/default/edit');

            //new test - mary can delete a comment she wrote
            $maryCommentId = $socialItems[0]->comments->offsetGet(1)->id;
            $this->assertEquals($socialItems[0]->comments->offsetGet(1)->createdByUser->id, $mary->id);
            $superCommentId = $socialItems[0]->comments->offsetGet(0)->id;
            $this->assertEquals($socialItems[0]->comments->offsetGet(0)->createdByUser->id, $super->id);
            $this->setGetArray(array('relatedModelId'             => $socialItems[0]->id,
                                     'relatedModelClassName'      => 'SocialItem',
                                     'relatedModelRelationName'   => 'comments',
                                     'id'                         => $maryCommentId));
            $this->runControllerWithNoExceptionsAndGetContent('comments/default/deleteViaAjax', true);
            $socialItemId  = $socialItems[0]->id;
            $socialItems[0]->forget();
            $socialItem = SocialItem::getById($socialItemId);
            $this->assertEquals(1, $socialItem->comments->count());

            //new test - mary cannot delete a comment she did not write.
            $this->setGetArray(array('relatedModelId'             => $socialItems[0]->id,
                                     'relatedModelClassName'      => 'SocialItem',
                                     'relatedModelRelationName'   => 'comments',
                                     'id'                         => $superCommentId));
            $this->runControllerShouldResultInAjaxAccessFailureAndGetContent('comments/default/deleteViaAjax');
            $socialItemId  = $socialItems[0]->id;
            $socialItems[0]->forget();
            $socialItem = SocialItem::getById($socialItemId);
            $this->assertEquals(1, $socialItem->comments->count());
            $this->assertEquals(1, $socialItem->comments->count());

            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->assertTrue($socialItem->owner->isSame($super));

            //new test , super can delete the socialItem
            $this->setGetArray(array('id' => $socialItem->id));
            $this->runControllerWithNoExceptionsAndGetContent('socialItems/default/deleteViaAjax', true);

            $socialItems  = SocialItem::getAll();
            $this->assertEquals(1, count($socialItems));
        }

        /**
         * @depends testUsersCanReadAndWriteSocialItemsOkThatAreNotOwner
         */
        public function testPostGameNotificationToProfile()
        {
            if (!SECURITY_OPTIMIZED) //because testUsersCanReadAndWriteSocialItemsOkThatAreNotOwner
                                     //there are cascading effects so we dont run this
            {
                return;
            }
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $socialItems  = SocialItem::getAll();
            $this->assertEquals(1, count($socialItems));
            $super   = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->setGetArray(array('content' => 'Example game notice'));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('socialItems/default/PostGameNotificationToProfile', true);

            $socialItems  = SocialItem::getAll();
            $this->assertEquals(2, count($socialItems));
            $this->assertEquals('Example game notice', $socialItems[1]->description);
        }

        /**
         * @depends testPostGameNotificationToProfile
         */
        public function testPostingAnNoteCarriesPermissionsCorrectly()
        {
            if (!SECURITY_OPTIMIZED) //because testUsersCanReadAndWriteSocialItemsOkThatAreNotOwner
                                     //there are cascading effects so we dont run this
            {
                return;
            }
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $socialItems  = SocialItem::getAll();
            $this->assertEquals(2, count($socialItems));
            $superAccountId       = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $activityItemPostData = array('Account' => array('id' => $superAccountId));
            $this->setGetArray(array('redirectUrl' => 'someRedirect'));
            $this->setPostArray(array('ActivityItemForm' => $activityItemPostData, 'postToProfile' => true,
                                      'Note' => array('description' => 'a note that is promoted')));
            $content = $this->runControllerWithRedirectExceptionAndGetContent('notes/default/inlineCreateSave');
            $notes  = Note::getAll();
            $this->assertEquals(1, count($notes));
            $socialItems  = SocialItem::getAll();
            $this->assertEquals(3, count($socialItems));
            $this->assertEquals($notes[0]->id, $socialItems[2]->note->id);
            $this->assertNull($socialItems[2]->description);
        }

        /**
         * @depends testPostingAnNoteCarriesPermissionsCorrectly
         */
        public function testUserWithAccessToSocialItemsShowsHomeDashboardCorrectly()
        {
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->runControllerWithNoExceptionsAndGetContent('users/default/profile');
            $this->runControllerWithNoExceptionsAndGetContent('home/default/index');
        }

        /**
         * @depends testUserWithAccessToSocialItemsShowsHomeDashboardCorrectly
         */
        public function testUserWithoutAccesstoSocialItemsShowsCorrectly()
        {
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $mary           = User::getByUsername('mary');
            $mary->setRight('SocialItemsModule',   SocialItemsModule::RIGHT_ACCESS_SOCIAL_ITEMS, Right::DENY);
            $saved = $mary->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
            $mary           = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->runControllerWithNoExceptionsAndGetContent('users/default/profile');
            $this->runControllerWithNoExceptionsAndGetContent('home/default/index');
        }
    }
?>