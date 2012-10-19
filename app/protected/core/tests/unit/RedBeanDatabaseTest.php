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

    class RedBeanDatabaseTest extends BaseTest
    {
        /**
         * @expectedException NotSupportedException
         */
        public function testGetDatabaseTypeFromDsnString()
        {
            $dsn = 'mysql:host=localhost;port=3306;dbname=zurmo'; // Not Coding Standard
            $databaseType = RedBeanDatabase::getDatabaseTypeFromDsnString($dsn);
            $this->assertEquals('mysql', $databaseType);

            $dsn = 'oci:host=localhost;dbname=zurmo'; // Not Coding Standard
            $databaseType = RedBeanDatabase::getDatabaseTypeFromDsnString($dsn);
            $this->assertEquals('oci', $databaseType);

            // Invalid connection string, so NotSupportedException should be thrown.
            $dsn = 'host=localhost;dbname=zurmo'; // Not Coding Standard
            $databaseType = RedBeanDatabase::getDatabaseTypeFromDsnString($dsn);
        }

        /**
         * @expectedException FailedAssertionException
         */
        public function testGetDatabaseInfoFromConnectionString()
        {
            $dsn = 'mysql:host=localhost;port=3306;dbname=zurmo'; // Not Coding Standard
            $databaseConnectionInfo = RedBeanDatabase::getDatabaseInfoFromDsnString($dsn);
            $compareData = array(
                'databaseType' => 'mysql',
                'databaseHost' => 'localhost',
                'databasePort' => '3306',
                'databaseName' => 'zurmo',
            );
            $this->assertEquals($compareData, $databaseConnectionInfo);

            $dsn = 'mysql:host=127.0.0.1;dbname=zurmo'; // Not Coding Standard
            $databaseConnectionInfo = RedBeanDatabase::getDatabaseInfoFromDsnString($dsn);
            $compareData = array(
                'databaseType' => 'mysql',
                'databaseHost' => '127.0.0.1',
                'databasePort' => '3306',
                'databaseName' => 'zurmo',
            );
            $this->assertEquals($compareData, $databaseConnectionInfo);

            $dsn = 'mysql:host=localhost;dbname=zurmo;port=3306;'; // Not Coding Standard
            $databaseConnectionInfo = RedBeanDatabase::getDatabaseInfoFromDsnString($dsn);
            $compareData = array(
                'databaseType' => 'mysql',
                'databaseHost' => 'localhost',
                'databasePort' => '3306',
                'databaseName' => 'zurmo',
            );
            $this->assertEquals($compareData, $databaseConnectionInfo);

            $dsn = 'mysql:host=localhost;'; // Not Coding Standard
            $databaseConnectionInfo = RedBeanDatabase::getDatabaseInfoFromDsnString($dsn);
        }

        /**
         * @expectedException FailedAssertionException
         */
        public function testGetDatabaseNameFromConnectionString()
        {
            $dsn = 'mysql:host=localhost;port=3306;dbname=zurmo'; // Not Coding Standard
            $databaseName = RedBeanDatabase::getDatabaseNameFromDsnString($dsn);
            $this->assertEquals('zurmo', $databaseName);

            $dsn = 'mysql:host=localhost;'; // Not Coding Standard
            $databaseName = RedBeanDatabase::getDatabaseNameFromDsnString($dsn);
        }
    }
?>