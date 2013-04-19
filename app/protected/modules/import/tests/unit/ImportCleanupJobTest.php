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

    class ImportCleanUpJobTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testRun()
        {
            $quote = DatabaseCompatibilityUtil::getQuote();
            //Create 2 imports, and set one with a date over a week ago (8 days ago) for the modifiedDateTime
            $import                            = new Import();
            $serializedData['importRulesType'] = 'ImportModelTestItem';
            $import->serializedData            = serialize($serializedData);
            $this->assertTrue($import->save());
            ImportTestHelper::createTempTableByFileNameAndTableName('importAnalyzerTest.csv', $import->getTempTableName());
            $modifiedDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - (60 * 60 *24 * 8));
            $sql = "Update item set modifieddatetime = '" . $modifiedDateTime . "' where id = " .
                   $import->getClassId('Item');
            R::exec($sql);
            $staleImportId = $import->id;

            $import2                            = new Import();
            $serializedData['importRulesType'] = 'ImportModelTestItem';
            $import2->serializedData            = serialize($serializedData);
            $this->assertTrue($import2->save());
            ImportTestHelper::createTempTableByFileNameAndTableName('importAnalyzerTest.csv', $import2->getTempTableName());
            $this->assertEquals(2, count(Import::getAll()));
            $row = R::getRow('show tables like "' . $import->getTempTableName(). '"');
            $this->assertNotEmpty($row);

            $job = new ImportCleanupJob();
            $this->assertTrue($job->run());
            $row = R::getRow('show tables like "' . $import->getTempTableName(). '"');
            $this->assertEmpty($row);
            $imports = Import::getAll();
            $this->assertEquals(1, count($imports));
            $this->assertEquals($import2->id, $imports[0]->id);
        }
    }
?>