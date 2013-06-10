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

    class CampaignTestHelper
    {
        public static function createCampaign($name, $subject, $textContent, $htmlContent = null, $fromName = null,
                                            $fromAddress = null, $supportsRichText = null, $status = null,
                                            $sendOnDateTime = null, $enableTracking = null,
                                            $marketingList = null, $runValidation = true)
        {
            assert('is_bool($runValidation)');
            $campaign       = static::populateCampaign($name, $subject, $textContent, $htmlContent, $fromName,
                                                    $fromAddress, $supportsRichText, $status,
                                                    $sendOnDateTime, $enableTracking, $marketingList);
            $saved          = $campaign->save($runValidation);
            if (!$saved)
            {
                throw new FailedToSaveModelException();
            }
            return $campaign;
        }

        public static function populateCampaign($name, $subject, $textContent, $htmlContent = null, $fromName = null,
                                                $fromAddress = null, $supportsRichText = null,
                                                $status = null, $sendOnDateTime = null,
                                                $enableTracking = null, $marketingList = null)
        {
            assert('is_string($name)');
            assert('is_string($subject)');
            assert('is_string($textContent)');
            assert('is_string($htmlContent) || $htmlContent === null');
            assert('is_string($fromName) || $fromName === null');
            assert('is_string($fromAddress) || $fromAddress === null');
            assert('is_string($supportsRichText) || is_int($supportsRichText) || $supportsRichText === null');
            assert('is_string($status) || is_int($status) || $status === null');
            assert('is_string($sendOnDateTime) || is_int($sendOnDateTime) || $sendOnDateTime === null');
            assert('is_bool($enableTracking) || is_int($enableTracking) || $enableTracking === null');
            assert('is_object($marketingList) || $marketingList === null');
            if ($supportsRichText == null)
            {
                $supportsRichText   = 1;
            }
            if ($sendOnDateTime == null)
            {
                $sendOnDateTime = '0000-00-00 00:00:00';
            }
            if ($status == null)
            {
                $status             = Campaign::STATUS_ACTIVE;
            }
            if ($enableTracking == null)
            {
                $enableTracking     = 1;
            }
            if ($fromName == null)
            {
                $fromName       = 'Support Team';
            }
            if ($fromAddress == null)
            {
                $fromAddress    = 'support@zurmo.com';
            }
            if (empty($marketingList))
            {
                $marketingLists = MarketingList::getAll();
                if  (!empty($marketingLists))
                {
                    $marketingList  = RandomDataUtil::getRandomValueFromArray($marketingLists);
                }
            }
            $campaign                           = new Campaign();
            $campaign->name                     = $name;
            $campaign->subject                  = $subject;
            $campaign->textContent              = $textContent;
            $campaign->htmlContent              = $htmlContent;
            $campaign->status                   = $status;
            $campaign->fromName                 = $fromName;
            $campaign->fromAddress              = $fromAddress;
            $campaign->supportsRichText         = $supportsRichText;
            $campaign->enableTracking           = $enableTracking;
            $campaign->sendOnDateTime          = $sendOnDateTime;
            $campaign->marketingList            = $marketingList;
            return $campaign;
        }
    }
?>