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

    class UserImportTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testSimplUserImportWhereAllRowsSucceed()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $users                      = User::getAll();
            $this->assertEquals(1, count($users));
            $import                                = new Import();
            $serializedData['importRulesType']     = 'User';
            $serializedData['firstRowIsHeaderRow'] = true;
            $import->serializedData                = serialize($serializedData);
            $this->assertTrue($import->save());

            ImportTestHelper::
            createTempTableByFileNameAndTableName('importAnalyzerTest.csv', $import->getTempTableName(),
                                                  Yii::getPathOfAlias('application.modules.users.tests.unit.files'));

            $this->assertEquals(11, ImportDatabaseUtil::getCount($import->getTempTableName())); // includes header rows.

            $mappingData = array(
                'column_0'  => array('attributeIndexOrDerivedType' => 'username',
                                         'type' => 'importColumn',
                                      'mappingRulesData' => array()),

                'column_1'  => array('attributeIndexOrDerivedType' => 'Password',
                                      'type' => 'importColumn',
                                      'mappingRulesData' => array(
                                          'PasswordDefaultValueModelAttributeMappingRuleForm' =>
                                          array('defaultValue' => null))),
                'column_2'  => array('attributeIndexOrDerivedType' => 'UserStatus',
                                      'type' => 'importColumn',
                                      'mappingRulesData' => array(
                                          'UserStatusDefaultValueMappingRuleForm' =>
                                          array('defaultValue' => UserStatusUtil::ACTIVE))),
                'column_3'  => ImportMappingUtil::makeStringColumnMappingData      ('firstName'),
                'column_4'  => ImportMappingUtil::makeStringColumnMappingData      ('lastName')
            );

            $importRules  = ImportRulesUtil::makeImportRulesByType('Users');
            $page         = 0;
            $config       = array('pagination' => array('pageSize' => 50)); //This way all rows are processed.
            $dataProvider = new ImportDataProvider($import->getTempTableName(), true, $config);
            $dataProvider->getPagination()->setCurrentPage($page);
            $importResultsUtil = new ImportResultsUtil($import);
            ImportUtil::importByDataProvider($dataProvider,
                                             $importRules,
                                             $mappingData,
                                             $importResultsUtil,
                                             new ExplicitReadWriteModelPermissions());
            $importResultsUtil->processStatusAndMessagesForEachRow();

            //Confirm that 10 models where created.
            $users = User::getAll();
            $this->assertEquals(11, count($users));
            $activeUser   = User::getByUsername('myusername7');
            $userStatus   = UserStatusUtil::makeByUser($activeUser);
            $this->assertTrue($userStatus->isActive());
            $inactiveUser = User::getByUsername('myusername8');
            $userStatus   = UserStatusUtil::makeByUser($inactiveUser);
            $this->assertFalse($userStatus->isActive());

            //Confirm 10 rows were processed as 'created'.
            $this->assertEquals(10, ImportDatabaseUtil::getCount($import->getTempTableName(), "status = "
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