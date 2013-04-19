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

    class ModelRelationsAndAttributesToReportAdapterTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $attributeName = 'calculated';
            $attributeForm = new CalculatedNumberAttributeForm();
            $attributeForm->attributeName    = $attributeName;
            $attributeForm->attributeLabels  = array('en' => 'Test Calculated');
            $attributeForm->formula          = 'integer + float';
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new ReportModelTestItem());
            $adapter->setAttributeMetadataFromForm($attributeForm);
        }

        public function setup()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
            ModelRelationsAndAttributesToSummableReportAdapter::forgetAll();
        }

        public function testIsDisplayAttributeMadeViaSelect()
        {
            $model              = new ReportModelTestItem();
            $rules              = new ReportsTestReportRules(); //ReportsTestModule rules
            $report             = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report->getType());
            $this->assertFalse($adapter->isDisplayAttributeMadeViaSelect('something'));
        }

        public function testGetAllRelations()
        {
            $model              = new ReportModelTestItem();
            $rules              = new ReportsTestReportRules(); //ReportsTestModule rules
            $report             = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report->getType());
            $relations          = $adapter->getAllRelationsData();
            $this->assertEquals(20, count($relations));
        }

        /**
         * @depends testGetAllRelations
         * Make sure HAS_MANY_BELONGS_TO relations show up
         */
        public function testGetHasManyBelongsToRelations()
        {
            $model              = new ReportModelTestItem9();
            $rules              = new ReportsTestReportRules(); //ReportsTestModule rules
            $report             = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToRowsAndColumnsReportAdapter($model, $rules,
                                  $report->getType());
            $relations          = $adapter->getSelectableRelationsData();
            $this->assertEquals(9, count($relations));
            $compareData        = array('label' => 'Report Model Test Item 9');
            $this->assertEquals($compareData, $relations['reportModelTestItem9']);
            $compareData        = array('label' => 'Report Model Test Item 9s');
            $this->assertEquals($compareData, $relations['reportModelTestItem9s']);
        }

        /**
         * @depends testGetHasManyBelongsToRelations
         */
        public function testPassingPrecedingRelationThatHasAssumptiveLinkIsProperlyHandled()
        {
            $model              = new ReportModelTestItem3();
            $rules              = new ReportsTestReportRules();
            $report             = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report->getType());
            $relations = $adapter->getSelectableRelationsData(new ReportModelTestItem(), 'hasMany');
            $this->assertFalse(isset($relations['hasMany1']));
        }

        /**
         * @depends testPassingPrecedingRelationThatHasAssumptiveLinkIsProperlyHandled
         */
        public function testGetAllReportableRelations()
        {
            //ReportModelTestItem has hasOne, hasMany, and hasOneAlso.  In addition it has a
            //derivedRelationsViaCastedUpModel to ReportModelTestItem5.
            //Excludes any customField relations and relationsReportedOnAsAttributes
            //Also excludes any non-reportable relations
            //Get relations through adapter and confirm everything matches up as expected
            $model              = new ReportModelTestItem();
            $rules              = new ReportsTestReportRules();
            $report             = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report->getType());
            $relations = $adapter->getSelectableRelationsData();
            $this->assertEquals(11, count($relations));
            $compareData        = array('label' => 'Has One');
            $this->assertEquals($compareData, $relations['hasOne']);
            $compareData        = array('label' => 'Has One Again');
            $this->assertEquals($compareData, $relations['hasOneAgain']);
            $compareData        = array('label' => 'Has Many');
            $this->assertEquals($compareData, $relations['hasMany']);
            $compareData        = array('label' => 'Has One Also');
            $this->assertEquals($compareData, $relations['hasOneAlso']);
            $compareData        = array('label' => 'Model 5 Via Item');
            $this->assertEquals($compareData, $relations['model5ViaItem']);
            $compareData        = array('label' => 'Primary Email');
            $this->assertEquals($compareData, $relations['primaryEmail']);
            $compareData        = array('label' => 'Primary Address');
            $this->assertEquals($compareData, $relations['primaryAddress']);
            $compareData        = array('label' => 'Secondary Email');
            $this->assertEquals($compareData, $relations['secondaryEmail']);
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $relations['owner']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $relations['createdByUser']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $relations['modifiedByUser']);
        }

        /**
         * When retrieving available relations, make sure it does not give a relation based on what model it is coming
         * from.  If you are in a Contact and the parent relation is account, then Contact should not return the account
         * as an available relation.
         * @depends testGetAllReportableRelations
         */
        public function testGetAvailableRelationsDoesNotCauseFeedbackLoop()
        {
            $model              = new ReportModelTestItem2();
            $rules              = new ReportsTestReportRules();
            $report             = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report->getType());
            $relations = $adapter->getSelectableRelationsData();
            $this->assertEquals(5, count($relations));
            $compareData        = array('label' => 'Has Many 2');
            $this->assertEquals($compareData, $relations['hasMany2']);
            $compareData        = array('label' => 'Has Many 3');
            $this->assertEquals($compareData, $relations['hasMany3']);
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $relations['owner']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $relations['createdByUser']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $relations['modifiedByUser']);

            $precedingModel     = new ReportModelTestItem();
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report->getType());
            $relations = $adapter->getSelectableRelationsData($precedingModel, 'hasOne');
            $this->assertEquals(4, count($relations));
            $compareData        = array('label' => 'Has Many 3');
            $this->assertEquals($compareData, $relations['hasMany3']);
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $relations['owner']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $relations['createdByUser']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $relations['modifiedByUser']);
        }

        /**
         * @depends testGetAvailableRelationsDoesNotCauseFeedbackLoop
         */
        public function testGetReportableAttributes()
        {
            $model              = new ReportModelTestItem();
            $rules              = new ReportsTestReportRules();
            $report             = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report->getType());
            $attributes = $adapter->getAttributesIncludingDerivedAttributesData();
            $this->assertEquals(27, count($attributes));
            $compareData        = array('label' => 'Id');
            $this->assertEquals($compareData, $attributes['id']);
            $compareData        = array('label' => 'Created Date Time');
            $this->assertEquals($compareData, $attributes['createdDateTime']);
            $compareData        = array('label' => 'Modified Date Time');
            $this->assertEquals($compareData, $attributes['modifiedDateTime']);
            $compareData        = array('label' => 'First Name');
            $this->assertEquals($compareData, $attributes['firstName']);
            $compareData        = array('label' => 'Last Name');
            $this->assertEquals($compareData, $attributes['lastName']);
            $compareData        = array('label' => 'Boolean');
            $this->assertEquals($compareData, $attributes['boolean']);
            $compareData        = array('label' => 'Date');
            $this->assertEquals($compareData, $attributes['date']);
            $compareData        = array('label' => 'Date Time');
            $this->assertEquals($compareData, $attributes['dateTime']);
            $compareData        = array('label' => 'Float');
            $this->assertEquals($compareData, $attributes['float']);
            $compareData        = array('label' => 'Integer');
            $this->assertEquals($compareData, $attributes['integer']);
            $compareData        = array('label' => 'Phone');
            $this->assertEquals($compareData, $attributes['phone']);
            $compareData        = array('label' => 'String');
            $this->assertEquals($compareData, $attributes['string']);
            $compareData        = array('label' => 'Text Area');
            $this->assertEquals($compareData, $attributes['textArea']);
            $compareData        = array('label' => 'Url');
            $this->assertEquals($compareData, $attributes['url']);
            $compareData        = array('label' => 'Drop Down');
            $this->assertEquals($compareData, $attributes['dropDown']);
            $compareData        = array('label' => 'Drop Down 2');
            $this->assertEquals($compareData, $attributes['dropDown2']);
            $compareData        = array('label' => 'Radio Drop Down');
            $this->assertEquals($compareData, $attributes['radioDropDown']);
            $compareData        = array('label' => 'Multi Drop Down');
            $this->assertEquals($compareData, $attributes['multiDropDown']);
            $compareData        = array('label' => 'Tag Cloud');
            $this->assertEquals($compareData, $attributes['tagCloud']);
            $compareData        = array('label' => 'Reported As Attribute');
            $this->assertEquals($compareData, $attributes['reportedAsAttribute']);
            //Currency is treated as a relation reported as an attribute just like drop downs
            $compareData        = array('label' => 'Currency Value');
            $this->assertEquals($compareData, $attributes['currencyValue']);
            //likeContactState is a relation reported as attribute.
            //Makes sure the label is using the proper label translation via attributeLabels
            $compareData        = array('label' => 'A name for a state');
            $this->assertEquals($compareData, $attributes['likeContactState']);
            //Includes derived attributes as well
            $compareData        = array('label' => 'Test Calculated', 'derivedAttributeType' => 'CalculatedNumber');
            $this->assertEquals($compareData, $attributes['calculated']);
            $compareData        = array('label' => 'Full Name',       'derivedAttributeType' => 'FullName');
            $this->assertEquals($compareData, $attributes['FullName']);
            //Add Dynamically Derived Attributes
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $attributes['owner__User']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $attributes['createdByUser__User']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $attributes['modifiedByUser__User']);
        }

        /**
         * @depends testGetReportableAttributes
         * Testing where a model relates to another model via something like Item. An example is notes which connects
         * to accounts via activityItems MANY_MANY through Items.  On Notes we need to be able to show  these relations
         * as selectable in reporting.
         *
         * In this example ReportModelTestItem5 connects to ReportModelTestItem and ReportModelTestItem2
         * via MANY_MANY through Item using the reportItems relation
         * Known as viaRelations: model5ViaItem on ReportModelItem and model5ViaItem on ReportModelItem2
         */
        public function testGetInferredRelationsData()
        {
            $model              = new ReportModelTestItem5();
            $rules              = new ReportsTestReportRules();
            $report             = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report->getType());
            $relations = $adapter->getInferredRelationsData();
            $this->assertEquals(2, count($relations));
            $compareData        = array('label' => 'Reports Tests');
            $this->assertEquals($compareData, $relations['ReportModelTestItem__reportItems__Inferred']);
            $compareData        = array('label' => 'ReportModelTestItem2s');
            $this->assertEquals($compareData, $relations['ReportModelTestItem2__reportItems__Inferred']);

            //Getting all selectable relations. Should yield all 3 relations
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report->getType());
            $relations = $adapter->getSelectableRelationsData();
            $this->assertEquals(6, count($relations));
            $compareData        = array('label' => 'Reports Tests');
            $this->assertEquals($compareData, $relations['ReportModelTestItem__reportItems__Inferred']);
            $compareData        = array('label' => 'ReportModelTestItem2s');
            $this->assertEquals($compareData, $relations['ReportModelTestItem2__reportItems__Inferred']);
            $compareData        = array('label' => 'Report Items');
            $this->assertEquals($compareData, $relations['reportItems']);
            //Add Dynamically Derived Attributes
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $relations['owner']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $relations['createdByUser']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $relations['modifiedByUser']);
        }

        /**
         * @depends testGetInferredRelationsData
         */
        public function testGetInferredRelationsDataWithPrecedingModel()
        {
            $model              = new ReportModelTestItem5();
            $rules              = new ReportsTestReportRules();
            $report             = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $precedingModel     = new ReportModelTestItem7();
            $report->setModuleClassName('ReportsTestModule');
            //Test calling on model 5 with a preceding model that is NOT part of reportItems
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report->getType());
            $relations = $adapter->getSelectableRelationsData($precedingModel, 'model5');
            $this->assertEquals(6, count($relations));
            $compareData        = array('label' => 'Reports Tests');
            $this->assertEquals($compareData, $relations['ReportModelTestItem__reportItems__Inferred']);
            $compareData        = array('label' => 'ReportModelTestItem2s');
            $this->assertEquals($compareData, $relations['ReportModelTestItem2__reportItems__Inferred']);
            $compareData        = array('label' => 'Report Items');
            $this->assertEquals($compareData, $relations['reportItems']);
            //Add Dynamically Derived Attributes
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $relations['owner']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $relations['createdByUser']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $relations['modifiedByUser']);

            //Test calling on model 5 with a preceding model that is one of the reportItem models
            $precedingModel     = new ReportModelTestItem();
            $relations = $adapter->getSelectableRelationsData($precedingModel, 'model5ViaItem');
            $this->assertEquals(5, count($relations));
            $compareData        = array('label' => 'ReportModelTestItem2s');
            $this->assertEquals($compareData, $relations['ReportModelTestItem2__reportItems__Inferred']);
            $compareData        = array('label' => 'Report Items');
            $this->assertEquals($compareData, $relations['reportItems']);
            //Add Dynamically Derived Attributes
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $relations['owner']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $relations['createdByUser']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $relations['modifiedByUser']);
        }

        /**
         * @depends testGetInferredRelationsDataWithPrecedingModel
         */
        public function testGetDerivedRelationsViaCastedUpModelDataWithPrecedingModel()
        {
            //test with preceding model that is not the via relation
            $model              = new ReportModelTestItem();
            $precedingModel     = new ReportModelTestItem5();
            $rules              = new ReportsTestReportRules();
            $report             = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report->getType());
            $relations = $adapter->getSelectableRelationsData($precedingModel, 'nonReportable2');
            $this->assertEquals(11, count($relations));
            $compareData        = array('label' => 'Has One');
            $this->assertEquals($compareData, $relations['hasOne']);
            $compareData        = array('label' => 'Has One Again');
            $this->assertEquals($compareData, $relations['hasOneAgain']);
            $compareData        = array('label' => 'Has Many');
            $this->assertEquals($compareData, $relations['hasMany']);
            $compareData        = array('label' => 'Has One Also');
            $this->assertEquals($compareData, $relations['hasOneAlso']);
            $compareData        = array('label' => 'Model 5 Via Item');
            $this->assertEquals($compareData, $relations['model5ViaItem']);
            $compareData        = array('label' => 'Primary Email');
            $this->assertEquals($compareData, $relations['primaryEmail']);
            $compareData        = array('label' => 'Primary Address');
            $this->assertEquals($compareData, $relations['primaryAddress']);
            $compareData        = array('label' => 'Secondary Email');
            $this->assertEquals($compareData, $relations['secondaryEmail']);
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $relations['owner']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $relations['createdByUser']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $relations['modifiedByUser']);

            //test with preceding model that is the via relation
            $rules              = new ReportsTestReportRules();
            $report             = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, $report->getType());
            $relations = $adapter->getSelectableRelationsData($precedingModel, 'reportItems');
            $this->assertEquals(10, count($relations));
            $compareData        = array('label' => 'Has One');
            $this->assertEquals($compareData, $relations['hasOne']);
            $compareData        = array('label' => 'Has One Again');
            $this->assertEquals($compareData, $relations['hasOneAgain']);
            $compareData        = array('label' => 'Has Many');
            $this->assertEquals($compareData, $relations['hasMany']);
            $compareData        = array('label' => 'Has One Also');
            $this->assertEquals($compareData, $relations['hasOneAlso']);
            $compareData        = array('label' => 'Primary Email');
            $this->assertEquals($compareData, $relations['primaryEmail']);
            $compareData        = array('label' => 'Primary Address');
            $this->assertEquals($compareData, $relations['primaryAddress']);
            $compareData        = array('label' => 'Secondary Email');
            $this->assertEquals($compareData, $relations['secondaryEmail']);
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $relations['owner']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $relations['createdByUser']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $relations['modifiedByUser']);
        }

        /**
         * @depends testGetDerivedRelationsViaCastedUpModelDataWithPrecedingModel
         */
        public function testGetAvailableAttributesForRowsAndColumnsFilters()
        {
            $model              = new ReportModelTestItem();
            $rules              = new ReportsTestReportRules();
            $report             = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToRowsAndColumnsReportAdapter($model, $rules, $report->getType());
            $attributes = $adapter->getAttributesForFilters();
            $this->assertEquals(24, count($attributes));

            $compareData        = array('label' => 'Created Date Time');
            $this->assertEquals($compareData, $attributes['createdDateTime']);
            $compareData        = array('label' => 'Modified Date Time');
            $this->assertEquals($compareData, $attributes['modifiedDateTime']);
            $compareData        = array('label' => 'First Name');
            $this->assertEquals($compareData, $attributes['firstName']);
            $compareData        = array('label' => 'Last Name');
            $this->assertEquals($compareData, $attributes['lastName']);
            $compareData        = array('label' => 'Boolean');
            $this->assertEquals($compareData, $attributes['boolean']);
            $compareData        = array('label' => 'Date');
            $this->assertEquals($compareData, $attributes['date']);
            $compareData        = array('label' => 'Date Time');
            $this->assertEquals($compareData, $attributes['dateTime']);
            $compareData        = array('label' => 'Float');
            $this->assertEquals($compareData, $attributes['float']);
            $compareData        = array('label' => 'Integer');
            $this->assertEquals($compareData, $attributes['integer']);
            $compareData        = array('label' => 'Phone');
            $this->assertEquals($compareData, $attributes['phone']);
            $compareData        = array('label' => 'String');
            $this->assertEquals($compareData, $attributes['string']);
            $compareData        = array('label' => 'Text Area');
            $this->assertEquals($compareData, $attributes['textArea']);
            $compareData        = array('label' => 'Url');
            $this->assertEquals($compareData, $attributes['url']);
            $compareData        = array('label' => 'Drop Down');
            $this->assertEquals($compareData, $attributes['dropDown']);
            $compareData        = array('label' => 'Drop Down 2');
            $this->assertEquals($compareData, $attributes['dropDown2']);
            $compareData        = array('label' => 'Radio Drop Down');
            $this->assertEquals($compareData, $attributes['radioDropDown']);
            $compareData        = array('label' => 'Multi Drop Down');
            $this->assertEquals($compareData, $attributes['multiDropDown']);
            $compareData        = array('label' => 'Tag Cloud');
            $this->assertEquals($compareData, $attributes['tagCloud']);
            $compareData        = array('label' => 'Reported As Attribute');
            $this->assertEquals($compareData, $attributes['reportedAsAttribute']);
            //Currency is treated as a relation reported as an attribute just like drop downs
            $compareData        = array('label' => 'Currency Value');
            $this->assertEquals($compareData, $attributes['currencyValue']);
            //likeContactState is a relation reported as attribute.
            //Makes sure the label is using the proper label translation via attributeLabels
            $compareData        = array('label' => 'A name for a state');
            $this->assertEquals($compareData, $attributes['likeContactState']);
            //Add Dynamically Derived Attributes
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $attributes['owner__User']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $attributes['createdByUser__User']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $attributes['modifiedByUser__User']);
        }

        /**
         * @depends testGetAvailableAttributesForRowsAndColumnsFilters
         */
        public function testGetAvailableAttributesForRowsAndColumnsDisplayColumns()
        {
            $model              = new ReportModelTestItem();
            $rules              = new ReportsTestReportRules();
            $report             = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToRowsAndColumnsReportAdapter($model, $rules, $report->getType());
            $attributes = $adapter->getAttributesForDisplayAttributes();
            $this->assertEquals(26, count($attributes));

            //Includes derived attributes as well
            $compareData        = array('label' => 'Test Calculated', 'derivedAttributeType' => 'CalculatedNumber');
            $this->assertEquals($compareData, $attributes['calculated']);
            $compareData        = array('label' => 'Full Name',       'derivedAttributeType' => 'FullName');
            $this->assertEquals($compareData, $attributes['FullName']);
        }

        /**
         * @depends testGetAvailableAttributesForRowsAndColumnsDisplayColumns
         */
        public function testGetAvailableAttributesForRowsAndColumnsOrderBys()
        {
            $model              = new ReportModelTestItem();
            $rules              = new ReportsTestReportRules();
            $report             = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToRowsAndColumnsReportAdapter($model, $rules, $report->getType());
            $attributes = $adapter->getAttributesForOrderBys();
            $this->assertEquals(21, count($attributes));

            $compareData        = array('label' => 'Created Date Time');
            $this->assertEquals($compareData, $attributes['createdDateTime']);
            $compareData        = array('label' => 'Modified Date Time');
            $this->assertEquals($compareData, $attributes['modifiedDateTime']);
            $compareData        = array('label' => 'First Name');
            $this->assertEquals($compareData, $attributes['firstName']);
            $compareData        = array('label' => 'Last Name');
            $this->assertEquals($compareData, $attributes['lastName']);
            $compareData        = array('label' => 'Boolean');
            $this->assertEquals($compareData, $attributes['boolean']);
            $compareData        = array('label' => 'Date');
            $this->assertEquals($compareData, $attributes['date']);
            $compareData        = array('label' => 'Date Time');
            $this->assertEquals($compareData, $attributes['dateTime']);
            $compareData        = array('label' => 'Float');
            $this->assertEquals($compareData, $attributes['float']);
            $compareData        = array('label' => 'Integer');
            $this->assertEquals($compareData, $attributes['integer']);
            $compareData        = array('label' => 'Phone');
            $this->assertEquals($compareData, $attributes['phone']);
            $compareData        = array('label' => 'String');
            $this->assertEquals($compareData, $attributes['string']);
            $compareData        = array('label' => 'Url');
            $this->assertEquals($compareData, $attributes['url']);
            $compareData        = array('label' => 'Drop Down');
            $this->assertEquals($compareData, $attributes['dropDown']);
            $compareData        = array('label' => 'Drop Down 2');
            $this->assertEquals($compareData, $attributes['dropDown2']);
            $compareData        = array('label' => 'Radio Drop Down');
            $this->assertEquals($compareData, $attributes['radioDropDown']);
            $compareData        = array('label' => 'Reported As Attribute');
            $this->assertEquals($compareData, $attributes['reportedAsAttribute']);
            //Currency is treated as a relation reported as an attribute just like drop downs
            $compareData        = array('label' => 'Currency Value');
            $this->assertEquals($compareData, $attributes['currencyValue']);
            //likeContactState is a relation reported as attribute.
            //Makes sure the label is using the proper label translation via attributeLabels
            $compareData        = array('label' => 'A name for a state');
            $this->assertEquals($compareData, $attributes['likeContactState']);
            //Add Dynamically Derived Attributes
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $attributes['owner__User']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $attributes['createdByUser__User']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $attributes['modifiedByUser__User']);
        }

        /**
         * @depends testGetAvailableAttributesForRowsAndColumnsOrderBys
         */
        public function testGetAvailableAttributesForSummationFilters()
        {
            $model              = new ReportModelTestItem();
            $rules              = new ReportsTestReportRules();
            $report             = new Report();
            $report->setType(Report::TYPE_SUMMATION);
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToSummationReportAdapter($model, $rules, $report->getType());
            $attributes = $adapter->getAttributesForFilters();
            $this->assertEquals(24, count($attributes));

            $compareData        = array('label' => 'Created Date Time');
            $this->assertEquals($compareData, $attributes['createdDateTime']);
            $compareData        = array('label' => 'Modified Date Time');
            $this->assertEquals($compareData, $attributes['modifiedDateTime']);
            $compareData        = array('label' => 'First Name');
            $this->assertEquals($compareData, $attributes['firstName']);
            $compareData        = array('label' => 'Last Name');
            $this->assertEquals($compareData, $attributes['lastName']);
            $compareData        = array('label' => 'Boolean');
            $this->assertEquals($compareData, $attributes['boolean']);
            $compareData        = array('label' => 'Date');
            $this->assertEquals($compareData, $attributes['date']);
            $compareData        = array('label' => 'Date Time');
            $this->assertEquals($compareData, $attributes['dateTime']);
            $compareData        = array('label' => 'Float');
            $this->assertEquals($compareData, $attributes['float']);
            $compareData        = array('label' => 'Integer');
            $this->assertEquals($compareData, $attributes['integer']);
            $compareData        = array('label' => 'Phone');
            $this->assertEquals($compareData, $attributes['phone']);
            $compareData        = array('label' => 'String');
            $this->assertEquals($compareData, $attributes['string']);
            $compareData        = array('label' => 'Text Area');
            $this->assertEquals($compareData, $attributes['textArea']);
            $compareData        = array('label' => 'Url');
            $this->assertEquals($compareData, $attributes['url']);
            $compareData        = array('label' => 'Drop Down');
            $this->assertEquals($compareData, $attributes['dropDown']);
            $compareData        = array('label' => 'Drop Down 2');
            $this->assertEquals($compareData, $attributes['dropDown2']);
            $compareData        = array('label' => 'Radio Drop Down');
            $this->assertEquals($compareData, $attributes['radioDropDown']);
            $compareData        = array('label' => 'Multi Drop Down');
            $this->assertEquals($compareData, $attributes['multiDropDown']);
            $compareData        = array('label' => 'Tag Cloud');
            $this->assertEquals($compareData, $attributes['tagCloud']);
            $compareData        = array('label' => 'Reported As Attribute');
            $this->assertEquals($compareData, $attributes['reportedAsAttribute']);
            //Currency is treated as a relation reported as an attribute just like drop downs
            $compareData        = array('label' => 'Currency Value');
            $this->assertEquals($compareData, $attributes['currencyValue']);
            //likeContactState is a relation reported as attribute.
            //Makes sure the label is using the proper label translation via attributeLabels
            $compareData        = array('label' => 'A name for a state');
            $this->assertEquals($compareData, $attributes['likeContactState']);
            //Add Dynamically Derived Attributes
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $attributes['owner__User']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $attributes['createdByUser__User']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $attributes['modifiedByUser__User']);
        }

        /**
         * @depends testGetAvailableAttributesForSummationFilters
         */
        public function testGetAvailableAttributesForSummationDisplayAttributes()
        {
            //Depends on the selected Group By values.  This will determine what is available for display
            //Without any group by displayed, nothing is available
            $model              = new ReportModelTestItem();
            $rules              = new ReportsTestReportRules();
            $report             = new Report();
            $report->setType(Report::TYPE_SUMMATION);
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToSummationReportAdapter($model, $rules, $report->getType());
            $attributes = $adapter->getAttributesForDisplayAttributes($report->getGroupBys());
            $this->assertEquals(0, count($attributes));

            //Select dropDown as the groupBy attribute
            $report             = new Report();
            $report->setType(Report::TYPE_SUMMATION);
            $groupBy            = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $groupBy->attributeIndexOrDerivedType = 'dropDown';
            $report->setModuleClassName('ReportsTestModule');
            $report->addGroupBy($groupBy);
            $adapter            = new ModelRelationsAndAttributesToSummationReportAdapter($model, $rules, $report->getType());
            $attributes         = $adapter->getAttributesForDisplayAttributes($report->getGroupBys());
            $this->assertEquals(22, count($attributes));
            $compareData        = array('label' => 'Drop Down');
            $this->assertEquals($compareData, $attributes['dropDown']);

            $compareData        = array('label' => 'Count');
            $this->assertEquals($compareData, $attributes['Count']);

            $compareData        = array('label' => 'Created Date Time -(Min)');
            $this->assertEquals($compareData, $attributes['createdDateTime__Minimum']);
            $compareData        = array('label' => 'Created Date Time -(Max)');
            $this->assertEquals($compareData, $attributes['createdDateTime__Maximum']);
            $compareData        = array('label' => 'Modified Date Time -(Min)');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Minimum']);
            $compareData        = array('label' => 'Modified Date Time -(Max)');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Maximum']);

            $compareData        = array('label' => 'Date -(Min)');
            $this->assertEquals($compareData, $attributes['date__Minimum']);
            $compareData        = array('label' => 'Date -(Max)');
            $this->assertEquals($compareData, $attributes['date__Maximum']);
            $compareData        = array('label' => 'Date Time -(Min)');
            $this->assertEquals($compareData, $attributes['dateTime__Minimum']);
            $compareData        = array('label' => 'Date Time -(Max)');
            $this->assertEquals($compareData, $attributes['dateTime__Maximum']);

            $compareData        = array('label' => 'Float -(Min)');
            $this->assertEquals($compareData, $attributes['float__Minimum']);
            $compareData        = array('label' => 'Float -(Max)');
            $this->assertEquals($compareData, $attributes['float__Maximum']);
            $compareData        = array('label' => 'Float -(Sum)');
            $this->assertEquals($compareData, $attributes['float__Summation']);
            $compareData        = array('label' => 'Float -(Avg)');
            $this->assertEquals($compareData, $attributes['float__Average']);

            $compareData        = array('label' => 'Integer -(Min)');
            $this->assertEquals($compareData, $attributes['integer__Minimum']);
            $compareData        = array('label' => 'Integer -(Max)');
            $this->assertEquals($compareData, $attributes['integer__Maximum']);
            $compareData        = array('label' => 'Integer -(Sum)');
            $this->assertEquals($compareData, $attributes['integer__Summation']);
            $compareData        = array('label' => 'Integer -(Avg)');
            $this->assertEquals($compareData, $attributes['integer__Average']);

            $compareData        = array('label' => 'Currency Value -(Min)');
            $this->assertEquals($compareData, $attributes['currencyValue__Minimum']);
            $compareData        = array('label' => 'Currency Value -(Max)');
            $this->assertEquals($compareData, $attributes['currencyValue__Maximum']);
            $compareData        = array('label' => 'Currency Value -(Sum)');
            $this->assertEquals($compareData, $attributes['currencyValue__Summation']);
            $compareData        = array('label' => 'Currency Value -(Avg)');
            $this->assertEquals($compareData, $attributes['currencyValue__Average']);

            //Add a second groupBy attribute radioDropDown on the same model
            $report             = new Report();
            $report->setType(Report::TYPE_SUMMATION);
            $groupBy            = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $groupBy->attributeIndexOrDerivedType = 'dropDown';
            $groupBy2           = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $groupBy2->attributeIndexOrDerivedType = 'radioDropDown';
            $report->setModuleClassName('ReportsTestModule');
            $report->addGroupBy($groupBy);
            $report->addGroupBy($groupBy2);
            $adapter            = new ModelRelationsAndAttributesToSummationReportAdapter($model, $rules, $report->getType());
            $attributes         = $adapter->getAttributesForDisplayAttributes($report->getGroupBys());
            $this->assertEquals(23, count($attributes));
            $compareData        = array('label' => 'Drop Down');
            $this->assertEquals($compareData, $attributes['dropDown']);
            $compareData        = array('label' => 'Radio Drop Down');
            $this->assertEquals($compareData, $attributes['radioDropDown']);
        }

        /**
         * @depends testGetAvailableAttributesForSummationDisplayAttributes
         */
        public function testGroupingOnDifferentModelAndMakingSureCorrectDisplayAttributesAreAvailable()
        {
            //Grouping on ReportModelTestItem, but we are looking at attributes in ReportModelTestItem2
            //so the name attribute should not show up as being available.
            $report             = new Report();
            $report->setType(Report::TYPE_SUMMATION);
            $groupBy            = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $groupBy->attributeIndexOrDerivedType = 'phone';
            $model              = new ReportModelTestItem2();
            $rules              = new ReportsTestReportRules();
            $report->addGroupBy($groupBy);
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToSummationReportAdapter($model, $rules, $report->getType());
            $attributes = $adapter->getAttributesForDisplayAttributes($report->getGroupBys(), new ReportModelTestItem(), 'hasOne');
            $this->assertEquals(5, count($attributes));
            $compareData        = array('label' => 'Count');
            $this->assertEquals($compareData, $attributes['Count']);
            $compareData        = array('label' => 'Created Date Time -(Min)');
            $this->assertEquals($compareData, $attributes['createdDateTime__Minimum']);
            $compareData        = array('label' => 'Created Date Time -(Max)');
            $this->assertEquals($compareData, $attributes['createdDateTime__Maximum']);
            $compareData        = array('label' => 'Modified Date Time -(Min)');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Minimum']);
            $compareData        = array('label' => 'Modified Date Time -(Max)');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Maximum']);

            //Now test where there is a second group by and it is the name attribute on ReportModelTestItem2
            $groupBy            = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $groupBy->attributeIndexOrDerivedType = 'hasOne___name';
            $report->addGroupBy($groupBy);
            $adapter            = new ModelRelationsAndAttributesToSummationReportAdapter($model, $rules, $report->getType());
            $attributes         = $adapter->getAttributesForDisplayAttributes($report->getGroupBys(), new ReportModelTestItem(), 'hasOne');
            $this->assertEquals(6, count($attributes));
            $compareData        = array('label' => 'Name');
            $this->assertEquals($compareData, $attributes['name']);
            $compareData        = array('label' => 'Count');
            $this->assertEquals($compareData, $attributes['Count']);
            $compareData        = array('label' => 'Created Date Time -(Min)');
            $this->assertEquals($compareData, $attributes['createdDateTime__Minimum']);
            $compareData        = array('label' => 'Created Date Time -(Max)');
            $this->assertEquals($compareData, $attributes['createdDateTime__Maximum']);
            $compareData        = array('label' => 'Modified Date Time -(Min)');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Minimum']);
            $compareData        = array('label' => 'Modified Date Time -(Max)');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Maximum']);

            //Now test where there is a second group by and it is the name attribute on ReportModelTestItem2 but we
            //are coming from a different relationship
            $adapter            = new ModelRelationsAndAttributesToSummationReportAdapter($model, $rules, $report->getType());
            $attributes = $adapter->getAttributesForDisplayAttributes($report->getGroupBys(), new ReportModelTestItem(), 'hasOneAgain');
            $this->assertEquals(5, count($attributes));
            $this->assertFalse(isset($attributes['name']));

            //Test where the group by is 2 levels above
            $model              = new ReportModelTestItem3();
            $groupBy            = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $groupBy->attributeIndexOrDerivedType = 'hasOne___hasMany3___somethingOn3';
            $report->addGroupBy($groupBy);
            $adapter            = new ModelRelationsAndAttributesToSummationReportAdapter($model, $rules, $report->getType());
            $attributes = $adapter->getAttributesForDisplayAttributes($report->getGroupBys(), new ReportModelTestItem2(), 'hasMany3');
            $this->assertEquals(6, count($attributes));
            $compareData        = array('label' => 'Something On 3');
            $this->assertEquals($compareData, $attributes['somethingOn3']);
            $compareData        = array('label' => 'Count');
            $this->assertEquals($compareData, $attributes['Count']);
            $compareData        = array('label' => 'Created Date Time -(Min)');
            $this->assertEquals($compareData, $attributes['createdDateTime__Minimum']);
            $compareData        = array('label' => 'Created Date Time -(Max)');
            $this->assertEquals($compareData, $attributes['createdDateTime__Maximum']);
            $compareData        = array('label' => 'Modified Date Time -(Min)');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Minimum']);
            $compareData        = array('label' => 'Modified Date Time -(Max)');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Maximum']);
        }

        /**
         * @depends testGroupingOnDifferentModelAndMakingSureCorrectDisplayAttributesAreAvailable
         */
        public function testGetAvailableAttributesForSummationOrderBys()
        {
            //You can only order what is grouped on.
            //You can order on nothing because there are no group bys selected
            $model              = new ReportModelTestItem();
            $rules              = new ReportsTestReportRules();
            $report             = new Report();
            $report->setType(Report::TYPE_SUMMATION);
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToSummationReportAdapter($model, $rules, $report->getType());
            $attributes         = $adapter->getAttributesForOrderBys($report->getGroupBys());
            $this->assertEquals(0, count($attributes));

            //A group by is selected on the base model ReportModelTestItem
            $groupBy            = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $groupBy->attributeIndexOrDerivedType = 'dropDown';
            $model              = new ReportModelTestItem();
            $report->addGroupBy($groupBy);
            $adapter            = new ModelRelationsAndAttributesToSummationReportAdapter($model, $rules, $report->getType());
            $attributes         = $adapter->getAttributesForOrderBys($report->getGroupBys());
            $this->assertEquals(1, count($attributes));
            $compareData        = array('label' => 'Drop Down');
            $this->assertEquals($compareData, $attributes['dropDown']);

            //Now test when a group by is also selected on the related ReportModelTestItem2
            //Should return as phone, since the getAttributesForOrderBys is called from the
            //@see ReportRelationsAndAttributesToTreeAdapter
            $groupBy            = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $groupBy->attributeIndexOrDerivedType = 'hasOne___phone';
            $model              = new ReportModelTestItem2();
            $report->addGroupBy($groupBy);
            $adapter            = new ModelRelationsAndAttributesToSummationReportAdapter($model, $rules, $report->getType());
            $attributes = $adapter->getAttributesForOrderBys($report->getGroupBys(), array(), new ReportModelTestItem(), 'hasOne');
            $this->assertEquals(1, count($attributes));
            $compareData        = array('label' => 'Phone');
            $this->assertEquals($compareData, $attributes['phone']);

            //Now test a third group by on the base model ReportModelTestItem
            $groupBy            = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $groupBy->attributeIndexOrDerivedType = 'radioDropDown';
            $model              = new ReportModelTestItem();
            $report->addGroupBy($groupBy);
            $adapter            = new ModelRelationsAndAttributesToSummationReportAdapter($model, $rules, $report->getType());
            $attributes         = $adapter->getAttributesForOrderBys($report->getGroupBys());
            $this->assertEquals(2, count($attributes));
            $compareData        = array('label' => 'Drop Down');
            $this->assertEquals($compareData, $attributes['dropDown']);
            $compareData        = array('label' => 'Radio Drop Down');
            $this->assertEquals($compareData, $attributes['radioDropDown']);
        }

        /**
         * @depends testGetAvailableAttributesForSummationOrderBys
         */
        public function testGetAvailableAttributesForSummationOrderBysThatAreDisplayCalculations()
        {
            //You can only order what is grouped on or a display calculation attribute
            $model              = new ReportModelTestItem();
            $rules              = new ReportsTestReportRules();
            $report             = new Report();
            $report->setType(Report::TYPE_SUMMATION);
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToSummationReportAdapter($model, $rules, $report->getType());
            $attributes         = $adapter->getAttributesForOrderBys($report->getGroupBys(), $report->getDisplayAttributes());
            $this->assertEquals(0, count($attributes));

            $displayAttribute   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_SUMMATION);
            $this->assertNull($displayAttribute->label);
            $displayAttribute->attributeIndexOrDerivedType = 'createdDateTime__Minimum';
            $report->addDisplayAttribute($displayAttribute);
            $adapter            = new ModelRelationsAndAttributesToSummationReportAdapter($model, $rules, $report->getType());
            $attributes         = $adapter->getAttributesForOrderBys($report->getGroupBys(), $report->getDisplayAttributes());
            $this->assertEquals(1, count($attributes));
            $this->assertTrue(isset($attributes['createdDateTime__Minimum']));
            $compareData        = array('label' => 'Created Date Time -(Min)');
            $this->assertEquals($compareData, $attributes['createdDateTime__Minimum']);

            $displayAttribute   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_SUMMATION);
            $this->assertNull($displayAttribute->label);
            $displayAttribute->attributeIndexOrDerivedType = 'integer__Minimum';
            $report->addDisplayAttribute($displayAttribute);
            $adapter            = new ModelRelationsAndAttributesToSummationReportAdapter($model, $rules, $report->getType());
            $attributes         = $adapter->getAttributesForOrderBys($report->getGroupBys(), $report->getDisplayAttributes());
            $this->assertEquals(2, count($attributes));
            $this->assertTrue(isset($attributes['integer__Minimum']));
            $compareData        = array('label' => 'Integer -(Min)');
            $this->assertEquals($compareData, $attributes['integer__Minimum']);

            //This should not add because we are at the wrong point in the chain
            $displayAttribute   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                  Report::TYPE_SUMMATION);
            $this->assertNull($displayAttribute->label);
            $displayAttribute->attributeIndexOrDerivedType = 'hasOne___createdDateTime__Minimum';
            $report->addDisplayAttribute($displayAttribute);
            $adapter            = new ModelRelationsAndAttributesToSummationReportAdapter($model, $rules, $report->getType());
            $attributes         = $adapter->getAttributesForOrderBys($report->getGroupBys(), $report->getDisplayAttributes());
            $this->assertEquals(2, count($attributes));
            $this->assertFalse(isset($attributes['hasOne___createdDateTime__Minimum']));
        }

        /**
         * @depends testGetAvailableAttributesForSummationOrderBysThatAreDisplayCalculations
         */
        public function testGetAvailableAttributesForSummationGroupBys()
        {
            $model              = new ReportModelTestItem();
            $rules              = new ReportsTestReportRules();
            $report             = new Report();
            $report->setType(Report::TYPE_SUMMATION);
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToSummationReportAdapter($model, $rules, $report->getType());
            $attributes         = $adapter->getAttributesForGroupBys();
            $this->assertEquals(38, count($attributes));

            //Date/DateTime columns first...
            $compareData        = array('label' => 'Date -(Year)');
            $this->assertEquals($compareData, $attributes['date__Year']);
            $compareData        = array('label' => 'Date -(Quarter)');
            $this->assertEquals($compareData, $attributes['date__Quarter']);
            $compareData        = array('label' => 'Date -(Month)');
            $this->assertEquals($compareData, $attributes['date__Month']);
            $compareData        = array('label' => 'Date -(Week)');
            $this->assertEquals($compareData, $attributes['date__Week']);
            $compareData        = array('label' => 'Date -(Day)');
            $this->assertEquals($compareData, $attributes['date__Day']);

            $compareData        = array('label' => 'Date Time -(Year)');
            $this->assertEquals($compareData, $attributes['dateTime__Year']);
            $compareData        = array('label' => 'Date Time -(Quarter)');
            $this->assertEquals($compareData, $attributes['dateTime__Quarter']);
            $compareData        = array('label' => 'Date Time -(Month)');
            $this->assertEquals($compareData, $attributes['dateTime__Month']);
            $compareData        = array('label' => 'Date Time -(Week)');
            $this->assertEquals($compareData, $attributes['dateTime__Week']);
            $compareData        = array('label' => 'Date Time -(Day)');
            $this->assertEquals($compareData, $attributes['dateTime__Day']);

            $compareData        = array('label' => 'Created Date Time -(Year)');
            $this->assertEquals($compareData, $attributes['createdDateTime__Year']);
            $compareData        = array('label' => 'Created Date Time -(Quarter)');
            $this->assertEquals($compareData, $attributes['createdDateTime__Quarter']);
            $compareData        = array('label' => 'Created Date Time -(Month)');
            $this->assertEquals($compareData, $attributes['createdDateTime__Month']);
            $compareData        = array('label' => 'Created Date Time -(Week)');
            $this->assertEquals($compareData, $attributes['createdDateTime__Week']);
            $compareData        = array('label' => 'Created Date Time -(Day)');
            $this->assertEquals($compareData, $attributes['createdDateTime__Day']);

            $compareData        = array('label' => 'Modified Date Time -(Year)');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Year']);
            $compareData        = array('label' => 'Modified Date Time -(Quarter)');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Quarter']);
            $compareData        = array('label' => 'Modified Date Time -(Month)');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Month']);
            $compareData        = array('label' => 'Modified Date Time -(Week)');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Week']);
            $compareData        = array('label' => 'Modified Date Time -(Day)');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Day']);

            //and then the rest of the attributes... (exclude text area)
            $compareData        = array('label' => 'First Name');
            $this->assertEquals($compareData, $attributes['firstName']);
            $compareData        = array('label' => 'Last Name');
            $this->assertEquals($compareData, $attributes['lastName']);
            $compareData        = array('label' => 'Boolean');
            $this->assertEquals($compareData, $attributes['boolean']);
            $compareData        = array('label' => 'Float');
            $this->assertEquals($compareData, $attributes['float']);
            $compareData        = array('label' => 'Integer');
            $this->assertEquals($compareData, $attributes['integer']);
            $compareData        = array('label' => 'Phone');
            $this->assertEquals($compareData, $attributes['phone']);
            $compareData        = array('label' => 'String');
            $this->assertEquals($compareData, $attributes['string']);
            $compareData        = array('label' => 'Url');
            $this->assertEquals($compareData, $attributes['url']);
            $compareData        = array('label' => 'Drop Down');
            $this->assertEquals($compareData, $attributes['dropDown']);
            $compareData        = array('label' => 'Drop Down 2');
            $this->assertEquals($compareData, $attributes['dropDown2']);
            $compareData        = array('label' => 'Radio Drop Down');
            $this->assertEquals($compareData, $attributes['radioDropDown']);
            $compareData        = array('label' => 'Reported As Attribute');
            $this->assertEquals($compareData, $attributes['reportedAsAttribute']);
            $compareData        = array('label' => 'Currency Value');
            $this->assertEquals($compareData, $attributes['currencyValue']);
            $compareData        = array('label' => 'A name for a state');
            $this->assertEquals($compareData, $attributes['likeContactState']);
            //Add Id field
            $compareData        = array('label' => 'Id');
            $this->assertEquals($compareData, $attributes['id']);
            //Add Dynamically Derived Attributes
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $attributes['owner__User']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $attributes['createdByUser__User']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $attributes['modifiedByUser__User']);
        }

        /**
         * @depends testGetAvailableAttributesForSummationGroupBys
         */
        public function testGetAvailableAttributesForSummationDrillDownDisplayAttributes()
        {
            //Should be the same as the RowsAndColumns display columns
            $model              = new ReportModelTestItem();
            $rules              = new ReportsTestReportRules();
            $report             = new Report();
            $report->setType(Report::TYPE_SUMMATION);
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToSummationReportAdapter($model, $rules, $report->getType());
            $attributes = $adapter->getForDrillDownAttributes();
            $this->assertEquals(26, count($attributes));

            //Includes derived attributes as well
            $compareData        = array('label' => 'Test Calculated', 'derivedAttributeType' => 'CalculatedNumber');
            $this->assertEquals($compareData, $attributes['calculated']);
            $compareData        = array('label' => 'Full Name',       'derivedAttributeType' => 'FullName');
            $this->assertEquals($compareData, $attributes['FullName']);
        }

        /**
         * @depends testGetAvailableAttributesForSummationDrillDownDisplayAttributes
         */
        public function testGetAvailableAttributesForMatrixFilters()
        {
            $model              = new ReportModelTestItem();
            $rules              = new ReportsTestReportRules();
            $report             = new Report();
            $report->setType(Report::TYPE_MATRIX);
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToMatrixReportAdapter($model, $rules, $report->getType());
            $attributes         = $adapter->getAttributesForFilters();
            $this->assertEquals(24, count($attributes));

            $compareData        = array('label' => 'Created Date Time');
            $this->assertEquals($compareData, $attributes['createdDateTime']);
            $compareData        = array('label' => 'Modified Date Time');
            $this->assertEquals($compareData, $attributes['modifiedDateTime']);
            $compareData        = array('label' => 'First Name');
            $this->assertEquals($compareData, $attributes['firstName']);
            $compareData        = array('label' => 'Last Name');
            $this->assertEquals($compareData, $attributes['lastName']);
            $compareData        = array('label' => 'Boolean');
            $this->assertEquals($compareData, $attributes['boolean']);
            $compareData        = array('label' => 'Date');
            $this->assertEquals($compareData, $attributes['date']);
            $compareData        = array('label' => 'Date Time');
            $this->assertEquals($compareData, $attributes['dateTime']);
            $compareData        = array('label' => 'Float');
            $this->assertEquals($compareData, $attributes['float']);
            $compareData        = array('label' => 'Integer');
            $this->assertEquals($compareData, $attributes['integer']);
            $compareData        = array('label' => 'Phone');
            $this->assertEquals($compareData, $attributes['phone']);
            $compareData        = array('label' => 'String');
            $this->assertEquals($compareData, $attributes['string']);
            $compareData        = array('label' => 'Text Area');
            $this->assertEquals($compareData, $attributes['textArea']);
            $compareData        = array('label' => 'Url');
            $this->assertEquals($compareData, $attributes['url']);
            $compareData        = array('label' => 'Drop Down');
            $this->assertEquals($compareData, $attributes['dropDown']);
            $compareData        = array('label' => 'Drop Down 2');
            $this->assertEquals($compareData, $attributes['dropDown2']);
            $compareData        = array('label' => 'Radio Drop Down');
            $this->assertEquals($compareData, $attributes['radioDropDown']);
            $compareData        = array('label' => 'Multi Drop Down');
            $this->assertEquals($compareData, $attributes['multiDropDown']);
            $compareData        = array('label' => 'Tag Cloud');
            $this->assertEquals($compareData, $attributes['tagCloud']);
            $compareData        = array('label' => 'Reported As Attribute');
            $this->assertEquals($compareData, $attributes['reportedAsAttribute']);
            //Currency is treated as a relation reported as an attribute just like drop downs
            $compareData        = array('label' => 'Currency Value');
            $this->assertEquals($compareData, $attributes['currencyValue']);
            //likeContactState is a relation reported as attribute.
            //Makes sure the label is using the proper label translation via attributeLabels
            $compareData        = array('label' => 'A name for a state');
            $this->assertEquals($compareData, $attributes['likeContactState']);
            //Add Dynamically Derived Attributes
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $attributes['owner__User']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $attributes['createdByUser__User']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $attributes['modifiedByUser__User']);
        }

        /**
         * @depends testGetAvailableAttributesForMatrixFilters
         */
        public function testGetAvailableAttributesForMatrixGroupBys()
        {
            $model              = new ReportModelTestItem();
            $rules              = new ReportsTestReportRules();
            $report             = new Report();
            $report->setType(Report::TYPE_MATRIX);
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToMatrixReportAdapter($model, $rules, $report->getType());
            $attributes         = $adapter->getAttributesForGroupBys();
            $this->assertEquals(34, count($attributes));

            //Date/DateTime columns first...
            $compareData        = array('label' => 'Date -(Year)');
            $this->assertEquals($compareData, $attributes['date__Year']);
            $compareData        = array('label' => 'Date -(Quarter)');
            $this->assertEquals($compareData, $attributes['date__Quarter']);
            $compareData        = array('label' => 'Date -(Month)');
            $this->assertEquals($compareData, $attributes['date__Month']);
            $compareData        = array('label' => 'Date -(Week)');
            $this->assertEquals($compareData, $attributes['date__Week']);
            $compareData        = array('label' => 'Date -(Day)');
            $this->assertEquals($compareData, $attributes['date__Day']);

            $compareData        = array('label' => 'Date Time -(Year)');
            $this->assertEquals($compareData, $attributes['dateTime__Year']);
            $compareData        = array('label' => 'Date Time -(Quarter)');
            $this->assertEquals($compareData, $attributes['dateTime__Quarter']);
            $compareData        = array('label' => 'Date Time -(Month)');
            $this->assertEquals($compareData, $attributes['dateTime__Month']);
            $compareData        = array('label' => 'Date Time -(Week)');
            $this->assertEquals($compareData, $attributes['dateTime__Week']);
            $compareData        = array('label' => 'Date Time -(Day)');
            $this->assertEquals($compareData, $attributes['dateTime__Day']);

            $compareData        = array('label' => 'Created Date Time -(Year)');
            $this->assertEquals($compareData, $attributes['createdDateTime__Year']);
            $compareData        = array('label' => 'Created Date Time -(Quarter)');
            $this->assertEquals($compareData, $attributes['createdDateTime__Quarter']);
            $compareData        = array('label' => 'Created Date Time -(Month)');
            $this->assertEquals($compareData, $attributes['createdDateTime__Month']);
            $compareData        = array('label' => 'Created Date Time -(Week)');
            $this->assertEquals($compareData, $attributes['createdDateTime__Week']);
            $compareData        = array('label' => 'Created Date Time -(Day)');
            $this->assertEquals($compareData, $attributes['createdDateTime__Day']);

            $compareData        = array('label' => 'Modified Date Time -(Year)');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Year']);
            $compareData        = array('label' => 'Modified Date Time -(Quarter)');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Quarter']);
            $compareData        = array('label' => 'Modified Date Time -(Month)');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Month']);
            $compareData        = array('label' => 'Modified Date Time -(Week)');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Week']);
            $compareData        = array('label' => 'Modified Date Time -(Day)');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Day']);

            //and then the rest of the attributes... (exclude text area)
            $compareData        = array('label' => 'Boolean');
            $this->assertEquals($compareData, $attributes['boolean']);
            $compareData        = array('label' => 'Drop Down');
            $this->assertEquals($compareData, $attributes['dropDown']);
            $compareData        = array('label' => 'Drop Down 2');
            $this->assertEquals($compareData, $attributes['dropDown2']);
            $compareData        = array('label' => 'Radio Drop Down');
            $this->assertEquals($compareData, $attributes['radioDropDown']);
            $compareData        = array('label' => 'Reported As Attribute');
            $this->assertEquals($compareData, $attributes['reportedAsAttribute']);
            $compareData        = array('label' => 'A name for a state');
            $this->assertEquals($compareData, $attributes['likeContactState']);
            //Add Dynamically Derived Attributes
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $attributes['owner__User']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $attributes['createdByUser__User']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $attributes['modifiedByUser__User']);
            //Text, Url, and Id attributes
            $compareData        = array('label' => 'Id');
            $this->assertEquals($compareData, $attributes['id']);
            $compareData        = array('label' => 'String');
            $this->assertEquals($compareData, $attributes['string']);
            $compareData        = array('label' => 'Url');
            $this->assertEquals($compareData, $attributes['url']);
            $compareData        = array('label' => 'First Name');
            $this->assertEquals($compareData, $attributes['firstName']);
            $compareData        = array('label' => 'Last Name');
            $this->assertEquals($compareData, $attributes['lastName']);
        }

        /**
         * @depends testGetAvailableAttributesForMatrixGroupBys
         */
        public function testGetAvailableAttributesForMatrixDisplayAttributes()
        {
            //Without any group by displayed, nothing is available
            $model              = new ReportModelTestItem();
            $rules              = new ReportsTestReportRules();
            $report             = new Report();
            $report->setType(Report::TYPE_MATRIX);
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToMatrixReportAdapter($model, $rules, $report->getType());
            $attributes         = $adapter->getAttributesForDisplayAttributes($report->getGroupBys());
            $this->assertEquals(0, count($attributes));

            //Select dropDown as the groupBy attribute
            $groupBy            = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $groupBy->attributeIndexOrDerivedType = 'dropDown';
            $report             = new Report();
            $report->setType(Report::TYPE_MATRIX);
            $report->setModuleClassName('ReportsTestModule');
            $report->addGroupBy($groupBy);
            $adapter            = new ModelRelationsAndAttributesToMatrixReportAdapter($model, $rules, $report->getType());
            $attributes         = $adapter->getAttributesForDisplayAttributes($report->getGroupBys());
            $this->assertEquals(21, count($attributes));
            $compareData        = array('label' => 'Count');
            $this->assertEquals($compareData, $attributes['Count']);

            $compareData        = array('label' => 'Created Date Time -(Min)');
            $this->assertEquals($compareData, $attributes['createdDateTime__Minimum']);
            $compareData        = array('label' => 'Created Date Time -(Max)');
            $this->assertEquals($compareData, $attributes['createdDateTime__Maximum']);
            $compareData        = array('label' => 'Modified Date Time -(Min)');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Minimum']);
            $compareData        = array('label' => 'Modified Date Time -(Max)');
            $this->assertEquals($compareData, $attributes['modifiedDateTime__Maximum']);

            $compareData        = array('label' => 'Date -(Min)');
            $this->assertEquals($compareData, $attributes['date__Minimum']);
            $compareData        = array('label' => 'Date -(Max)');
            $this->assertEquals($compareData, $attributes['date__Maximum']);
            $compareData        = array('label' => 'Date Time -(Min)');
            $this->assertEquals($compareData, $attributes['dateTime__Minimum']);
            $compareData        = array('label' => 'Date Time -(Max)');
            $this->assertEquals($compareData, $attributes['dateTime__Maximum']);

            $compareData        = array('label' => 'Float -(Min)');
            $this->assertEquals($compareData, $attributes['float__Minimum']);
            $compareData        = array('label' => 'Float -(Max)');
            $this->assertEquals($compareData, $attributes['float__Maximum']);
            $compareData        = array('label' => 'Float -(Sum)');
            $this->assertEquals($compareData, $attributes['float__Summation']);
            $compareData        = array('label' => 'Float -(Avg)');
            $this->assertEquals($compareData, $attributes['float__Average']);

            $compareData        = array('label' => 'Integer -(Min)');
            $this->assertEquals($compareData, $attributes['integer__Minimum']);
            $compareData        = array('label' => 'Integer -(Max)');
            $this->assertEquals($compareData, $attributes['integer__Maximum']);
            $compareData        = array('label' => 'Integer -(Sum)');
            $this->assertEquals($compareData, $attributes['integer__Summation']);
            $compareData        = array('label' => 'Integer -(Avg)');
            $this->assertEquals($compareData, $attributes['integer__Average']);

            $compareData        = array('label' => 'Currency Value -(Min)');
            $this->assertEquals($compareData, $attributes['currencyValue__Minimum']);
            $compareData        = array('label' => 'Currency Value -(Max)');
            $this->assertEquals($compareData, $attributes['currencyValue__Maximum']);
            $compareData        = array('label' => 'Currency Value -(Sum)');
            $this->assertEquals($compareData, $attributes['currencyValue__Summation']);
            $compareData        = array('label' => 'Currency Value -(Avg)');
            $this->assertEquals($compareData, $attributes['currencyValue__Average']);
        }

        /**
         * @depends testGetAvailableAttributesForMatrixDisplayAttributes
         */
        public function testIsRelation()
        {
            $model              = new ReportModelTestItem();
            $rules              = new ReportsTestReportRules();
            $report             = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToMatrixReportAdapter($model, $rules, $report->getType());
            $this->assertTrue($adapter->isReportedOnAsARelation('hasOne'));
            $this->assertFalse($adapter->isReportedOnAsARelation('garbage'));
            $this->assertFalse($adapter->isReportedOnAsARelation('float'));
            $this->assertFalse($adapter->isReportedOnAsARelation('firstname'));
            $this->assertFalse($adapter->isReportedOnAsARelation('createdByUser__User'));
            $this->assertTrue($adapter->isReportedOnAsARelation('modifiedByUser'));
            $this->assertTrue($adapter->isReportedOnAsARelation('model5ViaItem'));
            $this->assertTrue($adapter->isReportedOnAsARelation('primaryEmail'));
            $this->assertFalse($adapter->isReportedOnAsARelation('dropDown'));

            $model              = new ReportModelTestItem5();
            $rules              = new ReportsTestReportRules();
            $report             = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToMatrixReportAdapter($model, $rules, $report->getType());
            $this->assertTrue ($adapter->isReportedOnAsARelation('ReportModelTestItem2__reportItems__Inferred'));
            $this->assertTrue ($adapter->isReportedOnAsARelation('ReportModelTestItem__reportItems__Inferred'));
            $this->assertTrue ($adapter->isReportedOnAsARelation('ReportModelTestItem__reportItems__Inferred'));
        }

        /**
         * @depends testIsRelation
         */
        public function testIsRelationASingularRelation()
        {
            $model              = new ReportModelTestItem();
            $rules              = new ReportsTestReportRules();
            $report             = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToRowsAndColumnsReportAdapter($model, $rules, $report->getType());
            $this->assertTrue($adapter->isRelationASingularRelation('hasOne'));
            $this->assertFalse($adapter->isRelationASingularRelation('hasMany'));

            $model              = new ReportModelTestItem5();
            $rules              = new ReportsTestReportRules();
            $report             = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToRowsAndColumnsReportAdapter($model, $rules, $report->getType());
            $this->assertFalse($adapter->isRelationASingularRelation('ReportModelTestItem2__reportItems__Inferred'));
            $this->assertFalse($adapter->isRelationASingularRelation('ReportModelTestItem__reportItems__Inferred'));
        }

        /**
         * @depends testIsRelationASingularRelation
         */
        public function testGetFilterValueElementTypeForNonStatefulAttributes()
        {
            $model              = new ReportModelTestItem();
            $rules              = new ReportsTestReportRules();
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, Report::TYPE_ROWS_AND_COLUMNS);
            $this->assertEquals('BooleanForWizardStaticDropDown', $adapter->getFilterValueElementType('boolean'));
            $this->assertEquals('MixedCurrencyValueTypes',        $adapter->getFilterValueElementType('currencyValue'));
            $this->assertEquals('MixedDateTypesForReport',        $adapter->getFilterValueElementType('date'));
            $this->assertEquals('MixedDateTypesForReport',        $adapter->getFilterValueElementType('dateTime'));
            $this->assertEquals('StaticDropDownForReport',        $adapter->getFilterValueElementType('dropDown'));
            $this->assertEquals('MixedNumberTypes',               $adapter->getFilterValueElementType('float'));
            $this->assertEquals('MixedNumberTypes',               $adapter->getFilterValueElementType('integer'));
            $this->assertEquals('StaticDropDownForReport',        $adapter->getFilterValueElementType('multiDropDown'));
            $this->assertEquals('Text',                           $adapter->getFilterValueElementType('phone'));
            $this->assertEquals('StaticDropDownForReport',        $adapter->getFilterValueElementType('radioDropDown'));
            $this->assertEquals('Text',                           $adapter->getFilterValueElementType('string'));
            $this->assertEquals('StaticDropDownForReport',        $adapter->getFilterValueElementType('tagCloud'));
            $this->assertEquals('Text',                           $adapter->getFilterValueElementType('textArea'));
            $this->assertEquals('Text',                           $adapter->getFilterValueElementType('url'));
        }

        /**
         * @depends testGetFilterValueElementTypeForNonStatefulAttributes
         */
        public function testGetFilterValueElementTypeForAStatefulAttribute()
        {
            $model              = new ReportModelTestItem();
            $rules              = new ReportsTestReportRules();
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, Report::TYPE_ROWS_AND_COLUMNS);
            $this->assertEquals('AllContactStatesStaticDropDownForWizardModel', $adapter->getFilterValueElementType('likeContactState'));

            $model              = new ReportModelTestItem();
            $rules              = new ReportsAlternateStateTestReportRules();
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, Report::TYPE_ROWS_AND_COLUMNS);
            $this->assertEquals('AllContactStatesStaticDropDownForWizardModel', $adapter->getFilterValueElementType('likeContactState'));
        }

        /**
         * @depends testGetFilterValueElementTypeForAStatefulAttribute
         */
        public function testGetFilterValueElementType()
        {
            $model              = new ReportModelTestItem();
            $rules              = new ReportsTestReportRules();
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, Report::TYPE_ROWS_AND_COLUMNS);
            $this->assertEquals('BooleanForWizardStaticDropDown', $adapter->getFilterValueElementType('boolean'));
            $this->assertEquals('MixedCurrencyValueTypes',        $adapter->getFilterValueElementType('currencyValue'));
            $this->assertEquals('MixedDateTypesForReport',        $adapter->getFilterValueElementType('date'));
            $this->assertEquals('MixedDateTypesForReport',        $adapter->getFilterValueElementType('dateTime'));
            $this->assertEquals('StaticDropDownForReport',        $adapter->getFilterValueElementType('dropDown'));
            $this->assertEquals('MixedNumberTypes',               $adapter->getFilterValueElementType('float'));
            $this->assertEquals('MixedNumberTypes',               $adapter->getFilterValueElementType('integer'));
            $this->assertEquals('StaticDropDownForReport',        $adapter->getFilterValueElementType('multiDropDown'));
            $this->assertEquals('UserNameId',                     $adapter->getFilterValueElementType('owner__User'));
            $this->assertEquals('Text',                           $adapter->getFilterValueElementType('phone'));
            $this->assertEquals('StaticDropDownForReport',        $adapter->getFilterValueElementType('radioDropDown'));
            $this->assertEquals('Text',                           $adapter->getFilterValueElementType('string'));
            $this->assertEquals('StaticDropDownForReport',        $adapter->getFilterValueElementType('tagCloud'));
            $this->assertEquals('Text',                           $adapter->getFilterValueElementType('textArea'));
            $this->assertEquals('Text',                           $adapter->getFilterValueElementType('url'));

            $this->assertEquals('AllContactStatesStaticDropDownForWizardModel', $adapter->getFilterValueElementType('likeContactState'));
            $model              = new ReportModelTestItem();
            $rules              = new ReportsAlternateStateTestReportRules();
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, Report::TYPE_ROWS_AND_COLUMNS);
            $this->assertEquals('AllContactStatesStaticDropDownForWizardModel', $adapter->getFilterValueElementType('likeContactState'));
        }

        /**
         * @depends testGetFilterValueElementType
         */
        public function testGetAvailableOperatorsType()
        {
            $model              = new ReportModelTestItem();
            $rules              = new ReportsTestReportRules();
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, Report::TYPE_ROWS_AND_COLUMNS);
            $this->assertEquals(ModelAttributeToReportOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_STRING,
                                $adapter->getAvailableOperatorsType('string'));

            $model              = new ReportModelTestItem();
            $rules              = new ReportsTestReportRules();
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, Report::TYPE_ROWS_AND_COLUMNS);
            $this->assertNull($adapter->getAvailableOperatorsType('boolean'));
            $this->assertEquals(ModelAttributeToReportOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_NUMBER,
                                $adapter->getAvailableOperatorsType('currencyValue'));
            $this->assertNull($adapter->getAvailableOperatorsType('date'));
            $this->assertNull($adapter->getAvailableOperatorsType('dateTime'));
            $this->assertEquals(ModelAttributeToReportOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_DROPDOWN,
                                $adapter->getAvailableOperatorsType('dropDown'));
            $this->assertEquals(ModelAttributeToReportOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_NUMBER,
                                $adapter->getAvailableOperatorsType('float'));
            $this->assertEquals(ModelAttributeToReportOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_NUMBER,
                                $adapter->getAvailableOperatorsType('integer'));
            $this->assertEquals(ModelAttributeToReportOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_DROPDOWN,
                                $adapter->getAvailableOperatorsType('multiDropDown'));
            $this->assertEquals(ModelAttributeToReportOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_HAS_ONE,
                                $adapter->getAvailableOperatorsType('owner__User'));
            $this->assertEquals(ModelAttributeToReportOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_STRING,
                                $adapter->getAvailableOperatorsType('phone'));
            $this->assertEquals(ModelAttributeToReportOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_DROPDOWN,
            $adapter->getAvailableOperatorsType('radioDropDown'));
            $this->assertEquals(ModelAttributeToReportOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_STRING,
                                $adapter->getAvailableOperatorsType('string'));
            $this->assertEquals(ModelAttributeToReportOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_DROPDOWN,
                                $adapter->getAvailableOperatorsType('tagCloud'));
            $this->assertEquals(ModelAttributeToReportOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_STRING,
                                $adapter->getAvailableOperatorsType('textArea'));
            $this->assertEquals(ModelAttributeToReportOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_STRING,
                                $adapter->getAvailableOperatorsType('url'));

            $this->assertEquals(ModelAttributeToReportOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_DROPDOWN,
                                $adapter->getAvailableOperatorsType('likeContactState'));
            $model              = new ReportModelTestItem();
            $rules              = new ReportsAlternateStateTestReportRules();
            $adapter            = new ModelRelationsAndAttributesToReportAdapter($model, $rules, Report::TYPE_ROWS_AND_COLUMNS);
            $this->assertEquals(ModelAttributeToReportOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_DROPDOWN,
                                $adapter->getAvailableOperatorsType('likeContactState'));
        }
    }
?>
