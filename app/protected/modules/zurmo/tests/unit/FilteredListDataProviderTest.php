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

    class FilteredListDataProviderTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testMakeWhereUsingAnd()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $metadata = array(
                'clauses' => array(
                    1 => array(
                        'attributeName' => 'name',
                        'operatorType' => 'equals',
                        'value' => 'Vomo'
                    ),
                    2 => array(
                        'attributeName' => 'billingAddress',
                        'relatedAttributeName' => 'city',
                        'operatorType' => 'startsWith',
                        'value' => 'Chicago'
                    ),
                ),
                'structure' => '1 and 2',
            );
            $quote = DatabaseCompatibilityUtil::getQuote();
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Account');
            $where = FilteredListDataProvider::makeWhere('Account', $metadata, $joinTablesAdapter);
            $this->assertEquals("({$quote}account$quote.{$quote}name$quote = lower('Vomo')) and "     .
                                "({$quote}address$quote.{$quote}city$quote like lower('Chicago%'))",
                                $where);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testMakeWhereUsingOr()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $metadata = array(
                'clauses' => array(
                    1 => array(
                        'attributeName' => 'name',
                        'operatorType' => 'equals',
                        'value' => 'Vomo'
                    ),
                    2 => array(
                        'attributeName' => 'billingAddress',
                        'relatedAttributeName' => 'city',
                        'operatorType' => 'startsWith',
                        'value' => 'Chicago'
                    ),
                ),
                'structure' => '1 or 2',
            );
            $quote = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Account');
            $where = FilteredListDataProvider::makeWhere('Account', $metadata, $joinTablesAdapter);
            $this->assertEquals("({$quote}account$quote.{$quote}name$quote = lower('Vomo')) or "     .
                                "({$quote}address$quote.{$quote}city$quote like lower('Chicago%'))",
                                $where);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testMakeWhereUsingOrMultipleRelatedParts()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $metadata = array(
                'clauses' => array(
                    1 => array(
                        'attributeName' => 'name',
                        'operatorType' => 'equals',
                        'value' => 'Vomo'
                    ),
                    2 => array(
                        'attributeName' => 'billingAddress',
                        'relatedAttributeName' => 'city',
                        'operatorType' => 'startsWith',
                        'value' => 'Chicago'
                    ),
                    3 => array(
                        'attributeName' => 'billingAddress',
                        'relatedAttributeName' => 'state',
                        'operatorType' => 'equals',
                        'value' => 'IL'
                    ),
                ),
                'structure' => '1 or 2 or 3',
            );
            $quote = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Account');
            $where = FilteredListDataProvider::makeWhere('Account', $metadata, $joinTablesAdapter);
            $this->assertEquals("({$quote}account$quote.{$quote}name$quote = lower('Vomo')) or "     .
                                "({$quote}address$quote.{$quote}city$quote like lower('Chicago%')) or " .
                                "({$quote}address$quote.{$quote}state$quote = lower('IL'))",
                                $where);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testMakeWhereUsingNoRelatedParts()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $metadata = array(
                'clauses' => array(
                    1 => array(
                        'attributeName' => 'name',
                        'operatorType' => 'equals',
                        'value' => 'Vomo'
                    ),
                ),
                'structure' => '1',
            );
            $quote = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Account');
            $where = FilteredListDataProvider::makeWhere('Account', $metadata, $joinTablesAdapter);
            $this->assertEquals("({$quote}account$quote.{$quote}name$quote = lower('Vomo'))",
                                $where);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testMakeWhereOperatorTypes()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $quote = DatabaseCompatibilityUtil::getQuote();
            $metadata = array(
                'clauses' => array(
                    1 => array(
                        'attributeName' => 'name',
                        'operatorType' => 'equals',
                        'value' => 'Vomo'
                    ),
                ),
                'structure' => '1',
            );

            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Account');
            $where = FilteredListDataProvider::makeWhere('Account', $metadata, $joinTablesAdapter);
            $this->assertEquals("({$quote}account$quote.{$quote}name$quote = lower('Vomo'))",
                                $where);

            $metadata = array(
                'clauses' => array(
                    1 => array(
                        'attributeName' => 'name',
                        'operatorType' => 'doesNotEqual',
                        'value' => 'Vomo'
                    ),
                ),
                'structure' => '1',
            );
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Account');
            $where = FilteredListDataProvider::makeWhere('Account', $metadata, $joinTablesAdapter);
            $this->assertEquals("({$quote}account$quote.{$quote}name$quote != lower('Vomo'))",
                                $where);
            $metadata = array(
                'clauses' => array(
                    1 => array(
                        'attributeName' => 'name',
                        'operatorType' => 'startsWith',
                        'value' => 'Vomo'
                    ),
                ),
                'structure' => '1',
            );
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Account');
            $where = FilteredListDataProvider::makeWhere('Account', $metadata, $joinTablesAdapter);
            $this->assertEquals("({$quote}account$quote.{$quote}name$quote like lower('Vomo%'))",
                                $where);
            $metadata = array(
                'clauses' => array(
                    1 => array(
                        'attributeName' => 'name',
                        'operatorType' => 'endsWith',
                        'value' => 'Vomo'
                    ),
                ),
                'structure' => '1',
            );
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Account');
            $where = FilteredListDataProvider::makeWhere('Account', $metadata, $joinTablesAdapter);
            $this->assertEquals("({$quote}account$quote.{$quote}name$quote like lower('%Vomo'))",
                                $where);
            $metadata = array(
                'clauses' => array(
                    1 => array(
                        'attributeName' => 'name',
                        'operatorType' => 'greaterThan',
                        'value' => 5
                    ),
                ),
                'structure' => '1',
            );
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Account');
            $where = FilteredListDataProvider::makeWhere('Account', $metadata, $joinTablesAdapter);
            $this->assertEquals("({$quote}account$quote.{$quote}name$quote > 5)",
                                $where);
            $metadata = array(
                'clauses' => array(
                    1 => array(
                        'attributeName' => 'name',
                        'operatorType' => 'lessThan',
                        'value' => 4
                    ),
                ),
                'structure' => '1',
            );
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Account');
            $where = FilteredListDataProvider::makeWhere('Account', $metadata, $joinTablesAdapter);
            $this->assertEquals("({$quote}account$quote.{$quote}name$quote < 4)",
                                $where);
        }

        public function testMakeWhereNoClause()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $metadata = array(
                'clauses' => array(
                ),
                'structure' => '',
            );
            $quote = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Account');
            $where = FilteredListDataProvider::makeWhere('Account', $metadata, $joinTablesAdapter);
            $this->assertEquals('', $where);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testMakeWhereUsingIncorrectOperatorAssignment()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $metadata = array(
                'clauses' => array(
                    1 => array(
                        'attributeName' => 'name',
                        'operatorType' => 'aWrongOperator',
                        'value' => 'Vomo'
                    ),
                ),
                'structure' => '1',
            );
            $quote = DatabaseCompatibilityUtil::getQuote();
            try
            {
                $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Account');
                $where = FilteredListDataProvider::makeWhere('Account', $metadata, $joinTablesAdapter);
                $this->fail();
            }
            catch (NotSupportedException $e)
            {
                //success
            }
        }

        public function testRedBeanModelGetForeignKeyName()
        {
            $this->assertEquals('createdbyuser__user_id',       RedBeanModel::getForeignKeyName('Account', 'createdByUser'));
            $this->assertEquals('modifiedbyuser__user_id',      RedBeanModel::getForeignKeyName('Account', 'modifiedByUser'));
            $this->assertEquals('owner__user_id',               RedBeanModel::getForeignKeyName('Account', 'owner'));
            $this->assertEquals('primaryemail_email_id',        RedBeanModel::getForeignKeyName('Account', 'primaryEmail'));
            $this->assertEquals('secondaryemail_email_id',      RedBeanModel::getForeignKeyName('Account', 'secondaryEmail'));
            $this->assertEquals('billingaddress_address_id',    RedBeanModel::getForeignKeyName('Account', 'billingAddress'));
            $this->assertEquals('shippingaddress_address_id',   RedBeanModel::getForeignKeyName('Account', 'shippingAddress'));
            $this->assertEquals('industry_ownedcustomfield_id', RedBeanModel::getForeignKeyName('Account', 'industry'));
            $this->assertEquals('type_ownedcustomfield_id',     RedBeanModel::getForeignKeyName('Account', 'type'));
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testRedBeanModelGetForeignKeyNameBarfsForBadAttribute()
        {
            RedBeanModel::getForeignKeyName('Account', 'massagedByUser');
        }

        /**
         * @depends testRedBeanModelGetForeignKeyName
         */
        public function testMakeWhereUsingCreatedOnlyFilter()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $metadata = array(
                'clauses' => array(
                    1 => array(
                        'attributeName' => 'createdByUser',
                        'operatorType'  => 'equals',
                        'value'         => 100
                    ),
                ),
                'structure' => '1',
            );
            $quote = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Account');
            $where = FilteredListDataProvider::makeWhere('Account', $metadata, $joinTablesAdapter);
            $this->assertEquals("({$quote}item$quote.{$quote}createdbyuser__user_id$quote = 100)", $where);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals('ownedsecurableitem',   $leftTables[0]['tableName']);
            $this->assertEquals('securableitem',        $leftTables[1]['tableName']);
            $this->assertEquals('item',                 $leftTables[2]['tableName']);
        }
    }
?>
