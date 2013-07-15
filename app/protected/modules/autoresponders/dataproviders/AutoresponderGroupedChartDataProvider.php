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

    class AutoresponderGroupedChartDataProvider extends MarketingGroupByEmailMessagesChartDataProvider
    {
        protected $autoresponder;

        public function __construct(Autoresponder $autoresponder)
        {
            assert('$autoresponder->id > 0');
            $this->autoresponder = $autoresponder;
        }

        public function getChartData()
        {
            $sql = static::makeSqlQuery(static::makeSearchAttributeData($this->autoresponder));
            $row = R::getRow($sql);
            $data = static::resolveChartDataBaseGroupElements();
            foreach ($data as $index => $notUsed)
            {
                if ($row[$index] != null)
                {
                    $data[$index] = $row[$index];
                }
            }
            return $data;
        }

        protected static function makeSearchAttributeData(Autoresponder $autoresponder)
        {
            assert('$autoresponder->id > 0');
            $searchAttributeData = array();
            $searchAttributeData['clauses'][1] = array(
                    'attributeName'        => 'id',
                    'operatorType'         => 'equals',
                    'value'                => $autoresponder->id);
            $searchAttributeData['clauses'][2] = array(
                    'attributeName' => 'autoresponderItems',
                    'relatedModelData' => array(
                        'attributeName'     => 'processed',                        
                        'operatorType'      => 'equals',
                        'value'             => true,                        
                    ));                        
            $searchAttributeData['structure'] = '1 and 2';
            return $searchAttributeData;
        }

        protected static function makeSqlQuery($searchAttributeData)
        {
            $quote                      = DatabaseCompatibilityUtil::getQuote();
            $where                      = null;
            $selectDistinct             = false;
            $autoresponderTableName     = Autoresponder::getTableName('Autoresponder');
            $autoresponderItemTableName = AutoresponderItem::getTableName('AutoresponderItem');
            $emailMessageTableName      = EmailMessage::getTableName('EmailMessage');
            $sentDateTimeColumnName     = EmailMessage::getColumnNameByAttribute('sentDateTime');
            $joinTablesAdapter          = new RedBeanModelJoinTablesQueryAdapter('Autoresponder');
            $selectQueryAdapter         = new RedBeanModelSelectQueryAdapter($selectDistinct);
            $queuedEmailsSelectPart     = "sum(CASE WHEN {$quote}{$emailMessageTableName}{$quote}.{$quote}{$sentDateTimeColumnName}" .
                                          $quote . " = '0000-00-00 00:00:00' OR {$quote}{$emailMessageTableName}{$quote}" .
                                          ".{$quote}{$sentDateTimeColumnName}{$quote} IS NULL THEN 1 ELSE 0 END)"; // Not Coding Standard
            $sentEmailsSelectPart   = "sum(CASE WHEN {$quote}{$emailMessageTableName}{$quote}.{$quote}{$sentDateTimeColumnName}" .
                                      $quote . " > '0000-00-00 00:00:00' THEN 1 ELSE 0 END)";
            $uniqueOpensSelectPart  = static::resolveAutoresponderTypeSubQuery(EmailMessageActivity::TYPE_OPEN);
            $uniqueClicksSelectPart = static::resolveAutoresponderTypeSubQuery(EmailMessageActivity::TYPE_CLICK);
            $bouncedSelectPart      = static::resolveAutoresponderTypeSubQuery(EmailMessageActivity::TYPE_BOUNCE);
            $optedOutSelectPart     = static::resolveAutoresponderTypeSubQuery(EmailMessageActivity::TYPE_UNSUBSCRIBE);
            static::addEmailMessageDayDateClause            ($selectQueryAdapter, $sentDateTimeColumnName);
            static::addEmailMessageFirstDayOfWeekDateClause ($selectQueryAdapter, $sentDateTimeColumnName);
            static::addEmailMessageFirstDayOfMonthDateClause($selectQueryAdapter, $sentDateTimeColumnName);
            $selectQueryAdapter->addNonSpecificCountClause();
            $selectQueryAdapter->addClauseByQueryString($queuedEmailsSelectPart,  static::QUEUED);
            $selectQueryAdapter->addClauseByQueryString($sentEmailsSelectPart,  static::SENT);
            $selectQueryAdapter->addClauseByQueryString("count((" . $uniqueOpensSelectPart  . "))",  static::UNIQUE_OPENS);
            $selectQueryAdapter->addClauseByQueryString("count((" . $uniqueClicksSelectPart . "))",  static::UNIQUE_CLICKS);
            $selectQueryAdapter->addClauseByQueryString("count((" . $bouncedSelectPart . "))",       static::BOUNCED);
            $selectQueryAdapter->addClauseByQueryString("count((" . $optedOutSelectPart . "))",      static::UNSUBSCRIBED);
            $joinTablesAdapter->addLeftTableAndGetAliasName($autoresponderItemTableName, 'id', $autoresponderTableName, 'autoresponder_id');
            $joinTablesAdapter->addLeftTableAndGetAliasName($emailMessageTableName, 'emailmessage_id', $autoresponderItemTableName, 'id');
            $where = RedBeanModelDataProvider::makeWhere('Autoresponder', $searchAttributeData, $joinTablesAdapter);
            $sql   = SQLQueryUtil::makeQuery($autoresponderTableName, $selectQueryAdapter, $joinTablesAdapter, null, null, $where);
            return $sql;
        }

        protected static function resolveChartDataBaseGroupElements()
        {
            return array(self::QUEUED        => 0,
                         self::SENT          => 0,
                         self::UNIQUE_CLICKS => 0,
                         self::UNIQUE_OPENS  => 0,
                         self::BOUNCED       => 0,
                         self::UNSUBSCRIBED  => 0);
        }
    }
?>