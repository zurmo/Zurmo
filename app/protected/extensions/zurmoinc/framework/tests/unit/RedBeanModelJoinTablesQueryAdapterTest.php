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
     * Testing joinTablesQueryAdapter
     */
    class RedBeanModelJoinTablesQueryAdapterTest extends BaseTest
    {
        public function testAddLeftTable()
        {
            $quote = DatabaseCompatibilityUtil::getQuote();
            $adapter = new RedBeanModelJoinTablesQueryAdapter('QueryFromModel');
            $alias = $adapter->addLeftTableAndGetAliasName('zz', 'joinid');
            $this->assertEquals('zz', $alias);
            $this->assertEquals(0, $adapter->getFromTableJoinCount());
            $this->assertEquals(1, $adapter->getLeftTableJoinCount());
            $fromPart   = $adapter->getJoinFromQueryPart();
            $joinPart   = $adapter->getJoinQueryPart();
            $wherePart  = $adapter->getJoinWhereQueryPart();
            $compareFromPart   = null;
            $compareJoinPart   = "left join {$quote}zz{$quote} ";
            $compareJoinPart  .= "on {$quote}zz{$quote}.{$quote}id{$quote} = {$quote}queryfrommodel{$quote}.{$quote}joinid{$quote} ";
            $compareWherePart  = null;
            $this->assertEquals($compareFromPart,  $fromPart);
            $this->assertEquals($compareJoinPart,  $joinPart);
            $this->assertEquals($compareWherePart, $wherePart);

            //Add a second left join
            $alias = $adapter->addLeftTableAndGetAliasName('a', 'joinid');
            $this->assertEquals('a', $alias);
            $this->assertEquals(0, $adapter->getFromTableJoinCount());
            $this->assertEquals(2, $adapter->getLeftTableJoinCount());
            $fromPart   = $adapter->getJoinFromQueryPart();
            $joinPart   = $adapter->getJoinQueryPart();
            $wherePart  = $adapter->getJoinWhereQueryPart();
            $compareFromPart   = null;
            $compareJoinPart   = "left join {$quote}zz{$quote} ";
            $compareJoinPart  .= "on {$quote}zz{$quote}.{$quote}id{$quote} = {$quote}queryfrommodel{$quote}.{$quote}joinid{$quote} ";
            $compareJoinPart  .= "left join {$quote}a{$quote} ";
            $compareJoinPart  .= "on {$quote}a{$quote}.{$quote}id{$quote} = {$quote}queryfrommodel{$quote}.{$quote}joinid{$quote} ";
            $compareWherePart  = null;
            $this->assertEquals($compareFromPart,  $fromPart);
            $this->assertEquals($compareJoinPart,  $joinPart);
            $this->assertEquals($compareWherePart, $wherePart);

            //Add a third left join that repeats an existing left join table
            $alias = $adapter->addLeftTableAndGetAliasName('zz', 'otherjoinid');
            $this->assertEquals('zz1', $alias);
            $this->assertEquals(0, $adapter->getFromTableJoinCount());
            $this->assertEquals(3, $adapter->getLeftTableJoinCount());
            $fromPart   = $adapter->getJoinFromQueryPart();
            $joinPart   = $adapter->getJoinQueryPart();
            $wherePart  = $adapter->getJoinWhereQueryPart();
            $compareFromPart   = null;
            $compareJoinPart   = "left join {$quote}zz{$quote} ";
            $compareJoinPart  .= "on {$quote}zz{$quote}.{$quote}id{$quote} = {$quote}queryfrommodel{$quote}.{$quote}joinid{$quote} ";
            $compareJoinPart  .= "left join {$quote}a{$quote} ";
            $compareJoinPart  .= "on {$quote}a{$quote}.{$quote}id{$quote} = {$quote}queryfrommodel{$quote}.{$quote}joinid{$quote} ";
            $compareJoinPart  .= "left join {$quote}zz{$quote} zz1 ";
            $compareJoinPart  .= "on {$quote}zz1{$quote}.{$quote}id{$quote} = {$quote}queryfrommodel{$quote}.{$quote}otherjoinid{$quote} ";
            $compareWherePart  = null;
            $this->assertEquals($compareFromPart,  $fromPart);
            $this->assertEquals($compareJoinPart,  $joinPart);
            $this->assertEquals($compareWherePart, $wherePart);

            //Add a fourth left join that is a repeat of not only an existing table, but an existing join relationship.
            $alias = $adapter->addLeftTableAndGetAliasName('zz', 'otherjoinid');
            $this->assertEquals('zz1', $alias);
            $this->assertEquals(0, $adapter->getFromTableJoinCount());
            $this->assertEquals(3, $adapter->getLeftTableJoinCount());
            $fromPart   = $adapter->getJoinFromQueryPart();
            $joinPart   = $adapter->getJoinQueryPart();
            $wherePart  = $adapter->getJoinWhereQueryPart();
            $compareFromPart   = null;
            $compareJoinPart   = "left join {$quote}zz{$quote} ";
            $compareJoinPart  .= "on {$quote}zz{$quote}.{$quote}id{$quote} = {$quote}queryfrommodel{$quote}.{$quote}joinid{$quote} ";
            $compareJoinPart  .= "left join {$quote}a{$quote} ";
            $compareJoinPart  .= "on {$quote}a{$quote}.{$quote}id{$quote} = {$quote}queryfrommodel{$quote}.{$quote}joinid{$quote} ";
            $compareJoinPart  .= "left join {$quote}zz{$quote} zz1 ";
            $compareJoinPart  .= "on {$quote}zz1{$quote}.{$quote}id{$quote} = {$quote}queryfrommodel{$quote}.{$quote}otherjoinid{$quote} ";
            $compareWherePart  = null;
            $this->assertEquals($compareFromPart,  $fromPart);
            $this->assertEquals($compareJoinPart,  $joinPart);
            $this->assertEquals($compareWherePart, $wherePart);

            //Add a fifth left join that repeats the main from table that the adapter was constructed with
            $alias = $adapter->addLeftTableAndGetAliasName('queryfrommodel', 'joinid');
            $this->assertEquals('queryfrommodel1', $alias);
            $this->assertEquals(0, $adapter->getFromTableJoinCount());
            $this->assertEquals(4, $adapter->getLeftTableJoinCount());
            $fromPart   = $adapter->getJoinFromQueryPart();
            $joinPart   = $adapter->getJoinQueryPart();
            $wherePart  = $adapter->getJoinWhereQueryPart();
            $compareFromPart   = null;
            $compareJoinPart   = "left join {$quote}zz{$quote} ";
            $compareJoinPart  .= "on {$quote}zz{$quote}.{$quote}id{$quote} = {$quote}queryfrommodel{$quote}.{$quote}joinid{$quote} ";
            $compareJoinPart  .= "left join {$quote}a{$quote} ";
            $compareJoinPart  .= "on {$quote}a{$quote}.{$quote}id{$quote} = {$quote}queryfrommodel{$quote}.{$quote}joinid{$quote} ";
            $compareJoinPart  .= "left join {$quote}zz{$quote} zz1 ";
            $compareJoinPart  .= "on {$quote}zz1{$quote}.{$quote}id{$quote} = {$quote}queryfrommodel{$quote}.{$quote}otherjoinid{$quote} ";
            $compareJoinPart  .= "left join {$quote}queryfrommodel{$quote} queryfrommodel1 ";
            $compareJoinPart  .= "on {$quote}queryfrommodel1{$quote}.{$quote}id{$quote} = {$quote}queryfrommodel{$quote}.{$quote}joinid{$quote} ";
            $compareWherePart  = null;
            $this->assertEquals($compareFromPart,  $fromPart);
            $this->assertEquals($compareJoinPart,  $joinPart);
            $this->assertEquals($compareWherePart, $wherePart);
        }

        public function testAddFromTable()
        {
            $quote = DatabaseCompatibilityUtil::getQuote();
            $adapter = new RedBeanModelJoinTablesQueryAdapter('QueryFromModel');
            $alias = $adapter->addFromTableAndGetAliasName('zz', 'somejoinid');
            $this->assertEquals(1, $adapter->getFromTableJoinCount());
            $this->assertEquals(0, $adapter->getLeftTableJoinCount());
            $this->assertEquals('zz', $alias);
            $fromPart   = $adapter->getJoinFromQueryPart();
            $joinPart   = $adapter->getJoinQueryPart();
            $wherePart  = $adapter->getJoinWhereQueryPart();
            $compareFromPart  = "{$quote}zz{$quote}";
            $compareJoinPart  = null;
            $compareWherePart = "{$quote}zz{$quote}.{$quote}id{$quote} = {$quote}queryfrommodel{$quote}.{$quote}somejoinid{$quote}";
            $this->assertEquals($compareFromPart,  $fromPart);
            $this->assertEquals($compareJoinPart,  $joinPart);
            $this->assertEquals($compareWherePart, $wherePart);

            //Add second from table
            $alias = $adapter->addFromTableAndGetAliasName('a', 'somejoinid');
            $this->assertEquals(2, $adapter->getFromTableJoinCount());
            $this->assertEquals(0, $adapter->getLeftTableJoinCount());
            $this->assertEquals('a', $alias);
            $fromPart   = $adapter->getJoinFromQueryPart();
            $joinPart   = $adapter->getJoinQueryPart();
            $wherePart  = $adapter->getJoinWhereQueryPart();
            $compareFromPart  = "{$quote}zz{$quote}, {$quote}a{$quote}";
            $compareJoinPart  = null;
            $compareWherePart = "{$quote}zz{$quote}.{$quote}id{$quote} = {$quote}queryfrommodel{$quote}.{$quote}somejoinid{$quote} and ";
            $compareWherePart .= "{$quote}a{$quote}.{$quote}id{$quote} = {$quote}queryfrommodel{$quote}.{$quote}somejoinid{$quote}";
            $this->assertEquals($compareFromPart,  $fromPart);
            $this->assertEquals($compareJoinPart,  $joinPart);
            $this->assertEquals($compareWherePart, $wherePart);

            //add third from table that was a table alread added
            $alias = $adapter->addFromTableAndGetAliasName('zz', 'somejoinid');
            $this->assertEquals(3, $adapter->getFromTableJoinCount());
            $this->assertEquals(0, $adapter->getLeftTableJoinCount());
            $this->assertEquals('zz1', $alias);
            $fromPart   = $adapter->getJoinFromQueryPart();
            $joinPart   = $adapter->getJoinQueryPart();
            $wherePart  = $adapter->getJoinWhereQueryPart();
            $compareFromPart  = "{$quote}zz{$quote}, {$quote}a{$quote}, {$quote}zz{$quote} zz1";
            $compareJoinPart  = null;
            $compareWherePart = "{$quote}zz{$quote}.{$quote}id{$quote} = {$quote}queryfrommodel{$quote}.{$quote}somejoinid{$quote} and ";
            $compareWherePart .= "{$quote}a{$quote}.{$quote}id{$quote} = {$quote}queryfrommodel{$quote}.{$quote}somejoinid{$quote} and ";
            $compareWherePart .= "{$quote}zz1{$quote}.{$quote}id{$quote} = {$quote}queryfrommodel{$quote}.{$quote}somejoinid{$quote}";
            $this->assertEquals($compareFromPart,  $fromPart);
            $this->assertEquals($compareJoinPart,  $joinPart);
            $this->assertEquals($compareWherePart, $wherePart);

            //Add a fourth table, that is a left join.
            $alias = $adapter->addLeftTableAndGetAliasName('z', 'joinid');
            $this->assertEquals(3, $adapter->getFromTableJoinCount());
            $this->assertEquals(1, $adapter->getLeftTableJoinCount());
            $this->assertEquals('z', $alias);
            $fromPart   = $adapter->getJoinFromQueryPart();
            $joinPart   = $adapter->getJoinQueryPart();
            $wherePart  = $adapter->getJoinWhereQueryPart();
            $compareFromPart   = "{$quote}zz{$quote}, {$quote}a{$quote}, {$quote}zz{$quote} zz1";
            $compareJoinPart   = "left join {$quote}z{$quote} ";
            $compareJoinPart  .= "on {$quote}z{$quote}.{$quote}id{$quote} = {$quote}queryfrommodel{$quote}.{$quote}joinid{$quote} ";
            $compareWherePart  = "{$quote}zz{$quote}.{$quote}id{$quote} = {$quote}queryfrommodel{$quote}.{$quote}somejoinid{$quote} and ";
            $compareWherePart .= "{$quote}a{$quote}.{$quote}id{$quote} = {$quote}queryfrommodel{$quote}.{$quote}somejoinid{$quote} and ";
            $compareWherePart .= "{$quote}zz1{$quote}.{$quote}id{$quote} = {$quote}queryfrommodel{$quote}.{$quote}somejoinid{$quote}";
            $this->assertEquals($compareFromPart,  $fromPart);
            $this->assertEquals($compareJoinPart,  $joinPart);
            $this->assertEquals($compareWherePart, $wherePart);

            //Add a fifth table, that is a left join of an existing from table.
            $alias = $adapter->addLeftTableAndGetAliasName('queryfrommodel', 'joinid');
            $this->assertEquals('queryfrommodel1', $alias);
            $this->assertEquals(3, $adapter->getFromTableJoinCount());
            $this->assertEquals(2, $adapter->getLeftTableJoinCount());
            $fromPart   = $adapter->getJoinFromQueryPart();
            $joinPart   = $adapter->getJoinQueryPart();
            $wherePart  = $adapter->getJoinWhereQueryPart();
            $compareFromPart   = "{$quote}zz{$quote}, {$quote}a{$quote}, {$quote}zz{$quote} zz1";
            $compareJoinPart   = "left join {$quote}z{$quote}";
            $compareJoinPart  .= " on {$quote}z{$quote}.{$quote}id{$quote} = {$quote}queryfrommodel{$quote}.{$quote}joinid{$quote}";
            $compareJoinPart  .= " left join {$quote}queryfrommodel{$quote} queryfrommodel1";
            $compareJoinPart  .= " on {$quote}queryfrommodel1{$quote}.{$quote}id{$quote} = {$quote}queryfrommodel{$quote}.{$quote}joinid{$quote} ";
            $compareWherePart  = "{$quote}zz{$quote}.{$quote}id{$quote} = {$quote}queryfrommodel{$quote}.{$quote}somejoinid{$quote} and ";
            $compareWherePart .= "{$quote}a{$quote}.{$quote}id{$quote} = {$quote}queryfrommodel{$quote}.{$quote}somejoinid{$quote} and ";
            $compareWherePart .= "{$quote}zz1{$quote}.{$quote}id{$quote} = {$quote}queryfrommodel{$quote}.{$quote}somejoinid{$quote}";
            $this->assertEquals($compareFromPart,  $fromPart);
            $this->assertEquals($compareJoinPart,  $joinPart);
            $this->assertEquals($compareWherePart, $wherePart);

            //Add a sixth table that exists already to demonstrate the alias working for a third table.
            //You must use a different join id so that it creates a new alias. using cjoinid instead of joinid
            $alias = $adapter->addLeftTableAndGetAliasName('queryfrommodel', 'cjoinid');
            $this->assertEquals('queryfrommodel2', $alias);

            //If we just use joinid, then it will not create a new alias.
            $alias = $adapter->addLeftTableAndGetAliasName('queryfrommodel', 'joinid');
            $this->assertEquals('queryfrommodel1', $alias);
            $alias = $adapter->addLeftTableAndGetAliasName('queryfrommodel', 'cjoinid');
            $this->assertEquals('queryfrommodel2', $alias);
        }

        public function testAddLeftTableAndGetAliasNameWithSpecifiedOnTableAliasName()
        {
            $quote = DatabaseCompatibilityUtil::getQuote();
            $adapter = new RedBeanModelJoinTablesQueryAdapter('QueryFromModel');
            $alias = $adapter->addLeftTableAndGetAliasName('zz', 'joinid');
            $this->assertEquals('zz', $alias);
            $this->assertEquals(0, $adapter->getFromTableJoinCount());
            $this->assertEquals(1, $adapter->getLeftTableJoinCount());
            $fromPart   = $adapter->getJoinFromQueryPart();
            $joinPart   = $adapter->getJoinQueryPart();
            $wherePart  = $adapter->getJoinWhereQueryPart();
            $compareFromPart   = null;
            $compareJoinPart   = "left join {$quote}zz{$quote} ";
            $compareJoinPart  .= "on {$quote}zz{$quote}.{$quote}id{$quote} = {$quote}queryfrommodel{$quote}.{$quote}joinid{$quote} ";
            $compareWherePart  = null;
            $this->assertEquals($compareFromPart,  $fromPart);
            $this->assertEquals($compareJoinPart,  $joinPart);
            $this->assertEquals($compareWherePart, $wherePart);

            //Now add a specified onTableAliasName
            $alias = $adapter->addLeftTableAndGetAliasName('xyz', 'ajoinid', 'zz');
            $this->assertEquals('xyz', $alias);
            $this->assertEquals(0, $adapter->getFromTableJoinCount());
            $this->assertEquals(2, $adapter->getLeftTableJoinCount());
            $fromPart   = $adapter->getJoinFromQueryPart();
            $joinPart   = $adapter->getJoinQueryPart();
            $wherePart  = $adapter->getJoinWhereQueryPart();
            $compareFromPart   = null;
            $compareJoinPart   = "left join {$quote}zz{$quote} ";
            $compareJoinPart  .= "on {$quote}zz{$quote}.{$quote}id{$quote} = {$quote}queryfrommodel{$quote}.{$quote}joinid{$quote} ";
            $compareJoinPart  .= "left join {$quote}xyz{$quote} ";
            $compareJoinPart  .= "on {$quote}xyz{$quote}.{$quote}id{$quote} = {$quote}zz{$quote}.{$quote}ajoinid{$quote} ";
            $compareWherePart  = null;
            $this->assertEquals($compareFromPart,  $fromPart);
            $this->assertEquals($compareJoinPart,  $joinPart);
            $this->assertEquals($compareWherePart, $wherePart);
        }
    }
?>
