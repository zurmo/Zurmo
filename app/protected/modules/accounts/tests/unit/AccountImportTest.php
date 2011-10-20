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

    class AccountImportTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            Yii::import('application.extensions.zurmoinc.framework.data.*');
            Yii::import('application.modules.accounts.data.*');
            $defaultDataMaker = new AccountsDefaultDataMaker();
            $defaultDataMaker->make();
        }

        public function testParentAccountHasCorrectAttributeImportType()
        {
            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                    'Accounts', 'account');
            $this->assertTrue($attributeImportRules instanceof AccountAttributeImportRules);
        }

        public function testShortEmailIsValidEmail()
        {
            $validator = new CEmailValidator();
            $validatedEmail = $validator->validateValue('a@a.com');
            $this->assertTrue($validatedEmail);
        }

        /**
         * @depends testParentAccountHasCorrectAttributeImportType
         */
        public function testSimpleUserImportWhereAllRowsSucceed()
        {
            Yii::app()->user->userModel            = User::getByUsername('super');
            $parentAccount                         = AccountTestHelper::
                                                     createAccountByNameForOwner('parentAccount',
                                                                                 Yii::app()->user->userModel);
            $parentAccountId = $parentAccount->id;
            $accounts                              = Account::getAll();
            $this->assertEquals(1, count($accounts));
            $import                                = new Import();
            $serializedData['importRulesType']     = 'Accounts';
            $serializedData['firstRowIsHeaderRow'] = true;
            $import->serializedData                = serialize($serializedData);
            $this->assertTrue($import->save());

            ImportTestHelper::
            createTempTableByFileNameAndTableName('importTest.csv', $import->getTempTableName(),
                                                  Yii::getPathOfAlias('application.modules.accounts.tests.unit.files'));

            //update the ids of the account column to match the parent account.
            R::exec("update " . $import->getTempTableName() . " set column_16 = " .
                    $parentAccount->id . " where id != 1 limit 4");

            $this->assertEquals(4, ImportDatabaseUtil::getCount($import->getTempTableName())); // includes header rows.

            $mappingData = array(
                'column_0'  => ImportTestHelper::makeStringColumnMappingData      ('name'),
                'column_1'  => ImportTestHelper::makeStringColumnMappingData      ('officePhone'),
                'column_2'  => ImportTestHelper::makeStringColumnMappingData      ('officeFax'),
                'column_3'  => ImportTestHelper::makeIntegerColumnMappingData     ('employees'),
                'column_4'  => ImportTestHelper::makeUrlColumnMappingData         ('website'),
                'column_5'  => ImportTestHelper::makeFloatColumnMappingData       ('annualRevenue'),
                'column_6'  => ImportTestHelper::makeTextAreaColumnMappingData    ('description'),
                'column_7'  => ImportTestHelper::makeStringColumnMappingData      ('billingAddress__city'),
                'column_8'  => ImportTestHelper::makeStringColumnMappingData      ('billingAddress__country'),
                'column_9'  => ImportTestHelper::makeStringColumnMappingData      ('billingAddress__postalCode'),
                'column_10' => ImportTestHelper::makeStringColumnMappingData      ('billingAddress__state'),
                'column_11' => ImportTestHelper::makeStringColumnMappingData      ('billingAddress__street1'),
                'column_12' => ImportTestHelper::makeStringColumnMappingData      ('billingAddress__street2'),
                'column_13' => ImportTestHelper::makeEmailColumnMappingData       ('primaryEmail__emailAddress'),
                'column_14' => ImportTestHelper::makeBooleanColumnMappingData     ('primaryEmail__isInvalid'),
                'column_15' => ImportTestHelper::makeBooleanColumnMappingData     ('primaryEmail__optOut'),
                'column_16' => ImportTestHelper::makeHasOneColumnMappingData      ('account'),
                'column_17' => ImportTestHelper::makeDropDownColumnMappingData    ('industry'),
                'column_18' => ImportTestHelper::makeDropDownColumnMappingData    ('type'),
            );

            $importRules  = ImportRulesUtil::makeImportRulesByType('Accounts');
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
            $accounts = Account::getAll();
            $this->assertEquals(4, count($accounts));

            $accounts = Account::getByName('account1');
            $this->assertEquals(1,                         count($accounts[0]));
            $this->assertEquals(123456,                    $accounts[0]->officePhone);
            $this->assertEquals(555,                       $accounts[0]->officeFax);
            $this->assertEquals(1,                         $accounts[0]->employees);
            $this->assertEquals('http://www.account1.com', $accounts[0]->website);
            $this->assertEquals(100,                       $accounts[0]->annualRevenue);
            $this->assertEquals('desc1',                   $accounts[0]->description);
            $this->assertEquals('city1',                   $accounts[0]->billingAddress->city);
            $this->assertEquals('country1',                $accounts[0]->billingAddress->country);
            $this->assertEquals('postal1',                 $accounts[0]->billingAddress->postalCode);
            $this->assertEquals('state1',                  $accounts[0]->billingAddress->state);
            $this->assertEquals('street11',                $accounts[0]->billingAddress->street1);
            $this->assertEquals('street21',                $accounts[0]->billingAddress->street2);
            $this->assertEquals('a@a.com',                 $accounts[0]->primaryEmail->emailAddress);
            $this->assertEquals(null,                      $accounts[0]->primaryEmail->isInvalid);
            $this->assertEquals(null,                      $accounts[0]->primaryEmail->optOut);
            $this->assertTrue($accounts[0]->account->isSame($parentAccount));
            $this->assertEquals('Automotive',              $accounts[0]->industry->value);
            $this->assertEquals('Prospect',                $accounts[0]->type->value);

            $accounts = Account::getByName('account2');
            $this->assertEquals(1,                         count($accounts[0]));
            $this->assertEquals(223456,                    $accounts[0]->officePhone);
            $this->assertEquals(666,                       $accounts[0]->officeFax);
            $this->assertEquals(2,                         $accounts[0]->employees);
            $this->assertEquals('http://www.account2.com', $accounts[0]->website);
            $this->assertEquals(200,                       $accounts[0]->annualRevenue);
            $this->assertEquals('desc2',                   $accounts[0]->description);
            $this->assertEquals('city2',                   $accounts[0]->billingAddress->city);
            $this->assertEquals('country2',                $accounts[0]->billingAddress->country);
            $this->assertEquals('postal2',                 $accounts[0]->billingAddress->postalCode);
            $this->assertEquals('state2',                  $accounts[0]->billingAddress->state);
            $this->assertEquals('street12',                $accounts[0]->billingAddress->street1);
            $this->assertEquals('street22',                $accounts[0]->billingAddress->street2);
            $this->assertEquals('b@b.com',                 $accounts[0]->primaryEmail->emailAddress);
            $this->assertEquals('1',                       $accounts[0]->primaryEmail->isInvalid);
            $this->assertEquals('1',                       $accounts[0]->primaryEmail->optOut);
            $this->assertTrue($accounts[0]->account->isSame($parentAccount));
            $this->assertEquals('Banking',                 $accounts[0]->industry->value);
            $this->assertEquals('Customer',                $accounts[0]->type->value);

            $accounts = Account::getByName('account3');
            $this->assertEquals(1,                         count($accounts[0]));
            $this->assertEquals(323456,                    $accounts[0]->officePhone);
            $this->assertEquals(777,                       $accounts[0]->officeFax);
            $this->assertEquals(3,                         $accounts[0]->employees);
            $this->assertEquals('http://www.account3.com', $accounts[0]->website);
            $this->assertEquals(300,                       $accounts[0]->annualRevenue);
            $this->assertEquals('desc3',                   $accounts[0]->description);
            $this->assertEquals('city3',                   $accounts[0]->billingAddress->city);
            $this->assertEquals('country3',                $accounts[0]->billingAddress->country);
            $this->assertEquals('postal3',                 $accounts[0]->billingAddress->postalCode);
            $this->assertEquals('state3',                  $accounts[0]->billingAddress->state);
            $this->assertEquals('street13',                $accounts[0]->billingAddress->street1);
            $this->assertEquals('street23',                $accounts[0]->billingAddress->street2);
            $this->assertEquals('c@c.com',                 $accounts[0]->primaryEmail->emailAddress);
            $this->assertEquals(null,                      $accounts[0]->primaryEmail->isInvalid);
            $this->assertEquals(null,                      $accounts[0]->primaryEmail->optOut);
            $this->assertTrue($accounts[0]->account->isSame($parentAccount));
            $this->assertEquals('Energy',                  $accounts[0]->industry->value);
            $this->assertEquals('Vendor',                  $accounts[0]->type->value);

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

            //test the parent account has 3 children
            $parentAccount->forget();
            $parentAccount = Account::getById($parentAccountId);
            $this->assertEquals(3, $parentAccount->accounts->count());
        }
    }
?>