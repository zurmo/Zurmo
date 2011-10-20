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

    class OpportunityImportTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            Yii::import('application.extensions.zurmoinc.framework.data.*');
            Yii::import('application.modules.opportunities.data.*');
            $defaultDataMaker = new OpportunitiesDefaultDataMaker();
            $defaultDataMaker->make();
            Yii::import('application.modules.contacts.data.*');
            $defaultDataMaker = new ContactsDefaultDataMaker();
            $defaultDataMaker->make();
            Currency::getAll(); //forces base currency to be created.
        }

        public function testSimpleUserImportWhereAllRowsSucceed()
        {
            Yii::app()->user->userModel            = User::getByUsername('super');
            $account                               = AccountTestHelper::
                                                     createAccountByNameForOwner('Account',
                                                                                 Yii::app()->user->userModel);
            $accountId = $account->id;
            $opportunities                         = Opportunity::getAll();
            $this->assertEquals(0, count($opportunities));
            $import                                = new Import();
            $serializedData['importRulesType']     = 'Opportunities';
            $serializedData['firstRowIsHeaderRow'] = true;
            $import->serializedData                = serialize($serializedData);
            $this->assertTrue($import->save());

            ImportTestHelper::
            createTempTableByFileNameAndTableName('importTest.csv', $import->getTempTableName(),
                                                  Yii::getPathOfAlias('application.modules.opportunities.tests.unit.files'));

            //update the ids of the account column to match the parent account.
            R::exec("update " . $import->getTempTableName() . " set column_4 = " .
                    $account->id . " where id != 1 limit 4");

            $this->assertEquals(4, ImportDatabaseUtil::getCount($import->getTempTableName())); // includes header rows.

            $currency = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());

            $mappingData = array(
                'column_0' => ImportMappingUtil::makeStringColumnMappingData    ('name'),
                'column_1' => ImportMappingUtil::makeDateColumnMappingData      ('closeDate'),
                'column_2' => ImportMappingUtil::makeIntegerColumnMappingData   ('probability'),
                'column_3' => ImportMappingUtil::makeIntegerColumnMappingData   ('description'),
                'column_4' => ImportMappingUtil::makeHasOneColumnMappingData    ('account'),
                'column_5' => ImportMappingUtil::makeDropDownColumnMappingData  ('stage'),
                'column_6' => ImportMappingUtil::makeDropDownColumnMappingData  ('source'),
                'column_7' => ImportMappingUtil::makeCurrencyColumnMappingData  ('amount', $currency),
            );

            $importRules  = ImportRulesUtil::makeImportRulesByType('Opportunities');
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

            //Confirm that 3 models where created.
            $opportunities = Opportunity::getAll();
            $this->assertEquals(3, count($opportunities));

            $opportunities = Opportunity::getByName('opp1');
            $this->assertEquals(1,                         count($opportunities[0]));
            $this->assertEquals('opp1',                    $opportunities[0]->name);
            $this->assertEquals('1980-06-03',              $opportunities[0]->closeDate);
            $this->assertEquals(10,                        $opportunities[0]->probability);
            $this->assertEquals('desc1',                   $opportunities[0]->description);
            $this->assertTrue($opportunities[0]->account->isSame($account));
            $this->assertEquals('Prospecting',             $opportunities[0]->stage->value);
            $this->assertEquals('Self-Generated',          $opportunities[0]->source->value);
            $this->assertEquals(500,                       $opportunities[0]->amount->value);

            $opportunities = Opportunity::getByName('opp2');
            $this->assertEquals(1,                         count($opportunities[0]));
            $this->assertEquals('opp2',                    $opportunities[0]->name);
            $this->assertEquals('1980-06-04',              $opportunities[0]->closeDate);
            $this->assertEquals(20,                        $opportunities[0]->probability);
            $this->assertEquals('desc2',                   $opportunities[0]->description);
            $this->assertTrue($opportunities[0]->account->isSame($account));
            $this->assertEquals('Qualification',           $opportunities[0]->stage->value);
            $this->assertEquals('Inbound Call',            $opportunities[0]->source->value);
            $this->assertEquals(501,                       $opportunities[0]->amount->value);

            $opportunities = Opportunity::getByName('opp3');
            $this->assertEquals(1,                         count($opportunities[0]));
            $this->assertEquals('opp3',                    $opportunities[0]->name);
            $this->assertEquals('1980-06-05',              $opportunities[0]->closeDate);
            $this->assertEquals(30,                        $opportunities[0]->probability);
            $this->assertEquals('desc3',                   $opportunities[0]->description);
            $this->assertTrue($opportunities[0]->account->isSame($account));
            $this->assertEquals('Negotiating',             $opportunities[0]->stage->value);
            $this->assertEquals('Tradeshow',               $opportunities[0]->source->value);
            $this->assertEquals(502,                       $opportunities[0]->amount->value);

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

            //test the account has 3 opportunities
            $account->forget();
            $account = Account::getById($accountId);
            $this->assertEquals(3, $account->opportunities->count());
        }
    }
?>