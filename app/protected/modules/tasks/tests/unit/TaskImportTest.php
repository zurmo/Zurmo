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

    class TaskImportTest extends ActivityImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testSimplUserImportWhereAllRowsSucceed()
        {
            Yii::app()->user->userModel            = User::getByUsername('super');

            $tasks                              = Task::getAll();
            $this->assertEquals(0, count($tasks));
            $import                                = new Import();
            $serializedData['importRulesType']     = 'Tasks';
            $serializedData['firstRowIsHeaderRow'] = true;
            $import->serializedData                = serialize($serializedData);
            $this->assertTrue($import->save());

            ImportTestHelper::
            createTempTableByFileNameAndTableName('simpleImportTest.csv', $import->getTempTableName(),
                                                  Yii::getPathOfAlias('application.modules.tasks.tests.unit.files'));

            $this->assertEquals(4, ImportDatabaseUtil::getCount($import->getTempTableName())); // includes header rows.

            $mappingData = array(
                'column_0' => ImportMappingUtil::makeStringColumnMappingData       ('name'),
                'column_1' => ImportMappingUtil::makeDateTimeColumnMappingData     ('dueDateTime'),
                'column_2' => ImportMappingUtil::makeDateTimeColumnMappingData     ('completedDateTime'),
                'column_3' => ImportMappingUtil::makeBooleanColumnMappingData      ('completed'),
                'column_4' => ImportMappingUtil::makeModelDerivedColumnMappingData ('AccountDerived'),
                'column_5' => ImportMappingUtil::makeModelDerivedColumnMappingData ('ContactDerived'),
                'column_6' => ImportMappingUtil::makeModelDerivedColumnMappingData ('OpportunityDerived'),
                'column_7' => ImportMappingUtil::makeTextAreaColumnMappingData     ('description'),
            );

            $importRules  = ImportRulesUtil::makeImportRulesByType('Tasks');
            $page         = 0;
            $config       = array('pagination' => array('pageSize' => 50)); //This way all rows are processed.
            $dataProvider = new ImportDataProvider($import->getTempTableName(), true, $config);
            $dataProvider->getPagination()->setCurrentPage($page);
            $importResultsUtil = new ImportResultsUtil($import);
            $actionDateTime    = substr(DateTimeUtil::convertTimestampToDbFormatDateTime(time()), 0, -3);
            $messageLogger     = new ImportMessageLogger();
            ImportUtil::importByDataProvider($dataProvider,
                                             $importRules,
                                             $mappingData,
                                             $importResultsUtil,
                                             new ExplicitReadWriteModelPermissions(),
                                             $messageLogger);
            $importResultsUtil->processStatusAndMessagesForEachRow();

            //Confirm that 3 models where created.
            $tasks = Task::getAll();
            $this->assertEquals(3, count($tasks));

            $tasks = Task::getByName('task1');
            $this->assertEquals(1,                  count($tasks[0]));
            $this->assertEquals(1,                  count($tasks[0]->activityItems));
            $this->assertEquals('testAccount',      $tasks[0]->activityItems[0]->name);
            $this->assertEquals('Account',          get_class($tasks[0]->activityItems[0]));
            $this->assertNull  ($tasks[0]->completed);
            $this->assertEquals($actionDateTime, substr($tasks[0]->latestDateTime, 0, -3));

            $tasks = Task::getByName('task2');
            $this->assertEquals(1,                  count($tasks[0]));
            $this->assertEquals(1,                  count($tasks[0]->activityItems));
            $this->assertEquals('testContact',      $tasks[0]->activityItems[0]->firstName);
            $this->assertEquals('Contact',          get_class($tasks[0]->activityItems[0]));
            $this->assertEquals(1,                  $tasks[0]->completed);
            $this->assertEquals('2011-12-22 06:03', substr($tasks[0]->latestDateTime, 0, -3));

            $tasks = Task::getByName('task3');
            $this->assertEquals(1,                 count($tasks[0]));
            $this->assertEquals(1,                 count($tasks[0]->activityItems));
            $this->assertEquals('testOpportunity', $tasks[0]->activityItems[0]->name);
            $this->assertEquals('Opportunity',     get_class($tasks[0]->activityItems[0]));
            $this->assertNull  ($tasks[0]->completed);
            $this->assertEquals($actionDateTime, substr($tasks[0]->latestDateTime, 0, -3));

            //Confirm 10 rows were processed as 'created'.
            $this->assertEquals(3, ImportDatabaseUtil::getCount($import->getTempTableName(), "status = "
                                                                 . ImportRowDataResultsUtil::CREATED));

            //Confirm that 0 rows were processed as 'updated'.
            $this->assertEquals(0, ImportDatabaseUtil::getCount($import->getTempTableName(),  "status = "
                                                                 . ImportRowDataResultsUtil::UPDATED));

            //Confirm 2 rows were processed as 'errors'.
            $this->assertEquals(0, ImportDatabaseUtil::getCount($import->getTempTableName(),  "status = "
                                                                 . ImportRowDataResultsUtil::ERROR));

            $beansWithErrors = ImportDatabaseUtil::getSubset($import->getTempTableName(),     "status = "
                                                                 . ImportRowDataResultsUtil::ERROR);
            $this->assertEquals(0, count($beansWithErrors));
        }
    }
?>