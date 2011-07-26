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

    class ImportDatabaseUtilTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testMakeDatabaseTableByFileHandleAndTableName()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $testTableName = 'testimporttable';
            $this->assertTrue(ImportTestHelper::createTempTableByFileNameAndTableName('importTest.csv', $testTableName));
            $sql = 'select * from ' . $testTableName;
            $tempTableData = R::getAll($sql);
            $compareData   = array(
                array
                (
                    'id' => 1,
                    'column_0' => 'name',
                    'column_1' => 'phone',
                    'column_2' => 'industry',
                ),
                array
                (
                    'id' => 2,
                    'column_0' => 'abc',
                    'column_1' => '123',
                    'column_2' => 'a',
                ),
                array
                (
                    'id' => 3,
                    'column_0' => 'def',
                    'column_1' => '563',
                    'column_2' => 'b',
                ),
                array
                (
                    'id' => 4,
                    'column_0' => 'efg',
                    'column_1' => '456',
                    'column_2' => 'a',
                ),
                array
                (
                    'id' => 5,
                    'column_0' => 'we1s',
                    'column_1' => null,
                    'column_2' => 'b',
                ),
            );
            $this->assertEquals($compareData, $tempTableData);

            //Now test that using a different file on an existing temporary table will delete the temporary table first
            $this->assertTrue(ImportTestHelper::createTempTableByFileNameAndTableName('importTest2.csv', $testTableName));
            $sql = 'select * from ' . $testTableName;
            $tempTableData = R::getAll($sql);
            $compareData   = array(
                array
                (
                    'id' => 1,
                    'column_0' => 'def',
                    'column_1' => '563',
                    'column_2' => 'b',
                ),
                array
                (
                    'id' => 2,
                    'column_0' => 'efg',
                    'column_1' => '456',
                    'column_2' => 'a',
                ),
            );
            $this->assertEquals($compareData, $tempTableData);
        }
    }
?>
