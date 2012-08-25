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

    class DynamicSearchDataProviderMetadataAdapterTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        /**
         * Testing using between, since it has more than 1 clause for the first position
         */
        public function testDynamicSearchUsingInBetweenDateTime()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $sanitizedDynamicSearchAttributes = array(
                0 => array(
                    'dateTime__DateTime'          => array( 'type'       => 'Between',
                                                            'firstDate'  => '2012-08-01',
                                                            'secondDate' => '2012-08-15'),
                    'attributeIndexOrDerivedType' => 'dateTime__DateTime',
                    'structurePosition'           => '1',
                ),
                1 => array(
                    'iiiMember'                   => 'abc',
                    'attributeIndexOrDerivedType' => 'iiiMember',
                    'structurePosition'           => '2',
                ),
            );
            $dynamicStructure = '1 AND 2';
            $metadata         = array('clauses' => array(), 'structure' => '');
            $metadataAdapter = new DynamicSearchDataProviderMetadataAdapter(
                $metadata,
                new IIISearchFormTestModel(new III(false)),
                (int)Yii::app()->user->userModel->id,
                $sanitizedDynamicSearchAttributes,
                $dynamicStructure);
            $metadata = $metadataAdapter->getAdaptedDataProviderMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'dateTime',
                    'operatorType'         => 'greaterThanOrEqualTo',
                    'value'                => '2012-08-01 00:00:00',
                ),
                2 => array(
                    'attributeName'        => 'dateTime',
                    'operatorType'         => 'lessThanOrEqualTo',
                    'value'                => '2012-08-15 23:59:59',
                ),
                3 => array(
                    'attributeName'        => 'iiiMember',
                    'operatorType'         => 'startsWith',
                    'value'                => 'abc',
                ),
            );
            $compareStructure = '((1 and 2) and 3)';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        /**
         * @depends testDynamicSearchUsingInBetweenDateTime
         * Test a regular attribute, a single level of nesting, and deeper nesting.
         */
        public function testDynamicSearchWithNestedData()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $sanitizedDynamicSearchAttributes = array(
                0 => array(
                    'iiiMember'                   => 'someThing',
                    'attributeIndexOrDerivedType' => 'iiiMember',
                    'structurePosition'           => '1',
                ),
                2 => array(
                    'ccc' => array(
                        'relatedData'                 => true,
                        'cccMember'                   => 'cccMemberValue',
                    ),
                    'attributeIndexOrDerivedType' => 'ccc' . DynamicSearchUtil::RELATION_DELIMITER . 'cccMember',
                    'structurePosition'           => '2',
                ),
                4 => array(
                    'ccc' => array(
                        'relatedData'           => true,
                        'bbb'                   => array(
                            'relatedData'                 => true,
                            'bbbMember'                   => 'bbbMemberValue',

                        ),
                    ),
                    'attributeIndexOrDerivedType' => 'ccc' . DynamicSearchUtil::RELATION_DELIMITER . 'bbb' . DynamicSearchUtil::RELATION_DELIMITER . 'bbbMember',
                    'structurePosition'           => '3',
                ),
            );
            $dynamicStructure = '(1 or 2) and 3';
            $metadata         = array('clauses' => array(), 'structure' => '');
            $metadataAdapter = new DynamicSearchDataProviderMetadataAdapter(
                $metadata,
                new IIISearchFormTestModel(new III(false)),
                (int)Yii::app()->user->userModel->id,
                $sanitizedDynamicSearchAttributes,
                $dynamicStructure);
            $metadata = $metadataAdapter->getAdaptedDataProviderMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'iiiMember',
                    'operatorType'         => 'startsWith',
                    'value'                => 'someThing',
                ),
                2 => array(
                    'attributeName'        => 'ccc',
                    'relatedModelData'     => array(
                        'attributeName'        => 'cccMember',
                        'operatorType'         => 'startsWith',
                        'value'                => 'cccMemberValue',
                    ),
                ),
                3 => array(
                    'attributeName'        => 'ccc',
                    'relatedModelData'     => array(
                        'attributeName'        => 'bbb',
                        'relatedModelData'     => array(
                            'attributeName'        => 'bbbMember',
                            'operatorType'         => 'startsWith',
                            'value'                => 'bbbMemberValue',
                        ),
                    ),
                ),
            );
            $compareStructure = '((1 or 2) and 3)';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        /**
         * @depends testDynamicSearchWithNestedData
         */
        public function testDynamicSearch()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $sanitizedDynamicSearchAttributes = array(
                0 => array(
                    'iiiMember'                   => 'someThing',
                    'attributeIndexOrDerivedType' => 'iiiMember',
                    'structurePosition'           => '1',
                ),
                2 => array(
                    'iiiMember2'                   => 'someThing2',
                    'attributeIndexOrDerivedType' => 'iiiMember2',
                    'structurePosition'           => '2',
                ),
                4 => array(
                    'iiiMember2'                   => 'someThing3',
                    'attributeIndexOrDerivedType' => 'iiiMember2',
                    'structurePosition'           => '3',
                )
            );
            $dynamicStructure = '(1 or 2) and 3';
            $metadata         = array('clauses' => array(), 'structure' => '');
            $metadataAdapter = new DynamicSearchDataProviderMetadataAdapter(
                $metadata,
                new IIISearchFormTestModel(new III(false)),
                (int)Yii::app()->user->userModel->id,
                $sanitizedDynamicSearchAttributes,
                $dynamicStructure);
            $metadata = $metadataAdapter->getAdaptedDataProviderMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'iiiMember',
                    'operatorType'         => 'startsWith',
                    'value'                => 'someThing',
                ),
                2 => array(
                    'attributeName'        => 'iiiMember2',
                    'operatorType'         => 'startsWith',
                    'value'                => 'someThing2',
                ),
                3 => array(
                    'attributeName'        => 'iiiMember2',
                    'operatorType'         => 'startsWith',
                    'value'                => 'someThing3',
                ),
            );
            $compareStructure = '((1 or 2) and 3)';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        /**
         * @depends testDynamicSearch
         */
        public function testDynamicSearchAndBasicSearchTogether()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $sanitizedDynamicSearchAttributes = array(
                0 => array(
                    'iiiMember'                   => 'someThing',
                    'attributeIndexOrDerivedType' => 'iiiMember',
                    'structurePosition'           => '1',
                ),
                2 => array(
                    'iiiMember2'                   => 'someThing2',
                    'attributeIndexOrDerivedType' => 'iiiMember2',
                    'structurePosition'           => '2',
                ),
                4 => array(
                    'iiiMember2'                   => 'someThing3',
                    'attributeIndexOrDerivedType' => 'iiiMember2',
                    'structurePosition'           => '3',
                )
            );
            $dynamicStructure = '(1 or 2) and 3';
            $metadata         = array('clauses' => array(1 => array(
                                                                    'attributeName'        => 'iiiMember',
                                                                    'operatorType'         => 'startsWith',
                                                                    'value'                => 'someThingFirst'),
                                                         2 => array(
                                                                    'attributeName'        => 'iiiMember',
                                                                    'operatorType'         => 'startsWith',
                                                                    'value'                => 'someThingSecond')),
                                                         'structure' => '1 and 2');
            $metadataAdapter = new DynamicSearchDataProviderMetadataAdapter(
                $metadata,
                new IIISearchFormTestModel(new III(false)),
                (int)Yii::app()->user->userModel->id,
                $sanitizedDynamicSearchAttributes,
                $dynamicStructure);
            $metadata = $metadataAdapter->getAdaptedDataProviderMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'iiiMember',
                    'operatorType'         => 'startsWith',
                    'value'                => 'someThingFirst',
                ),
                2 => array(
                    'attributeName'        => 'iiiMember',
                    'operatorType'         => 'startsWith',
                    'value'                => 'someThingSecond',
                ),
                3 => array(
                    'attributeName'        => 'iiiMember',
                    'operatorType'         => 'startsWith',
                    'value'                => 'someThing',
                ),
                4 => array(
                    'attributeName'        => 'iiiMember2',
                    'operatorType'         => 'startsWith',
                    'value'                => 'someThing2',
                ),
                5 => array(
                    'attributeName'        => 'iiiMember2',
                    'operatorType'         => 'startsWith',
                    'value'                => 'someThing3',
                ),
            );
            $compareStructure = '(1 and 2) and ((3 or 4) and 5)';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        /**
         * @depends testDynamicSearchAndBasicSearchTogether
         */
        public function testDynamicSearchWithNullValues()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $sanitizedDynamicSearchAttributes = array(
                0 => array(
                    'iiiMember'                   => 'someThing',
                    'attributeIndexOrDerivedType' => 'iiiMember',
                    'structurePosition'           => '1',
                ),
                2 => array(
                    'iiiMember2'                   => null, //must be null not '' to show its removal in test
                    'attributeIndexOrDerivedType' => 'iiiMember2',
                    'structurePosition'           => '2',
                ),
                4 => array(
                    'iiiMember2'                   => 'someThing3',
                    'attributeIndexOrDerivedType' => 'iiiMember2',
                    'structurePosition'           => '3',
                )
            );
            $dynamicStructure = '(1 or 2) and 3';
            $metadata         = array('clauses' => array(), 'structure' => '');
            $metadataAdapter = new DynamicSearchDataProviderMetadataAdapter(
                $metadata,
                new IIISearchFormTestModel(new III(false)),
                (int)Yii::app()->user->userModel->id,
                $sanitizedDynamicSearchAttributes,
                $dynamicStructure);
            $metadata = $metadataAdapter->getAdaptedDataProviderMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName'        => 'iiiMember',
                    'operatorType'         => 'startsWith',
                    'value'                => 'someThing',
                ),
                2 => array(
                    'attributeName'        => 'iiiMember2',
                    'operatorType'         => 'startsWith',
                    'value'                => 'someThing3',
                ),
            );
            $compareStructure = '((1) and 2)';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }
    }
?>