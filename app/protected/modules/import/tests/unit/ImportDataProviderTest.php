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

    class ImportDataProviderTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testGetData()
        {
            $testTableName = 'testimporttable';
            ImportTestHelper::createTempTableByFileNameAndTableName('importTest.csv', $testTableName);
            $config = array('pagination' => array('pageSize' => 1));
            $dataProvider = new ImportDataProvider($testTableName, false, $config);
            $data = $dataProvider->getData();
            $this->assertEquals(1, count($data));
            $firstBean = current($data);
            $this->assertEquals(1, $firstBean->id);
            $this->assertEquals('name',     $firstBean->column_0);
            $this->assertEquals('phone',    $firstBean->column_1);
            $this->assertEquals('industry', $firstBean->column_2);
            $dataProvider = new ImportDataProvider($testTableName, true, $config);
            $data = $dataProvider->getData();
            $this->assertEquals(1, count($data));
            $firstBean = current($data);
            $this->assertEquals(2, $firstBean->id);
            $this->assertEquals('abc',     $firstBean->column_0);
            $this->assertEquals('123',    $firstBean->column_1);
            $this->assertEquals('a', $firstBean->column_2);

            //Test getting the pager content
            $content = ImportDataProviderPagerUtil::renderPagerAndHeaderTextContent($dataProvider, 'aurl');
            $this->assertNotNull($content);
            //todo: add more pager content tests, based on offset changes.
        }

        /**
         * @depends testGetData
         */
        public function testGetDataFilteredByStatus()
        {
            $testTableName = 'testimporttable';
            ImportTestHelper::createTempTableByFileNameAndTableName('importTest.csv', $testTableName);
            $config = array('pagination' => array('pageSize' => 99));
            $dataProvider = new ImportDataProvider($testTableName, true, $config);
            $data = $dataProvider->getData();
            $this->assertEquals(4, count($data));
            R::exec("update " . $testTableName . " set status = " . ImportRowDataResultsUtil::ERROR . " where id != 1 limit 1");

            //Filter by error status.
            $dataProvider = new ImportDataProvider($testTableName, true, $config, ImportRowDataResultsUtil::ERROR);
            $data = $dataProvider->getData();
            $this->assertEquals(1, count($data));

            //Do without a filter
            $dataProvider = new ImportDataProvider($testTableName, true, $config);
            $data = $dataProvider->getData();
            $this->assertEquals(4, count($data));
        }
    }
?>