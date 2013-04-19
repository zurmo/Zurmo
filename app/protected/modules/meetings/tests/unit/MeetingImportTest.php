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

    class MeetingImportTest extends ActivityImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super                      = SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = $super;
            Yii::app()->timeZoneHelper->load();
            MeetingTestHelper::createCategories();
        }

        public function testSimpleUserImportWhereAllRowsSucceed()
        {
            Yii::app()->user->userModel            = User::getByUsername('super');

            $meetings                              = Meeting::getAll();
            $this->assertEquals(0, count($meetings));
            $import                                = new Import();
            $serializedData['importRulesType']     = 'Meetings';
            $serializedData['firstRowIsHeaderRow'] = true;
            $import->serializedData                = serialize($serializedData);
            $this->assertTrue($import->save());

            ImportTestHelper::
            createTempTableByFileNameAndTableName('importAnalyzerTest.csv', $import->getTempTableName(),
                                                  Yii::getPathOfAlias('application.modules.meetings.tests.unit.files'));

            $this->assertEquals(4, ImportDatabaseUtil::getCount($import->getTempTableName())); // includes header rows.

            $mappingData = array(
                'column_0' => ImportMappingUtil::makeStringColumnMappingData       ('name'),
                'column_1' => ImportMappingUtil::makeStringColumnMappingData       ('location'),
                'column_2' => ImportMappingUtil::makeDateTimeColumnMappingData     ('startDateTime'),
                'column_3' => ImportMappingUtil::makeDateTimeColumnMappingData     ('endDateTime'),
                'column_4' => ImportMappingUtil::makeDropDownColumnMappingData     ('category'),
                'column_5' => ImportMappingUtil::makeModelDerivedColumnMappingData ('AccountDerived'),
                'column_6' => ImportMappingUtil::makeModelDerivedColumnMappingData ('ContactDerived'),
                'column_7' => ImportMappingUtil::makeModelDerivedColumnMappingData ('OpportunityDerived'),
                'column_8' => ImportMappingUtil::makeTextAreaColumnMappingData     ('description'),
            );

            $importRules  = ImportRulesUtil::makeImportRulesByType('Meetings');
            $page         = 0;
            $config       = array('pagination' => array('pageSize' => 50)); //This way all rows are processed.
            $dataProvider = new ImportDataProvider($import->getTempTableName(), true, $config);
            $dataProvider->getPagination()->setCurrentPage($page);
            $importResultsUtil = new ImportResultsUtil($import);
            $messageLogger     = new ImportMessageLogger();
            ImportUtil::importByDataProvider($dataProvider,
                                             $importRules,
                                             $mappingData,
                                             $importResultsUtil,
                                             new ExplicitReadWriteModelPermissions(),
                                             $messageLogger);
            $importResultsUtil->processStatusAndMessagesForEachRow();

            //Confirm that 3 models where created.
            $meetings = Meeting::getAll();
            $this->assertEquals(3, count($meetings));

            $meetings = Meeting::getByName('meeting1');
            $this->assertEquals(1,                  count($meetings[0]));
            $this->assertEquals(1,                  count($meetings[0]->activityItems));
            $this->assertEquals('testAccount',      $meetings[0]->activityItems[0]->name);
            $this->assertEquals('Account',          get_class($meetings[0]->activityItems[0]));
            $this->assertEquals('2011-12-22 05:03', substr($meetings[0]->latestDateTime, 0, -3));

            $meetings = Meeting::getByName('meeting2');
            $this->assertEquals(1,                  count($meetings[0]));
            $this->assertEquals(1,                  count($meetings[0]->activityItems));
            $this->assertEquals('testContact',      $meetings[0]->activityItems[0]->firstName);
            $this->assertEquals('Contact',          get_class($meetings[0]->activityItems[0]));
            $this->assertEquals('2011-12-22 05:03', substr($meetings[0]->latestDateTime, 0, -3));

            $meetings = Meeting::getByName('meeting3');
            $this->assertEquals(1,                  count($meetings[0]));
            $this->assertEquals(1,                  count($meetings[0]->activityItems));
            $this->assertEquals('testOpportunity',  $meetings[0]->activityItems[0]->name);
            $this->assertEquals('Opportunity',      get_class($meetings[0]->activityItems[0]));
            $this->assertEquals('2011-12-22 06:03', substr($meetings[0]->latestDateTime, 0, -3));

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