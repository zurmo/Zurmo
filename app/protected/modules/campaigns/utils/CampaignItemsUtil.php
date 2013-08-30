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

    /**
     * Helper class for working with campaignItem
     */
    abstract class CampaignItemsUtil extends AutoresponderAndCampaignItemsUtil
    {
        const DEFAULT_CAMPAIGNITEMS_TO_CREATE_PAGE_SIZE = 200;

        /**
         * For now we should limit to process one campaign at a time until it is completely processed. This will
         * avoid potential performance problems.
         * @param null $pageSize - used to determine how many campaignItems to create per run.
         * @param integer $campaignPageSize
         * @return bool
         */
        public static function generateCampaignItemsForDueCampaigns($pageSize = null, $campaignPageSize = 1)
        {
            assert('is_int($pageSize) || $pageSize == null');
            assert('is_int($campaignPageSize)');
            $dueCampaigns   = Campaign::getByStatusAndSendingTime(Campaign::STATUS_ACTIVE, time(), $campaignPageSize);
            foreach ($dueCampaigns as $dueCampaign)
            {
                if (static::generateCampaignItems($dueCampaign, $pageSize))
                {
                    $dueCampaign->status = Campaign::STATUS_PROCESSING;
                    if (!$dueCampaign->save())
                    {
                        throw new FailedToSaveModelException("Unable to save campaign");
                    }
                }
            }
            return true;
        }

        protected static function generateCampaignItems($campaign, $pageSize)
        {
            if ($pageSize == null)
            {
                $pageSize = self::DEFAULT_CAMPAIGNITEMS_TO_CREATE_PAGE_SIZE;
            }
            $contacts = array();
            $quote    = DatabaseCompatibilityUtil::getQuote();
            $marketingListMemberTableName  = RedBeanModel::getTableName('MarketingListMember');
            $campaignItemTableName = RedBeanModel::getTableName('CampaignItem');
            $sql  = "select {$quote}{$marketingListMemberTableName}{$quote}.{$quote}contact_id{$quote} from {$quote}{$marketingListMemberTableName}{$quote}"; // Not Coding Standard
            $sql .= "left join {$quote}{$campaignItemTableName}{$quote} on ";
            $sql .= "{$quote}{$campaignItemTableName}{$quote}.{$quote}contact_id{$quote} ";
            $sql .= "= {$quote}{$marketingListMemberTableName}{$quote}.{$quote}contact_id{$quote}";
            $sql .= "AND {$quote}{$campaignItemTableName}{$quote}.{$quote}campaign_id{$quote} = " . $campaign->id . " " ;
            $sql .= "where {$quote}{$marketingListMemberTableName}{$quote}.{$quote}marketinglist_id{$quote} = " . $campaign->marketingList->id ;
            $sql .= " and {$quote}{$campaignItemTableName}{$quote}.{$quote}id{$quote} is null limit " . $pageSize;
            $ids = R::getCol($sql);

            foreach ($ids as $contactId)
            {
                $contacts[] = Contact::getById((int)$contactId);
            }
            if (!empty($contacts))
            {
                //todo: if the return value is false, then we might need to catch that since it didn't go well.
                CampaignItem::registerCampaignItemsByCampaign($campaign, $contacts);
                if (count($ids) < $pageSize)
                {
                    return true;
                }
            }
            else
            {
                return true;
            }
            return false;
        }
    }
?>