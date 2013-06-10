<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class AutoresponderDefaultControllerRegularUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        protected $user;

        protected static $superUserMarketingListId;

        protected static $regularUserMarketingListId;

        protected static $superUserAutoresponderId;

        protected static $regularUserAutoresponderId;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = User::getByUsername('super');
            $superUserMarketingList             = MarketingListTestHelper::createMarketingListByName(
                                                                                                'superMarketingList');
            static::$superUserMarketingListId   = $superUserMarketingList->id;
            $superUserAutoresponder             = AutoresponderTestHelper::createAutoresponder('superAutoresponder',
                                                    'superText', 'superHtml', 10, Autoresponder::OPERATION_SUBSCRIBE,
                                                    true, $superUserMarketingList);
            static::$superUserAutoresponderId   = $superUserAutoresponder->id;
            Yii::app()->user->userModel         = UserTestHelper::createBasicUser('nobody');
            $regularUserMarketingList           = MarketingListTestHelper::createMarketingListByName(
                                                                                                'regularMarketingList');
            static::$regularUserMarketingListId   = $regularUserMarketingList->id;
            $regularUserAutoresponder             = AutoresponderTestHelper::createAutoresponder('regularAutoresponder',
                                                    'regularText', 'regularHtml', 10, Autoresponder::OPERATION_SUBSCRIBE,
                                                    true, $regularUserMarketingList);
            static::$regularUserAutoresponderId   = $regularUserAutoresponder->id;
            ReadPermissionsOptimizationUtil::rebuild();
        }

        public function setUp()
        {
            parent::setUp();
            $this->user = $this->logoutCurrentUserLoginNewUserAndGetByUsername('nobody');
            Yii::app()->user->userModel = $this->user;
        }

        public function testRegularUserAllActionsWithNoMarketingListRight()
        {
            $content    = $this->runControllerWithNoExceptionsAndGetContent('autoresponders/default/create');
            $this->assertTrue(strpos($content, '<div id="UserIsMissingMarketingListAccessSplashView"') !== false);
            $this->assertTrue(strpos($content, '<div class="Warning"><h2>Not so fast!</h2>') !== false);
            $this->assertTrue(strpos($content, '<p>To manage Marketing Lists related features you must have access ' .
                                                'to marketing lists first. Contact the CRM administrator' .
                                                ' about this issue.</p>') !== false);
            $content    = $this->runControllerWithNoExceptionsAndGetContent('autoresponders/default/edit');
            $this->assertTrue(strpos($content, '<div id="UserIsMissingMarketingListAccessSplashView"') !== false);
            $this->assertTrue(strpos($content, '<div class="Warning"><h2>Not so fast!</h2>') !== false);
            $this->assertTrue(strpos($content, '<p>To manage Marketing Lists related features you must have access ' .
                                                'to marketing lists first. Contact the CRM administrator' .
                                                ' about this issue.</p>') !== false);
            $content    = $this->runControllerWithNoExceptionsAndGetContent('autoresponders/default/details');
            $this->assertTrue(strpos($content, '<div id="UserIsMissingMarketingListAccessSplashView"') !== false);
            $this->assertTrue(strpos($content, '<div class="Warning"><h2>Not so fast!</h2>') !== false);
            $this->assertTrue(strpos($content, '<p>To manage Marketing Lists related features you must have access ' .
                                                'to marketing lists first. Contact the CRM administrator' .
                                                ' about this issue.</p>') !== false);
            $content    = $this->runControllerWithNoExceptionsAndGetContent('autoresponders/default/delete');
            $this->assertTrue(strpos($content, '<div id="UserIsMissingMarketingListAccessSplashView"') !== false);
            $this->assertTrue(strpos($content, '<div class="Warning"><h2>Not so fast!</h2>') !== false);
            $this->assertTrue(strpos($content, '<p>To manage Marketing Lists related features you must have access ' .
                                                'to marketing lists first. Contact the CRM administrator' .
                                                ' about this issue.</p>') !== false);
        }

        public function testRegularUserActionsWithMarketingListRightButInsufficientPermission()
        {
            $this->user->setRight('MarketingListsModule', MarketingListsModule::getAccessRight());
            $this->assertTrue($this->user->save());
            $this->setGetArray(array(
                'marketingListId'       => static::$superUserMarketingListId,
                'redirectUrl'           => 'http://www.zurmo.com/',
            ));
            $content    = $this->runControllerWithExitExceptionAndGetContent('autoresponders/default/create');
            $this->assertTrue(strpos($content, 'You have tried to access a page you do not have access to.') !== false);

            $this->setGetArray(array(
                'id'            => static::$superUserAutoresponderId,
                'redirectUrl'           => 'http://www.zurmo.com/',
            ));
            $content    = $this->runControllerWithExitExceptionAndGetContent('autoresponders/default/edit');
            $this->assertTrue(strpos($content, 'You have tried to access a page you do not have access to.') !== false);
            $content    = $this->runControllerWithExitExceptionAndGetContent('autoresponders/default/details');
            $this->assertTrue(strpos($content, 'You have tried to access a page you do not have access to.') !== false);
            $content    = $this->runControllerWithExitExceptionAndGetContent('autoresponders/default/delete');
            $this->assertTrue(strpos($content, 'You have tried to access a page you do not have access to.') !== false);
      }

        public function testRegularUserActionsWithMarketingListRightAndRequiredPermissions()
        {
            $this->setGetArray(array(
                'marketingListId'       => static::$regularUserMarketingListId,
                'redirectUrl'           => 'http://www.zurmo.com/',
            ));
            $this->runControllerWithNoExceptionsAndGetContent('autoresponders/default/create');

            $this->setGetArray(array(
                'id'            => static::$regularUserAutoresponderId,
                'redirectUrl'           => 'http://www.zurmo.com/',
            ));
            $this->runControllerWithNoExceptionsAndGetContent('autoresponders/default/edit');
            $this->runControllerWithNoExceptionsAndGetContent('autoresponders/default/details');
            $this->runControllerWithRedirectExceptionAndGetUrl('autoresponders/default/delete');
        }
    }
?>