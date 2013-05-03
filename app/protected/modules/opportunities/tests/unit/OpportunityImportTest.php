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

    class OpportunityImportTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            Yii::import('application.core.data.*');
            Yii::import('application.modules.opportunities.data.*');
            $defaultDataMaker = new OpportunitiesDefaultDataMaker();
            $defaultDataMaker->make();
            Yii::import('application.modules.contacts.data.*');
            $defaultDataMaker = new ContactsDefaultDataMaker();
            $defaultDataMaker->make();
            Currency::getAll(); //forces base currency to be created.

            $currency = new Currency();
            $currency->code       = 'EUR';
            $currency->rateToBase = 2;
            assert($currency->save()); // Not Coding Standard

            $currency = new Currency();
            $currency->code       = 'GBP';
            $currency->rateToBase = 2;
            assert($currency->save()); // Not Coding Standard
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
            R::exec("update " . $import->getTempTableName() . " set column_3 = " .
                    $account->id . " where id != 1 limit 4");

            $this->assertEquals(4, ImportDatabaseUtil::getCount($import->getTempTableName())); // includes header rows.

            $currency = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());

            $mappingData = array(
                'column_0' => ImportMappingUtil::makeStringColumnMappingData    ('name'),
                'column_1' => ImportMappingUtil::makeDateColumnMappingData      ('closeDate'),
                'column_2' => ImportMappingUtil::makeIntegerColumnMappingData   ('description'),
                'column_3' => ImportMappingUtil::makeHasOneColumnMappingData    ('account'),
                'column_4' => ImportMappingUtil::makeDropDownColumnMappingData  ('stage'),
                'column_5' => ImportMappingUtil::makeDropDownColumnMappingData  ('source'),
                'column_6' => ImportMappingUtil::makeCurrencyColumnMappingData  ('amount', $currency),
            );

            $importRules  = ImportRulesUtil::makeImportRulesByType('Opportunities');
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
            $this->assertEquals(25,                        $opportunities[0]->probability);
            $this->assertEquals('desc2',                   $opportunities[0]->description);
            $this->assertTrue($opportunities[0]->account->isSame($account));
            $this->assertEquals('Qualification',           $opportunities[0]->stage->value);
            $this->assertEquals('Inbound Call',            $opportunities[0]->source->value);
            $this->assertEquals(501,                       $opportunities[0]->amount->value);

            $opportunities = Opportunity::getByName('opp3');
            $this->assertEquals(1,                         count($opportunities[0]));
            $this->assertEquals('opp3',                    $opportunities[0]->name);
            $this->assertEquals('1980-06-05',              $opportunities[0]->closeDate);
            $this->assertEquals(50,                        $opportunities[0]->probability);
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

            $opportunities = Opportunity::getAll();
            $this->assertEquals(3, count($opportunities));
            $this->assertTrue($opportunities[0]->delete());
            $this->assertTrue($opportunities[1]->delete());
            $this->assertTrue($opportunities[2]->delete());
        }

        /**
         * There is a special way you can import rateToBase and currencyCode for an amount attribute.
         * if the column data is formatted like: $54.67__1.2__USD  then it will split the column and properly
         * handle rate and currency code.  Eventually this will be exposed in the user interface
         */
        public function testImportWithRateAndCurrencyCodeSpecified()
        {
            Yii::app()->user->userModel            = User::getByUsername('super');
            $account                               = AccountTestHelper::
                createAccountByNameForOwner('Account',
                Yii::app()->user->userModel);
            $accountId = $account->id;
            $opportunities                         = Opportunity::getAll();
            $this->assertEquals(0,                   count($opportunities));
            $import                                = new Import();
            $serializedData['importRulesType']     = 'Opportunities';
            $serializedData['firstRowIsHeaderRow'] = true;
            $import->serializedData                = serialize($serializedData);
            $this->assertTrue($import->save());

            ImportTestHelper::
                createTempTableByFileNameAndTableName('importTestIncludingRateAndCurrencyCode.csv', $import->getTempTableName(),
                Yii::getPathOfAlias('application.modules.opportunities.tests.unit.files'));

            //update the ids of the account column to match the parent account.
            R::exec("update " . $import->getTempTableName() . " set column_3 = " .
                $account->id . " where id != 1 limit 4");

            $this->assertEquals(4, ImportDatabaseUtil::getCount($import->getTempTableName())); // includes header rows.

            $currency = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());

            $mappingData = array(
                'column_0' => ImportMappingUtil::makeStringColumnMappingData    ('name'),
                'column_1' => ImportMappingUtil::makeDateColumnMappingData      ('closeDate'),
                'column_2' => ImportMappingUtil::makeIntegerColumnMappingData   ('description'),
                'column_3' => ImportMappingUtil::makeHasOneColumnMappingData    ('account'),
                'column_4' => ImportMappingUtil::makeDropDownColumnMappingData  ('stage'),
                'column_5' => ImportMappingUtil::makeDropDownColumnMappingData  ('source'),
                'column_6' => ImportMappingUtil::makeCurrencyColumnMappingData  ('amount', $currency),
            );

            $importRules  = ImportRulesUtil::makeImportRulesByType('Opportunities');
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
            $this->assertEquals(1,                         $opportunities[0]->amount->rateToBase);
            $this->assertEquals('USD',                     $opportunities[0]->amount->currency->code);

            $opportunities = Opportunity::getByName('opp2');
            $this->assertEquals(1,                         count($opportunities[0]));
            $this->assertEquals('opp2',                    $opportunities[0]->name);
            $this->assertEquals('1980-06-04',              $opportunities[0]->closeDate);
            $this->assertEquals(25,                        $opportunities[0]->probability);
            $this->assertEquals('desc2',                   $opportunities[0]->description);
            $this->assertTrue($opportunities[0]->account->isSame($account));
            $this->assertEquals('Qualification',           $opportunities[0]->stage->value);
            $this->assertEquals('Inbound Call',            $opportunities[0]->source->value);
            $this->assertEquals(501,                       $opportunities[0]->amount->value);
           // $this->assertEquals(2.7,                       $opportunities[0]->amount->rateToBase);
            $this->assertEquals('GBP',                     $opportunities[0]->amount->currency->code);

            $opportunities = Opportunity::getByName('opp3');
            $this->assertEquals(1,                         count($opportunities[0]));
            $this->assertEquals('opp3',                    $opportunities[0]->name);
            $this->assertEquals('1980-06-05',              $opportunities[0]->closeDate);
            $this->assertEquals(50,                        $opportunities[0]->probability);
            $this->assertEquals('desc3',                   $opportunities[0]->description);
            $this->assertTrue($opportunities[0]->account->isSame($account));
            $this->assertEquals('Negotiating',             $opportunities[0]->stage->value);
            $this->assertEquals('Tradeshow',               $opportunities[0]->source->value);
            $this->assertEquals(502,                       $opportunities[0]->amount->value);
           // $this->assertEquals(3.2,                       $opportunities[0]->amount->rateToBase);
            $this->assertEquals('EUR',                     $opportunities[0]->amount->currency->code);

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