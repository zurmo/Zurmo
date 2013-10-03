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

    class ProductTemplateImportTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            Yii::import('application.core.data.*');
            Yii::import('application.modules.productTemplates.data.*');
            $defaultDataMaker = new ProductsDefaultDataMaker();
            $defaultDataMaker->make();
        }

        public function testSimpleUserImportWhereAllRowsSucceed()
        {
            Yii::app()->user->userModel            = User::getByUsername('super');
            $import                                = new Import();
            $serializedData['importRulesType']     = 'ProductTemplates';
            $serializedData['firstRowIsHeaderRow'] = true;
            $import->serializedData                = serialize($serializedData);
            $this->assertTrue($import->save());

            ImportTestHelper::
            createTempTableByFileNameAndTableName('productTemplates.csv', $import->getTempTableName(),
                                                  Yii::getPathOfAlias('application.modules.productTemplates.tests.unit.files'));

            $this->assertEquals(3, ImportDatabaseUtil::getCount($import->getTempTableName())); // includes header rows.

            $currencies     = Currency::getAll();

            $mappingData = array(
                'column_0'  => ImportMappingUtil::makeStringColumnMappingData      ('name'),
                'column_1'  => ImportMappingUtil::makeTextAreaColumnMappingData    ('description'),
                'column_2'  => ImportMappingUtil::makeIntegerColumnMappingData     ('sellPriceFormula__type'),
                'column_3'  => ImportMappingUtil::makeFloatColumnMappingData       ('sellPriceFormula__discountOrMarkupPercentage'),
                'column_4'  => ImportMappingUtil::makeCurrencyColumnMappingData    ('cost', $currencies[0]),
                'column_5'  => ImportMappingUtil::makeCurrencyColumnMappingData    ('listPrice', $currencies[0]),
                'column_6'  => ImportMappingUtil::makeCurrencyColumnMappingData    ('sellPrice', $currencies[0]),
                'column_7'  => ImportMappingUtil::makeIntegerColumnMappingData     ('priceFrequency'),
                'column_8'  => ImportMappingUtil::makeIntegerColumnMappingData     ('type'),
                'column_9'  => ImportMappingUtil::makeIntegerColumnMappingData     ('status'),
            );

            $importRules  = ImportRulesUtil::makeImportRulesByType('ProductTemplates');
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
            $productTemplates = ProductTemplate::getAll();
            $this->assertEquals(2, count($productTemplates));

            $productTemplates = ProductTemplate::getByName('A Gift of Monotheists import');
            $this->assertEquals(1,                         count($productTemplates[0]));
            $this->assertEquals('A Gift of Monotheists import',   $productTemplates[0]->name);
            $this->assertEquals(2,                         $productTemplates[0]->sellPriceFormula->type);
            $this->assertEquals(10,                        $productTemplates[0]->sellPriceFormula->discountOrMarkupPercentage);
            $this->assertEquals(180,                       $productTemplates[0]->sellPrice->value);
            $this->assertEquals(200,                       $productTemplates[0]->listPrice->value);
            $this->assertEquals(200,                       $productTemplates[0]->cost->value);
            $this->assertEquals(2,                         $productTemplates[0]->priceFrequency);
            $this->assertEquals(2,                         $productTemplates[0]->type);
            $this->assertEquals(2,                         $productTemplates[0]->status);

            $productTemplates[0]->delete();

            $productTemplates = ProductTemplate::getByName('A Gift of Monotheists import Copy');
            $this->assertEquals(1,                         count($productTemplates[0]));
            $this->assertEquals('A Gift of Monotheists import Copy',   $productTemplates[0]->name);
            $this->assertEquals(2,                         $productTemplates[0]->sellPriceFormula->type);
            $this->assertEquals(5,                        $productTemplates[0]->sellPriceFormula->discountOrMarkupPercentage);
            $this->assertEquals(180,                       $productTemplates[0]->sellPrice->value);
            $this->assertEquals(200,                       $productTemplates[0]->listPrice->value);
            $this->assertEquals(200,                       $productTemplates[0]->cost->value);
            $this->assertEquals(2,                         $productTemplates[0]->priceFrequency);
            $this->assertEquals(2,                         $productTemplates[0]->type);
            $this->assertEquals(2,                         $productTemplates[0]->status);

            $productTemplates[0]->delete();

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

        public function testAnalyzeByRowForPriceFrequencySanitization()
        {
            Yii::app()->user->userModel            = User::getByUsername('super');
            $import                                = new Import();
            $serializedData['importRulesType']     = 'ProductTemplates';
            $serializedData['firstRowIsHeaderRow'] = true;
            $import->serializedData                = serialize($serializedData);
            $this->assertTrue($import->save());

            ImportTestHelper::
            createTempTableByFileNameAndTableName('productTemplates.csv', $import->getTempTableName(),
                                                  Yii::getPathOfAlias('application.modules.productTemplates.tests.unit.files'));

            $this->assertEquals(3, ImportDatabaseUtil::getCount($import->getTempTableName())); // includes header rows.

            $currencies     = Currency::getAll();

            $mappingData = array(
                'column_0'  => ImportMappingUtil::makeStringColumnMappingData      ('name'),
                'column_1'  => ImportMappingUtil::makeTextAreaColumnMappingData    ('description'),
                'column_2'  => ImportMappingUtil::makeIntegerColumnMappingData     ('sellPriceFormula__type'),
                'column_3'  => ImportMappingUtil::makeFloatColumnMappingData       ('sellPriceFormula__discountOrMarkupPercentage'),
                'column_4'  => ImportMappingUtil::makeCurrencyColumnMappingData    ('cost', $currencies[0]),
                'column_5'  => ImportMappingUtil::makeCurrencyColumnMappingData    ('listPrice', $currencies[0]),
                'column_6'  => ImportMappingUtil::makeCurrencyColumnMappingData    ('sellPrice', $currencies[0]),
                'column_7'  => ImportMappingUtil::makeIntegerColumnMappingData     ('priceFrequency'),
                'column_8'  => ImportMappingUtil::makeIntegerColumnMappingData     ('type'),
                'column_9'  => ImportMappingUtil::makeIntegerColumnMappingData     ('status'),
            );

            $importRules  = ImportRulesUtil::makeImportRulesByType('ProductTemplates');
            $page         = 0;
            $config       = array('pagination' => array('pageSize' => 50)); //This way all rows are processed.
            $dataProvider = new ImportDataProvider($import->getTempTableName(), true, $config);
            $dataProvider->getPagination()->setCurrentPage($page);

            $sanitizer = ImportSanitizerUtilFactory::
                                         make('PriceFrequency', 'ProductTemplate', 'priceFrequency',
                                         'pricefrequency', $mappingData['column_7']);
            $data = $dataProvider->getData(true);
            foreach ($data as $rowBean)
            {
                $sanitizer->analyzeByRow($rowBean);
                $this->assertEquals(0, count($sanitizer->getAnalysisMessages()));
            }
        }

        public function testPriceFrequencySanitizationWithBothInvalidAndValidData()
        {
            $mappingData = array();
            $data = array();
            $this->processSantizerCSVAndGetData($mappingData, $data);
            $counter = 1;
            foreach ($data as $rowBean)
            {
                //@see ImportDataAnalyze::analyzePage
                $sanitizer = ImportSanitizerUtilFactory::
                                         make('PriceFrequency', 'ProductTemplate', 'priceFrequency',
                                         'column_7', $mappingData['column_7']);
                if ($counter == 1)
                {
                    $sanitizer->analyzeByRow($rowBean);
                    $this->assertEquals(1, count($sanitizer->getAnalysisMessages()));
                    $this->assertTrue((bool)$sanitizer->getShouldSkipRow());
                    try
                    {
                        $sanitizer->sanitizeValue($rowBean->column_7);
                    }
                    catch (InvalidValueToSanitizeException $e)
                    {
                        $this->assertEquals(Zurmo::t('ProductTemplatesModule', 'Price Frequency specified is invalid.'), $e->getMessage());
                    }
                }
                elseif ($counter == 2)
                {
                    $sanitizer->analyzeByRow($rowBean);
                    $this->assertEquals(0, count($sanitizer->getAnalysisMessages()));
                    $value = $sanitizer->sanitizeValue($rowBean->column_7);
                    $this->assertEquals(2, $value);
                }
                elseif ($counter == 3)
                {
                    $value = $sanitizer->sanitizeValue($rowBean->column_7);
                    $this->assertEquals(2, $value);
                    R::store($rowBean);
                    $sanitizer->analyzeByRow($rowBean);
                    $this->assertEquals(0, count($sanitizer->getAnalysisMessages()));
                }
                elseif ($counter == 4)
                {
                    $value = $sanitizer->sanitizeValue($rowBean->column_7);
                    $this->assertEquals(1, $value);
                    R::store($rowBean);
                    $sanitizer->analyzeByRow($rowBean);
                    $this->assertEquals(0, count($sanitizer->getAnalysisMessages()));
                }
                $counter++;
            }
        }

        public function testStatusSanitizationWithBothInvalidAndValidData()
        {
            $mappingData = array();
            $data = array();
            $this->processSantizerCSVAndGetData($mappingData, $data);
            $counter = 1;
            foreach ($data as $rowBean)
            {
                //@see ImportDataAnalyze::analyzePage
                $sanitizer = ImportSanitizerUtilFactory::
                                         make('ProductTemplateStatus', 'ProductTemplate', 'status',
                                         'column_9', $mappingData['column_9']);
                if ($counter == 1)
                {
                    $sanitizer->analyzeByRow($rowBean);
                    $this->assertEquals(1, count($sanitizer->getAnalysisMessages()));
                    $this->assertTrue((bool)$sanitizer->getShouldSkipRow());
                    try
                    {
                        $sanitizer->sanitizeValue($rowBean->column_9);
                    }
                    catch (InvalidValueToSanitizeException $e)
                    {
                        $this->assertEquals(Zurmo::t('ProductTemplatesModule', 'Status specified is invalid.'), $e->getMessage());
                    }
                }
                elseif ($counter == 2)
                {
                    $sanitizer->analyzeByRow($rowBean);
                    $this->assertEquals(0, count($sanitizer->getAnalysisMessages()));
                    $value = $sanitizer->sanitizeValue($rowBean->column_9);
                    $this->assertEquals(2, $value);
                }
                elseif ($counter == 3)
                {
                    $value = $sanitizer->sanitizeValue($rowBean->column_9);
                    $this->assertEquals(1, $value);
                    R::store($rowBean);
                    $sanitizer->analyzeByRow($rowBean);
                    $this->assertEquals(0, count($sanitizer->getAnalysisMessages()));
                }
                elseif ($counter == 4)
                {
                    $value = $sanitizer->sanitizeValue($rowBean->column_9);
                    $this->assertEquals(1, $value);
                    R::store($rowBean);
                    $sanitizer->analyzeByRow($rowBean);
                    $this->assertEquals(0, count($sanitizer->getAnalysisMessages()));
                }
                $counter++;
            }
        }

        public function testProductTemplateTypeSanitizationWithBothInvalidAndValidData()
        {
            $mappingData = array();
            $data = array();
            $this->processSantizerCSVAndGetData($mappingData, $data);
            $counter = 1;
            foreach ($data as $rowBean)
            {
                //@see ImportDataAnalyze::analyzePage
                $sanitizer = ImportSanitizerUtilFactory::
                                         make('ProductTemplateType', 'ProductTemplate', 'type',
                                         'column_8', $mappingData['column_8']);
                if ($counter == 1)
                {
                    $sanitizer->analyzeByRow($rowBean);
                    $this->assertEquals(1, count($sanitizer->getAnalysisMessages()));
                    $this->assertTrue((bool)$sanitizer->getShouldSkipRow());
                    try
                    {
                        $sanitizer->sanitizeValue($rowBean->column_8);
                    }
                    catch (InvalidValueToSanitizeException $e)
                    {
                        $this->assertEquals(Zurmo::t('ProductTemplatesModule', 'Type specified is invalid.'), $e->getMessage());
                    }
                }
                elseif ($counter == 2)
                {
                    $sanitizer->analyzeByRow($rowBean);
                    $this->assertEquals(0, count($sanitizer->getAnalysisMessages()));
                    $value = $sanitizer->sanitizeValue($rowBean->column_8);
                    $this->assertEquals(2, $value);
                }
                elseif ($counter == 3)
                {
                    $value = $sanitizer->sanitizeValue($rowBean->column_8);
                    $this->assertEquals(3, $value);
                    R::store($rowBean);
                    $sanitizer->analyzeByRow($rowBean);
                    $this->assertEquals(0, count($sanitizer->getAnalysisMessages()));
                }
                elseif ($counter == 4)
                {
                    $value = $sanitizer->sanitizeValue($rowBean->column_8);
                    $this->assertEquals(1, $value);
                    R::store($rowBean);
                    $sanitizer->analyzeByRow($rowBean);
                    $this->assertEquals(0, count($sanitizer->getAnalysisMessages()));
                }
                $counter++;
            }
        }

        public function testSellPriceFormulaSanitizationWithBothInvalidAndValidData()
        {
            $mappingData = array();
            $data = array();
            $this->processSantizerCSVAndGetData($mappingData, $data);
            $counter = 1;
            foreach ($data as $rowBean)
            {
                //@see ImportDataAnalyze::analyzePage
                $sanitizer = ImportSanitizerUtilFactory::
                                         make('SellPriceFormulaType', 'ProductTemplate', 'sellPriceFormula__type',
                                         'column_2', $mappingData['column_2']);
                if ($counter == 1)
                {
                    $sanitizer->analyzeByRow($rowBean);
                    $this->assertEquals(1, count($sanitizer->getAnalysisMessages()));
                    $this->assertTrue((bool)$sanitizer->getShouldSkipRow());
                    try
                    {
                        $sanitizer->sanitizeValue($rowBean->column_2);
                    }
                    catch (InvalidValueToSanitizeException $e)
                    {
                        $this->assertEquals(Zurmo::t('ProductTemplatesModule', 'Sell Price Formula type specified is invalid.'), $e->getMessage());
                    }
                }
                elseif ($counter == 2)
                {
                    $sanitizer->analyzeByRow($rowBean);
                    $this->assertEquals(0, count($sanitizer->getAnalysisMessages()));
                    $value = $sanitizer->sanitizeValue($rowBean->column_2);
                    $this->assertEquals(2, $value);
                }
                elseif ($counter == 3)
                {
                    $value = $sanitizer->sanitizeValue($rowBean->column_2);
                    $this->assertEquals(2, $value);
                    R::store($rowBean);
                    $sanitizer->analyzeByRow($rowBean);
                    $this->assertEquals(0, count($sanitizer->getAnalysisMessages()));
                }
                elseif ($counter == 4)
                {
                    $value = $sanitizer->sanitizeValue($rowBean->column_2);
                    $this->assertEquals(3, $value);
                    R::store($rowBean);
                    $sanitizer->analyzeByRow($rowBean);
                    $this->assertEquals(0, count($sanitizer->getAnalysisMessages()));
                }
                $counter++;
            }
        }

        /**
         * Process csv and get data
         * @param array $mappingData
         * @param array $data
         */
        protected function processSantizerCSVAndGetData(&$mappingData, &$data)
        {
            Yii::app()->user->userModel            = User::getByUsername('super');
            $import                                = new Import();
            $serializedData['importRulesType']     = 'ProductTemplates';
            $serializedData['firstRowIsHeaderRow'] = true;
            $import->serializedData                = serialize($serializedData);
            $this->assertTrue($import->save());

            ImportTestHelper::
            createTempTableByFileNameAndTableName('productTemplatesSanitizer.csv', $import->getTempTableName(),
                                                  Yii::getPathOfAlias('application.modules.productTemplates.tests.unit.files'));

            $this->assertEquals(5, ImportDatabaseUtil::getCount($import->getTempTableName())); // includes header rows.

            $currencies     = Currency::getAll();

            $mappingData = array(
                'column_0'  => ImportMappingUtil::makeStringColumnMappingData      ('name'),
                'column_1'  => ImportMappingUtil::makeTextAreaColumnMappingData    ('description'),
                'column_2'  => ImportMappingUtil::makeIntegerColumnMappingData     ('sellPriceFormula__type'),
                'column_3'  => ImportMappingUtil::makeFloatColumnMappingData       ('sellPriceFormula__discountOrMarkupPercentage'),
                'column_4'  => ImportMappingUtil::makeCurrencyColumnMappingData    ('cost', $currencies[0]),
                'column_5'  => ImportMappingUtil::makeCurrencyColumnMappingData    ('listPrice', $currencies[0]),
                'column_6'  => ImportMappingUtil::makeCurrencyColumnMappingData    ('sellPrice', $currencies[0]),
                'column_7'  => ImportMappingUtil::makeIntegerColumnMappingData     ('priceFrequency'),
                'column_8'  => ImportMappingUtil::makeIntegerColumnMappingData     ('type'),
                'column_9'  => ImportMappingUtil::makeIntegerColumnMappingData     ('status'),
            );

            $importRules  = ImportRulesUtil::makeImportRulesByType('ProductTemplates');
            $page         = 0;
            $config       = array('pagination' => array('pageSize' => 50)); //This way all rows are processed.
            $dataProvider = new ImportDataProvider($import->getTempTableName(), true, $config);
            $dataProvider->getPagination()->setCurrentPage($page);
            $data = $dataProvider->getData(true);
        }
    }
?>