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
    class CampaignItemSummaryListViewColumnAdapterTest extends ZurmoBaseTest
    {
        private $campaignItem;
        private $contact;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
            $super = User::getByUsername('super');
            $super->primaryEmail->emailAddress = 'super@zurmo.org';
            $saved = $super->save();
            if (!$saved)
            {
                throw new FailedToSaveModelException();
            }
            MarketingListTestHelper::createMarketingListByName('testMarketingList');
            $campaign                   = CampaignTestHelper::createCampaign('testCampaign', 'testSubject', 'testContent');
            $contact                    = ContactTestHelper::createContactByNameForOwner('test', $super);
            $emailMessage               = EmailMessageTestHelper::createArchivedUnmatchedSentMessage($super);
            $campaignItem               = new CampaignItem();
            $campaignItem->contact      = $contact;
            $campaignItem->processed    = true;
            $campaignItem->campaign     = $campaign;
            $campaignItem->emailMessage = $emailMessage;
            $campaignItem->unrestrictedSave();
            if (!$saved)
            {
                throw new FailedToSaveModelException();
            }
            ReadPermissionsOptimizationUtil::rebuild();
        }

        public function setUp()
        {
            parent::setUp();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $campaigns                  = Campaign::getAll();
            $contacts                   = Contact::getByName('test testson');
            $this->contact              = $contacts[0];
            $this->campaignItem         = $campaigns[0]->campaignItems[0];
        }

        public function testResolveContactAndMetricsSummary()
        {
            //Test with super
            $content    = CampaignItemSummaryListViewColumnAdapter::
                            resolveContactAndMetricsSummary($this->campaignItem);
            $this->assertContains('test testson', $content);

            //Betty dont have access to contact
            $betty      = User::getByUsername('betty');
            Yii::app()->user->userModel = $betty;
            $content    = CampaignItemSummaryListViewColumnAdapter::
                            resolveContactAndMetricsSummary($this->campaignItem);
            $this->assertContains('You cannot see this contact due to limited access', $content);

            //Giving betty access to contact
            Yii::app()->user->userModel = User::getByUsername('super');
            $this->contact->addPermissions($betty, Permission::READ_WRITE_CHANGE_PERMISSIONS_CHANGE_OWNER);
            $this->assertTrue($this->contact->save());

            //Betty has now access to contact but not the emailMessage
            Yii::app()->user->userModel = $betty;
            $content = CampaignItemSummaryListViewColumnAdapter::
                            resolveContactAndMetricsSummary($this->campaignItem);
            $this->assertContains('You cannot see the performance metrics due to limited access', $content);

            //Giving betty access to emailMessage
            Yii::app()->user->userModel = User::getByUsername('super');
            $emailMessage = $this->campaignItem->emailMessage;
            $emailMessage->addPermissions($betty, Permission::READ_WRITE_CHANGE_PERMISSIONS_CHANGE_OWNER);
            $this->assertTrue($emailMessage->save());

            //Betty has now access to contact and emailMessage
            Yii::app()->user->userModel = $betty;
            $content = CampaignItemSummaryListViewColumnAdapter::
                            resolveContactAndMetricsSummary($this->campaignItem);
            $this->assertContains('test testson', $content);
        }

        public function testResolveContactWithLink()
        {
            $contacts   = Contact::getByName('test testson');
            $content    = CampaignItemSummaryListViewColumnAdapter::
                                resolveContactWithLink($contacts[0]);
            $this->assertContains('test testson', $content);

            //Benny dont have access to contact
            Yii::app()->user->userModel = User::getByUsername('benny');
            $content = CampaignItemSummaryListViewColumnAdapter::
                                resolveContactWithLink($contacts[0]);
            $this->assertContains('You cannot see this contact due to limited access', $content);
        }
    }
?>