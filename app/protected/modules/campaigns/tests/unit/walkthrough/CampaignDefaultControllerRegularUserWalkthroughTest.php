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

    class CampaignDefaultControllerRegularUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        protected $user;

        protected static $campaignOwnedBySuper;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            UserTestHelper::createBasicUser('nobody');

            MarketingListTestHelper::createMarketingListByName('MarketingListName',
                                                                'MarketingList Description',
                                                                'first',
                                                                'first@zurmo.com');
            static::$campaignOwnedBySuper = CampaignTestHelper::createCampaign('campaign01',
                                                                                    'campaign subject 01',
                                                                                    'text content for campaign 01',
                                                                                    'html content for campaign 01',
                                                                                    'fromCampaign',
                                                                                    'fromCampaign@zurmo.com');
            ReadPermissionsOptimizationUtil::rebuild();
        }

        public function setUp()
        {
            parent::setUp();
            $this->user = User::getByUsername('nobody');
            Yii::app()->user->userModel = $this->user;
        }

        public function testRegularUserAllDefaultControllerActions()
        {
            MarketingListTestHelper::createMarketingListByName('MarketingListName02',
                                                                'MarketingList Description',
                                                                'first',
                                                                'first@zurmo.com');
            $campaign       = CampaignTestHelper::createCampaign('campaign02',
                                                                    'campaign subject 02',
                                                                    'text content for campaign 02',
                                                                    'html content for campaign 02',
                                                                    'fromCampaign',
                                                                    'fromCampaign@zurmo.com');
            $this->runControllerShouldResultInAccessFailureAndGetContent('campaigns/default');
            $this->runControllerShouldResultInAccessFailureAndGetContent('campaigns/default/index');
            $this->runControllerShouldResultInAccessFailureAndGetContent('campaigns/default/list');
            $this->runControllerShouldResultInAccessFailureAndGetContent('campaigns/default/create');
            $this->setGetArray(array('id' => $campaign->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('campaigns/default/edit');
            $this->runControllerShouldResultInAccessFailureAndGetContent('campaigns/default/details');
            $this->resetGetArray();

            $this->user->setRight('CampaignsModule', CampaignsModule::getAccessRight());
            $this->assertTrue($this->user->save());
            $content = $this->runControllerWithNoExceptionsAndGetContent('campaigns/default');
            $this->assertTrue(strpos($content, '<p>To manage campaigns you must have access to email templates and ' .
                                    'marketing lists. Contact the CRM administrator about this issue.</p>') !== false);
            $this->user->setRight('MarketingListsModule', MarketingListsModule::getAccessRight());
            $this->user->setRight('EmailTemplatesModule', EmailTemplatesModule::getAccessRight());
            $this->assertTrue($this->user->save());
            $content = $this->runControllerWithNoExceptionsAndGetContent('campaigns/default');
            $this->assertTrue(strpos($content, '<p>To manage campaigns you must have access to email templates and ' .
                                    'marketing lists. Contact the CRM administrator about this issue.</p>') === false);
            $content = $this->runControllerWithNoExceptionsAndGetContent('campaigns/default/index');
            $this->assertTrue(strpos($content, '<p>To manage campaigns you must have access to email templates and ' .
                                    'marketing lists. Contact the CRM administrator about this issue.</p>') === false);
            $content = $this->runControllerWithNoExceptionsAndGetContent('campaigns/default/list');
            $this->assertTrue(strpos($content, '<p>To manage campaigns you must have access to email templates and ' .
                                    'marketing lists. Contact the CRM administrator about this issue.</p>') === false);

            $this->setGetArray(array('id' => $campaign->id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('campaigns/default/details');
            $this->assertTrue(strpos($content, '<p>To manage campaigns you must have access to email templates and ' .
                                    'marketing lists. Contact the CRM administrator about this issue.</p>') === false);
            $content = $this->runControllerWithNoExceptionsAndGetContent('campaigns/default/details');
            $this->assertTrue(strpos($content, '<p>To manage campaigns you must have access to email templates and ' .
                                    'marketing lists. Contact the CRM administrator about this issue.</p>') === false);

            $this->resetGetArray();
            $this->user->setRight('CampaignsModule', CampaignsModule::getCreateRight());
            $this->assertTrue($this->user->save());
            $content = $this->runControllerWithNoExceptionsAndGetContent('campaigns/default/create');
            $this->assertTrue(strpos($content, '<p>To manage campaigns you must have access to email templates and ' .
                                    'marketing lists. Contact the CRM administrator about this issue.</p>') === false);

            $this->setGetArray(array('id' => $campaign->id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('campaigns/default/details');
            $this->assertTrue(strpos($content, '<p>To manage campaigns you must have access to email templates and ' .
                                    'marketing lists. Contact the CRM administrator about this issue.</p>') === false);
            $this->resetGetArray();

            $this->user->setRight('CampaignsModule', CampaignsModule::getCreateRight());
            $this->assertTrue($this->user->save());
            $content = $this->runControllerWithNoExceptionsAndGetContent('campaigns/default/create');
            $this->assertTrue(strpos($content, '<p>To manage campaigns you must have access to email templates and ' .
                                    'marketing lists. Contact the CRM administrator about this issue.</p>') === false);

            $this->setGetArray(array('id' => $campaign->id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('campaigns/default/edit');
            $this->assertTrue(strpos($content, '<p>To manage campaigns you must have access to email templates and ' .
                                    'marketing lists. Contact the CRM administrator about this issue.</p>') === false);

            $this->user->setRight('CampaignsModule', CampaignsModule::getDeleteRight());
            $this->assertTrue($this->user->save());
            $this->runControllerWithRedirectExceptionAndGetUrl('campaigns/default/delete');

            $this->setGetArray(array('id' => static::$campaignOwnedBySuper->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('campaigns/default/edit');
            $this->runControllerShouldResultInAccessFailureAndGetContent('campaigns/default/details');
            $this->runControllerShouldResultInAccessFailureAndGetContent('campaigns/default/delete');
        }
    }
?>