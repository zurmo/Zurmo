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

    class SQLOperatorUtilTest extends BaseTest
    {
        public function testIsValidOperatorTypeByValue()
        {
            $this->assertTrue(SQLOperatorUtil::isValidOperatorTypeByValue  ('startsWith', 'abc'));
            $this->assertTrue(SQLOperatorUtil::isValidOperatorTypeByValue  ('greaterThan', 'abc'));
            $this->assertFalse(SQLOperatorUtil::isValidOperatorTypeByValue ('startsWith', 5));
            $this->assertTrue(SQLOperatorUtil::isValidOperatorTypeByValue  ('greaterThan', 5));
            $this->assertFalse(SQLOperatorUtil::isValidOperatorTypeByValue ('doesNotMatter', null));
            $this->assertTrue(SQLOperatorUtil::isValidOperatorTypeByValue  ('oneOf', array(4, 5, 6)));
            $this->assertFalse(SQLOperatorUtil::isValidOperatorTypeByValue ('oneOf', null));
            $this->assertTrue(SQLOperatorUtil::isValidOperatorTypeByValue  ('greaterThanOrEqualTo', 'abc'));
            $this->assertTrue(SQLOperatorUtil::isValidOperatorTypeByValue  ('lessThanOrEqualTo', 'abc'));
            $this->assertTrue(SQLOperatorUtil::isValidOperatorTypeByValue ('isNull', null));
            $this->assertTrue(SQLOperatorUtil::isValidOperatorTypeByValue ('isNotNull', null));
            $this->assertTrue(SQLOperatorUtil::isValidOperatorTypeByValue ('isEmpty', null));
            $this->assertTrue(SQLOperatorUtil::isValidOperatorTypeByValue ('isNotEmpty', null));
        }

        public function testGetOperatorByType()
        {
            $this->assertEquals('>', SQLOperatorUtil::getOperatorByType('greaterThan'));
        }

        public function testResolveOperatorAndValueForOneOf()
        {
            $queryPart = SQLOperatorUtil::resolveOperatorAndValueForOneOf('oneOf', array(5, 6, 7));
            $compareQueryPart = "IN(5,6,7)"; // Not Coding Standard
            $this->assertEquals($compareQueryPart, $queryPart);
            $queryPart = SQLOperatorUtil::resolveOperatorAndValueForOneOf('oneOf', array('a', 'b', 'c'));
            $compareQueryPart = "IN('a','b','c')"; // Not Coding Standard
            $this->assertEquals($compareQueryPart, $queryPart);
        }

        public function testResolveOperatorAndValueForNullOrEmpty()
        {
            $queryPart = SQLOperatorUtil::resolveOperatorAndValueForNullOrEmpty('isNull');
            $compareQueryPart = "IS NULL"; // Not Coding Standard
            $this->assertEquals($compareQueryPart, $queryPart);
            $queryPart = SQLOperatorUtil::resolveOperatorAndValueForNullOrEmpty('isNotNull');
            $compareQueryPart = "IS NOT NULL"; // Not Coding Standard
            $this->assertEquals($compareQueryPart, $queryPart);
            $queryPart = SQLOperatorUtil::resolveOperatorAndValueForNullOrEmpty('isEmpty');
            $compareQueryPart = "= ''"; // Not Coding Standard
            $this->assertEquals($compareQueryPart, $queryPart);
            $queryPart = SQLOperatorUtil::resolveOperatorAndValueForNullOrEmpty('isNotEmpty');
            $compareQueryPart = "!= ''"; // Not Coding Standard
            $this->assertEquals($compareQueryPart, $queryPart);
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testResolveOperatorAndValueForOneOfUnsupportedValue()
        {
            SQLOperatorUtil::resolveOperatorAndValueForOneOf('oneOf', array(array()));
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testGetOperatorByTypeForNullIsUnsupported()
        {
            SQLOperatorUtil::GetOperatorByType('isNull');
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testGetOperatorByTypeForNotNullIsUnsupported()
        {
            SQLOperatorUtil::GetOperatorByType('isNotNull');
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testGetOperatorByTypeForEmptyIsUnsupported()
        {
            SQLOperatorUtil::GetOperatorByType('isEmpty');
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testGetOperatorByTypeForNotEmptyIsUnsupported()
        {
            SQLOperatorUtil::GetOperatorByType('isNotEmpty');
        }

        public function testResolveOperatorAndValueForOneOfWithEscapedContent()
        {
            $queryPart = SQLOperatorUtil::resolveOperatorAndValueForOneOf('oneOf', array('a', "b'd", 'c'));
            $compareQueryPart = "IN(lower('a'),lower('b\'d'),lower('c'))"; // Not Coding Standard
            $this->assertEquals($compareQueryPart, $queryPart);
        }

        public function testDoesOperatorTypeAllowNullValues()
        {
            $this->assertTrue(SQLOperatorUtil::doesOperatorTypeAllowNullValues('isNull'));
            $this->assertTrue(SQLOperatorUtil::doesOperatorTypeAllowNullValues('isEmpty'));
            $this->assertTrue(SQLOperatorUtil::doesOperatorTypeAllowNullValues('isNotNull'));
            $this->assertTrue(SQLOperatorUtil::doesOperatorTypeAllowNullValues('isNotEmpty'));
            $this->assertFalse(SQLOperatorUtil::doesOperatorTypeAllowNullValues('startsWith'));
        }
    }
?>