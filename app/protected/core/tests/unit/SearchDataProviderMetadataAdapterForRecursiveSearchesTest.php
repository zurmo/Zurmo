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

    /**
     * Test class to specifically test nested or related model data searches.
     * @see ModelDataProviderUtilRecursiveDataTest

    Models and relations used in this class
                                III -> hasOne EEE
                                  |
                                  | CCC hasMany III
                                  | III hasOne  CCC
                                CCC -> hasOne EEE
                                  |
                                  | CCC hasMany BBB
         /-> hasOne EEE           | BBB hasOne  CCC
         |                        |
         |                        |/---> BBB hasOne GGG -> hasOne EEE
         |                        ||
         |                        ||
         FFF <-hasOnehasMany ->  BBB <- manyMany -> DDD -> hasOne EEE
                                  |
          FFF hasOne  BBB         | BBB hasMany AAA
          BBB hasMany FFF         | AAA hasOne  BBB
                                  |
                                  |
                                 AAA --- hasOne HHH -> hasOne EEE
                                      HHH hasOneBelongsTo AAA
     */
    class SearchDataProviderMetadataAdapterForRecursiveSearchesTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        public function testGetAdaptedMetadataForConcatedAttributesAcrossRelations()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $searchAttributes = array(
                'aaaMember' => 'Vomitorio Corp',
                'bbb' => array(
                    'relatedData' => true,
                    'bbbMember'  => 'bbbMemberValue',
                    'ccc'    => array(
                        'relatedData' => true,
                        'cccMember' => 'cccMemberValue',
                        'eee' => array(
                            'relatedData' => true,
                            'eeeMember' => 'eeeMemberValue',
                        ),
                        'concatedName' => 'Jimmy Jam',
                        'iii'    => array(
                           'relatedData' => true,
                           'concatedName' => 'Jimmy Jam',
                            'eee' => array(
                                'relatedData' => true,
                                'eeeMember' => 'eeeMemberValue',
                            )
                        )
                    )
                )
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new AAA(false),
                1,
                $searchAttributes
            );
            $metadata       = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                //Standard attribute on model
                1 => array(
                    'attributeName' => 'aaaMember',
                    'operatorType'  => 'startsWith',
                    'value'         => 'Vomitorio Corp',
                ),
                //Standard attribute on related model
                2 => array(
                    'attributeName'        => 'bbb',
                        'relatedModelData'  => array(
                            'attributeName'     => 'bbbMember',
                            'operatorType'      => 'startsWith',
                            'value'             => 'bbbMemberValue',
                        ),
                ),
                //Standard attribute on related related model
                3 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'     => 'cccMember',
                                'operatorType'      => 'startsWith',
                                'value'             => 'cccMemberValue',
                            ),
                    ),
                ),
                //Standard attribute on related related related model
                4 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'         => 'eee',
                                    'relatedModelData'  => array(
                                        'attributeName'         => 'eeeMember',
                                        'operatorType'          => 'startsWith',
                                        'value'                 => 'eeeMemberValue',
                                    ),
                            ),
                    ),
                ),
                //Concated nested attribute first part
                5 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'     => 'cccMember',
                                'operatorType'      => 'startsWith',
                                'value'             => 'Jimmy Jam',
                            ),
                    ),
                ),
                //Concated nested attribute second part
                6 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'     => 'cccMember2',
                                'operatorType'      => 'startsWith',
                                'value'             => 'Jimmy Jam',
                            ),
                    ),
                ),
                //Concated nested attribute third part
                7 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'concatedAttributeNames'    => array('cccMember', 'cccMember2'),
                                'operatorType'      => 'startsWith',
                                'value'             => 'Jimmy Jam',
                            ),
                    ),
                ),
                //Concated nested nested attribute first part
                8 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'     => 'iii',
                                    'relatedModelData'  => array(
                                        'attributeName'     => 'iiiMember',
                                        'operatorType'      => 'startsWith',
                                        'value'             => 'Jimmy Jam',
                                    ),
                            ),
                    ),
                ),
                //Concated nested nested attribute second part
                9 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'     => 'iii',
                                    'relatedModelData'  => array(
                                        'attributeName'     => 'iiiMember2',
                                        'operatorType'      => 'startsWith',
                                        'value'             => 'Jimmy Jam',
                                    ),
                            ),
                    ),
                ),
                //Concated nested nested attribute third part
                10 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'     => 'iii',
                                    'relatedModelData'  => array(
                                        'concatedAttributeNames'    => array('iiiMember', 'iiiMember2'),
                                        'operatorType'      => 'startsWith',
                                        'value'             => 'Jimmy Jam',
                                    ),
                            ),
                    ),
                ),
                //Standard nested nested nested attribute
                11 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'     => 'iii',
                                    'relatedModelData'  => array(
                                        'attributeName'         => 'eee',
                                            'relatedModelData'  => array(
                                                'attributeName'         => 'eeeMember',
                                                'operatorType'          => 'startsWith',
                                                'value'                 => 'eeeMemberValue',
                                            ),
                                    ),
                            ),
                    ),
                ),
            );
            $compareStructure = '1 and 2 and 3 and 4 and (5 or 6 or 7) and (8 or 9 or 10) and 11';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        /**
         * @depends testGetAdaptedMetadataForConcatedAttributesAcrossRelations
         */
        public function testGetAdaptedMetadataForAttributesAcrossRelationsStartingWitManyManyAndIds()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $searchAttributes = array(
                'id' => '5',
                'bbb' => array(
                    'relatedData' => true,
                    'id'  => '6',
                    'aaa'    => array(
                        'relatedData' => true,
                        'id' => '7',
                       )
                    )
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new DDD(false),
                1,
                $searchAttributes
            );
            $metadata = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                //Standard attribute on model
                1 => array(
                    'attributeName' => 'id',
                    'operatorType'  => 'equals',
                    'value'         => '5',
                ),
                //Standard attribute on related model
                2 => array(
                    'attributeName'        => 'bbb',
                        'relatedModelData'  => array(
                            'attributeName'     => 'id',
                            'operatorType'      => 'equals',
                            'value'             => '6',
                        ),
                ),
                //Standard attribute on related related model
                3 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'aaa',
                            'relatedModelData'  => array(
                                'attributeName'     => 'id',
                                'operatorType'      => 'equals',
                                'value'             => '7',
                            ),
                    ),
                ),
            );
            $compareStructure = '1 and 2 and 3';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        /**
         * @depends testGetAdaptedMetadataForAttributesAcrossRelationsStartingWitManyManyAndIds
         */
        public function testGetAdaptedMetadataForAttributesAcrossRelationsStartingWitManyMany()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $searchAttributes = array(
                'dddMember' => 'Vomitorio Corp',
                'bbb' => array(
                    'relatedData' => true,
                    'bbbMember'  => 'bbbMemberValue',
                    'aaa'    => array(
                        'relatedData' => true,
                        'aaaMember' => 'aaaMemberValue',
                       )
                    )
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new DDD(false),
                1,
                $searchAttributes
            );
            $metadata = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                //Standard attribute on model
                1 => array(
                    'attributeName' => 'dddMember',
                    'operatorType'  => 'startsWith',
                    'value'         => 'Vomitorio Corp',
                ),
                //Standard attribute on related model
                2 => array(
                    'attributeName'        => 'bbb',
                        'relatedModelData'  => array(
                            'attributeName'     => 'bbbMember',
                            'operatorType'      => 'startsWith',
                            'value'             => 'bbbMemberValue',
                        ),
                ),
                //Standard attribute on related related model
                3 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'aaa',
                            'relatedModelData'  => array(
                                'attributeName'     => 'aaaMember',
                                'operatorType'      => 'startsWith',
                                'value'             => 'aaaMemberValue',
                            ),
                    ),
                ),
            );
            $compareStructure = '1 and 2 and 3';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        /**
         * @depends testGetAdaptedMetadataForAttributesAcrossRelationsStartingWitManyMany
         */
        public function testGetAdaptedMetadataForAttributesAcrossRelationsStartingWithHasMany()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $searchAttributes = array(
                'cccMember' => 'Vomitorio Corp',
                'bbb' => array(
                    'relatedData' => true,
                    'bbbMember'  => 'bbbMemberValue',
                    'aaa'    => array(
                        'relatedData' => true,
                        'aaaMember' => 'aaaMemberValue',
                       )
                    )
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new CCC(false),
                1,
                $searchAttributes
            );
            $metadata = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                //Standard attribute on model
                1 => array(
                    'attributeName' => 'cccMember',
                    'operatorType'  => 'startsWith',
                    'value'         => 'Vomitorio Corp',
                ),
                //Standard attribute on related model
                2 => array(
                    'attributeName'        => 'bbb',
                        'relatedModelData'  => array(
                            'attributeName'     => 'bbbMember',
                            'operatorType'      => 'startsWith',
                            'value'             => 'bbbMemberValue',
                        ),
                ),
                //Standard attribute on related related model
                3 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'aaa',
                            'relatedModelData'  => array(
                                'attributeName'     => 'aaaMember',
                                'operatorType'      => 'startsWith',
                                'value'             => 'aaaMemberValue',
                            ),
                    ),
                ),
            );
            $compareStructure = '1 and 2 and 3';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        /**
         * @depends testGetAdaptedMetadataForAttributesAcrossRelationsStartingWithHasMany
         */
        public function testGetAdaptedMetadataForAttributesAcrossRelations()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $searchAttributes = array(
                'aaaMember' => 'Vomitorio Corp',
                'bbb' => array(
                    'relatedData' => true,
                    'bbbMember'  => 'bbbMemberValue',
                    'ccc'    => array(
                        'relatedData' => true,
                        'cccMember' => 'cccMemberValue',
                        'eee' => array(
                            'relatedData' => true,
                            'eeeMember' => 'eeeMemberValue',
                       ),
                        'iii'    => array(
                           'relatedData' => true,
                            'eee' => array(
                                'relatedData' => true,
                                'eeeMember' => 'eeeMemberValue',
                            )
                        )
                    )
                )
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new AAA(false),
                1,
                $searchAttributes
            );
            $metadata = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                //Standard attribute on model
                1 => array(
                    'attributeName' => 'aaaMember',
                    'operatorType'  => 'startsWith',
                    'value'         => 'Vomitorio Corp',
                ),
                //Standard attribute on related model
                2 => array(
                    'attributeName'        => 'bbb',
                        'relatedModelData'  => array(
                            'attributeName'     => 'bbbMember',
                            'operatorType'      => 'startsWith',
                            'value'             => 'bbbMemberValue',
                        ),
                ),
                //Standard attribute on related related model
                3 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'     => 'cccMember',
                                'operatorType'      => 'startsWith',
                                'value'             => 'cccMemberValue',
                            ),
                    ),
                ),
                //Standard attribute on related related related model
                4 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'         => 'eee',
                                    'relatedModelData'  => array(
                                        'attributeName'         => 'eeeMember',
                                        'operatorType'          => 'startsWith',
                                        'value'                 => 'eeeMemberValue',
                                    ),
                            ),
                    ),
                ),
                5 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'     => 'iii',
                                    'relatedModelData'  => array(
                                        'attributeName'         => 'eee',
                                            'relatedModelData'  => array(
                                                'attributeName'         => 'eeeMember',
                                                'operatorType'          => 'startsWith',
                                                'value'                 => 'eeeMemberValue',
                                            ),
                                    ),
                            ),
                    ),
                ),
            );
            $compareStructure = '1 and 2 and 3 and 4 and 5';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        /**
         * @depends testGetAdaptedMetadataForAttributesAcrossRelations
         */
        public function testSearchingOnACustomFieldWithMultipleValuesWhenInRelatedData()
        {
            $searchAttributes = array(
                'industry'  => array(
                    'value'    => array('A', 'B', 'C'),
                ),
                'bbb' => array(
                    'relatedData' => true,
                    'industry'  => array(
                        'value'    => array('A', 'B', 'C'),
                    ),
                    'ccc'    => array(
                        'relatedData' => true,
                        'cccMember' => 'cccMemberValue',
                        'industry'  => array(
                            'value'    => array('A', 'B', 'C'),
                        ),
                        'eee' => array(
                            'relatedData' => true,
                            'eeeMember' => 'eeeMemberValue',
                       ),
                        'iii'    => array(
                           'relatedData' => true,
                            'eee' => array(
                                'relatedData' => true,
                                'industry'  => array(
                                    'value'    => array('A', 'B', 'C'),
                                ),
                                'eeeMember' => 'eeeMemberValue',
                            )
                        )
                    )
                )
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new AAA(false),
                1,
                $searchAttributes
            );
            $metadata = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                //Standard attribute on model
                1 => array(
                    'attributeName'        => 'industry',
                    'relatedAttributeName' => 'value',
                    'operatorType'         => 'oneOf',
                    'value'                => array('A', 'B', 'C'),
                ),
                //Standard attribute on related model
                2 => array(
                    'attributeName'        => 'bbb',
                        'relatedModelData'  => array(
                            'attributeName'        => 'industry',
                            'relatedAttributeName' => 'value',
                            'operatorType'         => 'oneOf',
                            'value'                => array('A', 'B', 'C'),
                        ),
                ),
                //Standard attribute on related related model
                3 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'     => 'cccMember',
                                'operatorType'      => 'startsWith',
                                'value'             => 'cccMemberValue',
                            ),
                    ),
                ),
                //Custom Field attribute on related related model
                4 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'        => 'industry',
                                'relatedAttributeName' => 'value',
                                'operatorType'         => 'oneOf',
                                'value'                => array('A', 'B', 'C'),
                            ),
                    ),
                ),
                //Standard attribute on related related related model
                5 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'         => 'eee',
                                    'relatedModelData'  => array(
                                        'attributeName'         => 'eeeMember',
                                        'operatorType'          => 'startsWith',
                                        'value'                 => 'eeeMemberValue',
                                    ),
                            ),
                    ),
                ),
                //Custom Field attribute on related related related related model
                6 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'     => 'iii',
                                    'relatedModelData'  => array(
                                        'attributeName'         => 'eee',
                                            'relatedModelData'  => array(
                                                'attributeName'        => 'industry',
                                                'relatedAttributeName' => 'value',
                                                'operatorType'         => 'oneOf',
                                                'value'                => array('A', 'B', 'C'),
                                            ),
                                    ),
                            ),
                    ),
                ),
                //Standard attribute on related related related related model
                7 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'     => 'iii',
                                    'relatedModelData'  => array(
                                        'attributeName'         => 'eee',
                                            'relatedModelData'  => array(
                                                'attributeName'         => 'eeeMember',
                                                'operatorType'          => 'startsWith',
                                                'value'                 => 'eeeMemberValue',
                                            ),
                                    ),
                            ),
                    ),
                ),
            );
            $compareStructure = '1 and 2 and 3 and 4 and 5 and 6 and 7';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        /**
         * @depends testSearchingOnACustomFieldWithMultipleValuesWhenInRelatedData
         */
        public function testSearchingOnACustomFieldWithMultipleValuesWhenInRelatedDataAndEmpty()
        {
            $searchAttributes = array(
                'industry' => array(
                    'value'    => array(''),
                ),
                'bbb' => array(
                    'relatedData' => true,
                    'industry' => array(
                        'value'    => array(''),
                    ),
                    'ccc'    => array(
                        'relatedData' => true,
                        'cccMember' => 'cccMemberValue',
                        'industry' => array(
                            'value'    => array(''),
                        ),
                        'eee' => array(
                            'relatedData' => true,
                            'eeeMember' => 'eeeMemberValue',
                       ),
                        'iii'    => array(
                           'relatedData' => true,
                            'eee' => array(
                                'relatedData' => true,
                                'industry' => array(
                                    'value'    => array(''),
                                ),
                                'eeeMember' => 'eeeMemberValue',
                            )
                        )
                    )
                )
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new AAA(false),
                1,
                $searchAttributes
            );
            $metadata = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                //Standard attribute on model
                //Standard attribute on related related model
                1 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'     => 'cccMember',
                                'operatorType'      => 'startsWith',
                                'value'             => 'cccMemberValue',
                            ),
                    ),
                ),
                //Standard attribute on related related related model
                2 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'         => 'eee',
                                    'relatedModelData'  => array(
                                        'attributeName'         => 'eeeMember',
                                        'operatorType'          => 'startsWith',
                                        'value'                 => 'eeeMemberValue',
                                    ),
                            ),
                    ),
                ),
                //Standard attribute on related related related related model
                3 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'     => 'iii',
                                    'relatedModelData'  => array(
                                        'attributeName'         => 'eee',
                                            'relatedModelData'  => array(
                                                'attributeName'         => 'eeeMember',
                                                'operatorType'          => 'startsWith',
                                                'value'                 => 'eeeMemberValue',
                                            ),
                                    ),
                            ),
                    ),
                ),
            );
            $compareStructure = '1 and 2 and 3';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        /**
         * Testing out nested search form attributes that need to be converted property to be adapted into metadata.
         * @depends testSearchingOnACustomFieldWithMultipleValuesWhenInRelatedDataAndEmpty
         */
        public function testSearchFormAttributesAreAdaptedProperly()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $searchAttributes = array(
                'aaaMember' => 'Vomitorio Corp',
                'bbb' => array(
                    'relatedData' => true,
                    'bbbMember'  => 'bbbMemberValue',
                    'ccc'    => array(
                        'relatedData' => true,
                        'cccMember'              => 'cccMemberValue',
                        'CCCName'                => 'someCCCValue',
                        'differentOperatorA'   => '1',
                        'differentOperatorB'   => 'something',
                        'eee' => array(
                            'relatedData' => true,
                            'eeeMember' => 'eeeMemberValue',
                       ),
                        'iii'    => array(
                           'relatedData' => true,
                            'IIIName'                => 'someIIIValue',
                            'differentOperatorA'   => '1',
                            'differentOperatorB'   => 'something',
                            'eee' => array(
                                'relatedData' => true,
                                'eeeMember' => 'eeeMemberValue',
                            )
                        )
                    )
                )
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new AAA(false),
                1,
                $searchAttributes
            );
            $metadata       = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                //Standard attribute on model
                1 => array(
                    'attributeName' => 'aaaMember',
                    'operatorType'  => 'startsWith',
                    'value'         => 'Vomitorio Corp',
                ),
                //Standard attribute on related model
                2 => array(
                    'attributeName'        => 'bbb',
                        'relatedModelData'  => array(
                            'attributeName'     => 'bbbMember',
                            'operatorType'      => 'startsWith',
                            'value'             => 'bbbMemberValue',
                        ),
                ),
                //Standard attribute on related related model
                3 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'     => 'cccMember',
                                'operatorType'      => 'startsWith',
                                'value'             => 'cccMemberValue',
                            ),
                    ),
                ),
                //Search form attribute , part #1 of 2
                4 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'     => 'cccMember',
                                'operatorType'      => 'startsWith',
                                'value'             => 'someCCCValue',
                            ),
                    ),
                ),
                //Search form attribute , part #2 of 2
                5 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'     => 'cccMember2',
                                'operatorType'      => 'startsWith',
                                'value'             => 'someCCCValue',
                            ),
                    ),
                ),
                //Search form attribute , owner Only
                6 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'     => 'cccMember',
                                'operatorType'      => 'startsWith',
                                'value'             => $super->id,
                            ),
                    ),
                ),
                //Search form attribute , alternative operator
                7 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'     => 'cccMember',
                                'operatorType'      => 'endsWith',
                                'value'             => 'something',
                            ),
                    ),
                ),
                //Standard attribute on related related related model
                8 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'         => 'eee',
                                    'relatedModelData'  => array(
                                        'attributeName'         => 'eeeMember',
                                        'operatorType'          => 'startsWith',
                                        'value'                 => 'eeeMemberValue',
                                    ),
                            ),
                    ),
                ),
                //Search form attribute , part #1 of 2 (recursively nested)
                9 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'     => 'iii',
                                    'relatedModelData'  => array(
                                        'attributeName'         => 'iiiMember',
                                        'operatorType'          => 'startsWith',
                                        'value'                 => 'someIIIValue',
                                    ),
                            ),
                    ),
                ),
                //Search form attribute , part #2 of 2 (recursively nested)
                10 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'     => 'iii',
                                    'relatedModelData'  => array(
                                        'attributeName'         => 'iiiMember2',
                                        'operatorType'          => 'startsWith',
                                        'value'                 => 'someIIIValue',
                                    ),
                            ),
                    ),
                ),
                //Search form attribute , owner Only (recursively nested)
                11 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'     => 'iii',
                                    'relatedModelData'  => array(
                                        'attributeName'     => 'iiiMember',
                                        'operatorType'      => 'startsWith',
                                        'value'             => $super->id,
                                    ),
                            ),
                    ),
                ),
                //Search form attribute , alternative operator
                12 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'     => 'iii',
                                    'relatedModelData'  => array(
                                        'attributeName'     => 'iiiMember',
                                        'operatorType'      => 'endsWith',
                                        'value'             => 'something',
                                    ),
                            ),
                    ),
                ),
                //Standard attribute recursively nested
                13 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'     => 'iii',
                                    'relatedModelData'  => array(
                                        'attributeName'         => 'eee',
                                            'relatedModelData'  => array(
                                                'attributeName'         => 'eeeMember',
                                                'operatorType'          => 'startsWith',
                                                'value'                 => 'eeeMemberValue',
                                            ),
                                    ),
                            ),
                    ),
                ),
            );
            $compareStructure = '1 and 2 and 3 and (4 or 5) and (6) and (7) and 8 and (9 or 10) and (11) and (12) and 13';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testSearchFormDynamicAttributes()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $searchAttributes = array(
                'aaaMember' => 'Vomitorio Corp',
                'bbb' => array(
                    'relatedData' => true,
                    'ccc'    => array(
                        'relatedData' => true,
                        'date__Date'          => array('type'         => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER,
                                                       'firstDate'  => '1991-03-04'),
                        'dateTime__DateTime'  => array('type'         => MixedDateTypesSearchFormAttributeMappingRules::TYPE_TODAY),
                        'dateTime2__DateTime' => array('value'        => null),
                        'eee' => array(
                            'relatedData' => true,
                            'eeeMember' => 'eeeMemberValue',
                       ),
                        'iii'    => array(
                           'relatedData' => true,
                                'date__Date'          => array('type'         => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER,
                                                               'firstDate'  => '1991-03-04'),
                                'dateTime__DateTime'  => array('type'         => MixedDateTypesSearchFormAttributeMappingRules::TYPE_TODAY),
                                'dateTime2__DateTime' => array('value'        => null),
                        )
                    )
                )
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new AAA(false),
                1,
                $searchAttributes
            );
            $todayDateTime      = new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser()));
            $today              = Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                                  $todayDateTime->getTimeStamp());
            $todayPlus7Days     = MixedDateTypesSearchFormAttributeMappingRules::calculateNewDateByDaysFromNow(7);
            $metadata       = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                //Standard attribute on model
                1 => array(
                    'attributeName' => 'aaaMember',
                    'operatorType'  => 'startsWith',
                    'value'         => 'Vomitorio Corp',
                ),
                //Standard date attribute on related model
                2 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'     => 'date',
                                'operatorType'      => 'greaterThanOrEqualTo',
                                'value'             => '1991-03-04',
                            ),
                    ),
                ),
                //Standard dateTime attribute on related model
                3 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'        => 'dateTime',
                                'operatorType'         => 'greaterThanOrEqualTo',
                                'value'                => DateTimeUtil::
                                                          convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($today),
                            ),
                    ),
                ),
                //Standard dateTime attribute on related model
                4 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'        => 'dateTime',
                                'operatorType'         => 'lessThanOrEqualTo',
                                'value'                => DateTimeUtil::
                                                          convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($today),
                            ),
                    ),
                ),
                //Standard attribute recursively nested
                5 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'         => 'eee',
                                    'relatedModelData'  => array(
                                        'attributeName'         => 'eeeMember',
                                        'operatorType'          => 'startsWith',
                                        'value'                 => 'eeeMemberValue',
                                    ),
                            ),
                    ),
                ),
                //Date attribute recursively nested
                6 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'     => 'iii',
                                    'relatedModelData'  => array(
                                        'attributeName'     => 'date',
                                        'operatorType'      => 'greaterThanOrEqualTo',
                                        'value'             => '1991-03-04',
                                    ),
                            ),
                    ),
                ),
                //DateTime attribute recursively nested
                7 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'     => 'iii',
                                    'relatedModelData'  => array(
                                        'attributeName'        => 'dateTime',
                                        'operatorType'         => 'greaterThanOrEqualTo',
                                        'value'                => DateTimeUtil::
                                                                  convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($today),
                                    ),
                            ),
                    ),
                ),
                //DateTime attribute recursively nested
                8 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'     => 'iii',
                                    'relatedModelData'  => array(
                                        'attributeName'        => 'dateTime',
                                        'operatorType'         => 'lessThanOrEqualTo',
                                        'value'                => DateTimeUtil::
                                                                  convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($today),
                                    ),
                            ),
                    ),
                ),
            );
            $compareStructure = '1 and (2) and (3 and 4) and 5 and (6) and (7 and 8)';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }
    }
?>