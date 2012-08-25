<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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

    class SearchCurrencyValueTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $user = SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = $user;
        }

        public function testSearchCurrencyValueWithoutPassingCurrencyid()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $searchForm = new AAASavedDynamicSearchFormTestModel(new CurrencyValueTestItem());
            $searchForm->dynamicClauses   = array(
                                                array('structurePosition'           => '1',
                                                      'attributeIndexOrDerivedType' => 'amount',
                                                      'amount' => array ('value' => '100'),
                                                     )
                                                 );
            $searchForm->dynamicStructure = '1';
            $searchForm->validateDynamicClauses('dynamicClauses', array());
            $this->assertFalse($searchForm->hasErrors());
            $searchForm->clearErrors();
        }

        public function testSearchCurrencyValueWithPassingCurrencyidAndValue()
        {
            $super              = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $searchAttributes   = array(array('structurePosition'           => '1',
                                              'attributeIndexOrDerivedType' => 'amount',
                                              'amount' => array('relatedData' => true,
                                                                  'currency'    => array('id' => '1'),
                                                                  'value'       => '100'),
                                              ));
            $searchForm         = new AAASavedDynamicSearchFormTestModel(new CurrencyValueTestItem());
            $searchForm->dynamicClauses   = $searchAttributes;
            $searchForm->dynamicStructure = '1';
            $searchForm->validateDynamicClauses('dynamicClauses', array());
            $this->assertFalse($searchForm->hasErrors());
            $searchForm->clearErrors();

            $dynamicStructure = '1';
            $metadata         = array('clauses' => array(), 'structure' => '');
            $metadataAdapter = new DynamicSearchDataProviderMetadataAdapter(
                $metadata,
                new AAASavedDynamicSearchFormTestModel(new CurrencyValueTestItem(false)),
                (int)Yii::app()->user->userModel->id,
                $searchAttributes,
                $dynamicStructure);
            $metadata = $metadataAdapter->getAdaptedDataProviderMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'amount',
                    'relatedModelData'     => array(
                        'attributeName'        => 'currency',
                        'relatedAttributeName' => 'id',
                        'operatorType'         => 'equals',
                        'value'                => '1',
                    ),
                ),
                2 => array(
                    'attributeName'        => 'amount',
                    'relatedModelData'     => array(
                        'attributeName'        => 'value',
                        'operatorType'         => 'equals',
                        'value'                => '100',
                    ),
                ),
            );
            $compareStructure = '(1 and 2)';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testSearchCurrencyValueWithPassingCurrencyidAndNoValue()
        {
            $super              = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $searchAttributes   = array(array('structurePosition'           => '1',
                                              'attributeIndexOrDerivedType' => 'amount',
                                              'amount' => array('relatedData' => true,
                                                                  'currency'    => array('id' => '1')),
                                              ));
            $searchForm         = new AAASavedDynamicSearchFormTestModel(new CurrencyValueTestItem());
            $searchForm->dynamicClauses   = $searchAttributes;
            $searchForm->dynamicStructure = '1';
            $searchForm->validateDynamicClauses('dynamicClauses', array());
            $this->assertFalse($searchForm->hasErrors());
            $searchForm->clearErrors();

            $dynamicStructure = '1';
            $metadata         = array('clauses' => array(), 'structure' => '');
            $metadataAdapter = new DynamicSearchDataProviderMetadataAdapter(
                $metadata,
                new AAASavedDynamicSearchFormTestModel(new CurrencyValueTestItem(false)),
                (int)Yii::app()->user->userModel->id,
                $searchAttributes,
                $dynamicStructure);
            $metadata = $metadataAdapter->getAdaptedDataProviderMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'amount',
                    'relatedModelData'     => array(
                        'attributeName'        => 'currency',
                        'relatedAttributeName' => 'id',
                        'operatorType'         => 'equals',
                        'value'                => '1',
                    ),
                ),
            );
            $compareStructure = '(1)';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testSearchCurrencyValueWithPassingCurrencyidAndANullValue()
        {
            $super              = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $searchAttributes   = array(array('structurePosition'           => '1',
                                              'attributeIndexOrDerivedType' => 'amount',
                                              'amount' => array('relatedData' => true,
                                                                  'currency'    => array('id' => '1'),
                                                                  'value'       => null),
                                              ));
            $searchForm         = new AAASavedDynamicSearchFormTestModel(new CurrencyValueTestItem());
            $searchForm->dynamicClauses   = $searchAttributes;
            $searchForm->dynamicStructure = '1';
            $searchForm->validateDynamicClauses('dynamicClauses', array());
            $this->assertFalse($searchForm->hasErrors());
            $searchForm->clearErrors();

            $dynamicStructure = '1';
            $metadata         = array('clauses' => array(), 'structure' => '');
            $metadataAdapter = new DynamicSearchDataProviderMetadataAdapter(
                $metadata,
                new AAASavedDynamicSearchFormTestModel(new CurrencyValueTestItem(false)),
                (int)Yii::app()->user->userModel->id,
                $searchAttributes,
                $dynamicStructure);
            $metadata = $metadataAdapter->getAdaptedDataProviderMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'amount',
                    'relatedModelData'     => array(
                        'attributeName'        => 'currency',
                        'relatedAttributeName' => 'id',
                        'operatorType'         => 'equals',
                        'value'                => '1',
                    ),
                ),
            );
            $compareStructure = '(1)';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }
    }
?>