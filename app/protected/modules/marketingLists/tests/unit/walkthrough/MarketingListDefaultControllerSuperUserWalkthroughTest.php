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

    class MarketingListDefaultControllerSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        protected $user;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            MarketingListTestHelper::createMarketingListByName('MarketingListName', 'MarketingList Description');
            MarketingListTestHelper::createMarketingListByName('MarketingListName2', 'MarketingList Description2');
            ReadPermissionsOptimizationUtil::rebuild();
        }

        public function setUp()
        {
            parent::setUp();
            $this->user = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            Yii::app()->user->userModel = $this->user;
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            // Test all default controller actions that do not require any POST/GET variables to be passed.
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default');
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/index');
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/list');
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/create');
        }

        /**
         * @depends testSuperUserAllDefaultControllerActions
         */
        public function testSuperUserListAction()
        {
            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/list');
            $this->assertTrue(strpos($content, 'anyMixedAttributes') !== false);
            $this->assertTrue(strpos($content, 'MarketingListName') !== false);
            $this->assertTrue(strpos($content, 'MarketingListName2') !== false);
            $this->assertEquals(2, substr_count($content, 'MarketingListName'));
            //Test the search or paging of the listview.
            Yii::app()->clientScript->reset(); //to make sure old js doesn't make it to the UI
            $this->setGetArray(array('ajax' => 'list-view'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/list');
            $this->assertTrue(strpos($content, 'anyMixedAttributes') === false);
            $this->resetGetArray();
        }

        /**
         * @depends testSuperUserListAction
         */
        public function testSuperUserCreateAction()
        {
            // TODO: @Shoaibi: Low: Add test with different permissions
            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/create');
            $this->assertTrue(strpos($content, 'Create Marketing List') !== false);
            $this->assertTrue(strpos($content, '<label for="MarketingList_name" class="required">Name ' .
                                                                '<span class="required">*</span></label>') !== false);
            $this->assertTrue(strpos($content, '<label for="MarketingList_description">Description</label>') !== false);
            $this->assertTrue(strpos($content, '<label for="MarketingList_fromName">From Name</label>') !== false);
            $this->assertTrue(strpos($content, '<label for="MarketingList_fromAddress">From Address</label>') !== false);
            $this->assertTrue(strpos($content, '<span class="z-label">Cancel</span>') !== false);
            $this->assertTrue(strpos($content, '<span class="z-label">Save</span>') !== false);

            $this->resetGetArray();
            $this->setPostArray(array('MarketingList' => array(
                'name'          => '',
                'description'   => '',
                'fromName'      => '',
                'fromAddress'   => '',
                )));
            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/create');
            $this->assertTrue(strpos($content, 'class="errorMessage">Name cannot be blank.</div>') !== false);
            $this->assertTrue(strpos($content, '<input id="MarketingList_name" name="MarketingList[name]" type="text"' .
                                                                ' maxlength="64" value="" class="error"') !== false);
            $this->assertTrue(strpos($content, '<label class="error required" for="MarketingList_name">Name ' .
                                                                 '<span class="required">*</span></label>') !== false);
            $this->resetGetArray();
            $this->setPostArray(array('MarketingList' => array(
                'name'            => 'New MarketingListName using Create',
                'description'     => 'New MarketingList Description using Create',
                'fromName'        => 'Zurmo Sales',
                'fromAddress'     => 'sales@zurmo.com',
                )));
            $redirectUrl    = $this->runControllerWithRedirectExceptionAndGetUrl('marketingLists/default/create');
            $marketingList = MarketingList::getByName('New MarketingListName using Create');
            $this->assertEquals(1, count($marketingList));
            $this->assertTrue  ($marketingList[0]->id > 0);
            $this->assertEquals('sales@zurmo.com', $marketingList[0]->fromAddress);
            $this->assertEquals('Zurmo Sales', $marketingList[0]->fromName);
            $this->assertEquals('New MarketingList Description using Create', $marketingList[0]->description);
            $this->assertTrue  ($marketingList[0]->owner == $this->user);
            $compareRedirectUrl = Yii::app()->createUrl('marketingLists/default/details', array('id' => $marketingList[0]->id));
            $this->assertEquals($compareRedirectUrl, $redirectUrl);
            $marketingList = MarketingList::getAll();
            $this->assertEquals(3, count($marketingList));
        }

        public function testSuperUserDetailsAction()
        {
            $marketingListId = self::getModelIdByModelNameAndName ('MarketingList', 'MarketingListName2');
            $this->setGetArray(array('id' => $marketingListId));
            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/details');
            $this->assertTrue(strpos($content, 'MarketingListName2') !== false);
            $this->assertEquals(3, substr_count($content, 'MarketingListName2'));
            $this->assertTrue(strpos($content, '<span>Details</span></a>') !== false);
            $this->assertTrue(strpos($content, '<strong class="marketing-list-subscribers-stats">' .
                                                                                    '0 Subscribed</strong>') !== false);
            $this->assertTrue(strpos($content, '<strong class="marketing-list-unsubscribers-stats">' .
                                                                                    '0 Unsubscribed</strong>') !== false);
            $this->assertTrue(strpos($content, 'MarketingList Description2') !== false);
            $this->assertTrue(strpos($content, '<span>Options</span></a>') !== false);
            $this->assertTrue(strpos($content, '<span>Edit</span></a></li>') !== false);
            $this->assertTrue(strpos($content, '<span>Delete</span></a></li>') !== false);
            $this->assertTrue(strpos($content, '<h3>Members</h3></div>') !== false);
            $this->assertTrue(strpos($content, '<span>Add Member</span></a>') !== false);
            $this->assertTrue(strpos($content, 'From Contacts/Leads</label>') !== false);
            $this->assertTrue(strpos($content, 'From a Report</label>') !== false);
            $this->assertTrue(strpos($content, '<span>Subscribe</span></a>') !== false);
            $this->assertTrue(strpos($content, '<span>Unsubscribe</span></a>') !== false);
            $this->assertTrue(strpos($content, '<span>Delete</span></a>') !== false);
        }

        /**
         * @depends testSuperUserCreateAction
         */
        public function testSuperUserEditAction()
        {
            $marketingListId = self::getModelIdByModelNameAndName ('MarketingList', 'New MarketingListName using Create');
            $this->setGetArray(array('id' => $marketingListId));
            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/edit');
            $this->assertTrue(strpos($content, 'New MarketingListName using Create') !== false);
            $this->assertEquals(2, substr_count($content, 'New MarketingListName using Create'));
            $this->assertTrue(strpos($content, 'New MarketingList Description using Create') !== false);
            $this->assertTrue(strpos($content, 'Zurmo Sales') !== false);
            $this->assertTrue(strpos($content, 'sales@zurmo.com') !== false);
            $this->assertTrue(strpos($content, 'Create Marketing List') === false);

            $this->setPostArray(array('MarketingList' => array(
                'name'            => 'New MarketingListName',
                'description'     => 'New MarketingList Description',
                'fromName'        => 'Zurmo Support',
                'fromAddress'     => 'support@zurmo.com',
            )));
            $redirectUrl    = $this->runControllerWithRedirectExceptionAndGetUrl('marketingLists/default/edit');
            $marketingList = MarketingList::getByName('New MarketingListName');
            $this->assertEquals(1, count($marketingList));
            $this->assertTrue  ($marketingList[0]->id > 0);
            $this->assertEquals('support@zurmo.com', $marketingList[0]->fromAddress);
            $this->assertEquals('Zurmo Support', $marketingList[0]->fromName);
            $this->assertEquals('New MarketingList Description', $marketingList[0]->description);
            $compareRedirectUrl = Yii::app()->createUrl('marketingLists/default/details', array('id' => $marketingList[0]->id));
            $this->assertEquals($compareRedirectUrl, $redirectUrl);
            $marketingList = MarketingList::getAll();
            $this->assertEquals(3, count($marketingList));
        }

        /**
         * @depends testSuperUserEditAction
         */
        public function testSuperUserDeleteAction()
        {
            $marketingListId = self::getModelIdByModelNameAndName ('MarketingList', 'New MarketingListName');

            // Delete an marketingList.
            $this->setGetArray(array('id' => $marketingListId));
            $this->resetPostArray();
            $redirectUrl = $this->runControllerWithRedirectExceptionAndGetUrl('marketingLists/default/delete');
            $compareRedirectUrl = Yii::app()->createUrl('marketingLists/default/index');
            $this->assertEquals($redirectUrl, $compareRedirectUrl);
            $marketingLists = MarketingList::getAll();
            $this->assertEquals(2, count($marketingLists));
        }
    }
?>