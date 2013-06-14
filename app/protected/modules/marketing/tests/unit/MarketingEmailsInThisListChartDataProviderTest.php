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
    class MarketingEmailsInThisListChartDataProviderTest extends ZurmoBaseTest
    {
        private $campaign;
        private $marketingList;
        private $emailBox;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();

            $emailBox = EmailBoxUtil::getDefaultEmailBoxByUser(User::getByUsername('super'));
            ContactTestHelper
                    ::createContactByNameForOwner('contact01', Yii::app()->user->userModel);
            ContactTestHelper
                    ::createContactByNameForOwner('contact02', Yii::app()->user->userModel);
            ContactTestHelper
                    ::createContactByNameForOwner('contact03', Yii::app()->user->userModel);
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
            $emailBoxes     = EmailBox::getAll();
            $this->emailBox = $emailBoxes[0];
            $this->marketingList    =
                    MarketingListTestHelper::createMarketingListByName('Test Marketing List');
            $this->campaign         =
                    CampaignTestHelper::createCampaign('Test Campaing 01', 'text', 'text exemple');
        }

        public function teardown()
        {
            $marketingLists = MarketingList::getAll();
            $this->assertCount(1, $marketingLists);
            $marketingLists[0]->delete();
            $campaigns      = Campaign::getAll();
            $this->assertCount(1, $campaigns);
            $campaigns[0]->delete();
            parent::teardown();
        }

        public function testGetChartDataWithoutData()
        {
            $chartDataProvider  = new MarketingEmailsInThisListChartDataProvider();
            $chartDataProvider->setBeginDate('2000-01-01');
            $chartDataProvider->setEndDate('2000-01-15');
            $chartDataProvider->setGroupBy(MarketingOverallMetricsForm::GROUPING_TYPE_DAY);
            $chartData          = $chartDataProvider->getChartData();
            $count              = 15;
            $this->assertCount($count, $chartData);
            for ($i = 0; $i < $count; $i++)
            {
                $this->assertEquals(0,                    $chartData[$i][MarketingChartDataProvider::QUEUED]);
                $this->assertEquals(0,                    $chartData[$i][MarketingChartDataProvider::SENT]);
                $this->assertEquals(0,                    $chartData[$i][MarketingChartDataProvider::UNIQUE_CLICKS]);
                $this->assertEquals(0,                    $chartData[$i][MarketingChartDataProvider::UNIQUE_OPENS]);
                $this->assertEquals(0,                    $chartData[$i][MarketingChartDataProvider::BOUNCED]);
                $this->assertEquals(0,                    $chartData[$i][MarketingChartDataProvider::UNSUBSCRIBED]);
                $this->assertEquals('Jan ' . ($i + 1),    $chartData[$i]['displayLabel']);
                $this->assertEquals('Jan ' . ($i + 1),    $chartData[$i]['dateBalloonLabel']);
            }

            $chartDataProvider->setGroupBy(MarketingOverallMetricsForm::GROUPING_TYPE_WEEK);
            $chartData          = $chartDataProvider->getChartData();
            $count              = 3;
            $this->assertCount($count,              $chartData);
            $this->assertEquals(0,                  $chartData[0][MarketingChartDataProvider::QUEUED]);
            $this->assertEquals(0,                  $chartData[0][MarketingChartDataProvider::SENT]);
            $this->assertEquals(0,                  $chartData[0][MarketingChartDataProvider::UNIQUE_CLICKS]);
            $this->assertEquals(0,                  $chartData[0][MarketingChartDataProvider::UNIQUE_OPENS]);
            $this->assertEquals(0,                  $chartData[0][MarketingChartDataProvider::BOUNCED]);
            $this->assertEquals(0,                  $chartData[0][MarketingChartDataProvider::UNSUBSCRIBED]);
            $this->assertEquals('Dec 27',           $chartData[0]['displayLabel']);
            $this->assertEquals('Week of Dec 27',   $chartData[0]['dateBalloonLabel']);
            $this->assertEquals(0,                  $chartData[1][MarketingChartDataProvider::QUEUED]);
            $this->assertEquals(0,                  $chartData[1][MarketingChartDataProvider::SENT]);
            $this->assertEquals(0,                  $chartData[1][MarketingChartDataProvider::UNIQUE_CLICKS]);
            $this->assertEquals(0,                  $chartData[1][MarketingChartDataProvider::UNIQUE_OPENS]);
            $this->assertEquals(0,                  $chartData[1][MarketingChartDataProvider::BOUNCED]);
            $this->assertEquals(0,                  $chartData[1][MarketingChartDataProvider::UNSUBSCRIBED]);
            $this->assertEquals('Jan 3',            $chartData[1]['displayLabel']);
            $this->assertEquals('Week of Jan 3',    $chartData[1]['dateBalloonLabel']);

            $chartDataProvider->setGroupBy(MarketingOverallMetricsForm::GROUPING_TYPE_MONTH);
            $chartData          = $chartDataProvider->getChartData();
            $count              = 1;
            $this->assertCount($count,      $chartData);
            $this->assertEquals(0,          $chartData[0][MarketingChartDataProvider::QUEUED]);
            $this->assertEquals(0,          $chartData[0][MarketingChartDataProvider::SENT]);
            $this->assertEquals(0,          $chartData[0][MarketingChartDataProvider::UNIQUE_CLICKS]);
            $this->assertEquals(0,          $chartData[0][MarketingChartDataProvider::UNIQUE_OPENS]);
            $this->assertEquals(0,          $chartData[0][MarketingChartDataProvider::BOUNCED]);
            $this->assertEquals(0,          $chartData[0][MarketingChartDataProvider::UNSUBSCRIBED]);
            $this->assertEquals('Jan',      $chartData[0]['displayLabel']);
            $this->assertEquals('Jan',      $chartData[0]['dateBalloonLabel']);
        }

        /**
         * @dataProvider dataForTestGetChartDataForCampaigns
         */
        public function testGetChartDataForCampaigns($campaingItemActivityCreationArray, $emailMessageSentDateTime)
        {
            $contacts = Contact::getAll();
            $this->addCampaingItem($contacts[0], $emailMessageSentDateTime, $campaingItemActivityCreationArray);

            $sent = true;
            if (!isset($emailMessageSentDateTime))
            {
                $emailMessageSentDateTime = date('Y-m-d');
                $sent = false;
            }
            //Test when beginDate < sentDate < endDate
            //Grouping by day
            $beginDate          = date('Y-m-d', strtotime($emailMessageSentDateTime) - 1 * 24 * 60 * 60);
            $endDate            = date('Y-m-d', strtotime($emailMessageSentDateTime) + 1 * 24 * 60 * 60);
            $chartDataProvider  = new MarketingEmailsInThisListChartDataProvider();
            $chartDataProvider->setBeginDate($beginDate);
            $chartDataProvider->setEndDate  ($endDate);
            $chartDataProvider->setGroupBy(MarketingOverallMetricsForm::GROUPING_TYPE_DAY);
            $chartDataProvider->setCampaign($this->campaign);
            $chartData          = $chartDataProvider->getChartData();

            $this->assertCount (3,          $chartData);

            $displayLabel = DateTimeUtil
                    ::resolveValueForDateLocaleFormattedDisplay(
                            $beginDate,
                            DateTimeUtil::DISPLAY_FORMAT_ABBREVIATED_MONTH_AND_DAY_WIDTH
                      );
            $expectedArray                      = array();
            $expectedArray['queued']            = 0;
            $expectedArray['sent']              = 0;
            $expectedArray['uniqueClicks']      = 0;
            $expectedArray['uniqueOpens']       = 0;
            $expectedArray['bounced']           = 0;
            $expectedArray['optedOut']          = 0;
            $expectedArray['displayLabel']      = $displayLabel;
            $expectedArray['dateBalloonLabel']  = $displayLabel;
            $this->assertChartDataColumnAsExpected($expectedArray, $chartData[0]);

            $displayLabel = DateTimeUtil
                    ::resolveValueForDateLocaleFormattedDisplay(
                            $emailMessageSentDateTime,
                            DateTimeUtil::DISPLAY_FORMAT_ABBREVIATED_MONTH_AND_DAY_WIDTH
                      );
            $expectedArray                      = array();
            $expectedArray['queued']            = 0;
            $expectedArray['sent']              = $sent;
            $expectedArray['displayLabel']      = $displayLabel;
            $expectedArray['dateBalloonLabel']  = $displayLabel;
            $this->resolveExpectedDataFromCreationArray($campaingItemActivityCreationArray, $expectedArray);
            $this->assertChartDataColumnAsExpected($expectedArray, $chartData[1]);

            //Grouping by week
            $beginDate          = date('Y-m-d', strtotime($emailMessageSentDateTime) - 7 * 24 * 60 * 60);
            $endDate            = date('Y-m-d', strtotime($emailMessageSentDateTime) + 7 * 24 * 60 * 60);
            $chartDataProvider  = new MarketingEmailsInThisListChartDataProvider();
            $chartDataProvider->setBeginDate($beginDate);
            $chartDataProvider->setEndDate  ($endDate);
            $chartDataProvider->setGroupBy(MarketingOverallMetricsForm::GROUPING_TYPE_WEEK);
            $chartDataProvider->setCampaign($this->campaign);
            $chartData          = $chartDataProvider->getChartData();

            $this->assertCount (3,          $chartData);

            $date = new DateTime($beginDate);
            $date->modify(('Sunday' == $date->format('l')) ? 'Monday last week' : 'Monday this week');
            $beginDateOfWeek = $date->format('Y-m-d');
            $displayLabel = DateTimeUtil
                    ::resolveValueForDateLocaleFormattedDisplay(
                            $beginDateOfWeek,
                            DateTimeUtil::DISPLAY_FORMAT_ABBREVIATED_MONTH_AND_DAY_WIDTH
                      );
            $expectedArray                      = array();
            $expectedArray['queued']            = 0;
            $expectedArray['sent']              = 0;
            $expectedArray['uniqueClicks']      = 0;
            $expectedArray['uniqueOpens']       = 0;
            $expectedArray['bounced']           = 0;
            $expectedArray['optedOut']          = 0;
            $expectedArray['displayLabel']      = $displayLabel;
            $expectedArray['dateBalloonLabel']  = 'Week of ' . $displayLabel;
            $this->assertChartDataColumnAsExpected($expectedArray, $chartData[0]);

            $date = new DateTime($emailMessageSentDateTime);
            $date->modify(('Sunday' == $date->format('l')) ? 'Monday last week' : 'Monday this week');
            $beginDateOfWeek = $date->format('Y-m-d');
            $displayLabel = DateTimeUtil
                    ::resolveValueForDateLocaleFormattedDisplay(
                            $beginDateOfWeek,
                            DateTimeUtil::DISPLAY_FORMAT_ABBREVIATED_MONTH_AND_DAY_WIDTH
                      );
            $expectedArray                      = array();
            $expectedArray['queued']            = 0;
            $expectedArray['sent']              = $sent;
            $expectedArray['displayLabel']      = $displayLabel;
            $expectedArray['dateBalloonLabel']  = 'Week of ' . $displayLabel;
            $this->resolveExpectedDataFromCreationArray($campaingItemActivityCreationArray, $expectedArray);
            $this->assertChartDataColumnAsExpected($expectedArray, $chartData[1]);

            //Test when beginDate < endDate < sentDate for day grouping
            $beginDate          = date('Y-m-d', strtotime($emailMessageSentDateTime) - 2 * 24 * 60 * 60);
            $endDate            = date('Y-m-d', strtotime($emailMessageSentDateTime) - 1 * 24 * 60 * 60);
            $chartDataProvider->setBeginDate($beginDate);
            $chartDataProvider->setEndDate  ($endDate);
            $chartDataProvider->setGroupBy(MarketingOverallMetricsForm::GROUPING_TYPE_DAY);
            $chartDataProvider->setCampaign($this->campaign);
            $chartData          = $chartDataProvider->getChartData();

            $this->assertCount (2,          $chartData);

            $displayLabel = DateTimeUtil
                    ::resolveValueForDateLocaleFormattedDisplay(
                            $beginDate,
                            DateTimeUtil::DISPLAY_FORMAT_ABBREVIATED_MONTH_AND_DAY_WIDTH
                      );
            $expectedArray                      = array();
            $expectedArray['queued']            = 0;
            $expectedArray['sent']              = 0;
            $expectedArray['uniqueClicks']      = 0;
            $expectedArray['uniqueOpens']       = 0;
            $expectedArray['bounced']           = 0;
            $expectedArray['optedOut']          = 0;
            $expectedArray['displayLabel']      = $displayLabel;
            $expectedArray['dateBalloonLabel']  = $displayLabel;
            $this->assertChartDataColumnAsExpected($expectedArray, $chartData[0]);

            $displayLabel = DateTimeUtil
                    ::resolveValueForDateLocaleFormattedDisplay(
                            $endDate,
                            DateTimeUtil::DISPLAY_FORMAT_ABBREVIATED_MONTH_AND_DAY_WIDTH
                      );
            $expectedArray['displayLabel']      = $displayLabel;
            $expectedArray['dateBalloonLabel']  = $displayLabel;
            $this->assertChartDataColumnAsExpected($expectedArray, $chartData[1]);

            //Test when sentDate < beginDate < endDate for day grouping
            $beginDate          = date('Y-m-d', strtotime($emailMessageSentDateTime) + 1 * 24 * 60 * 60);
            $endDate            = date('Y-m-d', strtotime($emailMessageSentDateTime) + 2 * 24 * 60 * 60);
            $chartDataProvider->setBeginDate($beginDate);
            $chartDataProvider->setEndDate  ($endDate);
            $chartDataProvider->setGroupBy(MarketingOverallMetricsForm::GROUPING_TYPE_DAY);
            $chartDataProvider->setCampaign($this->campaign);
            $chartData          = $chartDataProvider->getChartData();

            $this->assertCount (2,          $chartData);

            $displayLabel = DateTimeUtil
                    ::resolveValueForDateLocaleFormattedDisplay(
                            $beginDate,
                            DateTimeUtil::DISPLAY_FORMAT_ABBREVIATED_MONTH_AND_DAY_WIDTH
                      );
            $expectedArray                      = array();
            $expectedArray['queued']            = 0;
            $expectedArray['sent']              = 0;
            $expectedArray['uniqueClicks']      = 0;
            $expectedArray['uniqueOpens']       = 0;
            $expectedArray['bounced']           = 0;
            $expectedArray['optedOut']          = 0;
            $expectedArray['displayLabel']      = $displayLabel;
            $expectedArray['dateBalloonLabel']  = $displayLabel;
            $this->assertChartDataColumnAsExpected($expectedArray, $chartData[0]);

            $displayLabel = DateTimeUtil
                    ::resolveValueForDateLocaleFormattedDisplay(
                            $endDate,
                            DateTimeUtil::DISPLAY_FORMAT_ABBREVIATED_MONTH_AND_DAY_WIDTH
                      );
            $expectedArray['displayLabel']      = $displayLabel;
            $expectedArray['dateBalloonLabel']  = $displayLabel;
            $this->assertChartDataColumnAsExpected($expectedArray, $chartData[1]);

            //Test when sentDate = beginDate = endDate for day grouping
            $beginDate = $emailMessageSentDateTime;
            $endDate   = $emailMessageSentDateTime;
            $chartDataProvider  = new MarketingEmailsInThisListChartDataProvider();
            $chartDataProvider->setBeginDate($beginDate);
            $chartDataProvider->setEndDate  ($endDate);
            $chartDataProvider->setGroupBy(MarketingOverallMetricsForm::GROUPING_TYPE_DAY);
            $chartDataProvider->setCampaign($this->campaign);
            $chartData          = $chartDataProvider->getChartData();

            $this->assertCount (1,          $chartData);

            $displayLabel = DateTimeUtil
                    ::resolveValueForDateLocaleFormattedDisplay(
                            $emailMessageSentDateTime,
                            DateTimeUtil::DISPLAY_FORMAT_ABBREVIATED_MONTH_AND_DAY_WIDTH
                      );
            $expectedArray                      = array();
            $expectedArray['queued']            = 0;
            $expectedArray['sent']              = $sent;
            $expectedArray['displayLabel']      = $displayLabel;
            $expectedArray['dateBalloonLabel']  = $displayLabel;
            $this->resolveExpectedDataFromCreationArray($campaingItemActivityCreationArray, $expectedArray);
            $this->assertChartDataColumnAsExpected($expectedArray, $chartData[0]);
        }

        private function resolveExpectedDataFromCreationArray($creationArray, & $expectedArray, $multiplier = 1)
        {
            $sent               = $expectedArray['sent'];
            $uniqueClicks       = ($creationArray[CampaignItemActivity::TYPE_CLICK] >= 1 && $sent ) ? 1 : 0;
            $uniqueOpens        = ($creationArray[CampaignItemActivity::TYPE_OPEN] >= 1 && $sent ) ? 1 : 0;
            $bounced            = ($creationArray[CampaignItemActivity::TYPE_BOUNCE] >= 1 && $sent ) ? 1 : 0;
            $optedOut           = ($creationArray[CampaignItemActivity::TYPE_UNSUBSCRIBE] >= 1 && $sent ) ? 1 : 0;

            $expectedArray['uniqueClicks']  = $uniqueClicks * $multiplier;
            $expectedArray['uniqueOpens']   = $uniqueOpens * $multiplier;
            $expectedArray['bounced']       = $bounced * $multiplier;
            $expectedArray['optedOut']      = $optedOut * $multiplier;
        }

        private function assertChartDataColumnAsExpected(Array $expectedArray, $chartDataColumn)
        {
            $queued             = $expectedArray['queued'];
            $sent               = $expectedArray['sent'];
            $uniqueClicks       = $expectedArray['uniqueClicks'];
            $uniqueOpens        = $expectedArray['uniqueOpens'];
            $bounced            = $expectedArray['bounced'];
            $optedOut           = $expectedArray['optedOut'];
            $displayLabel       = $expectedArray['displayLabel'];
            $dateBalloonLabel   = $expectedArray['dateBalloonLabel'];

            $this->assertEquals($queued,
                                $chartDataColumn[MarketingChartDataProvider::QUEUED]);
            $this->assertEquals($sent,
                                $chartDataColumn[MarketingChartDataProvider::SENT]);
            $this->assertEquals($uniqueClicks,
                                $chartDataColumn[MarketingChartDataProvider::UNIQUE_CLICKS]);
            $this->assertEquals($uniqueOpens,
                                $chartDataColumn[MarketingChartDataProvider::UNIQUE_OPENS]);
            $this->assertEquals($bounced,
                                $chartDataColumn[MarketingChartDataProvider::BOUNCED]);
            $this->assertEquals($optedOut,
                                $chartDataColumn[MarketingChartDataProvider::UNSUBSCRIBED]);
            $this->assertEquals($displayLabel,
                                $chartDataColumn['displayLabel']);
            $this->assertEquals($dateBalloonLabel,
                                $chartDataColumn['dateBalloonLabel']);
        }

        private function addCampaingItem($contact, $emailMessageSentDateTime, $creationArray)
        {
            $emailBox                                = $this->emailBox;

            $emailMessage                            = new EmailMessage();
            $emailMessage->setScenario('importModel');
            $emailContent                            = new EmailMessageContent();
            $emailContent->textContent               = 'My First Message';
            $emailContent->htmlContent               = 'Some fake HTML content';

            $sender                                  = new EmailMessageSender();
            $sender->fromAddress                     = 'super@zurmotest.com';
            $sender->fromName                        = 'Super User';
            $sender->personOrAccount                 = Yii::app()->user->userModel;

            $recipient                               = new EmailMessageRecipient();
            $recipient->toAddress                    = 'test.to@zurmotest.com';
            $recipient->toName                       = strval($contact);
            $recipient->personOrAccount              = $contact;
            $recipient->type                         = EmailMessageRecipient::TYPE_TO;

            $emailMessage->owner                     = Yii::app()->user->userModel;
            $emailMessage->subject                   = 'A test archived sent email';
            $emailMessage->content                   = $emailContent;
            $emailMessage->sender                    = $sender;
            if (isset($emailMessageSentDateTime))
            {
                $emailMessage->sentDateTime              = DateTimeUtil
                        ::convertTimestampToDbFormatDateTime(strtotime($emailMessageSentDateTime));
                $emailMessage->createdDateTime           = $emailMessage->sentDateTime;
            }
            $emailMessage->folder                    =
                    EmailFolder::getByBoxAndType($emailBox, EmailFolder::TYPE_ARCHIVED);
            $emailMessage->recipients->add($recipient);

            $campaignItem                            = new CampaignItem();
            $campaignItem->contact                   = $contact;
            $campaignItem->processed                 = true;
            $campaignItem->emailMessage              = $emailMessage;
            $this->resolveCampaignItemActivities($contact, $creationArray, $campaignItem);
            $this->campaign->campaignItems->add($campaignItem);
            $this->assertTrue($this->campaign->save());
        }

        private function resolveCampaignItemActivities($contact, $creationArray, CampaignItem  & $campaignItem)
        {
            foreach ($creationArray as $type => $numberOfType)
            {
                for ($i = 1; $i <= $numberOfType; $i++)
                {
                    $activity             = new CampaignItemActivity();
                    $activity->person     = $contact;
                    $activity->type       = $type;
                    $activity->quantity   = 1;
                    $campaignItem->campaignItemActivities->add($activity);
                }
            }
        }

        public function dataForTestGetChartDataForCampaigns()
        {
            $data = array(
                array(array(CampaignItemActivity::TYPE_CLICK       => 1,
                            CampaignItemActivity::TYPE_BOUNCE      => 1,
                            CampaignItemActivity::TYPE_OPEN        => 1,
                            CampaignItemActivity::TYPE_SKIP        => 1,
                            CampaignItemActivity::TYPE_UNSUBSCRIBE => 1),
                      '2013-06-09'),
                array(array(CampaignItemActivity::TYPE_CLICK       => 1,
                            CampaignItemActivity::TYPE_BOUNCE      => 1,
                            CampaignItemActivity::TYPE_OPEN        => 1,
                            CampaignItemActivity::TYPE_SKIP        => 1,
                            CampaignItemActivity::TYPE_UNSUBSCRIBE => 1),
                      '2012-02-29'),
                array(array(CampaignItemActivity::TYPE_CLICK       => 1,
                            CampaignItemActivity::TYPE_BOUNCE      => 2,
                            CampaignItemActivity::TYPE_OPEN        => 3,
                            CampaignItemActivity::TYPE_SKIP        => 4,
                            CampaignItemActivity::TYPE_UNSUBSCRIBE => 5),
                      '2013-04-01'),
                array(array(CampaignItemActivity::TYPE_CLICK       => 5,
                            CampaignItemActivity::TYPE_BOUNCE      => 4,
                            CampaignItemActivity::TYPE_OPEN        => 3,
                            CampaignItemActivity::TYPE_SKIP        => 2,
                            CampaignItemActivity::TYPE_UNSUBSCRIBE => 1),
                      '2013-06-12'),
                array(array(CampaignItemActivity::TYPE_CLICK       => 1,
                            CampaignItemActivity::TYPE_BOUNCE      => 0,
                            CampaignItemActivity::TYPE_OPEN        => 0,
                            CampaignItemActivity::TYPE_SKIP        => 0,
                            CampaignItemActivity::TYPE_UNSUBSCRIBE => 0),
                      '2013-06-12'),
                array(array(CampaignItemActivity::TYPE_CLICK       => 0,
                            CampaignItemActivity::TYPE_BOUNCE      => 1,
                            CampaignItemActivity::TYPE_OPEN        => 0,
                            CampaignItemActivity::TYPE_SKIP        => 0,
                            CampaignItemActivity::TYPE_UNSUBSCRIBE => 0),
                      '2013-06-12'),
                array(array(CampaignItemActivity::TYPE_CLICK       => 0,
                            CampaignItemActivity::TYPE_BOUNCE      => 0,
                            CampaignItemActivity::TYPE_OPEN        => 1,
                            CampaignItemActivity::TYPE_SKIP        => 0,
                            CampaignItemActivity::TYPE_UNSUBSCRIBE => 0),
                      '2013-06-12'),
                array(array(CampaignItemActivity::TYPE_CLICK       => 0,
                            CampaignItemActivity::TYPE_BOUNCE      => 0,
                            CampaignItemActivity::TYPE_OPEN        => 0,
                            CampaignItemActivity::TYPE_SKIP        => 1,
                            CampaignItemActivity::TYPE_UNSUBSCRIBE => 0),
                      '2013-06-12'),
                array(array(CampaignItemActivity::TYPE_CLICK       => 0,
                            CampaignItemActivity::TYPE_BOUNCE      => 0,
                            CampaignItemActivity::TYPE_OPEN        => 0,
                            CampaignItemActivity::TYPE_SKIP        => 0,
                            CampaignItemActivity::TYPE_UNSUBSCRIBE => 1),
                      '2013-06-12'),
                array(array(CampaignItemActivity::TYPE_CLICK       => 0,
                            CampaignItemActivity::TYPE_BOUNCE      => 0,
                            CampaignItemActivity::TYPE_OPEN        => 0,
                            CampaignItemActivity::TYPE_SKIP        => 0,
                            CampaignItemActivity::TYPE_UNSUBSCRIBE => 0),
                      '2013-06-12'),
                array(array(CampaignItemActivity::TYPE_CLICK       => 1,
                            CampaignItemActivity::TYPE_BOUNCE      => 1,
                            CampaignItemActivity::TYPE_OPEN        => 1,
                            CampaignItemActivity::TYPE_SKIP        => 1,
                            CampaignItemActivity::TYPE_UNSUBSCRIBE => 1),
                      null),
            );
            return $data;
        }
    }
?>