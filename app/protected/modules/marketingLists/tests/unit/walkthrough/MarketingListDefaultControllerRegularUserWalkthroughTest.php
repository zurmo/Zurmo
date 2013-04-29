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

    class MarketingListDefaultControllerRegularUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        protected $user;

        protected static $listOwnedBySuper;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            UserTestHelper::createBasicUser('nobody');

            static::$listOwnedBySuper = MarketingListTestHelper::createMarketingListByName('MarketingListName',
                                                                                            'MarketingList Description');
            ReadPermissionsOptimizationUtil::rebuild();
        }

        public function setUp()
        {
            parent::setUp();
            $this->user = $this->logoutCurrentUserLoginNewUserAndGetByUsername('nobody');
            Yii::app()->user->userModel = $this->user;
        }

        public function testRegularUserAllDefaultControllerActions()
        {
            $marketingList = MarketingListTestHelper::createMarketingListByName('MarketingListName 01',
                                                                                'MarketingListDescription 01');
            $this->runControllerShouldResultInAccessFailureAndGetContent('marketingLists/default');
            $this->runControllerShouldResultInAccessFailureAndGetContent('marketingLists/default/index');
            $this->runControllerShouldResultInAccessFailureAndGetContent('marketingLists/default/list');
            $this->runControllerShouldResultInAccessFailureAndGetContent('marketingLists/default/create');
            $this->setGetArray(array('id' => $marketingList->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('marketingLists/default/edit');
            $this->runControllerShouldResultInAccessFailureAndGetContent('marketingLists/default/details');
            $this->resetGetArray();

            $this->user->setRight('MarketingListsModule', MarketingListsModule::getAccessRight());
            $this->assertTrue($this->user->save());
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default');
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/index');
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/list');

            $this->setGetArray(array('id' => $marketingList->id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/details');
            $this->assertTrue(strpos($content, '<p>To manage Marketing Lists you must have access to either contacts' .
                ' or leads. Contact the CRM administrator about this issue.</p>') !== false);
            $this->resetGetArray();

            $this->user->setRight('MarketingListsModule', MarketingListsModule::getCreateRight());
            $this->assertTrue($this->user->save());
            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/create');
            $this->assertTrue(strpos($content, '<p>To manage Marketing Lists you must have access to either contacts' .
                ' or leads. Contact the CRM administrator about this issue.</p>') !== false);

            $this->user->setRight('ContactsModule', ContactsModule::getAccessRight());
            $this->user->setRight('LeadsModule', LeadsModule::getAccessRight());
            $this->assertTrue($this->user->save());
            $this->setGetArray(array('id' => $marketingList->id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/details');
            $this->assertTrue(strpos($content, '<p>To manage Marketing Lists you must have access to either contacts' .
                ' or leads. Contact the CRM administrator about this issue.</p>') === false);
            $this->resetGetArray();

            $this->user->setRight('MarketingListsModule', MarketingListsModule::getCreateRight());
            $this->assertTrue($this->user->save());
            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/create');
            $this->assertTrue(strpos($content, '<p>To manage Marketing Lists you must have access to either contacts' .
                ' or leads. Contact the CRM administrator about this issue.</p>') === false);

            $this->setGetArray(array('id' => $marketingList->id));
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/edit');

            $this->user->setRight('MarketingListsModule', MarketingListsModule::getDeleteRight());
            $this->assertTrue($this->user->save());
            $this->runControllerWithRedirectExceptionAndGetUrl('marketingLists/default/delete');

            $this->setGetArray(array('id' => static::$listOwnedBySuper->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('marketingLists/default/edit');
            $this->runControllerShouldResultInAccessFailureAndGetContent('marketingLists/default/details');
            $this->runControllerShouldResultInAccessFailureAndGetContent('marketingLists/default/delete');
        }

        /**
         * @depends testRegularUserAllDefaultControllerActions
         */
        public function testRegularUserListAction()
        {
            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/list');
            $this->assertTrue(strpos($content, 'anyMixedAttributes') === false);
            $this->assertTrue(strpos($content, 'MarketingListName') === false);
            $this->assertTrue(strpos($content, 'MarketingListName2') === false);
            MarketingListTestHelper::createMarketingListByName('MarketingListName 02', 'MarketingListDescription 02');
            MarketingListTestHelper::createMarketingListByName('MarketingListName 03', 'MarketingListDescription 03');
            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/list');
            $this->assertTrue(strpos($content, 'anyMixedAttributes') !== false);
            $this->assertTrue(strpos($content, 'MarketingListName 02') !== false);
            $this->assertTrue(strpos($content, 'MarketingListName 03') !== false);
            $this->assertEquals(2, substr_count($content, 'MarketingListName'));
            //Test the search or paging of the listview.
            Yii::app()->clientScript->reset(); //to make sure old js doesn't make it to the UI
            $this->setGetArray(array('ajax' => 'list-view'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/list');
            $this->assertTrue(strpos($content, 'anyMixedAttributes') === false);
            $this->resetGetArray();
        }

        /**
         * @depends testRegularUserListAction
         */
        public function testRegularUserCreateAction()
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

        /**
         * @depends testRegularUserCreateAction
         */
        public function testRegularUserDetailsAction()
        {
            $marketingListId = self::getModelIdByModelNameAndName('MarketingList', 'New MarketingListName using Create');
            $this->setGetArray(array('id' => $marketingListId));
            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/details');
            $this->assertTrue(strpos($content, 'New MarketingListName using Create') !== false);
            $this->assertTrue(strpos($content, 'New MarketingList Description using Create') !== false);
            $this->assertEquals(3, substr_count($content, 'New MarketingListName using Create'));
            $this->assertTrue(strpos($content, '<span>Details</span></a>') !== false);
            $this->assertTrue(strpos($content, '<strong class="marketing-list-subscribers-stats">' .
                                                                                    '0 Subscribed</strong>') !== false);
            $this->assertTrue(strpos($content, '<strong class="marketing-list-unsubscribers-stats">' .
                                                                                    '0 Unsubscribed</strong>') !== false);
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
         * @depends testRegularUserCreateAction
         */
        public function testRegularUserEditAction()
        {
            $marketingListId = self::getModelIdByModelNameAndName ('MarketingList', 'New MarketingListName using Create');
            $this->setGetArray(array('id' => $marketingListId));
            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/edit');
            $this->assertTrue(strpos($content, 'New MarketingListName using Create') !== false);
            $this->assertEquals(3, substr_count($content, 'New MarketingListName using Create'));
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
         * @depends testRegularUserEditAction
         */
        public function testRegularUserDeleteAction()
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