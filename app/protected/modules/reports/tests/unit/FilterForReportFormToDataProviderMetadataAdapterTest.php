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

    class FilterForReportFormToDataProviderMetadataAdapterTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setup()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testDateAttributeWithModifier()
        {
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                   Report::TYPE_SUMMATION);
            $filter->attributeIndexOrDerivedType = 'date__Day';
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $filter->value                       = 'Zurmo';
            $metadataAdapter                     = new FilterForReportFormToDataProviderMetadataAdapter($filter);
            $metadata                            = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'date',
                    'operatorType'         => 'equals',
                    'value'                => 'Zurmo',
                    'modifierType'         => 'Day',
                ),
            );
            $compareStructure = '1';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testDateTimeAttributeWithModifier()
        {
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                   Report::TYPE_SUMMATION);
            $filter->attributeIndexOrDerivedType = 'dateTime__Week';
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $filter->value                       = 'Zurmo';
            $metadataAdapter                     = new FilterForReportFormToDataProviderMetadataAdapter($filter);
            $metadata                            = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'dateTime',
                    'operatorType'         => 'equals',
                    'value'                => 'Zurmo',
                    'modifierType'         => 'Week',
                ),
            );
            $compareStructure = '1';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testNonRelatedNonDerivedStringAttribute()
        {
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                   Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'string';
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $filter->value                       = 'Zurmo';
            $metadataAdapter                     = new FilterForReportFormToDataProviderMetadataAdapter($filter);
            $metadata                            = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'string',
                    'operatorType'         => 'equals',
                    'value'                => 'Zurmo',
                ),
            );
            $compareStructure = '1';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testRelatedNonDerivedOwnedStringAttribute()
        {
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'primaryAddress___street1';
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $filter->value                       = 'Zurmo';
            $metadataAdapter                     = new FilterForReportFormToDataProviderMetadataAdapter($filter);
            $metadata                            = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'street1',
                    'operatorType'         => 'equals',
                    'value'                => 'Zurmo',
                ),
            );
            $compareStructure = '1';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testNonRelatedNonDerivedBooleanAttribute()
        {
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'boolean';
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $filter->value                       = '1';
            $metadataAdapter                     = new FilterForReportFormToDataProviderMetadataAdapter($filter);
            $metadata                            = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'boolean',
                    'operatorType'         => 'equals',
                    'value'                => 1,
                ),
            );
            $compareStructure = '1';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testNonRelatedNonDerivedDateAttribute()
        {
            //Test non between
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'date';
            $filter->valueType                   = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $filter->value                       = '1991-03-04';
            $metadataAdapter                     = new FilterForReportFormToDataProviderMetadataAdapter($filter);
            $metadata                            = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'date',
                    'operatorType'         => 'equals',
                    'value'                => '1991-03-04',
                ),
            );
            $compareStructure = '1';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);

            //Test non between
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'date';
            $filter->valueType                   = MixedDateTypesSearchFormAttributeMappingRules::TYPE_BETWEEN;
            $filter->value                       = '1991-05-05';
            $filter->secondValue                 = '1991-06-05';
            $metadataAdapter                     = new FilterForReportFormToDataProviderMetadataAdapter($filter);
            $metadata                            = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'date',
                    'operatorType'         => 'greaterThanOrEqualTo',
                    'value'                => '1991-05-05',
                ),
                2 => array(
                    'attributeName'        => 'date',
                    'operatorType'         => 'lessThanOrEqualTo',
                    'value'                => '1991-06-05',
                ),

            );
            $compareStructure = '(1 and 2)';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testNonRelatedNonDerivedDateTimeAttribute()
        {
            //Test non between
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'dateTime';
            $filter->valueType                   = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $filter->value                       = '1991-03-04';
            $metadataAdapter                     = new FilterForReportFormToDataProviderMetadataAdapter($filter);
            $metadata                            = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'dateTime',
                    'operatorType'         => 'greaterThanOrEqualTo',
                    'value'                => '1991-03-04 00:00:00',
                ),
                2 => array(
                    'attributeName'        => 'dateTime',
                    'operatorType'         => 'lessThanOrEqualTo',
                    'value'                => '1991-03-04 23:59:59',
                ),
            );
            $compareStructure = '(1 and 2)';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);

            //Test non between
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'dateTime';
            $filter->valueType                   = MixedDateTypesSearchFormAttributeMappingRules::TYPE_BETWEEN;
            $filter->value                       = '1991-05-05';
            $filter->secondValue                 = '1991-06-05';
            $metadataAdapter                     = new FilterForReportFormToDataProviderMetadataAdapter($filter);
            $metadata                            = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'dateTime',
                    'operatorType'         => 'greaterThanOrEqualTo',
                    'value'                => '1991-05-05 00:00:00',
                ),
                2 => array(
                    'attributeName'        => 'dateTime',
                    'operatorType'         => 'lessThanOrEqualTo',
                    'value'                => '1991-06-05 23:59:59',
                ),
            );
            $compareStructure = '(1 and 2)';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testNonRelatedNonDerivedIntegerAttribute()
        {
            //Test non between
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'integer';
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $filter->value                       = '534';
            $metadataAdapter                     = new FilterForReportFormToDataProviderMetadataAdapter($filter);
            $metadata                            = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'integer',
                    'operatorType'         => 'equals',
                    'value'                => '534',
                ),
            );
            $compareStructure = '1';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);

            //Test non between
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'integer';
            $filter->operator                    = OperatorRules::TYPE_BETWEEN;
            $filter->value                       = '10';
            $filter->secondValue                 = '20';
            $metadataAdapter                     = new FilterForReportFormToDataProviderMetadataAdapter($filter);
            $metadata                            = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'integer',
                    'operatorType'         => 'greaterThanOrEqualTo',
                    'value'                => '10',
                ),
                2 => array(
                    'attributeName'        => 'integer',
                    'operatorType'         => 'lessThanOrEqualTo',
                    'value'                => '20',
                ),

            );
            $compareStructure = '(1 and 2)';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testCurrencyValueAttributeWithoutCurrencySpecified()
        {
            //Test non between
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'currencyValue';
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $filter->value                       = '534.23';
            $metadataAdapter                     = new FilterForReportFormToDataProviderMetadataAdapter($filter);
            $metadata                            = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'currencyValue',
                    'relatedAttributeName' => 'value',
                    'operatorType'         => 'equals',
                    'value'                => '534.23',
                ),
            );
            $compareStructure = '1';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);

            //Test non between
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'currencyValue';
            $filter->operator                    = OperatorRules::TYPE_BETWEEN;
            $filter->value                       = '10.05';
            $filter->secondValue                 = '20.45';
            $metadataAdapter                     = new FilterForReportFormToDataProviderMetadataAdapter($filter);
            $metadata                            = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'currencyValue',
                    'relatedAttributeName' => 'value',
                    'operatorType'         => 'greaterThanOrEqualTo',
                    'value'                => '10.05',
                ),
                2 => array(
                    'attributeName'        => 'currencyValue',
                    'relatedAttributeName' => 'value',
                    'operatorType'         => 'lessThanOrEqualTo',
                    'value'                => '20.45',
                ),

            );
            $compareStructure = '(1 and 2)';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testCurrencyValueAttributeWithCurrencySpecified()
        {
            //Test non between
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'currencyValue';
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $filter->value                       = '534.23';
            $filter->currencyIdForValue          = 8;
            $metadataAdapter                     = new FilterForReportFormToDataProviderMetadataAdapter($filter);
            $metadata                            = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'currencyValue',
                    'relatedAttributeName' => 'value',
                    'operatorType'         => 'equals',
                    'value'                => '534.23',
                ),
                2 => array(
                    'attributeName'        => 'currencyValue',
                    'relatedModelData'     => array(
                        'attributeName'        => 'currency',
                        'relatedAttributeName' => 'id',
                        'operatorType'         => 'equals',
                        'value'                => 8),
                ),
            );
            $compareStructure = '(1 and 2)';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);

            //Test non between
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'currencyValue';
            $filter->operator                    = OperatorRules::TYPE_BETWEEN;
            $filter->value                       = '10.05';
            $filter->secondValue                 = '20.45';
            $filter->currencyIdForValue          = 8;
            $metadataAdapter                     = new FilterForReportFormToDataProviderMetadataAdapter($filter);
            $metadata                            = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'currencyValue',
                    'relatedAttributeName' => 'value',
                    'operatorType'         => 'greaterThanOrEqualTo',
                    'value'                => '10.05',
                ),
                2 => array(
                    'attributeName'        => 'currencyValue',
                    'relatedAttributeName' => 'value',
                    'operatorType'         => 'lessThanOrEqualTo',
                    'value'                => '20.45',
                ),
                3 => array(
                    'attributeName'        => 'currencyValue',
                    'relatedModelData'     => array(
                        'attributeName'        => 'currency',
                        'relatedAttributeName' => 'id',
                        'operatorType'         => 'equals',
                        'value'                => 8),
                ),
            );
            $compareStructure = '(1 and 2 and 3)';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testRelatedDropDownAttribute()
        {
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                   Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'dropDown';
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $filter->value                       = 'Zurmo';
            $metadataAdapter                     = new FilterForReportFormToDataProviderMetadataAdapter($filter);
            $metadata                            = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'dropDown',
                    'relatedAttributeName' => 'value',
                    'operatorType'         => 'equals',
                    'value'                => 'Zurmo',
                ),
            );
            $compareStructure = '1';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);

            //Test OneOf
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                   Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'dropDown';
            $filter->operator                    = OperatorRules::TYPE_ONE_OF;
            $filter->value                       = array('a', 'b', 'c');
            $metadataAdapter                     = new FilterForReportFormToDataProviderMetadataAdapter($filter);
            $metadata                            = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'dropDown',
                    'relatedAttributeName' => 'value',
                    'operatorType'         => 'oneOf',
                    'value'                => array('a', 'b', 'c'),
                ),
            );
            $compareStructure = '1';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testRelatedMultiSelectDropDownAttribute()
        {
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                   Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'multiDropDown';
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $filter->value                       = 'Zurmo';
            $metadataAdapter                     = new FilterForReportFormToDataProviderMetadataAdapter($filter);
            $metadata                            = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'multiDropDown',
                    'relatedAttributeName' => 'values',
                    'operatorType'         => 'equals',
                    'value'                => 'Zurmo',
                ),
            );
            $compareStructure = '1';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);

            //Test OneOf
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                   Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'multiDropDown';
            $filter->operator                    = OperatorRules::TYPE_ONE_OF;
            $filter->value                       = array('a', 'b', 'c');
            $metadataAdapter                     = new FilterForReportFormToDataProviderMetadataAdapter($filter);
            $metadata                            = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'multiDropDown',
                    'relatedAttributeName' => 'values',
                    'operatorType'         => 'oneOf',
                    'value'                => array('a', 'b', 'c'),
                ),
            );
            $compareStructure = '1';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testRelatedNonDerivedIdAttribute()
        {
            //likeContactState
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                   Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'likeContactState';
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $filter->value                       = '5';
            $metadataAdapter                     = new FilterForReportFormToDataProviderMetadataAdapter($filter);
            $metadata                            = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'likeContactState',
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => '5',
                ),
            );
            $compareStructure = '1';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testDerivedIdAttribute()
        {
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                   Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'owner__User';
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $filter->value                       = '7';
            $metadataAdapter                     = new FilterForReportFormToDataProviderMetadataAdapter($filter);
            $metadata                            = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'owner',
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => '7',
                ),
            );
            $compareStructure = '1';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testIsNull()
        {
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                   Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'string';
            $filter->operator                    = OperatorRules::TYPE_IS_NULL;
            $metadataAdapter                     = new FilterForReportFormToDataProviderMetadataAdapter($filter);
            $metadata                            = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'string',
                    'operatorType'         => 'isNull',
                    'value'                =>  null,
                ),
            );
            $compareStructure = '1';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testIsNotNull()
        {
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                   Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'string';
            $filter->operator                    = OperatorRules::TYPE_IS_NOT_NULL;
            $metadataAdapter                     = new FilterForReportFormToDataProviderMetadataAdapter($filter);
            $metadata                            = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'string',
                    'operatorType'         => 'isNotNull',
                    'value'                =>  null,
                ),
            );
            $compareStructure = '1';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }
    }
?>