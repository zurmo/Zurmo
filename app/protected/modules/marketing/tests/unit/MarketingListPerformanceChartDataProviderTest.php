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
    class MarketingListPerformanceChartDataProviderTest extends ZurmoBaseTest
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
                    CampaignTestHelper::createCampaign('Test Campaing 01', 'text', 'text');
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
            parent::teardown();
        }

        public function testGetChartData()
        {
            $contact                = ContactTestHelper
                    ::createContactByNameForOwner('contact01', Yii::app()->user->userModel);
            $this->addCampaignItem(
                    $contact,
                    '2013-04-01',
                    array(
                        CampaignItemActivity::TYPE_CLICK       => 1,
                        CampaignItemActivity::TYPE_BOUNCE      => 0,
                        CampaignItemActivity::TYPE_OPEN        => 1,
                        CampaignItemActivity::TYPE_SKIP        => 0,
                        CampaignItemActivity::TYPE_UNSUBSCRIBE => 0
                    ));
            $campaignChartDataProvider                  = new MarketingListPerformanceChartDataProvider();
            $campaignChartDataProvider->setBeginDate         ('2013-04-01');
            $campaignChartDataProvider->setEndDate           ('2013-04-25');
            $campaignChartDataProvider->setGroupBy           (MarketingOverallMetricsForm::GROUPING_TYPE_DAY);
            $campaignChartDataProvider->setCampaign          ($this->campaign);

            $marketingListChartDataProvider             = new MarketingListPerformanceChartDataProvider();
            $marketingListChartDataProvider->setBeginDate    ('2013-04-01');
            $marketingListChartDataProvider->setEndDate      ('2013-04-25');
            $marketingListChartDataProvider->setGroupBy      (MarketingOverallMetricsForm::GROUPING_TYPE_DAY);
            $marketingListChartDataProvider->setMarketingList($this->marketingList);

            $combinedChartDataProvider                  = new MarketingListPerformanceChartDataProvider();
            $combinedChartDataProvider->setBeginDate         ('2013-04-01');
            $combinedChartDataProvider->setEndDate           ('2013-04-25');
            $combinedChartDataProvider->setGroupBy           (MarketingOverallMetricsForm::GROUPING_TYPE_DAY);

            $campaignChartData                          = $campaignChartDataProvider->getChartData();
            $expectedArray                              = array();
            $expectedArray['uniqueClickThroughRate']    = 100;
            $expectedArray['uniqueOpenRate']            = 100;
            $expectedArray['displayLabel']              = 'Apr 1';
            $expectedArray['dateBalloonLabel']          = 'Apr 1';
            $this->assertEquals($expectedArray, $campaignChartData[0]);
            $marketingListChartData                     = $marketingListChartDataProvider->getChartData();
            $expectedArray                              = array();
            $expectedArray['uniqueClickThroughRate']    = 0;
            $expectedArray['uniqueOpenRate']            = 0;
            $expectedArray['displayLabel']              = 'Apr 1';
            $expectedArray['dateBalloonLabel']          = 'Apr 1';
            $this->assertEquals($expectedArray, $marketingListChartData[0]);
            $combinedChartData                          = $combinedChartDataProvider->getChartData();
            $expectedArray                              = array();
            $expectedArray['uniqueClickThroughRate']    = 100;
            $expectedArray['uniqueOpenRate']            = 100;
            $expectedArray['displayLabel']              = 'Apr 1';
            $expectedArray['dateBalloonLabel']          = 'Apr 1';
            $this->assertEquals($expectedArray, $combinedChartData[0]);

            $this->addAutoresponderItem(
                    $contact,
                    '2013-04-01',
                    array(
                        CampaignItemActivity::TYPE_CLICK       => 1,
                        CampaignItemActivity::TYPE_BOUNCE      => 0,
                        CampaignItemActivity::TYPE_OPEN        => 0,
                        CampaignItemActivity::TYPE_SKIP        => 0,
                        CampaignItemActivity::TYPE_UNSUBSCRIBE => 0
                    ));
            $campaignChartData                          = $campaignChartDataProvider->getChartData();
            $expectedArray                              = array();
            $expectedArray['uniqueClickThroughRate']    = 100;
            $expectedArray['uniqueOpenRate']            = 100;
            $expectedArray['displayLabel']              = 'Apr 1';
            $expectedArray['dateBalloonLabel']          = 'Apr 1';
            $this->assertEquals($expectedArray, $campaignChartData[0]);
            $marketingListChartData                     = $marketingListChartDataProvider->getChartData();
            $expectedArray                              = array();
            $expectedArray['uniqueClickThroughRate']    = 100.0;
            $expectedArray['uniqueOpenRate']            = 0.0;
            $expectedArray['displayLabel']              = 'Apr 1';
            $expectedArray['dateBalloonLabel']          = 'Apr 1';
            $this->assertEquals($expectedArray, $marketingListChartData[0]);
            $combinedChartData                          = $combinedChartDataProvider->getChartData();
            $expectedArray                              = array();
            $expectedArray['uniqueClickThroughRate']    = 100.0;
            $expectedArray['uniqueOpenRate']            = 50.0;
            $expectedArray['displayLabel']              = 'Apr 1';
            $expectedArray['dateBalloonLabel']          = 'Apr 1';
            $this->assertEquals($expectedArray, $combinedChartData[0]);

            $contact                = ContactTestHelper
                    ::createContactByNameForOwner('contact02', Yii::app()->user->userModel);
            $this->addCampaignItem(
                    $contact,
                    '2013-04-25',
                    array(
                        CampaignItemActivity::TYPE_CLICK       => 0,
                        CampaignItemActivity::TYPE_BOUNCE      => 0,
                        CampaignItemActivity::TYPE_OPEN        => 0,
                        CampaignItemActivity::TYPE_SKIP        => 0,
                        CampaignItemActivity::TYPE_UNSUBSCRIBE => 0
                    ));
            $campaignChartData                          = $campaignChartDataProvider->getChartData();
            $expectedArray                              = array();
            $expectedArray['uniqueClickThroughRate']    = 0;
            $expectedArray['uniqueOpenRate']            = 0;
            $expectedArray['displayLabel']              = 'Apr 25';
            $expectedArray['dateBalloonLabel']          = 'Apr 25';
            $this->assertEquals($expectedArray, $campaignChartData[24]);
            $marketingListChartData                     = $marketingListChartDataProvider->getChartData();
            $expectedArray                              = array();
            $expectedArray['uniqueClickThroughRate']    = 0;
            $expectedArray['uniqueOpenRate']            = 0;
            $expectedArray['displayLabel']              = 'Apr 25';
            $expectedArray['dateBalloonLabel']          = 'Apr 25';
            $this->assertEquals($expectedArray, $marketingListChartData[24]);
            $combinedChartData                          = $combinedChartDataProvider->getChartData();
            $expectedArray                              = array();
            $expectedArray['uniqueClickThroughRate']    = 0;
            $expectedArray['uniqueOpenRate']            = 0;
            $expectedArray['displayLabel']              = 'Apr 25';
            $expectedArray['dateBalloonLabel']          = 'Apr 25';
            $this->assertEquals($expectedArray, $combinedChartData[24]);

            $campaignChartDataProvider->setGroupBy           (MarketingOverallMetricsForm::GROUPING_TYPE_WEEK);
            $marketingListChartDataProvider->setGroupBy      (MarketingOverallMetricsForm::GROUPING_TYPE_WEEK);
            $combinedChartDataProvider->setGroupBy           (MarketingOverallMetricsForm::GROUPING_TYPE_WEEK);
            $campaignChartData                          = $campaignChartDataProvider->getChartData();
            $expectedArray                              = array();
            $expectedArray['uniqueClickThroughRate']    = 100;
            $expectedArray['uniqueOpenRate']            = 100;
            $expectedArray['displayLabel']              = 'Apr 1';
            $expectedArray['dateBalloonLabel']          = 'Week of Apr 1';
            $this->assertEquals($expectedArray, $campaignChartData[0]);
            $expectedArray                              = array();
            $expectedArray['uniqueClickThroughRate']    = 0;
            $expectedArray['uniqueOpenRate']            = 0;
            $expectedArray['displayLabel']              = 'Apr 22';
            $expectedArray['dateBalloonLabel']          = 'Week of Apr 22';
            $this->assertEquals($expectedArray, $campaignChartData[3]);
            $marketingListChartData                     = $marketingListChartDataProvider->getChartData();
            $expectedArray                              = array();
            $expectedArray['uniqueClickThroughRate']    = 100;
            $expectedArray['uniqueOpenRate']            = 0;
            $expectedArray['displayLabel']              = 'Apr 1';
            $expectedArray['dateBalloonLabel']          = 'Week of Apr 1';
            $this->assertEquals($expectedArray, $marketingListChartData[0]);
            $expectedArray                              = array();
            $expectedArray['uniqueClickThroughRate']    = 0;
            $expectedArray['uniqueOpenRate']            = 0;
            $expectedArray['displayLabel']              = 'Apr 22';
            $expectedArray['dateBalloonLabel']          = 'Week of Apr 22';
            $this->assertEquals($expectedArray, $marketingListChartData[3]);
            $combinedChartData                          = $combinedChartDataProvider->getChartData();
            $expectedArray                              = array();
            $expectedArray['uniqueClickThroughRate']    = 100;
            $expectedArray['uniqueOpenRate']            = 50;
            $expectedArray['displayLabel']              = 'Apr 1';
            $expectedArray['dateBalloonLabel']          = 'Week of Apr 1';
            $this->assertEquals($expectedArray, $combinedChartData[0]);
            $expectedArray                              = array();
            $expectedArray['uniqueClickThroughRate']    = 0;
            $expectedArray['uniqueOpenRate']            = 0;
            $expectedArray['displayLabel']              = 'Apr 22';
            $expectedArray['dateBalloonLabel']          = 'Week of Apr 22';
            $this->assertEquals($expectedArray, $combinedChartData[3]);

            $campaignChartDataProvider->setGroupBy           (MarketingOverallMetricsForm::GROUPING_TYPE_MONTH);
            $marketingListChartDataProvider->setGroupBy      (MarketingOverallMetricsForm::GROUPING_TYPE_MONTH);
            $combinedChartDataProvider->setGroupBy           (MarketingOverallMetricsForm::GROUPING_TYPE_MONTH);
            $campaignChartData                          = $campaignChartDataProvider->getChartData();
            $expectedArray                              = array();
            $expectedArray['uniqueClickThroughRate']    = 50;
            $expectedArray['uniqueOpenRate']            = 50;
            $expectedArray['displayLabel']              = 'Apr';
            $expectedArray['dateBalloonLabel']          = 'Apr';
            $this->assertEquals($expectedArray, $campaignChartData[0]);
            $marketingListChartData                     = $marketingListChartDataProvider->getChartData();
            $expectedArray                              = array();
            $expectedArray['uniqueClickThroughRate']    = 100;
            $expectedArray['uniqueOpenRate']            = 0;
            $expectedArray['displayLabel']              = 'Apr';
            $expectedArray['dateBalloonLabel']          = 'Apr';
            $this->assertEquals($expectedArray, $marketingListChartData[0]);
            $combinedChartData                          = $combinedChartDataProvider->getChartData();
            $expectedArray                              = array();
            $expectedArray['uniqueClickThroughRate']    = 66.67;
            $expectedArray['uniqueOpenRate']            = 33.33;
            $expectedArray['displayLabel']              = 'Apr';
            $expectedArray['dateBalloonLabel']          = 'Apr';
            $this->assertEquals($expectedArray, $combinedChartData[0]);
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
    }
?>