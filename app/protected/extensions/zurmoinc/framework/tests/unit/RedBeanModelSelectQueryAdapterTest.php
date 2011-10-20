<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    class RedBeanModelSelectQueryAdapterTest extends BaseTest
    {
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

        public function testAddSummationClause()
        {
            $quote   = DatabaseCompatibilityUtil::getQuote();
            $adapter = new RedBeanModelSelectQueryAdapter();
            $this->assertEquals(0, $adapter->getClausesCount());
            $adapter->addSummationClause('abc', 'c');
            $this->assertEquals(1, $adapter->getClausesCount());
            $compareString = "select sum(abc) c ";
            $this->assertEquals($compareString, $adapter->getSelect());

            $adapter = new RedBeanModelSelectQueryAdapter(true);
            $this->assertEquals(0, $adapter->getClausesCount());
            $adapter->addSummationClause('def', 'c');
            $this->assertEquals(1, $adapter->getClausesCount());
            $compareString = "select distinct sum(def) c ";
            $this->assertEquals($compareString, $adapter->getSelect());
        }
    }
?>