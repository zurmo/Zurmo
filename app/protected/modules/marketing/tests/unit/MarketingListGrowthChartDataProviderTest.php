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
    class MarketingListGrowthChartDataProviderTest extends ZurmoBaseTest
    {
        private $marketingList;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $emailBox = EmailBoxUtil::getDefaultEmailBoxByUser(User::getByUsername('super'));
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
            $emailBoxes                 = EmailBox::getAll();
            $this->emailBox             = $emailBoxes[0];
            $this->marketingList        =
                    MarketingListTestHelper::createMarketingListByName('Test Marketing List');
        }

        public function teardown()
        {
            $marketingLists = MarketingList::getAll();
            $this->assertCount(1, $marketingLists);
            $marketingLists[0]->delete();
            parent::teardown();
        }

        public function testGetChartData()
        {
            $contact                = ContactTestHelper
                    ::createContactByNameForOwner('contact01', Yii::app()->user->userModel);
            $marketingListMember    = $this->createMarketingListMember($contact, '2013-04-01');
            $chartDataProvider      = new MarketingListGrowthChartDataProvider();
            $chartDataProvider->setBeginDate    ('2013-04-01');
            $chartDataProvider->setEndDate      ('2013-04-02');
            $chartDataProvider->setGroupBy      (MarketingOverallMetricsForm::GROUPING_TYPE_DAY);
            $chartDataProvider->setMarketingList($this->marketingList);
            $combinedDataProvider   = new MarketingListGrowthChartDataProvider();
            $combinedDataProvider->setBeginDate ('2013-04-01');
            $combinedDataProvider->setEndDate   ('2013-04-02');
            $combinedDataProvider->setGroupBy   (MarketingOverallMetricsForm::GROUPING_TYPE_DAY);
            $chartData              = $chartDataProvider->getChartData();
            $combinedChartData      = $combinedDataProvider->getChartData();
            $this->assertEquals(1, $chartData[0]['newSubscribersCount']);
            $this->assertEquals(0, $chartData[0]['existingSubscribersCount']);
            $this->assertEquals(0, $chartData[1]['newSubscribersCount']);
            $this->assertEquals(1, $chartData[1]['existingSubscribersCount']);
            $this->assertEquals($chartData, $combinedChartData);
            $marketingListMember->unsubscribed = true;
            $this->assertTrue($marketingListMember->unrestrictedSave());
            $chartData              = $chartDataProvider->getChartData();
            $combinedChartData      = $combinedDataProvider->getChartData();
            $this->assertEquals(1, $chartData[0]['newSubscribersCount']);
            $this->assertEquals(0, $chartData[0]['existingSubscribersCount']);
            $this->assertEquals(0, $chartData[1]['newSubscribersCount']);
            $this->assertEquals(0, $chartData[1]['existingSubscribersCount']);
            $this->assertEquals($chartData, $combinedChartData);

            $contact                = ContactTestHelper
                    ::createContactByNameForOwner('contact02', Yii::app()->user->userModel);
            $this->createMarketingListMember($contact, '2013-04-02');
            $chartData              = $chartDataProvider->getChartData();
            $combinedChartData      = $combinedDataProvider->getChartData();
            $this->assertEquals(1, $chartData[0]['newSubscribersCount']);
            $this->assertEquals(0, $chartData[0]['existingSubscribersCount']);
            $this->assertEquals(1, $chartData[1]['newSubscribersCount']);
            $this->assertEquals(0, $chartData[1]['existingSubscribersCount']);
            $this->assertEquals($chartData, $combinedChartData);
            $chartDataProvider->setGroupBy      (MarketingOverallMetricsForm::GROUPING_TYPE_WEEK);
            $combinedDataProvider->setGroupBy   (MarketingOverallMetricsForm::GROUPING_TYPE_WEEK);
            $chartData              = $chartDataProvider->getChartData();
            $combinedChartData      = $combinedDataProvider->getChartData();
            $this->assertEquals(2, $chartData[0]['newSubscribersCount']);
            $this->assertEquals(0, $chartData[0]['existingSubscribersCount']);
            $this->assertEquals($chartData, $combinedChartData);

            $contact                = ContactTestHelper
                    ::createContactByNameForOwner('contact03', Yii::app()->user->userModel);
            $this->createMarketingListMember($contact, '2013-05-15');
            $chartDataProvider->setEndDate      ('2013-05-17');
            $combinedDataProvider->setEndDate   ('2013-05-17');
            $chartDataProvider->setGroupBy      (MarketingOverallMetricsForm::GROUPING_TYPE_MONTH);
            $combinedDataProvider->setGroupBy   (MarketingOverallMetricsForm::GROUPING_TYPE_MONTH);
            $chartData              = $chartDataProvider->getChartData();
            $combinedChartData      = $combinedDataProvider->getChartData();
            $this->assertEquals(2, $chartData[0]['newSubscribersCount']);
            $this->assertEquals(0, $chartData[0]['existingSubscribersCount']);
            $this->assertEquals(1, $chartData[1]['newSubscribersCount']);
            $this->assertEquals(1, $chartData[1]['existingSubscribersCount']);
            $this->assertEquals($chartData, $combinedChartData);
            $chartDataProvider->setEndDate      ('2013-05-01');
            $combinedDataProvider->setEndDate   ('2013-05-01');
            $chartData              = $chartDataProvider->getChartData();
            $combinedChartData      = $combinedDataProvider->getChartData();
            $this->assertEquals(2, $chartData[0]['newSubscribersCount']);
            $this->assertEquals(0, $chartData[0]['existingSubscribersCount']);
            $this->assertEquals(1, $chartData[1]['newSubscribersCount']);
            $this->assertEquals(1, $chartData[1]['existingSubscribersCount']);
            $this->assertEquals($chartData, $combinedChartData);
        }

        private function createMarketingListMember($contact, $createdDateTime)
        {
            $marketingListMember                    = new MarketingListMember();
            $marketingListMember->setScenario('importModel');
            $marketingListMember->unsubscribed      = false;
            $marketingListMember->contact           = $contact;
            $marketingListMember->marketingList     = $this->marketingList;
            $marketingListMember->createdDateTime   = DateTimeUtil
                        ::convertTimestampToDbFormatDateTime(strtotime($createdDateTime));
            $this->assertTrue($marketingListMember->unrestrictedSave());
            return $marketingListMember;
        }
    }
?>