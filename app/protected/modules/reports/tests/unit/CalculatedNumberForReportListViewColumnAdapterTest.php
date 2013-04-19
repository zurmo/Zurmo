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

    class CalculatedNumberForReportListViewColumnAdapterTest extends ZurmoBaseTest
    {
        public $freeze = false;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $attributeName = 'calculated';
            $attributeForm = new CalculatedNumberAttributeForm();
            $attributeForm->attributeName    = $attributeName;
            $attributeForm->attributeLabels  = array('en' => 'Test Calculated');
            $attributeForm->formula          = 'integer + currencyValue';
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new ReportModelTestItem10());
            $adapter->setAttributeMetadataFromForm($attributeForm);

            $attributeName = 'calculated2';
            $attributeForm = new CalculatedNumberAttributeForm();
            $attributeForm->attributeName    = $attributeName;
            $attributeForm->attributeLabels  = array('en' => 'Test Calculated');
            $attributeForm->formula          = 'integer + amount';
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new ReportModelTestItem11());
            $adapter->setAttributeMetadataFromForm($attributeForm);
        }

        public function setup()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
            DisplayAttributeForReportForm::resetCount();
            ModelRelationsAndAttributesToSummableReportAdapter::forgetAll();
            ModelRelationsAndAttributesToRowsAndColumnsReportAdapter::forgetAll();
            ModelRelationsAndAttributesToMatrixReportAdapter::forgetAll();
            $freeze = false;
            if (RedBeanDatabase::isFrozen())
            {
                RedBeanDatabase::unfreeze();
                $freeze = true;
            }
            $this->freeze = $freeze;
        }

        public function teardown()
        {
            if ($this->freeze)
            {
                RedBeanDatabase::freeze();
            }
            parent::teardown();
        }

        public function testResolveValueOnBaseModelAndRelatedModel()
        {
            $reportModelTestItem11            = new ReportModelTestItem11();
            $reportModelTestItem11->integer   = 5;
            $currencies                       = Currency::getAll();
            $currencyValue                    = new CurrencyValue();
            $currencyValue->value             = 100;
            $currencyValue->currency          = $currencies[0];

            $reportModelTestItem11->amount    = $currencyValue;
            $reportModelTestItem11b           = new ReportModelTestItem11();
            $reportModelTestItem11b->integer  = 7;
            $currencyValue                    = new CurrencyValue();
            $currencyValue->value             = 200;
            $currencyValue->currency          = $currencies[0];
            $reportModelTestItem11b->amount   = $currencyValue;

            $reportModelTestItem10            = new ReportModelTestItem10();
            $reportModelTestItem10->integer   = 12;
            $currencyValue                    = new CurrencyValue();
            $currencyValue->value             = 400;
            $currencyValue->currency          = $currencies[0];
            $reportModelTestItem10->currencyValue   = $currencyValue;
            $reportModelTestItem10->reportModelTestItem11->add($reportModelTestItem11);
            $reportModelTestItem10->reportModelTestItem11->add($reportModelTestItem11b);
            $saved = $reportModelTestItem10->save();
            $this->assertTrue($saved);
            $displayAttributeX    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem10',
                                    Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttributeX->setModelAliasUsingTableAliasName('abc');
            $displayAttributeX->attributeIndexOrDerivedType = 'reportModelTestItem11___calculated2';
            $this->assertEquals('col0', $displayAttributeX->columnAliasName);

            $displayAttributeY    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem10',
                                    Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttributeY->setModelAliasUsingTableAliasName('def');
            $displayAttributeY->attributeIndexOrDerivedType = 'calculated';
            $this->assertEquals('col1', $displayAttributeY->columnAliasName);

            $reportResultsRowData = new ReportResultsRowData(array($displayAttributeX, $displayAttributeY), 4);
            $reportResultsRowData->addModelAndAlias($reportModelTestItem11b, 'abc');
            $reportResultsRowData->addModelAndAlias($reportModelTestItem10,  'def');

            //Get value for calculated which is on base model
            $value = CalculatedNumberForReportListViewColumnAdapter::resolveValue('attribute1', $reportResultsRowData);
            $this->assertEquals(412, $value);
            //Get value for calculated2 which is on a relateds model
            $value = CalculatedNumberForReportListViewColumnAdapter::resolveValue('attribute0', $reportResultsRowData);
            $this->assertEquals(207, $value);
        }
    }
?>