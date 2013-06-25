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
        private $marketingList;
        private $campaign;
        private $autoresponder;
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
            $emailBoxes                 = EmailBox::getAll();
            $this->emailBox             = $emailBoxes[0];
            $this->marketingList        =
                    MarketingListTestHelper::createMarketingListByName('Test Marketing List');
            $this->campaign             =
                    CampaignTestHelper::createCampaign('Test Campaing 01', 'text', 'text exemple');
            $this->autoresponder        =
                    AutoresponderTestHelper::createAutoresponder(
                            'Test Autoresponder 01',
                            'text',
                            'html',
                            60,
                            Autoresponder::OPERATION_SUBSCRIBE,
                            true);
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
         * @dataProvider dataForTestGetChartData
         */
        public function testGetChartData($activityCreationArray, $emailMessageSentDateTime, $isMultiplierOn)
        {
            $contacts = Contact::getAll();
            $this->addCampaignItem     ($contacts[0], $emailMessageSentDateTime, $activityCreationArray);
            $this->addAutoresponderItem($contacts[0], $emailMessageSentDateTime, $activityCreationArray);
            if ($isMultiplierOn)
            {
                $this->addCampaignItem     ($contacts[1], $emailMessageSentDateTime, $activityCreationArray);
                $this->addAutoresponderItem($contacts[1], $emailMessageSentDateTime, $activityCreationArray);
            }

            $isSent = true;
            if (!isset($emailMessageSentDateTime))
            {
                $emailMessageSentDateTime = date('Y-m-d');
                $isSent = false;
            }
            //Test when beginDate < sentDate < endDate
            //Grouping by day
            $this->assertChartDataAsExpectedBySentTimeAndConditionAndGroupingBy(
                                    $activityCreationArray,
                                    $emailMessageSentDateTime,
                                    'between',
                                    MarketingOverallMetricsForm::GROUPING_TYPE_DAY,
                                    $isSent,
                                    $isMultiplierOn);
            //Grouping by week
            $this->assertChartDataAsExpectedBySentTimeAndConditionAndGroupingBy(
                                    $activityCreationArray,
                                    $emailMessageSentDateTime,
                                    'between',
                                    MarketingOverallMetricsForm::GROUPING_TYPE_WEEK,
                                    $isSent,
                                    $isMultiplierOn);
            //Grouping by month
            $this->assertChartDataAsExpectedBySentTimeAndConditionAndGroupingBy(
                                    $activityCreationArray,
                                    $emailMessageSentDateTime,
                                    'between',
                                    MarketingOverallMetricsForm::GROUPING_TYPE_MONTH,
                                    $isSent,
                                    $isMultiplierOn);

            //Test when beginDate < endDate < sentDate
            //Grouping by day
            $this->assertChartDataAsExpectedBySentTimeAndConditionAndGroupingBy(
                                    $activityCreationArray,
                                    $emailMessageSentDateTime,
                                    'after',
                                    MarketingOverallMetricsForm::GROUPING_TYPE_DAY,
                                    $isSent,
                                    $isMultiplierOn);
            //Grouping by week
            $this->assertChartDataAsExpectedBySentTimeAndConditionAndGroupingBy(
                                    $activityCreationArray,
                                    $emailMessageSentDateTime,
                                    'after',
                                    MarketingOverallMetricsForm::GROUPING_TYPE_WEEK,
                                    $isSent,
                                    $isMultiplierOn);
            //Grouping by month
            $this->assertChartDataAsExpectedBySentTimeAndConditionAndGroupingBy(
                                    $activityCreationArray,
                                    $emailMessageSentDateTime,
                                    'after',
                                    MarketingOverallMetricsForm::GROUPING_TYPE_MONTH,
                                    $isSent,
                                    $isMultiplierOn);

            //Test when sentDate < beginDate < endDate
            //Grouping by day
            $this->assertChartDataAsExpectedBySentTimeAndConditionAndGroupingBy(
                                    $activityCreationArray,
                                    $emailMessageSentDateTime,
                                    'before',
                                    MarketingOverallMetricsForm::GROUPING_TYPE_DAY,
                                    $isSent,
                                    $isMultiplierOn);
            //Grouping by week
            $this->assertChartDataAsExpectedBySentTimeAndConditionAndGroupingBy(
                                    $activityCreationArray,
                                    $emailMessageSentDateTime,
                                    'before',
                                    MarketingOverallMetricsForm::GROUPING_TYPE_WEEK,
                                    $isSent,
                                    $isMultiplierOn);
            //Grouping by month
            $this->assertChartDataAsExpectedBySentTimeAndConditionAndGroupingBy(
                                    $activityCreationArray,
                                    $emailMessageSentDateTime,
                                    'before',
                                    MarketingOverallMetricsForm::GROUPING_TYPE_MONTH,
                                    $isSent,
                                    $isMultiplierOn);

            //Test when sentDate = beginDate = endDate
            $this->assertChartDataAsExpectedBySentTimeAndConditionAndGroupingBy(
                                    $activityCreationArray,
                                    $emailMessageSentDateTime,
                                    'equals',
                                    MarketingOverallMetricsForm::GROUPING_TYPE_DAY,
                                    $isSent,
                                    $isMultiplierOn);
            //Grouping by week
            $this->assertChartDataAsExpectedBySentTimeAndConditionAndGroupingBy(
                                    $activityCreationArray,
                                    $emailMessageSentDateTime,
                                    'equals',
                                    MarketingOverallMetricsForm::GROUPING_TYPE_WEEK,
                                    $isSent,
                                    $isMultiplierOn);
            //Grouping by month
            $this->assertChartDataAsExpectedBySentTimeAndConditionAndGroupingBy(
                                    $activityCreationArray,
                                    $emailMessageSentDateTime,
                                    'equals',
                                    MarketingOverallMetricsForm::GROUPING_TYPE_MONTH,
                                    $isSent,
                                    $isMultiplierOn);
        }

        private function assertChartDataAsExpectedBySentTimeAndConditionAndGroupingBy(
                $campaingItemActivityCreationArray,
                $emailMessageSentDateTime,
                $condition,
                $groupingBy,
                $isSent,
                $isMultiplierOn)
        {
            $count = null;
            switch ($condition)
            {
                case 'equals':
                    $count = 1;
                    break;
                case 'between':
                    $count = 3;
                    break;
                default:
                    $count = 2;
                    break;
            }
            $beginDate                  = $this->getBeginDateByConditionAndGroupingBy(
                    $emailMessageSentDateTime,
                    $condition,
                    $groupingBy);
            $endDate                    = $this->getEndDateByConditionAndGroupingBy(
                    $emailMessageSentDateTime,
                    $condition,
                    $groupingBy);
            $chartDataProvider          = new MarketingEmailsInThisListChartDataProvider();
            $chartDataProvider->setBeginDate($beginDate);
            $chartDataProvider->setEndDate  ($endDate);
            $chartDataProvider->setGroupBy  ($groupingBy);

            $chartDateProviderForMarketingList = clone $chartDataProvider;

            $combinedChartData          = $chartDataProvider->getChartData();

            $chartDataProvider->setCampaign($this->campaign);
            $campaignChartData          = $chartDataProvider->getChartData();

            $chartDateProviderForMarketingList->setMarketingList($this->marketingList);
            $marketingListChartData     = $chartDateProviderForMarketingList->getChartData();

            $this->assertCount ($count,          $combinedChartData);
            $this->assertCount ($count,          $campaignChartData);
            $this->assertCount ($count,          $marketingListChartData);

            if ($condition != 'equals')
            {
                $displayLabel = $this->getDisplayLabel($beginDate, $groupingBy);
                $expectedArray                      = array();
                $expectedArray['queued']            = 0;
                $expectedArray['sent']              = 0;
                $expectedArray['uniqueClicks']      = 0;
                $expectedArray['uniqueOpens']       = 0;
                $expectedArray['bounced']           = 0;
                $expectedArray['optedOut']          = 0;
                $expectedArray['displayLabel']      = $displayLabel;
                if ($groupingBy == 'Week')
                {
                    $expectedArray['dateBalloonLabel']  = 'Week of ' . $displayLabel;
                }
                else
                {
                    $expectedArray['dateBalloonLabel']  = $displayLabel;
                }
                $this->assertChartDataColumnAsExpected($expectedArray, $campaignChartData[0]);
                $this->assertChartDataColumnAsExpected($expectedArray, $marketingListChartData[0]);
            }

            if ($condition == 'equals')
            {
                $campaignColumn      = $campaignChartData[0];
                $marketingListColumn = $marketingListChartData[0];
            }
            elseif ($condition == 'between')
            {
                $campaignColumn      = $campaignChartData[1];
                $marketingListColumn = $marketingListChartData[1];
            }
            if (isset($campaignColumn))
            {
                $displayLabel = $this->getDisplayLabel($emailMessageSentDateTime, $groupingBy);
                $expectedArray                      = array();
                $expectedArray['displayLabel']      = $displayLabel;
                if ($groupingBy == 'Week')
                {
                    $expectedArray['dateBalloonLabel']  = 'Week of ' . $displayLabel;
                }
                else
                {
                    $expectedArray['dateBalloonLabel']  = $displayLabel;
                }
                $this->resolveExpectedDataFromIsSent(
                        $isSent,
                        $expectedArray,
                        $isMultiplierOn ? 2 : 1);
                $this->resolveExpectedDataFromCreationArray(
                        $campaingItemActivityCreationArray,
                        $expectedArray,
                        $isMultiplierOn ? 2 : 1);
                $this->assertChartDataColumnAsExpected($expectedArray, $campaignColumn);
                $this->assertChartDataColumnAsExpected($expectedArray, $marketingListColumn);
            }
            else
            {
                $displayLabel = $this->getDisplayLabel($endDate, $groupingBy);
                $expectedArray                      = array();
                $expectedArray['queued']            = 0;
                $expectedArray['sent']              = 0;
                $expectedArray['uniqueClicks']      = 0;
                $expectedArray['uniqueOpens']       = 0;
                $expectedArray['bounced']           = 0;
                $expectedArray['optedOut']          = 0;
                $expectedArray['displayLabel']      = $displayLabel;
                if ($groupingBy == 'Week')
                {
                    $expectedArray['dateBalloonLabel']  = 'Week of ' . $displayLabel;
                }
                else
                {
                    $expectedArray['dateBalloonLabel']  = $displayLabel;
                }
                $this->assertChartDataColumnAsExpected($expectedArray, $campaignChartData[1]);
                $this->assertChartDataColumnAsExpected($expectedArray, $marketingListChartData[1]);
            }
            $this->assertCombinedChartDataAsExpected($combinedChartData, $campaignChartData, $marketingListChartData);
        }

        private function getBeginDateByConditionAndGroupingBy($emailMessageSentDateTime, $condition, $groupingBy)
        {
            $add = null;
            switch ($condition)
            {
                case 'after':
                    $add = '-2';
                    break;
                case 'before':
                    $add = '+1'; // Not Coding Standard
                    break;
                case 'between':
                    $add = '-1';
                    break;
                default:
                    $add = '+0'; // Not Coding Standard
                    break;
            }
            $date       = new DateTime($emailMessageSentDateTime);
            $date->modify($add . $groupingBy);
            $beginDate  = $date->format('Y-m-d');
            return $beginDate;
        }

        private function getEndDateByConditionAndGroupingBy($emailMessageSentDateTime, $condition, $groupingBy)
        {
            $add = null;
            switch ($condition)
            {
                case 'after':
                    $add = '-1';
                    break;
                case 'before':
                    $add = '+2'; // Not Coding Standard
                    break;
                case 'between':
                    $add = '+1'; // Not Coding Standard
                    break;
                default:
                    $add = '+0'; // Not Coding Standard
                    break;
            }
            $date       = new DateTime($emailMessageSentDateTime);
            $date->modify($add . $groupingBy);
            $endDate    = $date->format('Y-m-d');
            return $endDate;
        }

        private function resolveExpectedDataFromCreationArray($creationArray, & $expectedArray, $multiplier = 1)
        {
            $uniqueClicks       = $creationArray[CampaignItemActivity::TYPE_CLICK] >= 1 ? 1 : 0;
            $uniqueOpens        = $creationArray[CampaignItemActivity::TYPE_OPEN] >= 1 ? 1 : 0;
            $bounced            = $creationArray[CampaignItemActivity::TYPE_BOUNCE] >= 1 ? 1 : 0;
            $optedOut           = $creationArray[CampaignItemActivity::TYPE_UNSUBSCRIBE] >= 1 ? 1 : 0;

            $expectedArray['uniqueClicks']  = $uniqueClicks * $multiplier;
            $expectedArray['uniqueOpens']   = $uniqueOpens * $multiplier;
            $expectedArray['bounced']       = $bounced * $multiplier;
            $expectedArray['optedOut']      = $optedOut * $multiplier;
        }

        private function resolveExpectedDataFromIsSent($isSent, & $expectedArray, $multiplier)
        {
            if ($isSent)
            {
                $expectedArray['sent']      = $multiplier;
                $expectedArray['queued']    = 0;
            }
            else
            {
                $expectedArray['sent']      = 0;
                $expectedArray['queued']    = $multiplier;
            }
        }

        private function assertCombinedChartDataAsExpected($combinedChartData, $campaignChartData, $marketingListChartData)
        {
            foreach ($combinedChartData as $key => $chartColumn)
            {
                $this->assertEquals($combinedChartData[$key]['queued'],
                                    $campaignChartData[$key]['queued'] + $marketingListChartData[$key]['queued']);
                $this->assertEquals($combinedChartData[$key]['sent'],
                                    $campaignChartData[$key]['sent'] + $marketingListChartData[$key]['sent']);
                $this->assertEquals($combinedChartData[$key]['uniqueClicks'],
                                    $campaignChartData[$key]['uniqueClicks'] + $marketingListChartData[$key]['uniqueClicks']);
                $this->assertEquals($combinedChartData[$key]['bounced'],
                                    $campaignChartData[$key]['bounced'] + $marketingListChartData[$key]['bounced']);
                $this->assertEquals($combinedChartData[$key]['optedOut'],
                                    $campaignChartData[$key]['optedOut'] + $marketingListChartData[$key]['optedOut']);
            }
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

        private function addCampaignItem($contact, $emailMessageSentDateTime, $creationArray)
        {
            $emailMessage                            = $this->createEmailMessage($contact, $emailMessageSentDateTime);
            $campaignItem                            = new CampaignItem();
            $campaignItem->contact                   = $contact;
            $campaignItem->processed                 = true;
            $campaignItem->emailMessage              = $emailMessage;
            $this->resolveCampaignItemActivities($contact, $creationArray, $campaignItem);
            $this->campaign->campaignItems->add($campaignItem);
            $this->assertTrue($this->campaign->save());
        }

        private function addAutoresponderItem($contact, $emailMessageSentDateTime, $creationArray)
        {
            $emailMessage                            = $this->createEmailMessage($contact, $emailMessageSentDateTime);
            $autoresponderItem                       = new AutoresponderItem();
            $autoresponderItem->contact              = $contact;
            $autoresponderItem->processed            = true;
            $autoresponderItem->emailMessage         = $emailMessage;
            $autoresponderItem->processDateTime      = DateTimeUtil
                        ::convertTimestampToDbFormatDateTime(time());;
            $this->resolveAutoresponderItemActivities($contact, $creationArray, $autoresponderItem);
            $this->autoresponder->autoresponderItems->add($autoresponderItem);
            $this->autoresponder->validate();
            $this->assertTrue($this->autoresponder->save());
        }

        private function createEmailMessage($contact, $emailMessageSentDateTime)
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
            return $emailMessage;
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

        private function resolveAutoresponderItemActivities($contact, $creationArray, AutoresponderItem  & $autoresponderItem)
        {
            foreach ($creationArray as $type => $numberOfType)
            {
                for ($i = 1; $i <= $numberOfType; $i++)
                {
                    $activity             = new AutoresponderItemActivity();
                    $activity->person     = $contact;
                    $activity->type       = $type;
                    $activity->quantity   = 1;
                    $autoresponderItem->autoresponderItemActivities->add($activity);
                }
            }
        }

        private function getDisplayLabel($date, $groupingBy)
        {
            $dateForLabel = $date;
            $format       = DateTimeUtil::DISPLAY_FORMAT_ABBREVIATED_MONTH_AND_DAY_WIDTH;
            if ($groupingBy == MarketingOverallMetricsForm::GROUPING_TYPE_WEEK)
            {
                $date = new DateTime($date);
                $date->modify(('Sunday' == $date->format('l')) ? 'Monday last week' : 'Monday this week');
                $dateForLabel = $date->format('Y-m-d');
            }
            elseif ($groupingBy == MarketingOverallMetricsForm::GROUPING_TYPE_MONTH)
            {
                $format = DateTimeUtil::DISPLAY_FORMAT_ABBREVIATED_MONTH_ONLY_WIDTH;
            }
            $displayLabel = DateTimeUtil
                        ::resolveValueForDateLocaleFormattedDisplay(
                                $dateForLabel,
                                $format
                          );
            return $displayLabel;
        }

        public function dataForTestGetChartData()
        {
            $data = array(
                array(array(CampaignItemActivity::TYPE_CLICK       => 1,
                            CampaignItemActivity::TYPE_BOUNCE      => 1,
                            CampaignItemActivity::TYPE_OPEN        => 1,
                            CampaignItemActivity::TYPE_SKIP        => 1,
                            CampaignItemActivity::TYPE_UNSUBSCRIBE => 1),
                      '2013-06-09',
                      false),
                array(array(CampaignItemActivity::TYPE_CLICK       => 1,
                            CampaignItemActivity::TYPE_BOUNCE      => 1,
                            CampaignItemActivity::TYPE_OPEN        => 1,
                            CampaignItemActivity::TYPE_SKIP        => 1,
                            CampaignItemActivity::TYPE_UNSUBSCRIBE => 1),
                      '2013-06-09',
                      true),
                array(array(CampaignItemActivity::TYPE_CLICK       => 1,
                            CampaignItemActivity::TYPE_BOUNCE      => 1,
                            CampaignItemActivity::TYPE_OPEN        => 1,
                            CampaignItemActivity::TYPE_SKIP        => 1,
                            CampaignItemActivity::TYPE_UNSUBSCRIBE => 1),
                      '2012-02-29',
                      false),
                array(array(CampaignItemActivity::TYPE_CLICK       => 1,
                            CampaignItemActivity::TYPE_BOUNCE      => 2,
                            CampaignItemActivity::TYPE_OPEN        => 3,
                            CampaignItemActivity::TYPE_SKIP        => 4,
                            CampaignItemActivity::TYPE_UNSUBSCRIBE => 5),
                      '2013-04-01',
                      false),
                array(array(CampaignItemActivity::TYPE_CLICK       => 5,
                            CampaignItemActivity::TYPE_BOUNCE      => 4,
                            CampaignItemActivity::TYPE_OPEN        => 3,
                            CampaignItemActivity::TYPE_SKIP        => 2,
                            CampaignItemActivity::TYPE_UNSUBSCRIBE => 1),
                      '2013-06-12',
                      false),
                array(array(CampaignItemActivity::TYPE_CLICK       => 1,
                            CampaignItemActivity::TYPE_BOUNCE      => 0,
                            CampaignItemActivity::TYPE_OPEN        => 0,
                            CampaignItemActivity::TYPE_SKIP        => 0,
                            CampaignItemActivity::TYPE_UNSUBSCRIBE => 0),
                      '2013-06-12',
                      false),
                array(array(CampaignItemActivity::TYPE_CLICK       => 0,
                            CampaignItemActivity::TYPE_BOUNCE      => 1,
                            CampaignItemActivity::TYPE_OPEN        => 0,
                            CampaignItemActivity::TYPE_SKIP        => 0,
                            CampaignItemActivity::TYPE_UNSUBSCRIBE => 0),
                      '2013-06-12',
                      false),
                array(array(CampaignItemActivity::TYPE_CLICK       => 0,
                            CampaignItemActivity::TYPE_BOUNCE      => 0,
                            CampaignItemActivity::TYPE_OPEN        => 1,
                            CampaignItemActivity::TYPE_SKIP        => 0,
                            CampaignItemActivity::TYPE_UNSUBSCRIBE => 0),
                      '2013-06-12',
                      false),
                array(array(CampaignItemActivity::TYPE_CLICK       => 0,
                            CampaignItemActivity::TYPE_BOUNCE      => 0,
                            CampaignItemActivity::TYPE_OPEN        => 0,
                            CampaignItemActivity::TYPE_SKIP        => 1,
                            CampaignItemActivity::TYPE_UNSUBSCRIBE => 0),
                      '2013-06-12',
                      false),
                array(array(CampaignItemActivity::TYPE_CLICK       => 0,
                            CampaignItemActivity::TYPE_BOUNCE      => 0,
                            CampaignItemActivity::TYPE_OPEN        => 0,
                            CampaignItemActivity::TYPE_SKIP        => 0,
                            CampaignItemActivity::TYPE_UNSUBSCRIBE => 1),
                      '2013-06-12',
                      false),
                array(array(CampaignItemActivity::TYPE_CLICK       => 0,
                            CampaignItemActivity::TYPE_BOUNCE      => 0,
                            CampaignItemActivity::TYPE_OPEN        => 0,
                            CampaignItemActivity::TYPE_SKIP        => 0,
                            CampaignItemActivity::TYPE_UNSUBSCRIBE => 0),
                      '2013-06-12',
                      false),
                array(array(CampaignItemActivity::TYPE_CLICK       => 1,
                            CampaignItemActivity::TYPE_BOUNCE      => 1,
                            CampaignItemActivity::TYPE_OPEN        => 1,
                            CampaignItemActivity::TYPE_SKIP        => 1,
                            CampaignItemActivity::TYPE_UNSUBSCRIBE => 1),
                      null,
                      false),
                array(array(CampaignItemActivity::TYPE_CLICK       => 1,
                            CampaignItemActivity::TYPE_BOUNCE      => 1,
                            CampaignItemActivity::TYPE_OPEN        => 1,
                            CampaignItemActivity::TYPE_SKIP        => 1,
                            CampaignItemActivity::TYPE_UNSUBSCRIBE => 1),
                      null,
                      true),
            );
            return $data;
        }
    }
?>