<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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
    /**
     * Models used:
     * I extends H.  I has_one G - used to test standard, casted up and relation ordering.
     *
     * TestCustomFieldsModel - used to test customFields ordering.
     *
     */
    class ModelDataProviderUtilTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testResolveSortAttributeColumnName()
        {
            $quote = DatabaseCompatibilityUtil::getQuote();

            //Test a standard non-relation attribute on I
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('I');
            $sort = ModelDataProviderUtil::resolveSortAttributeColumnName('I', $joinTablesAdapter, 'iMember');
            $this->assertEquals("{$quote}i{$quote}.{$quote}imember{$quote}", $sort);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            //Test a standard casted up attribute on H from I
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('I');
            $sort = ModelDataProviderUtil::resolveSortAttributeColumnName('I', $joinTablesAdapter, 'name');
            $this->assertEquals("{$quote}h{$quote}.{$quote}name{$quote}", $sort);
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            //Test a relation attribute G->g from H
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('H');
            $sort = ModelDataProviderUtil::resolveSortAttributeColumnName('H', $joinTablesAdapter, 'castUpHasOne', 'g');
            $this->assertEquals("{$quote}g{$quote}.{$quote}g{$quote}", $sort);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('g', $leftTables[0]['tableName']);

            //Test a relation attribute G->g where casted up from I
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('I');
            $sort = ModelDataProviderUtil::resolveSortAttributeColumnName('I', $joinTablesAdapter, 'castUpHasOne', 'g');
            $this->assertEquals("{$quote}g{$quote}.{$quote}g{$quote}", $sort);
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
            $fromTables = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals('h', $fromTables[0]['tableName']);
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('g', $leftTables[0]['tableName']);

            //Test a customField like TestCustomFieldsModel->industry
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('TestCustomFieldsModel');
            $sort = ModelDataProviderUtil::resolveSortAttributeColumnName(
                                            'TestCustomFieldsModel', $joinTablesAdapter, 'industry', 'value');
            $this->assertEquals("{$quote}customfield{$quote}.{$quote}value{$quote}", $sort);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('customfield', $leftTables[0]['tableName']);
        }
    }
?>
