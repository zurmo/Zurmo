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
                    'attributeIndexOrDerivedType' => 'ccc' . FormModelUtil::RELATION_DELIMITER . 'cccMember',
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
                    'attributeIndexOrDerivedType' => 'ccc' . FormModelUtil::RELATION_DELIMITER . 'bbb' . FormModelUtil::RELATION_DELIMITER . 'bbbMember',
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

        /**
         * @depends testDynamicSearchWithNullValues
         * Issue with more than 10 clauses meaning 11 and 12 can get replaced with the value for 1 and 2.
         * This test demonstrates the problem and also demonstrates the fix by passing.
         */
        public function testMixedClauseCountsOverTenAndDoTheClausesProperlyTranslateCorrectlyToQuery()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $sanitizedDynamicSearchAttributes = array(
                0 => array(
                    'iiiMember'                   => 'someThing12',
                    'attributeIndexOrDerivedType' => 'iiiMember',
                    'structurePosition'           => '12',
                ),
                1 => array(
                    'iiiMember'                   => 'someThing1',
                    'attributeIndexOrDerivedType' => 'iiiMember',
                    'structurePosition'           => '1',
                ),
                2 => array(
                    'iiiMember'                   => 'someThing2',
                    'attributeIndexOrDerivedType' => 'iiiMember',
                    'structurePosition'           => '2',
                ),
                3 => array(
                    'iiiMember'                   => 'someThing11',
                    'attributeIndexOrDerivedType' => 'iiiMember',
                    'structurePosition'           => '11',
                ),
                4 => array(
                    'iiiMember'                   => 'someThing22',
                    'attributeIndexOrDerivedType' => 'iiiMember',
                    'structurePosition'           => '22',
                )
            );
            $dynamicStructure = '(1 or 11) and 2 and 22 and 12';
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
                    'value'                => 'someThing12',
                ),
                2 => array(
                    'attributeName'        => 'iiiMember',
                    'operatorType'         => 'startsWith',
                    'value'                => 'someThing1',
                ),
                3 => array(
                    'attributeName'        => 'iiiMember',
                    'operatorType'         => 'startsWith',
                    'value'                => 'someThing2',
                ),
                4 => array(
                    'attributeName'        => 'iiiMember',
                    'operatorType'         => 'startsWith',
                    'value'                => 'someThing11',
                ),
                5 => array(
                    'attributeName'        => 'iiiMember',
                    'operatorType'         => 'startsWith',
                    'value'                => 'someThing22',
                ),
            );
            $compareStructure = '((2 or 4) and 3 and 5 and 1)';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

            /**
         * @depends testMixedClauseCountsOverTenAndDoTheClausesProperlyTranslateCorrectlyToQuery
         */
        public function testMoreThanTenClausesProperlyTranslateCorrectlyToQuery()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $sanitizedDynamicSearchAttributes = array(
                0 => array(
                    'iiiMember'                   => 'someThing1',
                    'attributeIndexOrDerivedType' => 'iiiMember',
                    'structurePosition'           => '1',
                ),
                1 => array(
                    'iiiMember'                   => 'someThing2',
                    'attributeIndexOrDerivedType' => 'iiiMember',
                    'structurePosition'           => '2',
                ),
                2 => array(
                    'iiiMember'                   => 'someThing3',
                    'attributeIndexOrDerivedType' => 'iiiMember',
                    'structurePosition'           => '3',
                ),
                3 => array(
                    'iiiMember'                   => 'someThing4',
                    'attributeIndexOrDerivedType' => 'iiiMember',
                    'structurePosition'           => '4',
                ),
                4 => array(
                    'iiiMember'                   => 'someThing5',
                    'attributeIndexOrDerivedType' => 'iiiMember',
                    'structurePosition'           => '5',
                ),
                5 => array(
                    'iiiMember'                   => 'someThing6',
                    'attributeIndexOrDerivedType' => 'iiiMember',
                    'structurePosition'           => '6',
                ),
                6 => array(
                    'iiiMember'                   => 'someThing7',
                    'attributeIndexOrDerivedType' => 'iiiMember',
                    'structurePosition'           => '7',
                ),
                7 => array(
                    'iiiMember'                   => 'someThing8',
                    'attributeIndexOrDerivedType' => 'iiiMember',
                    'structurePosition'           => '8',
                ),
                8 => array(
                    'iiiMember'                   => 'someThing9',
                    'attributeIndexOrDerivedType' => 'iiiMember',
                    'structurePosition'           => '9',
                ),
                9 => array(
                    'iiiMember'                   => 'someThing10',
                    'attributeIndexOrDerivedType' => 'iiiMember',
                    'structurePosition'           => '10',
                ),
                10 => array(
                    'iiiMember'                   => 'someThing11',
                    'attributeIndexOrDerivedType' => 'iiiMember',
                    'structurePosition'           => '11',
                ),
                11 => array(
                    'iiiMember'                   => 'someThing12',
                    'attributeIndexOrDerivedType' => 'iiiMember',
                    'structurePosition'           => '12',
                ),
                12 => array(
                    'iiiMember'                   => 'someThing13',
                    'attributeIndexOrDerivedType' => 'iiiMember',
                    'structurePosition'           => '13',
                ),
            );
            $dynamicStructure = '(1 and 2 and 3 and 4 and 5 and 6 and 7 and 8 and 9 and 10 and 11 and 12 and 13)';
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
                    'value'                => 'someThing1',
                ),
                2 => array(
                    'attributeName'        => 'iiiMember',
                    'operatorType'         => 'startsWith',
                    'value'                => 'someThing2',
                ),
                3 => array(
                    'attributeName'        => 'iiiMember',
                    'operatorType'         => 'startsWith',
                    'value'                => 'someThing3',
                ),
                4 => array(
                    'attributeName'        => 'iiiMember',
                    'operatorType'         => 'startsWith',
                    'value'                => 'someThing4',
                ),
                5 => array(
                    'attributeName'        => 'iiiMember',
                    'operatorType'         => 'startsWith',
                    'value'                => 'someThing5',
                ),
                6 => array(
                    'attributeName'        => 'iiiMember',
                    'operatorType'         => 'startsWith',
                    'value'                => 'someThing6',
                ),
                7 => array(
                    'attributeName'        => 'iiiMember',
                    'operatorType'         => 'startsWith',
                    'value'                => 'someThing7',
                ),
                8 => array(
                    'attributeName'        => 'iiiMember',
                    'operatorType'         => 'startsWith',
                    'value'                => 'someThing8',
                ),
                9 => array(
                    'attributeName'        => 'iiiMember',
                    'operatorType'         => 'startsWith',
                    'value'                => 'someThing9',
                ),
                10 => array(
                    'attributeName'        => 'iiiMember',
                    'operatorType'         => 'startsWith',
                    'value'                => 'someThing10',
                ),
                11 => array(
                    'attributeName'        => 'iiiMember',
                    'operatorType'         => 'startsWith',
                    'value'                => 'someThing11',
                ),
                12 => array(
                    'attributeName'        => 'iiiMember',
                    'operatorType'         => 'startsWith',
                    'value'                => 'someThing12',
                ),
                13 => array(
                    'attributeName'        => 'iiiMember',
                    'operatorType'         => 'startsWith',
                    'value'                => 'someThing13',
                ),
            );
            $compareStructure = '((1 and 2 and 3 and 4 and 5 and 6 and 7 and 8 and 9 and 10 and 11 and 12 and 13))';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testNumberToLetter()
        {
            $this->assertEquals('a', DynamicSearchDataProviderMetadataAdapter::numberToLetter(1));
            $this->assertEquals('b', DynamicSearchDataProviderMetadataAdapter::numberToLetter(2));
            $this->assertEquals('c', DynamicSearchDataProviderMetadataAdapter::numberToLetter(3));
            $this->assertEquals('d', DynamicSearchDataProviderMetadataAdapter::numberToLetter(4));
            $this->assertEquals('e', DynamicSearchDataProviderMetadataAdapter::numberToLetter(5));
            $this->assertEquals('o', DynamicSearchDataProviderMetadataAdapter::numberToLetter(15));
            $this->assertEquals('ss', DynamicSearchDataProviderMetadataAdapter::numberToLetter(45));
        }
    }
?>