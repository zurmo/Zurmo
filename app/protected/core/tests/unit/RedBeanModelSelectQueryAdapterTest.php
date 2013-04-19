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

    class RedBeanModelSelectQueryAdapterTest extends BaseTest
    {
        protected static $chicagoOffsetInSeconds = 0;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setup()
        {
            parent::setup();
            Yii::app()->user->userModel           = User::getByUsername('super');
            Yii::app()->user->userModel->timeZone = 'America/Chicago';
            //Deal with daylight savings time.
            $timeZoneObject  = new DateTimeZone(Yii::app()->user->userModel->timeZone);
            $offsetInSeconds = $timeZoneObject->getOffset(new DateTime());
            $this->assertTrue($offsetInSeconds == -18000 || $offsetInSeconds == -21600);
            self::$chicagoOffsetInSeconds = $offsetInSeconds;
        }

        public function testIsDistinct()
        {
            $adapter = new RedBeanModelSelectQueryAdapter();
            $this->assertFalse($adapter->isDistinct());
            $adapter = new RedBeanModelSelectQueryAdapter(true);
            $this->assertTrue($adapter->isDistinct());
        }

        public function testAddClause()
        {
            $quote   = DatabaseCompatibilityUtil::getQuote();
            $adapter = new RedBeanModelSelectQueryAdapter();
            $this->assertEquals(0, $adapter->getClausesCount());
            $adapter->addClause('a', 'b', 'c');
            $this->assertEquals(1, $adapter->getClausesCount());
            $compareString = "select {$quote}a{$quote}.{$quote}b{$quote} c ";
            $this->assertEquals($compareString, $adapter->getSelect());

            $adapter = new RedBeanModelSelectQueryAdapter(true);
            $this->assertEquals(0, $adapter->getClausesCount());
            $adapter->addClause('a', 'b', 'c');
            $this->assertEquals(1, $adapter->getClausesCount());
            $compareString = "select distinct {$quote}a{$quote}.{$quote}b{$quote} c ";
            $this->assertEquals($compareString, $adapter->getSelect());

            //Test with multiple clauses
            $quote   = DatabaseCompatibilityUtil::getQuote();
            $adapter = new RedBeanModelSelectQueryAdapter();
            $this->assertEquals(0, $adapter->getClausesCount());
            $adapter->addClause('a', 'b', 'c');
            $adapter->addClause('d', 'e', 'f');
            $this->assertEquals(2, $adapter->getClausesCount());
            $compareString = "select {$quote}a{$quote}.{$quote}b{$quote} c, {$quote}d{$quote}.{$quote}e{$quote} f ";
            $this->assertEquals($compareString, $adapter->getSelect());
        }

        public function testAddClauseWithColumnNameOnlyAndNoEnclosure()
        {
            $quote   = DatabaseCompatibilityUtil::getQuote();
            $adapter = new RedBeanModelSelectQueryAdapter();
            $this->assertEquals(0, $adapter->getClausesCount());
            $adapter->addClauseWithColumnNameOnlyAndNoEnclosure('a', 'b');
            $this->assertEquals(1, $adapter->getClausesCount());
            $compareString = "select a b ";
            $this->assertEquals($compareString, $adapter->getSelect());
        }

        public function testAddCountClause()
        {
            $quote   = DatabaseCompatibilityUtil::getQuote();
            $adapter = new RedBeanModelSelectQueryAdapter();
            $this->assertEquals(0, $adapter->getClausesCount());
            $adapter->addCountClause('a', 'b', 'c');
            $this->assertEquals(1, $adapter->getClausesCount());
            $compareString = "select count({$quote}a{$quote}.{$quote}b{$quote}) c ";
            $this->assertEquals($compareString, $adapter->getSelect());

            $adapter = new RedBeanModelSelectQueryAdapter(true);
            $this->assertEquals(0, $adapter->getClausesCount());
            $adapter->addCountClause('a', 'b', 'c');
            $this->assertEquals(1, $adapter->getClausesCount());
            $compareString = "select count(distinct {$quote}a{$quote}.{$quote}b{$quote}) c ";
            $this->assertEquals($compareString, $adapter->getSelect());
        }

        public function testAddNonSpecificCountClause()
        {
            $adapter = new RedBeanModelSelectQueryAdapter();
            $this->assertEquals(0, $adapter->getClausesCount());
            $adapter->addNonSpecificCountClause();
            $this->assertEquals(1, $adapter->getClausesCount());
            $compareString = "select count(*) ";
            $this->assertEquals($compareString, $adapter->getSelect());
        }

        public function testAddSummationClause()
        {
            $quote   = DatabaseCompatibilityUtil::getQuote();
            $adapter = new RedBeanModelSelectQueryAdapter();
            $this->assertEquals(0, $adapter->getClausesCount());
            $adapter->addSummationClause('table', 'abc', 'c');
            $this->assertEquals(1, $adapter->getClausesCount());
            $compareString = "select sum({$quote}table{$quote}.{$quote}abc{$quote}) c ";
            $this->assertEquals($compareString, $adapter->getSelect());

            $adapter = new RedBeanModelSelectQueryAdapter(true);
            $this->assertEquals(0, $adapter->getClausesCount());
            $adapter->addSummationClause('table', 'def', 'c');
            $this->assertEquals(1, $adapter->getClausesCount());
            $compareString = "select distinct sum({$quote}table{$quote}.{$quote}def{$quote}) c ";
            $this->assertEquals($compareString, $adapter->getSelect());

            $quote   = DatabaseCompatibilityUtil::getQuote();
            $adapter = new RedBeanModelSelectQueryAdapter();
            $this->assertEquals(0, $adapter->getClausesCount());
            $adapter->addSummationClause('table', 'abc', 'c', ' extra stuff');
            $this->assertEquals(1, $adapter->getClausesCount());
            $compareString = "select sum({$quote}table{$quote}.{$quote}abc{$quote} extra stuff) c ";
            $this->assertEquals($compareString, $adapter->getSelect());
        }

        public function testAddAverageClause()
        {
            $quote   = DatabaseCompatibilityUtil::getQuote();
            $adapter = new RedBeanModelSelectQueryAdapter();
            $this->assertEquals(0, $adapter->getClausesCount());
            $adapter->addAverageClause('table', 'abc', 'c');
            $this->assertEquals(1, $adapter->getClausesCount());
            $compareString = "select avg({$quote}table{$quote}.{$quote}abc{$quote}) c ";
            $this->assertEquals($compareString, $adapter->getSelect());

            $quote   = DatabaseCompatibilityUtil::getQuote();
            $adapter = new RedBeanModelSelectQueryAdapter();
            $this->assertEquals(0, $adapter->getClausesCount());
            $adapter->addAverageClause('table', 'abc', 'c', ' extra stuff');
            $this->assertEquals(1, $adapter->getClausesCount());
            $compareString = "select avg({$quote}table{$quote}.{$quote}abc{$quote} extra stuff) c ";
            $this->assertEquals($compareString, $adapter->getSelect());
        }

        public function testAddMinimumClause()
        {
            $quote   = DatabaseCompatibilityUtil::getQuote();
            $adapter = new RedBeanModelSelectQueryAdapter();
            $this->assertEquals(0, $adapter->getClausesCount());
            $adapter->addMinimumClause('table', 'abc', 'c');
            $this->assertEquals(1, $adapter->getClausesCount());
            $compareString = "select min({$quote}table{$quote}.{$quote}abc{$quote}) c ";
            $this->assertEquals($compareString, $adapter->getSelect());

            $quote   = DatabaseCompatibilityUtil::getQuote();
            $adapter = new RedBeanModelSelectQueryAdapter();
            $this->assertEquals(0, $adapter->getClausesCount());
            $adapter->addMinimumClause('table', 'abc', 'c', ' extra stuff');
            $this->assertEquals(1, $adapter->getClausesCount());
            $compareString = "select min({$quote}table{$quote}.{$quote}abc{$quote} extra stuff) c ";
            $this->assertEquals($compareString, $adapter->getSelect());
        }

        public function testAddMaximumClause()
        {
            $quote   = DatabaseCompatibilityUtil::getQuote();
            $adapter = new RedBeanModelSelectQueryAdapter();
            $this->assertEquals(0, $adapter->getClausesCount());
            $adapter->addMaximumClause('table', 'abc', 'c');
            $this->assertEquals(1, $adapter->getClausesCount());
            $compareString = "select max({$quote}table{$quote}.{$quote}abc{$quote}) c ";
            $this->assertEquals($compareString, $adapter->getSelect());

            $quote   = DatabaseCompatibilityUtil::getQuote();
            $adapter = new RedBeanModelSelectQueryAdapter();
            $this->assertEquals(0, $adapter->getClausesCount());
            $adapter->addMaximumClause('table', 'abc', 'c', ' extra stuff');
            $this->assertEquals(1, $adapter->getClausesCount());
            $compareString = "select max({$quote}table{$quote}.{$quote}abc{$quote} extra stuff) c ";
            $this->assertEquals($compareString, $adapter->getSelect());
        }

        public function testAddDayClause()
        {
            $quote   = DatabaseCompatibilityUtil::getQuote();
            $adapter = new RedBeanModelSelectQueryAdapter();
            $this->assertEquals(0, $adapter->getClausesCount());
            $adapter->addDayClause('table', 'abc', 'c');
            $this->assertEquals(1, $adapter->getClausesCount());
            $compareString = "select day({$quote}table{$quote}.{$quote}abc{$quote}) c ";
            $this->assertEquals($compareString, $adapter->getSelect());
        }

        public function testAddDayClauseWithTimeZoneAdjustment()
        {
            $quote   = DatabaseCompatibilityUtil::getQuote();
            $adapter = new RedBeanModelSelectQueryAdapter();
            $this->assertEquals(0, $adapter->getClausesCount());
            $adapter->addDayClause('table', 'abc', 'c', true);
            $this->assertEquals(1, $adapter->getClausesCount());
            $compareString = "select day({$quote}table{$quote}.{$quote}abc{$quote} - INTERVAL " .
                             abs(self::$chicagoOffsetInSeconds) . " SECOND) c ";
            $this->assertEquals($compareString, $adapter->getSelect());
        }

        public function testAddWeekClause()
        {
            $quote   = DatabaseCompatibilityUtil::getQuote();
            $adapter = new RedBeanModelSelectQueryAdapter();
            $this->assertEquals(0, $adapter->getClausesCount());
            $adapter->addWeekClause('table', 'abc', 'c');
            $this->assertEquals(1, $adapter->getClausesCount());
            $compareString = "select week({$quote}table{$quote}.{$quote}abc{$quote}) c ";
            $this->assertEquals($compareString, $adapter->getSelect());
        }

        public function testAddWeekClauseWithTimeZoneAdjustment()
        {
            $quote   = DatabaseCompatibilityUtil::getQuote();
            $adapter = new RedBeanModelSelectQueryAdapter();
            $this->assertEquals(0, $adapter->getClausesCount());
            $adapter->addWeekClause('table', 'abc', 'c', true);
            $this->assertEquals(1, $adapter->getClausesCount());
            $compareString = "select week({$quote}table{$quote}.{$quote}abc{$quote} - INTERVAL " .
                             abs(self::$chicagoOffsetInSeconds) . " SECOND) c ";
            $this->assertEquals($compareString, $adapter->getSelect());
        }

        public function testAddMonthClause()
        {
            $quote   = DatabaseCompatibilityUtil::getQuote();
            $adapter = new RedBeanModelSelectQueryAdapter();
            $this->assertEquals(0, $adapter->getClausesCount());
            $adapter->addMonthClause('table', 'abc', 'c');
            $this->assertEquals(1, $adapter->getClausesCount());
            $compareString = "select month({$quote}table{$quote}.{$quote}abc{$quote}) c ";
            $this->assertEquals($compareString, $adapter->getSelect());
        }

        public function testAddMonthClauseWithTimeZoneAdjustment()
        {
            $quote   = DatabaseCompatibilityUtil::getQuote();
            $adapter = new RedBeanModelSelectQueryAdapter();
            $this->assertEquals(0, $adapter->getClausesCount());
            $adapter->addMonthClause('table', 'abc', 'c', true);
            $this->assertEquals(1, $adapter->getClausesCount());
            $compareString = "select month({$quote}table{$quote}.{$quote}abc{$quote} - INTERVAL " .
                             abs(self::$chicagoOffsetInSeconds) . " SECOND) c ";
            $this->assertEquals($compareString, $adapter->getSelect());
        }

        public function testAddQuarterClause()
        {
            $quote   = DatabaseCompatibilityUtil::getQuote();
            $adapter = new RedBeanModelSelectQueryAdapter();
            $this->assertEquals(0, $adapter->getClausesCount());
            $adapter->addQuarterClause('table', 'abc', 'c');
            $this->assertEquals(1, $adapter->getClausesCount());
            $compareString = "select quarter({$quote}table{$quote}.{$quote}abc{$quote}) c ";
            $this->assertEquals($compareString, $adapter->getSelect());
        }

        public function testAddQuarterClauseWithTimeZoneAdjustment()
        {
            $quote   = DatabaseCompatibilityUtil::getQuote();
            $adapter = new RedBeanModelSelectQueryAdapter();
            $this->assertEquals(0, $adapter->getClausesCount());
            $adapter->addQuarterClause('table', 'abc', 'c', true);
            $this->assertEquals(1, $adapter->getClausesCount());
            $compareString = "select quarter({$quote}table{$quote}.{$quote}abc{$quote} - INTERVAL " .
                             abs(self::$chicagoOffsetInSeconds) . " SECOND) c ";
            $this->assertEquals($compareString, $adapter->getSelect());
        }

        public function testAddYearClause()
        {
            $quote   = DatabaseCompatibilityUtil::getQuote();
            $adapter = new RedBeanModelSelectQueryAdapter();
            $this->assertEquals(0, $adapter->getClausesCount());
            $adapter->addYearClause('table', 'abc', 'c');
            $this->assertEquals(1, $adapter->getClausesCount());
            $compareString = "select year({$quote}table{$quote}.{$quote}abc{$quote}) c ";
            $this->assertEquals($compareString, $adapter->getSelect());
        }

        public function testAddYearClauseWithTimeZoneAdjustment()
        {
            $quote   = DatabaseCompatibilityUtil::getQuote();
            $adapter = new RedBeanModelSelectQueryAdapter();
            $this->assertEquals(0, $adapter->getClausesCount());
            $adapter->addYearClause('table', 'abc', 'c', true);
            $this->assertEquals(1, $adapter->getClausesCount());
            $compareString = "select year({$quote}table{$quote}.{$quote}abc{$quote} - INTERVAL " .
                             abs(self::$chicagoOffsetInSeconds) . " SECOND) c ";
            $this->assertEquals($compareString, $adapter->getSelect());
        }

        public function testResolveIdClause()
        {
            $quote   = DatabaseCompatibilityUtil::getQuote();
            $adapter = new RedBeanModelSelectQueryAdapter();
            $this->assertEquals(0, $adapter->getClausesCount());
            $adapter->resolveIdClause('xModel', 'yTableAlias');
            $this->assertEquals(1, $adapter->getClausesCount());
            $adapter->resolveIdClause('xModel', 'yTableAlias');
            $this->assertEquals(1, $adapter->getClausesCount());
            $adapter->resolveIdClause('xModel', 'zTableAlias');
            $this->assertEquals(2, $adapter->getClausesCount());
        }

        public function testResolveForAliasName()
        {
            $quote   = DatabaseCompatibilityUtil::getQuote();
            $adapter = new RedBeanModelSelectQueryAdapter();
            $content = $adapter->resolveForAliasName('abc');
            $this->assertEquals('abc', $content);
            $content = $adapter->resolveForAliasName('abc', 'def');
            $this->assertEquals('abc def', $content);
        }

        public function testAddClauseByQueryString()
        {
            $adapter = new RedBeanModelSelectQueryAdapter();
            $this->assertEquals(0, $adapter->getClausesCount());
            $adapter->addClauseByQueryString('querystring');
            $this->assertEquals(1, $adapter->getClausesCount());
            $compareString = "select querystring ";
            $this->assertEquals($compareString, $adapter->getSelect());

            //Test with aliasName
            $quote   = DatabaseCompatibilityUtil::getQuote();
            $adapter = new RedBeanModelSelectQueryAdapter();
            $this->assertEquals(0, $adapter->getClausesCount());
            $adapter->addClauseByQueryString('querystring', 'aliasName');
            $this->assertEquals(1, $adapter->getClausesCount());
            $compareString = "select querystring aliasName ";
            $this->assertEquals($compareString, $adapter->getSelect());
        }
    }
?>