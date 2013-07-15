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
     * Base class for working with data that is grouped on email message information
     */
    abstract class MarketingGroupByEmailMessagesChartDataProvider extends MarketingChartDataProvider
    {
        /**
         * @param RedBeanModelSelectQueryAdapter $selectQueryAdapter
         * @param string $columnName
         */
        protected static function addEmailMessageDayDateClause(RedBeanModelSelectQueryAdapter $selectQueryAdapter, $columnName)
        {
            assert('is_string($columnName)');
            $quote       = DatabaseCompatibilityUtil::getQuote();
            $emailMessageTableName = EmailMessage::getTableName('EmailMessage');
            $selectQueryAdapter->addDayDateClause($emailMessageTableName, $columnName, static::DAY_DATE);
        }

        /**
         * @param RedBeanModelSelectQueryAdapter $selectQueryAdapter
         * @param string $columnName
         */
        protected static function addEmailMessageFirstDayOfWeekDateClause(RedBeanModelSelectQueryAdapter $selectQueryAdapter, $columnName)
        {
            assert('is_string($columnName)');
            $quote                 = DatabaseCompatibilityUtil::getQuote();
            $emailMessageTableName = EmailMessage::getTableName('EmailMessage');
            $selectQueryAdapter->addFirstDayOfWeekDateClause($emailMessageTableName, $columnName, static::FIRST_DAY_OF_WEEK_DATE);
        }

        /**
         * @param RedBeanModelSelectQueryAdapter $selectQueryAdapter
         * @param string $columnName
         */
        protected static function addEmailMessageFirstDayOfMonthDateClause(RedBeanModelSelectQueryAdapter $selectQueryAdapter, $columnName)
        {
            assert('is_string($columnName)');
            $quote                 = DatabaseCompatibilityUtil::getQuote();
            $emailMessageTableName = EmailMessage::getTableName('EmailMessage');
            $selectQueryAdapter->addFirstDayOfMonthDateClause($emailMessageTableName, $columnName, static::FIRST_DAY_OF_MONTH_DATE);
        }

        /**
         * @param string $type
         * @return string
         */
        protected static function resolveCampaignTypeSubQuery($type)
        {
            assert('is_int($type)');
            $quote                         = DatabaseCompatibilityUtil::getQuote();
            $where                         = null;
            $selectDistinct                = true;
            $campaignItemTableName         = CampaignItem::getTableName('CampaignItem');
            $campaignItemActivityTableName = CampaignItemActivity::getTableName('CampaignItemActivity');
            $emailMessageActivityTableName = EmailMessageActivity::getTableName('EmailMessageActivity');
            $selectQueryAdapter            = new RedBeanModelSelectQueryAdapter($selectDistinct);
            $joinTablesAdapter             = new RedBeanModelJoinTablesQueryAdapter('CampaignItemActivity');
            $selectQueryAdapter->addClauseByQueryString("campaign_id");
            $joinTablesAdapter->addFromTableAndGetAliasName($emailMessageActivityTableName, 'emailmessageactivity_id',
                                             $campaignItemActivityTableName);
            $where                         = "type = " . $type . " and {$quote}{$campaignItemActivityTableName}{$quote}" .
                                             ".campaignitem_id = {$quote}{$campaignItemTableName}{$quote}.id";
            $sql                           = SQLQueryUtil::makeQuery($campaignItemActivityTableName, $selectQueryAdapter,
                                             $joinTablesAdapter, null, null, $where);
            return $sql;
        }

        /**
         * @param string $type
         * @return string
         */
        public static function resolveAutoresponderTypeSubQuery($type)
        {
            assert('is_int($type)');
            $quote                         = DatabaseCompatibilityUtil::getQuote();
            $where                         = null;
            $selectDistinct                = true;
            $autoresponderItemActivityTableName = AutoresponderItemActivity::getTableName('AutoresponderItemActivity');
            $emailMessageActivityTableName = EmailMessageActivity::getTableName('EmailMessageActivity');
            $selectQueryAdapter            = new RedBeanModelSelectQueryAdapter($selectDistinct);
            $joinTablesAdapter             = new RedBeanModelJoinTablesQueryAdapter('AutoresponderItemActivity');
            $selectQueryAdapter->addClauseByQueryString("autoresponder_id");
            $joinTablesAdapter->addFromTableAndGetAliasName($emailMessageActivityTableName, 'emailmessageactivity_id',
                                             $autoresponderItemActivityTableName);
            $where                         = "type = " . $type . " and {$quote}{$autoresponderItemActivityTableName}{$quote}" .
                                             ".autoresponderitem_id = autoresponderitem.id";
            $sql                           = SQLQueryUtil::makeQuery($autoresponderItemActivityTableName, $selectQueryAdapter,
                $joinTablesAdapter, null, null, $where);
            return $sql;
        }

        /**
         * @param string $dateAttributeName
         * @param string $beginDateTime
         * @param string $endDateTime
         * @param null|Campaign $campaign
         * @return array
         */
        protected static function makeCampaignsSearchAttributeData($dateAttributeName, $beginDateTime, $endDateTime, $campaign)
        {
            assert('is_string($dateAttributeName)');
            assert('is_string($beginDateTime)');
            assert('is_string($endDateTime)');
            assert('$campaign == null || ($campaign instanceof Campaign && $campaign->id > 0)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName' => 'campaignItems',
                    'relatedModelData' => array(
                        'attributeName'     => 'emailMessage',
                        'relatedModelData'  => array(
                            'attributeName'     => $dateAttributeName,
                            'operatorType'      => 'greaterThanOrEqualTo',
                            'value'             => $beginDateTime,
                        ),
                    ),
                ),
                2 => array(
                    'attributeName' => 'campaignItems',
                    'relatedModelData' => array(
                        'attributeName'     => 'emailMessage',
                        'relatedModelData'  => array(
                            'attributeName'     => $dateAttributeName,
                            'operatorType'      => 'lessThanOrEqualTo',
                            'value'             => $endDateTime,
                        ),
                    ),
                ),
                3 => array(
                    'attributeName' => 'campaignItems',
                    'relatedModelData' => array(
                        'attributeName'     => 'processed',                        
                        'operatorType'      => 'equals',
                        'value'             => true,                        
                    )
                ),
            );
            if ($campaign instanceof Campaign && $campaign->id > 0)
            {
                $searchAttributeData['clauses'][4] = array(
                    'attributeName'        => 'id',
                    'operatorType'         => 'equals',
                    'value'                => $campaign->id);
                $searchAttributeData['structure'] = '1 and 2 and 3 and 4';
            }
            else
            {
                $searchAttributeData['structure'] = '1 and 2 and 3';
            }
            return $searchAttributeData;
        }

        /**
         * @param string $dateAttributeName
         * @param string $beginDateTime
         * @param string $endDateTime
         * @param null|MarketingList $marketingList
         * @return array
         */
        protected static function makeAutorespondersSearchAttributeData($dateAttributeName, $beginDateTime, $endDateTime, $marketingList)
        {
            assert('is_string($dateAttributeName)');
            assert('is_string($beginDateTime)');
            assert('is_string($endDateTime)');
            assert('$marketingList == null || ($marketingList instanceof MarketingList && $marketingList->id > 0)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName' => 'autoresponderItems',
                    'relatedModelData' => array(
                        'attributeName'     => 'emailMessage',
                        'relatedModelData'  => array(
                            'attributeName'     => $dateAttributeName,
                            'operatorType'      => 'greaterThanOrEqualTo',
                            'value'             => $beginDateTime,
                        ),
                    ),
                ),
                2 => array(
                    'attributeName' => 'autoresponderItems',
                    'relatedModelData' => array(
                        'attributeName'     => 'emailMessage',
                        'relatedModelData'  => array(
                            'attributeName'     => $dateAttributeName,
                            'operatorType'      => 'lessThanOrEqualTo',
                            'value'             => $endDateTime,
                        ),
                    ),
                ),
                3 => array(
                    'attributeName' => 'autoresponderItems',
                    'relatedModelData' => array(
                        'attributeName'     => 'processed',                        
                        'operatorType'      => 'equals',
                        'value'             => true,                        
                    )
                ),
            );
            if ($marketingList instanceof MarketingList && $marketingList->id > 0)
            {
                $searchAttributeData['clauses'][4] = array(
                    'attributeName'        => 'marketingList',
                    'operatorType'         => 'equals',
                    'value'                => $marketingList->id);
                $searchAttributeData['structure'] = '1 and 2 and 3 and 4';
            }
            else
            {
                $searchAttributeData['structure'] = '1 and 2 and 3';
            }
            return $searchAttributeData;
        }
    }
?>