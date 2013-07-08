<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class ProductImportTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            Yii::import('application.core.data.*');
            Yii::import('application.modules.products.data.*');
            $defaultDataMaker = new ProductsDefaultDataMaker();
            $defaultDataMaker->make();
        }

        public function testSimpleUserImportWhereAllRowsSucceed()
        {
            Yii::app()->user->userModel            = User::getByUsername('super');
            //Create account
            $sampleAccount                         = AccountTestHelper::
                                                     createAccountByNameForOwner('sampleAccount',
                                                                                 Yii::app()->user->userModel);
            $accountId                             = $sampleAccount->id;

            $import                                = new Import();
            $serializedData['importRulesType']     = 'Products';
            $serializedData['firstRowIsHeaderRow'] = true;
            $import->serializedData                = serialize($serializedData);
            $this->assertTrue($import->save());

            ImportTestHelper::
            createTempTableByFileNameAndTableName('productsSample.csv', $import->getTempTableName(),
                                                  Yii::getPathOfAlias('application.modules.products.tests.unit.files'));

            $this->assertEquals(3, ImportDatabaseUtil::getCount($import->getTempTableName())); // includes header rows.

            $currencies     = Currency::getAll();

            $ownerColumnMappingData         = array('attributeIndexOrDerivedType' => 'owner',
                                               'type' => 'importColumn', 'mappingRulesData' => array(
                                               'DefaultModelNameIdMappingRuleForm' =>
                                               array('defaultModelId' => null),
                                               'UserValueTypeModelAttributeMappingRuleForm' =>
                                               array('type' =>
                                               UserValueTypeModelAttributeMappingRuleForm::ZURMO_USERNAME)));

            $mappingData = array(
                'column_0'  => $ownerColumnMappingData,
                'column_1'  => ImportMappingUtil::makeStringColumnMappingData      ('name'),
                'column_2'  => ImportMappingUtil::makeTextAreaColumnMappingData    ('description'),
                'column_3'  => ImportMappingUtil::makeIntegerColumnMappingData     ('quantity'),
                'column_4'  => ImportMappingUtil::makeHasOneColumnMappingData      ('account',
                                    RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_NAME),
                'column_5'  => ImportMappingUtil::makeStringColumnMappingData      ('stage'),
                'column_6'  => ImportMappingUtil::makeCurrencyColumnMappingData    ('sellPrice', $currencies[0]),
                'column_7'  => ImportMappingUtil::makeIntegerColumnMappingData      ('priceFrequency'),
                'column_8'  => ImportMappingUtil::makeIntegerColumnMappingData      ('type'),
            );

            $importRules  = ImportRulesUtil::makeImportRulesByType('Products');
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
            $products = Product::getAll();
            $this->assertEquals(2, count($products));

            $products = Product::getByName('A Bend in the River November Issue import');
            $this->assertEquals(1,                         count($products[0]));
            $this->assertEquals('super',                   $products[0]->owner->username);
            $this->assertEquals('A Bend in the River November Issue import',   $products[0]->name);
            $this->assertEquals(6,                         $products[0]->quantity);
            $this->assertEquals('sampleAccount',           $products[0]->account->name);
            $this->assertEquals('Open',                    $products[0]->stage->value);
            $this->assertEquals('Test Desc',               $products[0]->description);
            $this->assertEquals(210,                       $products[0]->sellPrice->value);
            $this->assertEquals(2,                         $products[0]->priceFrequency);
            $this->assertEquals(2,                         $products[0]->type);

            $products[0]->delete();

            $products = Product::getByName('A Bend in the River November Issue import copy');
            $this->assertEquals(1,                         count($products[0]));
            $this->assertEquals('super',                   $products[0]->owner->username);
            $this->assertEquals('A Bend in the River November Issue import copy',   $products[0]->name);
            $this->assertEquals(6,                         $products[0]->quantity);
            $this->assertEquals('sampleAccount',           $products[0]->account->name);
            $this->assertEquals('Open',                    $products[0]->stage->value);
            $this->assertEquals('Test Desc 1',             $products[0]->description);
            $this->assertEquals(210,                       $products[0]->sellPrice->value);
            $this->assertEquals(2,                         $products[0]->priceFrequency);
            $this->assertEquals(2,                         $products[0]->type);

            $products[0]->delete();

            //Confirm 10 rows were processed as 'created'.
//            $this->assertEquals(2, ImportDatabaseUtil::getCount($import->getTempTableName(), "status = "
//                                                                 . ImportRowDataResultsUtil::CREATED));

            //Confirm that 2 rows were processed as 'updated'.
            $this->assertEquals(0, ImportDatabaseUtil::getCount($import->getTempTableName(),  "status = "
                                                                 . ImportRowDataResultsUtil::UPDATED));

            //Confirm 2 rows were processed as 'errors'.
            $this->assertEquals(0, ImportDatabaseUtil::getCount($import->getTempTableName(),  "status = "
                                                                 . ImportRowDataResultsUtil::ERROR));

            $beansWithErrors = ImportDatabaseUtil::getSubset($import->getTempTableName(),     "status = "
                                                                 . ImportRowDataResultsUtil::ERROR);
            $this->assertEquals(0, count($beansWithErrors));


        }

        /**
         * @depends testSimpleUserImportWhereAllRowsSucceed
         */
        public function testSimpleUserImportWithRelationsWhereAllRowsSucceed()
        {
            Yii::app()->user->userModel            = User::getByUsername('super');
            //Create account
            $sampleAccount                         = Account::getByName('sampleAccount');
            $accountId                             = $sampleAccount[0]->id;

            //Create Contact
            $contact                               = ContactTestHelper::createContactByNameForOwner("My Contact", Yii::app()->user->userModel);

            $import                                = new Import();
            $serializedData['importRulesType']     = 'Products';
            $serializedData['firstRowIsHeaderRow'] = true;
            $import->serializedData                = serialize($serializedData);
            $this->assertTrue($import->save());

            ImportTestHelper::
            createTempTableByFileNameAndTableName('productsSampleWithRelations.csv', $import->getTempTableName(),
                                                  Yii::getPathOfAlias('application.modules.products.tests.unit.files'));

            //update the ids of the account column to match the parent account.
            R::exec("update " . $import->getTempTableName() . " set column_9 = " .
                    $contact->id . " where id != 1 limit 3");

            $this->assertEquals(3, ImportDatabaseUtil::getCount($import->getTempTableName())); // includes header rows.

            $currencies     = Currency::getAll();

            $ownerColumnMappingData         = array('attributeIndexOrDerivedType' => 'owner',
                                               'type' => 'importColumn', 'mappingRulesData' => array(
                                               'DefaultModelNameIdMappingRuleForm' =>
                                               array('defaultModelId' => null),
                                               'UserValueTypeModelAttributeMappingRuleForm' =>
                                               array('type' =>
                                               UserValueTypeModelAttributeMappingRuleForm::ZURMO_USERNAME)));
            $mappingData = array(
                'column_0'  => $ownerColumnMappingData,
                'column_1'  => ImportMappingUtil::makeStringColumnMappingData      ('name'),
                'column_2'  => ImportMappingUtil::makeTextAreaColumnMappingData    ('description'),
                'column_3'  => ImportMappingUtil::makeIntegerColumnMappingData     ('quantity'),
                'column_4'  => ImportMappingUtil::makeHasOneColumnMappingData      ('account',
                               RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_NAME),
                'column_5'  => ImportMappingUtil::makeStringColumnMappingData      ('stage'),
                'column_6'  => ImportMappingUtil::makeCurrencyColumnMappingData    ('sellPrice', $currencies[0]),
                'column_7'  => ImportMappingUtil::makeIntegerColumnMappingData      ('priceFrequency'),
                'column_8'  => ImportMappingUtil::makeIntegerColumnMappingData      ('type'),
                //'column_9'  => ImportMappingUtil::makeHasOneColumnMappingData      ('contact'),
            );

            $importRules  = ImportRulesUtil::makeImportRulesByType('Products');
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
            $products = Product::getAll();
            $this->assertEquals(2, count($products));

            $products = Product::getByName('A Bend in the River November Issue import');

            $this->assertEquals(1,                         count($products));
            $this->assertEquals('super',                   $products[0]->owner->username);
            $this->assertEquals('A Bend in the River November Issue import',   $products[0]->name);
            $this->assertEquals(6,                         $products[0]->quantity);
            $this->assertEquals('sampleAccount',           $products[0]->account->name);
            $this->assertEquals('Open',                    $products[0]->stage->value);
            $this->assertEquals('Test Desc',               $products[0]->description);
            $this->assertEquals(210,                       $products[0]->sellPrice->value);
            $this->assertEquals(2,                         $products[0]->priceFrequency);
            $this->assertEquals(2,                         $products[0]->type);
            //$this->assertEquals('My Contact',              $products[0]->contact->firstName);

            $products = Product::getByName('A Bend in the River November Issue import copy');
            $this->assertEquals(1,                         count($products));
            $this->assertEquals('super',                   $products[0]->owner->username);
            $this->assertEquals('A Bend in the River November Issue import copy',   $products[0]->name);
            $this->assertEquals(6,                         $products[0]->quantity);
            $this->assertEquals('sampleAccount',           $products[0]->account->name);
            $this->assertEquals('Open',                    $products[0]->stage->value);
            $this->assertEquals('Test Desc 1',             $products[0]->description);
            $this->assertEquals(210,                       $products[0]->sellPrice->value);
            $this->assertEquals(2,                         $products[0]->priceFrequency);
            $this->assertEquals(2,                         $products[0]->type);
            //$this->assertEquals('My Contact',              $products[0]->contact->firstName);

            //Confirm that 2 rows were processed as 'updated'.
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