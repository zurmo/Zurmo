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

    class EmailMessageMashableActivityRulesTest extends ZurmoBaseTest
    {
        protected static $emailMessage;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super              = SecurityTestHelper::createSuperAdmin();
            self::$emailMessage = EmailMessageTestHelper::createDraftSystemEmail('a test message', $super);
            SecurityTestHelper::createUsers();
            SecurityTestHelper::createGroups();
            SecurityTestHelper::createRoles();
            RedBeanModel::forgetAll();
            //do the rebuild to ensure the tables get created properly.
            ReadPermissionsOptimizationUtil::rebuild();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testResolveSearchAttributesDataByRelatedItemId()
        {
            $quote               = DatabaseCompatibilityUtil::getQuote();
            $rules               = new EmailMessageMashableActivityRules();
            $searchAttributeData = $rules->resolveSearchAttributesDataByRelatedItemId(5);
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('EmailMessage');
            $where               = RedBeanModelDataProvider::makeWhere('EmailMessage', $searchAttributeData, $joinTablesAdapter);
            $compareWhere  = "(({$quote}emailmessagesender{$quote}.{$quote}personoraccount_item_id{$quote} = 5) or (1 = ";
            $compareWhere .= "(select 1 from {$quote}emailmessagerecipient{$quote} emailmessagerecipient where ";
            $compareWhere .= "{$quote}emailmessagerecipient{$quote}.{$quote}emailmessage_id` = {$quote}emailmessage";
            $compareWhere .= "{$quote}.id and {$quote}emailmessagerecipient{$quote}.{$quote}personoraccount_item_id` = 5 limit 1)))";
            $this->assertEquals($compareWhere, $where);

            $sql = EmailMessage::makeSubsetOrCountSqlQuery('emailmessage', $joinTablesAdapter, 1, 5, $where, null);
            $compareSubsetSql  = "select {$quote}emailmessage{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= "from {$quote}emailmessage{$quote} ";
            $compareSubsetSql .= "left join {$quote}emailmessagesender{$quote} on ";
            $compareSubsetSql .= "{$quote}emailmessagesender{$quote}.{$quote}id{$quote} = {$quote}emailmessage{$quote}.";
            $compareSubsetSql .= "{$quote}sender_emailmessagesender_id{$quote} ";
            $compareSubsetSql .= "where " . $compareWhere . ' ';
            $compareSubsetSql .= 'limit 5 offset 1';
            $this->assertEquals($compareSubsetSql, $sql);

            //Make sure the sql runs properly.
            $data = EmailMessage::getSubset($joinTablesAdapter, 0, 5, $where, null);
            $this->assertEquals(0, count($data));
        }

        public function testResolveSearchAttributesDataByRelatedItemIds()
        {
            $quote               = DatabaseCompatibilityUtil::getQuote();
            $rules               = new EmailMessageMashableActivityRules();
            $searchAttributeData = $rules->resolveSearchAttributesDataByRelatedItemIds(array(4, 5));
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('EmailMessage');
            $where               = RedBeanModelDataProvider::makeWhere('EmailMessage', $searchAttributeData, $joinTablesAdapter);
            $compareWhere  = "(({$quote}emailmessagesender{$quote}.{$quote}personoraccount_item_id{$quote} IN(4,5)) or (1 = "; // Not Coding Standard
            $compareWhere .= "(select 1 from {$quote}emailmessagerecipient{$quote} emailmessagerecipient where ";
            $compareWhere .= "{$quote}emailmessagerecipient{$quote}.{$quote}emailmessage_id` = {$quote}emailmessage";
            $compareWhere .= "{$quote}.id and {$quote}emailmessagerecipient{$quote}.{$quote}personoraccount_item_id` IN(4,5) limit 1)))";
            $this->assertEquals($compareWhere, $where);

            $sql = EmailMessage::makeSubsetOrCountSqlQuery('emailmessage', $joinTablesAdapter, 1, 5, $where, null);
            $compareSubsetSql  = "select {$quote}emailmessage{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= "from {$quote}emailmessage{$quote} ";
            $compareSubsetSql .= "left join {$quote}emailmessagesender{$quote} on ";
            $compareSubsetSql .= "{$quote}emailmessagesender{$quote}.{$quote}id{$quote} = {$quote}emailmessage{$quote}.";
            $compareSubsetSql .= "{$quote}sender_emailmessagesender_id{$quote} ";
            $compareSubsetSql .= "where " . $compareWhere . ' ';
            $compareSubsetSql .= 'limit 5 offset 1';
            $this->assertEquals($compareSubsetSql, $sql);

            //Make sure the sql runs properly.
            $data = EmailMessage::getSubset($joinTablesAdapter, 0, 5, $where, null);
            $this->assertEquals(0, count($data));
        }

        public function testResolveSearchAttributesDataByRelatedItemIdWithRegularUser()
        {
            Yii::app()->user->userModel = User::getByUsername('benny');
            $mungeIds = ReadPermissionsOptimizationUtil::getMungeIdsByUser(Yii::app()->user->userModel);
            $quote               = DatabaseCompatibilityUtil::getQuote();
            $rules               = new EmailMessageMashableActivityRules();
            $searchAttributeData = $rules->resolveSearchAttributesDataByRelatedItemId(5);
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('EmailMessage');
            $where               = RedBeanModelDataProvider::makeWhere('EmailMessage', $searchAttributeData, $joinTablesAdapter);
            $compareWhere  = "(({$quote}emailmessagesender{$quote}.{$quote}personoraccount_item_id{$quote} = 5) or (1 = ";
            $compareWhere .= "(select 1 from {$quote}emailmessagerecipient{$quote} emailmessagerecipient where ";
            $compareWhere .= "{$quote}emailmessagerecipient{$quote}.{$quote}emailmessage_id` = {$quote}emailmessage";
            $compareWhere .= "{$quote}.id and {$quote}emailmessagerecipient{$quote}.{$quote}personoraccount_item_id` = 5 limit 1)))";
            $this->assertEquals($compareWhere, $where);

            $sql = EmailMessage::makeSubsetOrCountSqlQuery('emailmessage', $joinTablesAdapter, 1, 5, $where, null);
            $compareSubsetSql  = "select distinct {$quote}emailmessage{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= "from ({$quote}emailmessage{$quote}, {$quote}ownedsecurableitem{$quote}) ";
            $compareSubsetSql .= "left join {$quote}emailmessagesender{$quote} on ";
            $compareSubsetSql .= "{$quote}emailmessagesender{$quote}.{$quote}id{$quote} = {$quote}emailmessage{$quote}.";
            $compareSubsetSql .= "{$quote}sender_emailmessagesender_id{$quote} ";
            $compareSubsetSql .= "left join {$quote}emailmessage_read{$quote} on ";
            $compareSubsetSql .= "{$quote}emailmessage_read{$quote}.{$quote}securableitem_id{$quote} = ";
            $compareSubsetSql .= "{$quote}ownedsecurableitem{$quote}.{$quote}securableitem_id{$quote} ";
            $compareSubsetSql .= "and {$quote}munge_id{$quote} in ('" . join("', '", $mungeIds) . "') ";
            $compareSubsetSql .= "where (" . $compareWhere . ') ';
            $compareSubsetSql .= "and ({$quote}ownedsecurableitem{$quote}.{$quote}owner__user_id{$quote} = " . Yii::app()->user->userModel->id. " ";
            $compareSubsetSql .= "OR {$quote}emailmessage_read{$quote}.{$quote}munge_id{$quote} IS NOT NULL) ";  // Not Coding Standard
            $compareSubsetSql .= "and {$quote}ownedsecurableitem{$quote}.{$quote}id{$quote} = ";
            $compareSubsetSql .= "{$quote}emailmessage{$quote}.{$quote}ownedsecurableitem_id{$quote} ";
            $compareSubsetSql .= 'limit 5 offset 1';
            $this->assertEquals($compareSubsetSql, $sql);

            //Make sure the sql runs properly.
            $data = EmailMessage::getSubset($joinTablesAdapter, 0, 5, $where, null);
            $this->assertEquals(0, count($data));
        }
    }
?>