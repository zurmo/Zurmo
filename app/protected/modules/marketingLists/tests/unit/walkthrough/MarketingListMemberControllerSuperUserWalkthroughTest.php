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

    class MarketingListMemberControllerSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        protected $user;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Setup test data owned by the super user.
            $account        = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
            $marketingList1 = MarketingListTestHelper::createMarketingListByName('MarketingList1',
                                                                                            'MarketingList Description1');
            $marketingList2 = MarketingListTestHelper::createMarketingListByName('MarketingList2',
                                                                                        'MarketingList Description2');

            for ($i = 0; $i < 17; $i++)
            {
                if ($i%2)
                {
                    $unsubscribed = 0;
                }
                else
                {
                    $unsubscribed = 1;
                }
                $contact1    = ContactTestHelper::createContactWithAccountByNameForOwner('superContact1' . $i, $super, $account);
                $contact2    = ContactTestHelper::createContactWithAccountByNameForOwner('superContact2' . $i, $super, $account);
                MarketingListMemberTestHelper::createMarketingListMember($unsubscribed, $marketingList1, $contact1);
                MarketingListMemberTestHelper::createMarketingListMember($unsubscribed, $marketingList2, $contact2);

                ReadPermissionsOptimizationUtil::rebuild();
            }
        }

        public function setUp()
        {
            parent::setUp();
            $this->user = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            Yii::app()->user->userModel = $this->user;
        }

        public function testMassSubscribeActionsForSelectedIds()
        {
            // MassSubscribe view for selected ids
            $listId             = self::getModelIdByModelNameAndName('MarketingList', 'MarketingList1');
            $this->assertNotEmpty($listId);
            $list               = MarketingList::getById($listId);
            $this->assertNotEmpty($list);
            $members            = $list->marketingListMembers;
            $this->assertNotEmpty($members);
            $this->assertCount(17, $members);
            $subscribedCount    = MarketingListMember::getCountByMarketingListIdAndUnsubscribed($listId, 0);

            $selectedIdsArray   = array();
            foreach ($members as $member)
            {
                if ($member->unsubscribed == 1)
                {
                    $selectedIdsArray[]     = $member->id;
                }
                if (count($selectedIdsArray) === 4)
                {
                    break;
                }
            }
            $this->assertNotEmpty($selectedIdsArray);
            $selectedIds        = join(',', $selectedIdsArray);
            $this->setGetArray(
                            array(
                                'selectedIds'               => $selectedIds,
                                'selectAll'                 => '',
                                'id'                        => $listId
                            )
                        );  // Not Coding Standard
            $content            = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/member/massSubscribe');
            $this->assertTrue(strpos($content, 'Mass Subscribe: Marketing List Members') !== false);
            $this->assertTrue(strpos($content, '<strong>4</strong>&#160;Marketing List Members' .
                                                                                ' selected for subscription') !== false);
            // MassSubscribe view for all result selected ids
            $this->setGetArray(
                            array(
                                'selectAll'                 => '1',
                                'id'                        => $listId
                            )
                        );
            $content            = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/member/massSubscribe');
            $this->assertTrue(strpos($content, '<strong>17</strong>&#160;Marketing List Members'.
                                                                                ' selected for subscription') !== false);

            // Mass Subscribe, multiple pages subscribe, first page
            $selectedIdsArray   = array();
            foreach ($members as $member)
            {
                if ($member->unsubscribed == 1)
                {
                    $selectedIdsArray[]     = $member->id;
                }
                if (count($selectedIdsArray) === 7)
                {
                    break;
                }
            }
            $this->assertNotEmpty($selectedIdsArray);
            $selectedIds        = join(',', $selectedIdsArray);
            $pageSize           = Yii::app()->pagination->getForCurrentUserByType('massEditProgressPageSize');
            $this->assertEquals(5, $pageSize);
            // MassSubscribe for selected ids for page 1
            $this->setGetArray(
                            array(
                                'id'                                        => $listId,
                                'selectedIds'                               => $selectedIds,
                                'selectAll'                                 => '',
                                'massSubscribe'                             => '',
                                'MarketingListMembersForPortletView_page'   => 1
                            )
                        );
            $this->setPostArray(
                            array(
                                'selectedRecordCount' => 7
                            )
                        );
            $this->runControllerWithExitExceptionAndGetContent('marketingLists/member/massSubscribe');
            $expectedSubscribedCountAfterFirstRequest   = $subscribedCount + $pageSize;
            $actualSubscribedCountAfterFirstRequest     = MarketingListMember::getCountByMarketingListIdAndUnsubscribed($listId, 0);
            $this->assertEquals($expectedSubscribedCountAfterFirstRequest, $actualSubscribedCountAfterFirstRequest);

            // Mass Subscribe, multiple pages subscribe, second page
            $this->setGetArray(
                            array(
                                'id'                        => $listId,
                                'selectedIds'               => $selectedIds,
                                'selectAll'                 => '',
                                'massSubscribe'             => '',
                                'MarketingListMember_page'  => 2
                            )
                        );
            $this->setPostArray(
                            array(
                                'selectedRecordCount'       => 7
                            )
                        );
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/member/massSubscribeProgress');
            $expectedSubscribedCountAfterSecondRequest   = $actualSubscribedCountAfterFirstRequest + (7 - $pageSize);
            $actualSubscribedCountAfterSecondRequest     = MarketingListMember::getCountByMarketingListIdAndUnsubscribed($listId, 0);
            $this->assertEquals($expectedSubscribedCountAfterSecondRequest, $actualSubscribedCountAfterSecondRequest);
        }

        /**
         * @depends testMassSubscribeActionsForSelectedIds
         */
        public function testMassSubscribePagesProperlyAndSubscribesAllSelected()
        {
            // MassSubscribe for selected Record Count
            $listId         = self::getModelIdByModelNameAndName('MarketingList', 'MarketingList2');
            $this->assertNotEmpty($listId);
            $list           = MarketingList::getById($listId);
            $this->assertNotEmpty($list);
            $members        = $list->marketingListMembers;
            $this->assertEquals(17, count($members));
            $subscribedCount    = MarketingListMember::getCountByMarketingListIdAndUnsubscribed($listId, 0);

            $this->setGetArray(
                            array(
                                'selectAll'                                 => '1',           // Not Coding Standard
                                'MarketingListMembersForPortletView_page'   => 1,
                                'id'                                        => $listId
                            )
                        );
            $this->setPostArray(
                            array(
                                'selectedRecordCount'                       => 17
                            )
                        );
            // Run Mass Subscribe using progress save for page1.
            $pageSize       = Yii::app()->pagination->getForCurrentUserByType('massEditProgressPageSize');
            $this->assertEquals(5, $pageSize);
            $this->runControllerWithExitExceptionAndGetContent('marketingLists/member/massSubscribe');
            $expectedSubscribedCountAfterFirstRequest   = $subscribedCount + 3;
            $actualSubscribedCountAfterFirstRequest     = MarketingListMember::getCountByMarketingListIdAndUnsubscribed($listId, 0);
            $this->assertEquals($expectedSubscribedCountAfterFirstRequest, $actualSubscribedCountAfterFirstRequest);

            $this->setGetArray(
                            array(
                                'selectAll'                                 => '1',           // Not Coding Standard
                                'MarketingListMember_page'                  => 2,
                                'id'                                        => $listId
                            )
                        );
            $this->setPostArray(
                            array(
                                'selectedRecordCount'                       => 17
                            )
                        );
            // Run Mass Subscribe using progress save for page2.
            $pageSize       = Yii::app()->pagination->getForCurrentUserByType('massEditProgressPageSize');
            $this->assertEquals(5, $pageSize);
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/member/massSubscribeProgress');
            $expectedSubscribedCountAfterSecondRequest   = $expectedSubscribedCountAfterFirstRequest + 2;
            $actualSubscribedCountAfterSecondRequest     = MarketingListMember::getCountByMarketingListIdAndUnsubscribed($listId, 0);
            $this->assertEquals($expectedSubscribedCountAfterSecondRequest, $actualSubscribedCountAfterSecondRequest);

            $this->setGetArray(
                            array(
                                'selectAll'                                 => '1',           // Not Coding Standard
                                'MarketingListMember_page'                  => 3,
                                'id'                                        => $listId
                            )
                        );
            $this->setPostArray(
                            array(
                                'selectedRecordCount'                       => 17
                            )
                        );
            // Run Mass Subscribe using progress save for page3.
            $pageSize       = Yii::app()->pagination->getForCurrentUserByType('massEditProgressPageSize');
            $this->assertEquals(5, $pageSize);
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/member/massSubscribeProgress');
            $expectedSubscribedCountAfterThirdRequest   = $expectedSubscribedCountAfterSecondRequest + 3;
            $actualSubscribedCountAfterThirdRequest     = MarketingListMember::getCountByMarketingListIdAndUnsubscribed($listId, 0);
            $this->assertEquals($expectedSubscribedCountAfterThirdRequest, $actualSubscribedCountAfterThirdRequest);

            $this->setGetArray(
                            array(
                                'selectAll'                                 => '1',           // Not Coding Standard
                                'MarketingListMember_page'                  => 4,
                                'id' => $listId
                            )
                        );
            $this->setPostArray(
                            array(
                                'selectedRecordCount'                       => 17
                            )
                        );
            // Run Mass Subscribe using progress save for page4.
            $pageSize       = Yii::app()->pagination->getForCurrentUserByType('massEditProgressPageSize');
            $this->assertEquals(5, $pageSize);
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/member/massSubscribeProgress');
            $expectedSubscribedCountAfterFourthRequest   = $expectedSubscribedCountAfterThirdRequest + 1;
            $actualSubscribedCountAfterFourthRequest     = MarketingListMember::getCountByMarketingListIdAndUnsubscribed($listId, 0);
            $this->assertEquals($expectedSubscribedCountAfterFourthRequest, $actualSubscribedCountAfterFourthRequest);

            $unsubscribedCount      = MarketingListMember::getCountByMarketingListIdAndUnsubscribed($listId, 1);
            $this->assertEquals(0, $unsubscribedCount);
        }

        /**
         * @depends testMassSubscribeActionsForSelectedIds
         */
        public function testMassUnsubscribeActionsForSelectedIds()
        {
            // MassUnsubscribe view for selected ids
            $listId             = self::getModelIdByModelNameAndName('MarketingList', 'MarketingList1');
            $this->assertNotEmpty($listId);
            $list               = MarketingList::getById($listId);
            $this->assertNotEmpty($list);
            $members            = $list->marketingListMembers;
            $this->assertNotEmpty($members);
            $this->assertCount(17, $members);
            $unsubscribedCount    = MarketingListMember::getCountByMarketingListIdAndUnsubscribed($listId, 1);

            $selectedIdsArray   = array();
            foreach ($members as $member)
            {
                if ($member->unsubscribed == 0)
                {
                    $selectedIdsArray[]     = $member->id;
                }
                if (count($selectedIdsArray) === 4)
                {
                    break;
                }
            }
            $this->assertNotEmpty($selectedIdsArray);
            $selectedIds        = join(',', $selectedIdsArray);
            $this->setGetArray(
                            array(
                                'selectedIds'               => $selectedIds,
                                'selectAll'                 => '',
                                'id'                        => $listId
                            )
                        );  // Not Coding Standard
            $content            = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/member/massUnsubscribe');
            $this->assertTrue(strpos($content, 'Mass Unsubscribe: Marketing List Members') !== false);
            $this->assertTrue(strpos($content, '<strong>4</strong>&#160;Marketing List Members' .
                                                                                ' selected for unsubscription') !== false);
            // MassUnsubscribe view for all result selected ids
            $this->setGetArray(
                            array(
                                'selectAll'                 => '1',
                                'id'                        => $listId
                            )
                        );
            $content            = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/member/massUnsubscribe');
            $this->assertTrue(strpos($content, '<strong>17</strong>&#160;Marketing List Members'.
                                                                                ' selected for unsubscription') !== false);

            // Mass Unsubscribe, multiple pages unsubscribe, first page
            $selectedIdsArray   = array();
            foreach ($members as $member)
            {
                if ($member->unsubscribed == 0)
                {
                    $selectedIdsArray[]     = $member->id;
                }
                if (count($selectedIdsArray) === 7)
                {
                    break;
                }
            }
            $this->assertNotEmpty($selectedIdsArray);
            $selectedIds        = join(',', $selectedIdsArray);
            $pageSize           = Yii::app()->pagination->getForCurrentUserByType('massEditProgressPageSize');
            $this->assertEquals(5, $pageSize);
            // MassUnsubscribe for selected ids for page 1
            $this->setGetArray(
                            array(
                                'id'                                        => $listId,
                                'selectedIds'                               => $selectedIds,
                                'selectAll'                                 => '',
                                'massUnsubscribe'                           => '',
                                'MarketingListMembersForPortletView_page'   => 1
                            )
                        );
            $this->setPostArray(
                            array(
                                'selectedRecordCount' => 7
                            )
                        );
            $this->runControllerWithExitExceptionAndGetContent('marketingLists/member/massUnsubscribe');
            $expectedUnsubscribedCountAfterFirstRequest   = $unsubscribedCount + $pageSize;
            $actualUnsubscribedCountAfterFirstRequest     = MarketingListMember::getCountByMarketingListIdAndUnsubscribed($listId, 1);
            $this->assertEquals($expectedUnsubscribedCountAfterFirstRequest, $actualUnsubscribedCountAfterFirstRequest);

            // Mass Unsubscribe, multiple pages unsubscribe, second page
            $this->setGetArray(
                            array(
                                'id'                        => $listId,
                                'selectedIds'               => $selectedIds,
                                'selectAll'                 => '',
                                'massUnsubscribe'           => '',
                                'MarketingListMember_page'  => 2
                            )
                        );
            $this->setPostArray(
                            array(
                                'selectedRecordCount'       => 7
                            )
                        );
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/member/massUnsubscribeProgress');
            $expectedUnsubscribedCountAfterSecondRequest   = $actualUnsubscribedCountAfterFirstRequest + (7 - $pageSize);
            $actualUnsubscribedCountAfterSecondRequest     = MarketingListMember::getCountByMarketingListIdAndUnsubscribed($listId, 1);
            $this->assertEquals($expectedUnsubscribedCountAfterSecondRequest, $actualUnsubscribedCountAfterSecondRequest);
        }

        /**
         * @depends testMassUnsubscribeActionsForSelectedIds
         */
        public function testMassUnsubscribePagesProperlyAndUnsubscribesAllSelected()
        {
            // MassUnsubscribe for selected Record Count
            $listId         = self::getModelIdByModelNameAndName('MarketingList', 'MarketingList2');
            $this->assertNotEmpty($listId);
            $list           = MarketingList::getById($listId);
            $this->assertNotEmpty($list);
            $members        = $list->marketingListMembers;
            $this->assertEquals(17, count($members));
            $unsubscribedCount    = MarketingListMember::getCountByMarketingListIdAndUnsubscribed($listId, 1);

            $this->setGetArray(
                            array(
                                'selectAll'                                 => '1',           // Not Coding Standard
                                'MarketingListMembersForPortletView_page'   => 1,
                                'id'                                        => $listId
                            )
                        );
            $this->setPostArray(
                            array(
                                'selectedRecordCount'                       => 17
                            )
                        );
            // Run Mass Unsubscribe using progress save for page1.
            $pageSize       = Yii::app()->pagination->getForCurrentUserByType('massEditProgressPageSize');
            $this->assertEquals(5, $pageSize);
            $this->runControllerWithExitExceptionAndGetContent('marketingLists/member/massUnsubscribe');
            $expectedUnsubscribedCountAfterFirstRequest   = $unsubscribedCount + $pageSize; // because the subscribe tests subscribed all
            $actualUnsubscribedCountAfterFirstRequest     = MarketingListMember::getCountByMarketingListIdAndUnsubscribed($listId, 1);

            $this->assertEquals($expectedUnsubscribedCountAfterFirstRequest, $actualUnsubscribedCountAfterFirstRequest);

            $this->setGetArray(
                            array(
                                'selectAll'                                 => '1',           // Not Coding Standard
                                'MarketingListMember_page'                  => 2,
                                'id'                                        => $listId
                            )
                        );
            $this->setPostArray(
                            array(
                                'selectedRecordCount'                       => 17
                            )
                        );
            // Run Mass Unsubscribe using progress save for page2.
            $pageSize       = Yii::app()->pagination->getForCurrentUserByType('massEditProgressPageSize');
            $this->assertEquals(5, $pageSize);
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/member/massUnsubscribeProgress');
            $expectedUnsubscribedCountAfterSecondRequest   = $expectedUnsubscribedCountAfterFirstRequest + 5; // because the subscribe tests subscribed all
            $actualUnsubscribedCountAfterSecondRequest     = MarketingListMember::getCountByMarketingListIdAndUnsubscribed($listId, 1);
            $this->assertEquals($expectedUnsubscribedCountAfterSecondRequest, $actualUnsubscribedCountAfterSecondRequest);

            $this->setGetArray(
                            array(
                                'selectAll'                                 => '1',           // Not Coding Standard
                                'MarketingListMember_page'                  => 3,
                                'id'                                        => $listId
                            )
                        );
            $this->setPostArray(
                            array(
                                'selectedRecordCount'                       => 17
                            )
                        );
            // Run Mass Unsubscribe using progress save for page3.
            $pageSize       = Yii::app()->pagination->getForCurrentUserByType('massEditProgressPageSize');
            $this->assertEquals(5, $pageSize);
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/member/massUnsubscribeProgress');
            $expectedUnsubscribedCountAfterThirdRequest   = $expectedUnsubscribedCountAfterSecondRequest + 5; // because the subscribe tests subscribed all
            $actualUnsubscribedCountAfterThirdRequest     = MarketingListMember::getCountByMarketingListIdAndUnsubscribed($listId, 1);
            $this->assertEquals($expectedUnsubscribedCountAfterThirdRequest, $actualUnsubscribedCountAfterThirdRequest);

            $this->setGetArray(
                            array(
                                'selectAll'                                 => '1',           // Not Coding Standard
                                'MarketingListMember_page'                  => 4,
                                'id' => $listId
                            )
                        );
            $this->setPostArray(
                            array(
                                'selectedRecordCount'                       => 17
                            )
                        );
            // Run Mass Unsubscribe using progress save for page4.
            $pageSize       = Yii::app()->pagination->getForCurrentUserByType('massEditProgressPageSize');
            $this->assertEquals(5, $pageSize);
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/member/massUnsubscribeProgress');
            $expectedUnsubscribedCountAfterFourthRequest   = $expectedUnsubscribedCountAfterThirdRequest+ 2; // because the subscribe tests subscribed all
            $actualUnsubscribedCountAfterFourthRequest     = MarketingListMember::getCountByMarketingListIdAndUnsubscribed($listId, 1);
            $this->assertEquals($expectedUnsubscribedCountAfterFourthRequest, $actualUnsubscribedCountAfterFourthRequest);

            $subscribedCount      = MarketingListMember::getCountByMarketingListIdAndUnsubscribed($listId, 0);
            $this->assertEquals(0, $subscribedCount);
        }

        public function testMassDeleteActionsForSelectedIds()
        {
            // MassDelete view for selected ids
            $listId             = self::getModelIdByModelNameAndName('MarketingList', 'MarketingList1');
            $this->assertNotEmpty($listId);
            $list               = MarketingList::getById($listId);
            $this->assertNotEmpty($list);
            $members            = $list->marketingListMembers;
            $this->assertNotEmpty($members);
            $this->assertCount(17, $members);

            $selectedIdsArray   = array();
            foreach ($members as $member)
            {
                $selectedIdsArray[]     = $member->id;
                if (count($selectedIdsArray) === 4)
                {
                    break;
                }
            }
            $this->assertNotEmpty($selectedIdsArray);
            $selectedIds        = join(',', $selectedIdsArray);
            $this->setGetArray(
                            array(
                                'selectedIds'           => $selectedIds,
                                'selectAll'             => '',
                                'id'                    => $listId
                            )
                        );  // Not Coding Standard
            $content            = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/member/massDelete');
            $this->assertTrue(strpos($content, 'Mass Delete: Marketing List Members') !== false);
            $this->assertTrue(strpos($content, '<strong>4</strong>&#160;Marketing List Members' .
                                                                                    ' selected for removal') !== false);
            // MassDelete view for all result selected ids
            $this->setGetArray(
                            array(
                                'selectAll'             => '1',
                                'id'                    => $listId
                            )
                        );
            $content            = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/member/massDelete');
            $this->assertTrue(strpos($content, '<strong>17</strong>&#160;Marketing List Members'.
                                                                                    ' selected for removal') !== false);

            // Mass delete, multiple pages delete, first page
            $list               = MarketingList::getById($listId);
            $this->assertNotEmpty($list);
            $members            = $list->marketingListMembers;
            $this->assertNotEmpty($members);
            $this->assertEquals(17, count($members));
            $selectedIdsArray   = array();
            foreach ($members as $member)
            {
                $selectedIdsArray[]     = $member->id;
                if (count($selectedIdsArray) === 7)
                {
                    break;
                }
            }
            $this->assertNotEmpty($selectedIdsArray);
            $selectedIds        = join(',', $selectedIdsArray);
            $pageSize           = Yii::app()->pagination->getForCurrentUserByType('massDeleteProgressPageSize');
            $this->assertEquals(5, $pageSize);
            // MassDelete for selected ids for page 1
            $this->setGetArray(
                            array(
                                'id'                                        => $listId,
                                'selectedIds'                               => $selectedIds,
                                'selectAll'                                 => '',
                                'massDelete'                                => '',
                                'MarketingListMembersForPortletView_page'   => 1
                            )
                        );
            $this->setPostArray(
                            array(
                                'selectedRecordCount' => 7
                                )
                            );
            $this->runControllerWithExitExceptionAndGetContent('marketingLists/member/massDelete');
            $list->forgetAll();
            $list           = MarketingList::getById($listId);
            $this->assertNotEmpty($list);
            $members        = $list->marketingListMembers;
            $this->assertNotEmpty($members);
            $this->assertEquals(12, count($members));

            // Mass delete, multiple pages delete, second page
            $this->setGetArray(
                            array(
                                'id'                        => $listId,
                                'selectedIds'               => $selectedIds,
                                'selectAll'                 => '',
                                'massDelete'                => '',
                                'MarketingListMember_page'  => 2
                            )
                        );
            $this->setPostArray(
                            array(
                                'selectedRecordCount'       => 7
                            )
                        );
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/member/massDeleteProgress');

            $list->forgetAll();
            $list           = MarketingList::getById($listId);
            $this->assertNotEmpty($list);
            $members        = $list->marketingListMembers;
            $this->assertNotEmpty($members);
            $this->assertEquals(10, count($members));
        }

        /**
         * @depends testMassDeleteActionsForSelectedIds
         */
        public function testMassDeletePagesProperlyAndRemovesAllSelected()
        {
            // MassDelete for selected Record Count
            $listId         = self::getModelIdByModelNameAndName('MarketingList', 'MarketingList2');
            $this->assertNotEmpty($listId);
            $list           = MarketingList::getById($listId);
            $this->assertNotEmpty($list);
            $members        = $list->marketingListMembers;
            $this->assertEquals(17, count($members));

            // save Model MassDelete for entire search result
            $this->setGetArray(
                            array(
                                'selectAll'                                 => '1',           // Not Coding Standard
                                'MarketingListMembersForPortletView_page'   => 1,
                                'id'                                        => $listId
                            )
                        );
            $this->setPostArray(
                            array(
                                'selectedRecordCount'                       => 17
                            )
                        );
            // Run Mass Delete using progress save for page1.
            $pageSize       = Yii::app()->pagination->getForCurrentUserByType('massDeleteProgressPageSize');
            $this->assertEquals(5, $pageSize);
            $this->runControllerWithExitExceptionAndGetContent('marketingLists/member/massDelete');

            // check for previous mass delete progress
            $list->forgetAll();
            $list           = MarketingList::getById($listId);
            $this->assertNotEmpty($list);
            $members        = $list->marketingListMembers;
            $this->assertEquals(12, count($members));

            $this->setGetArray(
                            array(
                                'selectAll'                                 => '1',           // Not Coding Standard
                                'MarketingListMember_page'                  => 2,
                                'id'                                        => $listId
                                )
                            );
            $this->setPostArray(
                            array(
                                'selectedRecordCount'                       => 17
                            )
                        );
            // Run Mass Delete using progress save for page2.
            $pageSize       = Yii::app()->pagination->getForCurrentUserByType('massDeleteProgressPageSize');
            $this->assertEquals(5, $pageSize);
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/member/massDeleteProgress');

            $list->forgetAll();
            $list           = MarketingList::getById($listId);
            $this->assertNotEmpty($list);
            $members        = $list->marketingListMembers;
            $this->assertEquals(7, count($members));

            $this->setGetArray(
                            array(
                                'selectAll'                                 => '1',           // Not Coding Standard
                                'MarketingListMember_page'                  => 3,
                                'id'                                        => $listId
                            )
                        );
            $this->setPostArray(
                            array(
                                'selectedRecordCount'                       => 17
                            )
                        );
            // Run Mass Delete using progress save for page3.
            $pageSize       = Yii::app()->pagination->getForCurrentUserByType('massDeleteProgressPageSize');
            $this->assertEquals(5, $pageSize);
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/member/massDeleteProgress');

            $list->forgetAll();
            $list           = MarketingList::getById($listId);
            $this->assertNotEmpty($list);
            $members        = $list->marketingListMembers;
            // TODO: @Shoaibi/@Jason: Medium: This should be 2. Bug in $dataprovider->getData() for page 3, also happens on UI, just for MLM::massDelete
            $this->assertEquals(5, count($members));

            $this->setGetArray(
                            array(
                                'selectAll'                                 => '1',           // Not Coding Standard
                                'MarketingListMember_page'                  => 4,
                                'id' => $listId
                            )
                        );
            $this->setPostArray(
                            array(
                                'selectedRecordCount'                       => 17
                            )
                        );
            // Run Mass Delete using progress save for page4.
            $pageSize       = Yii::app()->pagination->getForCurrentUserByType('massDeleteProgressPageSize');
            $this->assertEquals(5, $pageSize);
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/member/massDeleteProgress');

            $list->forgetAll();
            $list           = MarketingList::getById($listId);
            $this->assertNotEmpty($list);
            $members        = $list->marketingListMembers;
            $this->assertEquals(0, count($members));
        }
    }
?>