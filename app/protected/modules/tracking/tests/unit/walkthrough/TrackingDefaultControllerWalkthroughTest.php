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

    class TrackingDefaultControllerWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        protected static $user;

        protected static $personId;

        protected static $autoresponderItemId;

        protected static $campaignItemId;

        const TRACK_ROUTE = '/tracking/default/track';

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            static::$user = User::getByUsername('super');
            Yii::app()->user->userModel = static::$user;

            $contact            = ContactTestHelper::createContactByNameForOwner('contact 01', static::$user);
            $marketingList      = MarketingListTestHelper::createMarketingListByName('marketingList 01',
                                                                                        'description 01',
                                                                                        'fromName 01',
                                                                                        'fromAddress01@domain.com');
            $autoresponder      = AutoresponderTestHelper::createAutoresponder('subject 01',
                                                                                'textContent 01',
                                                                                'htmlContent 01',
                                                                                10,
                                                                                Autoresponder::OPERATION_SUBSCRIBE,
                                                                                1,
                                                                                $marketingList);
            $processed          = 0;
            $processDateTime    = DateTimeUtil::convertTimestampToDbFormatDateTime(time()-100);
            $autoresponderItem  = AutoresponderItemTestHelper::createAutoresponderItem($processed,
                                                                                        $processDateTime,
                                                                                        $autoresponder,
                                                                                        $contact);
            static::$personId               = $contact->getClassId('Person');
            static::$autoresponderItemId    = $autoresponderItem->id;
            $campaign                       = CampaignTestHelper::createCampaign('campaign 01',
                                                                                 'subject 01',
                                                                                 'text Content 01',
                                                                                 'html Content 01',
                                                                                 'fromName 01',
                                                                                 'fromAddress01@zurmo.com',
                                                                                 null,
                                                                                 Campaign::STATUS_ACTIVE,
                                                                                 null,
                                                                                 null,
                                                                                 $marketingList);
            $processed                      = 0;
            $campaignItem                   = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            static::$campaignItemId         = $campaignItem->id;

            ReadPermissionsOptimizationUtil::rebuild();
        }

        public function testGuestUserCanAccessTrackActionAndReturnsNothingWithoutHash()
        {
            $this->runControllerWithNoExceptionsAndGetContent(static::TRACK_ROUTE, true);
        }

        /**
         * @depends testGuestUserCanAccessTrackActionAndReturnsNothingWithoutHash
         */
        public function testTrackActionReturnsNothingForNonHexadecimalHash()
        {
            $hash       = 'Bo9iemeigh6muath8chu2leThohn8Abimoh5rebaihei4aiM1uFoThaith9eng1sei8aisuHu1ugoophiewoe1ieloo';
            $this->setGetArray(array(
                'id'    => $hash,
            ));
            $this->runControllerWithNoExceptionsAndGetContent(static::TRACK_ROUTE, true);
        }

        /**
         * @depends testTrackActionReturnsNothingForNonHexadecimalHash
         */
        public function testTrackActionReturnsNothingForIndecipherableHexadecimalHash()
        {
            $hash       = 'DEDF8F6C80D20528130EBBFBD293E49C9E2F0CBFDE8995FFE4EEAD8EC8F00B70';
            $this->setGetArray(array(
                'id'    => $hash,
            ));
            $this->runControllerWithNoExceptionsAndGetContent(static::TRACK_ROUTE, true);
        }

        /**
         * @depends testTrackActionReturnsNothingForIndecipherableHexadecimalHash
         */
        public function testTrackActionReturnsNothingForDecipherableHexadecimalHashWithMissingParameters()
        {
            $queryStringArray = array(
                'keyOne'    => 'valueOne',
                'keyTwo'    => 'valueTwo',
                'keyThree'  => 'ValueThree',
            );
            $resolveHashForQueryStringArrayFunction = static::getProtectedMethod('EmailMessageActivityUtil',
                                                                                    'resolveHashForQueryStringArray');
            $hash       = $resolveHashForQueryStringArrayFunction->invokeArgs(null, array($queryStringArray));
            $this->setGetArray(array(
                'id'    => $hash,
            ));
            $this->runControllerWithNoExceptionsAndGetContent(static::TRACK_ROUTE, true);
        }

        /**
         * @depends testTrackActionReturnsNothingForDecipherableHexadecimalHashWithMissingParameters
         */
        public function testTrackActionReturnsNothingForInvalidModelId()
        {
            $queryStringArray = array(
                'modelId'   => 100,
                'modelType' => 'AutoresponderItem',
                'personId'  => static::$personId,
            );
            $resolveHashForQueryStringArrayFunction = static::getProtectedMethod('EmailMessageActivityUtil',
                                                                                    'resolveHashForQueryStringArray');
            $hash       = $resolveHashForQueryStringArrayFunction->invokeArgs(null, array($queryStringArray));
            $this->setGetArray(array(
                'id'    => $hash,
            ));
            $this->runControllerWithNoExceptionsAndGetContent(static::TRACK_ROUTE, true);
        }

        /**
         * @depends testTrackActionReturnsNothingForInvalidModelId
         */
        public function testTrackActionReturnsNothingForInvalidPersonlId()
        {
            $queryStringArray = array(
                'modelId'   => static::$autoresponderItemId,
                'modelType' => 'AutoresponderItem',
                'personId'  => 200,
            );
            $resolveHashForQueryStringArrayFunction = static::getProtectedMethod('EmailMessageActivityUtil',
                                                                                    'resolveHashForQueryStringArray');
            $hash       = $resolveHashForQueryStringArrayFunction->invokeArgs(null, array($queryStringArray));
            $this->setGetArray(array(
                'id'    => $hash,
            ));
            $this->runControllerWithNoExceptionsAndGetContent(static::TRACK_ROUTE, true);
        }

        /**
         * @depends testTrackActionReturnsNothingForInvalidPersonlId
         */
        public function testTrackActionDoesNotComplainForMissingUrlParameterForAutoresponderItem()
        {
            $queryStringArray = array(
                'modelId'   => static::$autoresponderItemId,
                'modelType' => 'AutoresponderItem',
                'personId'  => static::$personId,
            );
            $resolveHashForQueryStringArrayFunction = static::getProtectedMethod('EmailMessageActivityUtil',
                                                                                    'resolveHashForQueryStringArray');
            $hash       = $resolveHashForQueryStringArrayFunction->invokeArgs(null, array($queryStringArray));
            $this->setGetArray(array(
                'id'    => $hash,
            ));
            // Need @ to ignore the headers already sent error.
            $content    = @$this->runControllerWithExitExceptionAndGetContent(static::TRACK_ROUTE);
            $image      = imagecreatefromstring($content);
            $this->assertTrue($image !== false);
            $path       = tempnam(sys_get_temp_dir() , '1x1-pixel') . '.png';
            $createdPng = imagepng($image, $path);
            $this->assertTrue($createdPng);
            $autoresponderItemActivity = AutoresponderItemActivity::getByTypeAndModelIdAndPersonIdAndUrl(
                                                                                AutoresponderItemActivity::TYPE_OPEN,
                                                                                static::$autoresponderItemId,
                                                                                static::$personId);
            $this->assertNotEmpty($autoresponderItemActivity);
            $this->assertCount(1, $autoresponderItemActivity);
            $this->assertEquals(1, $autoresponderItemActivity[0]->quantity);
        }

        /**
         * @depends testTrackActionDoesNotComplainForMissingUrlParameterForAutoresponderItem
         */
        public function testTrackActionThrowsRedirectExceptionForUrlParameterForAutoresponderItem()
        {
            $queryStringArray = array(
                'modelId'   => static::$autoresponderItemId,
                'modelType' => 'AutoresponderItem',
                'personId'  => static::$personId,
                'url'       => 'http://www.zurmo.com',
            );
            $resolveHashForQueryStringArrayFunction = static::getProtectedMethod('EmailMessageActivityUtil',
                                                                                    'resolveHashForQueryStringArray');
            $hash       = $resolveHashForQueryStringArrayFunction->invokeArgs(null, array($queryStringArray));
            $this->setGetArray(array(
                'id'    => $hash,
            ));
            $url        = $this->runControllerWithRedirectExceptionAndGetUrl(static::TRACK_ROUTE);
            $this->assertEquals($queryStringArray['url'], $url);
            $autoresponderItemActivity = AutoresponderItemActivity::getByTypeAndModelIdAndPersonIdAndUrl(
                                                                                AutoresponderItemActivity::TYPE_CLICK,
                                                                                static::$autoresponderItemId,
                                                                                static::$personId,
                                                                                $queryStringArray['url']);
            $this->assertNotEmpty($autoresponderItemActivity);
            $this->assertCount(1, $autoresponderItemActivity);
            $this->assertEquals(1, $autoresponderItemActivity[0]->quantity);
        }

        /**
         * @depends testTrackActionReturnsNothingForInvalidPersonlId
         */
        public function testTrackActionDoesNotComplainForMissingUrlParameterForCampaignItem()
        {
            $queryStringArray = array(
                'modelId'   => static::$campaignItemId,
                'modelType' => 'CampaignItem',
                'personId'  => static::$personId,
            );
            $resolveHashForQueryStringArrayFunction = static::getProtectedMethod('EmailMessageActivityUtil',
                                                                                    'resolveHashForQueryStringArray');
            $hash       = $resolveHashForQueryStringArrayFunction->invokeArgs(null, array($queryStringArray));
            $this->setGetArray(array(
                'id'    => $hash,
            ));
            // Need @ to ignore the headers already sent error.
            $content    = @$this->runControllerWithExitExceptionAndGetContent(static::TRACK_ROUTE);
            $image      = imagecreatefromstring($content);
            $this->assertTrue($image !== false);
            $path       = tempnam(sys_get_temp_dir() , '1x1-pixel') . '.png';
            $createdPng = imagepng($image, $path);
            $this->assertTrue($createdPng);
            $campaignItemActivity = CampaignItemActivity::getByTypeAndModelIdAndPersonIdAndUrl(
                                                                                    CampaignItemActivity::TYPE_OPEN,
                                                                                    static::$campaignItemId,
                                                                                    static::$personId);
            $this->assertNotEmpty($campaignItemActivity);
            $this->assertCount(1, $campaignItemActivity);
            $this->assertEquals(1, $campaignItemActivity[0]->quantity);
        }

        /**
         * @depends testTrackActionDoesNotComplainForMissingUrlParameterForCampaignItem
         */
        public function testTrackActionThrowsRedirectExceptionForUrlParameterForCampaignItem()
        {
            $queryStringArray = array(
                'modelId'   => static::$campaignItemId,
                'modelType' => 'CampaignItem',
                'personId'  => static::$personId,
                'url'       => 'http://www.zurmo.com',
            );
            $resolveHashForQueryStringArrayFunction = static::getProtectedMethod('EmailMessageActivityUtil',
                                                                                    'resolveHashForQueryStringArray');
            $hash       = $resolveHashForQueryStringArrayFunction->invokeArgs(null, array($queryStringArray));
            $this->setGetArray(array(
                'id'    => $hash,
            ));
            $url        = $this->runControllerWithRedirectExceptionAndGetUrl(static::TRACK_ROUTE);
            $this->assertEquals($queryStringArray['url'], $url);
            $campaignItemActivity = CampaignItemActivity::getByTypeAndModelIdAndPersonIdAndUrl(
                                                                                    CampaignItemActivity::TYPE_CLICK,
                                                                                    static::$campaignItemId,
                                                                                    static::$personId,
                                                                                    $queryStringArray['url']);
            $this->assertNotEmpty($campaignItemActivity);
            $this->assertCount(1, $campaignItemActivity);
            $this->assertEquals(1, $campaignItemActivity[0]->quantity);
        }
    }
?>