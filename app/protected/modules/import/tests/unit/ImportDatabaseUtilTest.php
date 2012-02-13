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

    class ImportDatabaseUtilTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testMakeDatabaseTableByFileHandleAndTableName()
        {
            $testTableName = 'testimporttable';
            $this->assertTrue(ImportTestHelper::createTempTableByFileNameAndTableName('importTest.csv', $testTableName));
            $sql = 'select * from ' . $testTableName;
            $tempTableData = R::getAll($sql);
            $compareData   = array(
                array
                (
                    'id' => 1,
                    'column_0'           => 'name',
                    'column_1'           => 'phone',
                    'column_2'           => 'industry',
                    'status'             => null,
                    'serializedmessages' => null,
                ),
                array
                (
                    'id' => 2,
                    'column_0'           => 'abc',
                    'column_1'           => '123',
                    'column_2'           => 'a',
                    'status'             => null,
                    'serializedmessages' => null,
                ),
                array
                (
                    'id' => 3,
                    'column_0'           => 'def',
                    'column_1'           => '563',
                    'column_2'           => 'b',
                    'status'             => null,
                    'serializedmessages' => null,
                ),
                array
                (
                    'id' => 4,
                    'column_0'           => 'efg',
                    'column_1'           => '456',
                    'column_2'           => 'a',
                    'status'             => null,
                    'serializedmessages' => null,
                ),
                array
                (
                    'id' => 5,
                    'column_0'           => 'we1s',
                    'column_1'           => null,
                    'column_2'           => 'b',
                    'status'             => null,
                    'serializedmessages' => null,
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
                    'id'                 => 1,
                    'column_0'           => 'def',
                    'column_1'           => '563',
                    'column_2'           => 'b',
                    'status'             => null,
                    'serializedmessages' => null,
                ),
                array
                (
                    'id' => 2,
                    'column_0'           => 'efg',
                    'column_1'           => '456',
                    'column_2'           => 'a',
                    'status'             => null,
                    'serializedmessages' => null,
                ),
            );
            $this->assertEquals($compareData, $tempTableData);

            //Now test using a file with a different enclosure/delimiter schema.

            $testTableName = 'testimporttable';
            $this->assertTrue(ImportTestHelper::
                              createTempTableByFileNameAndTableName('importWithDifferentEnclosureAndDelimiterTest.csv',
                                                                    $testTableName, null, "#", '"'));
            $sql = 'select * from ' . $testTableName;
            $tempTableData = R::getAll($sql);
            $compareData   = array(
                array
                (
                    'id' => 1,
                    'column_0'           => 'name',
                    'column_1'           => 'phone',
                    'column_2'           => 'industry',
                    'status'             => null,
                    'serializedmessages' => null,
                ),
                array
                (
                    'id' => 2,
                    'column_0'           => 'some',
                    'column_1'           => 'thing',
                    'column_2'           => 'else',
                    'status'             => null,
                    'serializedmessages' => null,
                ),
                array
                (
                    'id' => 3,
                    'column_0'           => 'some2',
                    'column_1'           => 'thing2',
                    'column_2'           => 'else2',
                    'status'             => null,
                    'serializedmessages' => null,
                ),
                array
                (
                    'id' => 4,
                    'column_0'           => 'some3',
                    'column_1'           => 'thing3',
                    'column_2'           => 'else3',
                    'status'             => null,
                    'serializedmessages' => null,
                ),
            );
            $this->assertEquals($compareData, $tempTableData);
        }

        /**
         * @depends testMakeDatabaseTableByFileHandleAndTableName
         */
        public function testGetColumnCountByTableName()
        {
            $this->assertEquals(5, ImportDatabaseUtil::getColumnCountByTableName('testimporttable'));
        }

        /**
         * @depends testGetColumnCountByTableName
         */
        public function testGetFirstRowByTableName()
        {
            $firstRowData = ImportDatabaseUtil::getFirstRowByTableName('testimporttable');
            $compareData   = array(
                    'id' => 1,
                    'column_0'           => 'name',
                    'column_1'           => 'phone',
                    'column_2'           => 'industry',
                    'status'             => null,
                    'serializedmessages' => null,
            );
            $this->assertEquals($compareData, $firstRowData);
        }

        /**
         * @depends testGetFirstRowByTableName
         */
        public function testGetSubset()
        {
            $firstBean = ImportDatabaseUtil::getSubset('testimporttable', null, 1, 1);
            $firstBean = current($firstBean);
            $this->assertTrue($firstBean instanceof RedBean_OODBBean);
            $this->assertEquals(2, $firstBean->id);
            $this->assertEquals('some', $firstBean->column_0);
            $this->assertEquals('thing', $firstBean->column_1);
            $this->assertEquals('else', $firstBean->column_2);
        }

        /**
         * @expectedException RedBean_Exception_SQL
         */
        public function testDropTableByTableName()
        {
            $testTableName = 'testimporttable';
            $sql           = 'select * from ' . $testTableName;
            $tempTableData = R::getAll($sql);
            $this->assertEquals(4, count($tempTableData));
            if (RedBeanDatabase::isFrozen())
            {
                ImportDatabaseUtil::dropTableByTableName($testTableName);
                $sql = 'select * from ' . $testTableName;
                R::getAll($sql);
            }
            else
            {
                //Unfrozen will not throw an exception in this type of situation.
                throw new RedBean_Exception_SQL();
            }
        }

        /**
         * @depends testDropTableByTableName
         */
        public function testGetCount()
        {
            $testTableName = 'testimporttable';
            $this->assertTrue(ImportTestHelper::createTempTableByFileNameAndTableName('importTest.csv', $testTableName));
            $count = ImportDatabaseUtil::getCount($testTableName);
            $this->assertEquals(5, $count);
            $count = ImportDatabaseUtil::getCount($testTableName, 'column_1 = "456"');
            $this->assertEquals(1, $count);
        }

        /**
         * @depends testGetCount
         */
        public function testUpdateRowAfterProcessing()
        {
            ImportDatabaseUtil::updateRowAfterProcessing('testimporttable', 2, 4, serialize(array('a' => 'b')));
            $bean = R::findOne('testimporttable', "id = :id", array('id' => 2));
            $this->assertEquals(4, $bean->status);
            $this->assertEquals(serialize(array('a' => 'b')), $bean->serializedmessages);
            $bean = R::findOne('testimporttable', "id = :id", array('id' => 1));
            $this->assertEquals(null, $bean->status);
            $this->assertEquals(null, $bean->serializedmessages);
            $bean = R::findOne('testimporttable', "id = :id", array('id' => 3));
            $this->assertEquals(null, $bean->status);
            $this->assertEquals(null, $bean->serializedmessages);
        }

        /**
        *
        * Test if import from file with Windows line-endings works file
        */
        public function testMakeDatabaseTableByFilePathAndTableNameUsingWindowsCsvFile()
        {
            $testTableName = 'testimporttable';
            //We make copy of filename, because ImportDatabaseUtil::makeDatabaseTableByFilePathAndTableName
            //convert windows line endings into linux lineendings.
            $fileName = 'importTestWindows.csv';
            $copyFileName = 'importTestWindowsCopy.csv';
            $pathToFiles = Yii::getPathOfAlias('application.modules.import.tests.unit.files');
            $filePath    = $pathToFiles . DIRECTORY_SEPARATOR . $fileName;
            $copyFilePath    = $pathToFiles . DIRECTORY_SEPARATOR . $copyFileName;
            if (is_file($copyFilePath))
            {
                unlink($copyFilePath);
            }
            $this->assertFalse(is_file($copyFilePath));
            copy($filePath, $copyFilePath);
            $this->assertTrue(is_file($copyFilePath));
            $this->assertTrue(ImportTestHelper::createTempTableByFileNameAndTableName($copyFileName, $testTableName));
            unlink($copyFilePath);
            $sql = 'select * from ' . $testTableName;
            $tempTableData = R::getAll($sql);
            $compareData   = array(
            array
            (
                                    'id' => 1,
                                    'column_0'           => 'name',
                                    'column_1'           => 'phone',
                                    'column_2'           => 'industry',
                                    'status'             => null,
                                    'serializedmessages' => null,
            ),
            array
            (
                                    'id' => 2,
                                    'column_0'           => 'abc',
                                    'column_1'           => '123',
                                    'column_2'           => 'a',
                                    'status'             => null,
                                    'serializedmessages' => null,
            ),
            array
            (
                                    'id' => 3,
                                    'column_0'           => 'def',
                                    'column_1'           => '563',
                                    'column_2'           => 'b',
                                    'status'             => null,
                                    'serializedmessages' => null,
            ),
            array
            (
                                    'id' => 4,
                                    'column_0'           => 'efg',
                                    'column_1'           => '456',
                                    'column_2'           => 'a',
                                    'status'             => null,
                                    'serializedmessages' => null,
            ),
            array
            (
                                    'id' => 5,
                                    'column_0'           => 'we1s',
                                    'column_1'           => null,
                                    'column_2'           => 'b',
                                    'status'             => null,
                                    'serializedmessages' => null,
            ),
            );
            $this->assertEquals($compareData, $tempTableData);
        }

            /**
        *
        * Test that various accents work correctly going into the database.
        */
        public function testMakeDatabaseTableFromISO188591WithAccents()
        {
            $testTableName = 'testimporttable';
            $this->assertTrue(ImportTestHelper::createTempTableByFileNameAndTableName('importISO88591WithAccentsTest.csv', $testTableName));
            $sql = 'select * from ' . $testTableName;
            $tempTableData = R::getAll($sql);
            $compareData   = array(
                array
                (
                    'id' => 1,
                    'column_0'           => 'name',
                    'status'             => null,
                    'serializedmessages' => null,
                ),
                array
                (
                    'id' => 2,
                    'column_0'           => 'didée BBB',
                    'status'             => null,
                    'serializedmessages' => null,
                ),
                array
                (
                    'id' => 3,
                    'column_0'           => 'Angêline Jone',
                    'status'             => null,
                    'serializedmessages' => null,
                ),
                array
                (
                    'id' => 4,
                    'column_0'           => 'Laura o\'brien',
                    'status'             => null,
                    'serializedmessages' => null,
                ),
            );
            $this->assertEquals($compareData, $tempTableData);
        }

        /**
        * @depends testDropTableByTableName
        */
        public function testBulkInsert()
        {
            $testTableName = 'testimporttable';
            $this->assertTrue(ImportTestHelper::createTempTableByFileNameAndTableName('importBulkTest.csv', $testTableName));
            $count = ImportDatabaseUtil::getCount($testTableName);
            $this->assertEquals(520, $count);
        }
    }
?>
