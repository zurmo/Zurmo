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

    class SearchUtilTest extends BaseTest
    {
        public function testGetSorAttributeFromSortArray()
        {
            $sortAttribute = SearchUtil::getSortAttributeFromSortString('name.desc');
            $this->assertEquals('name', $sortAttribute);
            $sortAttribute = SearchUtil::getSortAttributeFromSortString('name');
            $this->assertEquals('name', $sortAttribute);
            $sortAttribute = SearchUtil::getSortAttributeFromSortString('name.asc');
            $this->assertEquals('name', $sortAttribute);
            $sortAttribute = SearchUtil::getSortAttributeFromSortString('');
            $this->assertEquals('', $sortAttribute);

            $_GET['testing_sort'] = 'name.desc';
            $sortAttribute = SearchUtil::resolveSortAttributeFromGetArray('testing');
            $this->assertEquals('name', $sortAttribute);
            $_GET['testing_sort'] = 'name';
            $sortAttribute = SearchUtil::resolveSortAttributeFromGetArray('testing');
            $this->assertEquals('name', $sortAttribute);
            $_GET['testing_sort'] = 'name.asc';
            $sortAttribute = SearchUtil::resolveSortAttributeFromGetArray('testing');
            $this->assertEquals('name', $sortAttribute);
            $_GET['testing_sort'] = '';
            $sortAttribute = SearchUtil::resolveSortAttributeFromGetArray('testing');
            $this->assertEquals('', $sortAttribute);
        }

        public function testIsSortDescending()
        {
            $sortDescending = SearchUtil::isSortDescending('name.desc');
            $this->assertTrue($sortDescending);
            $sortDescending = SearchUtil::isSortDescending('name');
            $this->assertFalse($sortDescending);
            $sortDescending = SearchUtil::isSortDescending('name.asc');
            $this->assertFalse($sortDescending);

            $_GET['testing_sort'] = 'name.desc';
            $sortDescending = SearchUtil::resolveSortDescendingFromGetArray('testing');
            $this->assertTrue($sortDescending);
            $_GET['testing_sort'] = 'name';
            $sortDescending = SearchUtil::resolveSortDescendingFromGetArray('testing');
            $this->assertFalse($sortDescending);
            $_GET['testing_sort'] = 'name.asc';
            $sortDescending = SearchUtil::resolveSortDescendingFromGetArray('testing');
            $this->assertFalse($sortDescending);
        }

        public function testGetSearchAttributesFromSearchArray()
        {
            $searchArray = array(
                'a' => 'apple',
                'b' => '',
            );
            $testArray = array(
                'a' => 'apple',
                'b' => null,
            );
            $newArray = SearchUtil::getSearchAttributesFromSearchArray($searchArray);
            $this->assertEquals($testArray, $newArray);

            $_GET['testing'] = array(
                'a' => 'apple',
                'b' => '',
            );
            $newArray = SearchUtil::resolveSearchAttributesFromGetArray('testing', 'AAASearchFormTestModel');
            $this->assertEquals($testArray, $newArray);

            //Now test various empty and 0 combinations
            $_GET['testing'] = array(
                'a' => null,
            );
            $newArray = SearchUtil::resolveSearchAttributesFromGetArray('testing', 'AAASearchFormTestModel');
            $this->assertEquals(array('a' => null), $newArray);

            $_GET['testing'] = array(
                'a' => '',
            );
            $newArray = SearchUtil::resolveSearchAttributesFromGetArray('testing', 'AAASearchFormTestModel');
            $this->assertEquals(array('a' => null), $newArray);

            $_GET['testing'] = array(
                'a' => 0,
            );
            $newArray = SearchUtil::resolveSearchAttributesFromGetArray('testing', 'AAASearchFormTestModel');
            $this->assertEquals(array('a' => null), $newArray);

            $_GET['testing'] = array(
                'a' => '0',
            );
            $newArray = SearchUtil::resolveSearchAttributesFromGetArray('testing', 'AAASearchFormTestModel');
            $this->assertEquals(array('a' => '0'), $newArray);
        }

        public function testResolveSearchAttributesFromGetArrayForDynamicSearch()
        {
            $_GET['testing'] = array(
                'a' => '0',
                'dynamicClauses' => array(array('b' => '0')),
                'dynamicStructure' => '1 and 2',
            );
            $newArray = SearchUtil::resolveSearchAttributesFromGetArray('testing', 'AAASearchFormTestModel');
            $this->assertEquals(array('a' => '0'), $newArray);
        }

        public function testResolveSearchAttributesFromGetArrayForAnyMixedAttributeScopeName()
        {
            $_GET['testing'] = array(
                'a' => '0',
                SearchForm::ANY_MIXED_ATTRIBUTES_SCOPE_NAME => 'something',
                SearchForm::SELECTED_LIST_ATTRIBUTES        => array('something'),
            );
            $newArray = SearchUtil::resolveSearchAttributesFromGetArray('testing', 'AAASearchFormTestModel');
            $this->assertEquals(array('a' => '0'), $newArray);

            $_GET['testing'] = array(
                'a' => '0',
                SearchForm::ANY_MIXED_ATTRIBUTES_SCOPE_NAME => null,
                SearchForm::SELECTED_LIST_ATTRIBUTES        => null,
            );
            $newArray = SearchUtil::resolveSearchAttributesFromGetArray('testing', 'AAASearchFormTestModel');
            $this->assertEquals(array('a' => '0'), $newArray);

            $_GET['testing'] = array(
                'a' => '0',
                SearchForm::ANY_MIXED_ATTRIBUTES_SCOPE_NAME => array(),
                SearchForm::SELECTED_LIST_ATTRIBUTES        => array(),
            );
            $newArray = SearchUtil::resolveSearchAttributesFromGetArray('testing', 'AAASearchFormTestModel');
            $this->assertEquals(array('a' => '0'), $newArray);

            $_GET['testing'] = array(
                'a' => '0',
                SearchForm::ANY_MIXED_ATTRIBUTES_SCOPE_NAME => array('a' => 'b'),
                SearchForm::SELECTED_LIST_ATTRIBUTES        => array('a' => 'b'),
            );
            $newArray = SearchUtil::resolveSearchAttributesFromGetArray('testing', 'AAASearchFormTestModel');
            $this->assertEquals(array('a' => '0'), $newArray);
        }

        /**
         * This test is for testing the method SearchUtil::changeEmptyArrayValuesToNull.
         * if a value in the search array for multiselect attribute has an empty element it is removed(eliminated).
         */
        public function testGetSearchAttributesFromSearchArrayChangeEmptyArrayValuesToNull()
        {
            $searchArray = array('testMultiSelectDropDown' => array('values' => array(0 => '')));
            $newArray = SearchUtil::getSearchAttributesFromSearchArray($searchArray);
            $this->assertEquals(array(), $newArray);

            $searchArray = array('testMultiSelectDropDown' => array('values' => array(0 => null)));
            $newArray = SearchUtil::getSearchAttributesFromSearchArray($searchArray);
            $this->assertEquals(array(), $newArray);

            $searchArray = array('testMultiSelectDropDown' => array('values' => array(0 => null, 1 => 'xyz')));
            $resultArray = array('testMultiSelectDropDown' => array('values' => array(0 => 'xyz')));
            $newArray = SearchUtil::getSearchAttributesFromSearchArray($searchArray);
            $this->assertEquals($resultArray, $newArray);

            $searchArray = array('testDropDownAsMultiSelectDropDown' => array('value' => array(0 => '')));
            $newArray = SearchUtil::getSearchAttributesFromSearchArray($searchArray);
            $this->assertEquals(array(), $newArray);

            $searchArray = array('testDropDownAsMultiSelectDropDown' => array('value' => array(0 => null)));
            $newArray = SearchUtil::getSearchAttributesFromSearchArray($searchArray);
            $this->assertEquals(array(), $newArray);

            $searchArray = array('testDropDownAsMultiSelectDropDown' => array('value' => array(0 => null, 1 => 'xyz')));
            $resultArray = array('testDropDownAsMultiSelectDropDown' => array('value' => array(0 => 'xyz')));
            $newArray = SearchUtil::getSearchAttributesFromSearchArray($searchArray);
            $this->assertEquals($resultArray, $newArray);

            //Test recursion
            $searchArray = array('testDropDownAsMultiSelectDropDown' =>
                                    array('abc' => array('value' => array(0 => null, 1 => 'xyz'))));
            $resultArray = array('testDropDownAsMultiSelectDropDown' =>
                                    array('abc' => array('value' => array(0 => 'xyz'))));
            $newArray = SearchUtil::getSearchAttributesFromSearchArray($searchArray);
            $this->assertEquals($resultArray, $newArray);

            $searchArray = array('testMultiSelectDropDown' =>
                                    array('abc' => array('values' => array(0 => null, 1 => 'xyz'))));
            $resultArray = array('testMultiSelectDropDown' =>
                                    array('abc' => array('values' => array(0 => 'xyz'))));
            $newArray = SearchUtil::getSearchAttributesFromSearchArray($searchArray);
            $this->assertEquals($resultArray, $newArray);
        }

        public function testGetSearchAttributesFromSearchArrayForSavingExistingSearchCriteria()
        {
            $searchArray = array(
                'a' => 'apple',
                'b' => '',
            );
            $testArray = array(
                'a' => 'apple',
                'b' => null,
            );
            $newArray = SearchUtil::getSearchAttributesFromSearchArrayForSavingExistingSearchCriteria($searchArray);
            $this->assertEquals($testArray, $newArray);

            $searchArray = array(
                'a' => 'apple',
                'b' => '',
            );
            $newArray = SearchUtil::getSearchAttributesFromSearchArrayForSavingExistingSearchCriteria($searchArray);
            $this->assertEquals($testArray, $newArray);

            //Now test various empty and 0 combinations
            $searchArray = array(
                'a' => null,
            );
            $newArray = SearchUtil::getSearchAttributesFromSearchArrayForSavingExistingSearchCriteria($searchArray);
            $this->assertEquals(array('a' => null), $newArray);

            $searchArray = array(
                'a' => '',
            );
            $newArray = SearchUtil::getSearchAttributesFromSearchArrayForSavingExistingSearchCriteria($searchArray);
            $this->assertEquals(array('a' => null), $newArray);

            $searchArray = array(
                'a' => 0,
            );
            $newArray = SearchUtil::getSearchAttributesFromSearchArrayForSavingExistingSearchCriteria($searchArray);
            $this->assertEquals(array('a' => 0), $newArray);

            $searchArray = array(
                'a' => '0',
            );
            $newArray = SearchUtil::getSearchAttributesFromSearchArrayForSavingExistingSearchCriteria($searchArray);
            $this->assertEquals(array('a' => '0'), $newArray);

            $searchArray = array(
                'a' => array('values' => array(0 => '')),
            );
            $newArray = SearchUtil::getSearchAttributesFromSearchArrayForSavingExistingSearchCriteria($searchArray);
            $this->assertEquals(array(), $newArray);

            $searchArray = array(
                'a' => array('value' => array(0 => '')),
            );
            $newArray = SearchUtil::getSearchAttributesFromSearchArrayForSavingExistingSearchCriteria($searchArray);
            $this->assertEquals(array(), $newArray);

            $searchArray = array(
                'a' => array('values' => array(0 => null)),
            );
            $newArray = SearchUtil::getSearchAttributesFromSearchArrayForSavingExistingSearchCriteria($searchArray);
            $this->assertEquals(array(), $newArray);

            $searchArray = array(
                'a' => array('value' => array(0 => null)),
            );
            $newArray = SearchUtil::getSearchAttributesFromSearchArrayForSavingExistingSearchCriteria($searchArray);
            $this->assertEquals(array(), $newArray);

            $searchArray = array(
                'a' => array('value' => array(0 => null, 1 => 'xyz')),
            );
            $newArray = SearchUtil::getSearchAttributesFromSearchArrayForSavingExistingSearchCriteria($searchArray);
            $this->assertEquals(array('a' => array('value' => array(0 => 'xyz'))), $newArray);
        }

        public function testAdaptSearchAttributesToSetInRedBeanModel()
        {
            $model = new ASearchFormTestModel(new A(false));
            $searchAttributes = array(
                'differentOperatorB' => array('value' => 'thiswillstay'),
                'a'                  => array('value' => 'thiswillgo'),
                'differentOperatorB' => 'something',
                'name'               => array('value' => 'thiswillstay'),
            );
            $adaptedSearchAttributes = SearchUtil::adaptSearchAttributesToSetInRedBeanModel($searchAttributes, $model);
            $compareData = array(
                'differentOperatorB' => array('value' => 'thiswillstay'),
                'a'                  => 'thiswillgo',
                'differentOperatorB' => 'something',
                'name'               => array('value' => 'thiswillstay'),
            );
            $this->assertEquals($compareData, $adaptedSearchAttributes);
        }

        public function testResolveAnyMixedAttributesScopeForSearchModelFromGetArray()
        {
            $searchModel  = new ASearchFormTestModel(new A());
            $getArrayName = 'someArray';
            SearchUtil::resolveAnyMixedAttributesScopeForSearchModelFromGetArray($searchModel, $getArrayName);
            $this->assertNull($searchModel->getAnyMixedAttributesScope());

            //Test passing a value in the GET
            $_GET['someArray'][SearchForm::ANY_MIXED_ATTRIBUTES_SCOPE_NAME] = 'notAnArray';
            SearchUtil::resolveAnyMixedAttributesScopeForSearchModelFromGetArray($searchModel, $getArrayName);
            $this->assertNull($searchModel->getAnyMixedAttributesScope());

            $_GET['someArray'][SearchForm::ANY_MIXED_ATTRIBUTES_SCOPE_NAME] = array('All');
            SearchUtil::resolveAnyMixedAttributesScopeForSearchModelFromGetArray($searchModel, $getArrayName);
            $this->assertNull($searchModel->getAnyMixedAttributesScope());

            $_GET['someArray'][SearchForm::ANY_MIXED_ATTRIBUTES_SCOPE_NAME] = array('A', 'B', 'C');
            SearchUtil::resolveAnyMixedAttributesScopeForSearchModelFromGetArray($searchModel, $getArrayName);
            $this->assertEquals(array('A', 'B', 'C'), $searchModel->getAnyMixedAttributesScope());
        }

        public function testResolveSelectedListAttributesForSearchModelFromGetArray()
        {
            $searchModel  = new ASearchFormTestModel(new A());
            $listAttributesSelector         = new ListAttributesSelector('AListView', 'TestModule');
            $searchModel->setListAttributesSelector($listAttributesSelector);
            $getArrayName = 'someArray';
            SearchUtil::resolveSelectedListAttributesForSearchModelFromGetArray($searchModel, $getArrayName);
            $this->assertEquals(array('name'), $searchModel->getListAttributesSelector()->getSelected());

            //Test passing a value in the GET
            $_GET['someArray'][SearchForm::SELECTED_LIST_ATTRIBUTES] = array();
            SearchUtil::resolveSelectedListAttributesForSearchModelFromGetArray($searchModel, $getArrayName);
            $this->assertEquals(array('name'), $searchModel->getListAttributesSelector()->getSelected());

            $_GET['someArray'][SearchForm::SELECTED_LIST_ATTRIBUTES] = array('name', 'a');
            SearchUtil::resolveSelectedListAttributesForSearchModelFromGetArray($searchModel, $getArrayName);
            $this->assertEquals(array('name', 'a'), $searchModel->getListAttributesSelector()->getSelected());
        }

        public function testGetDynamicSearchAttributesFromGetArray()
        {
            //Test without any dynamic search
            $_GET['testing'] = array(
                'a' => null,
            );
            $newArray = SearchUtil::getDynamicSearchAttributesFromGetArray('testing');
            $this->assertNull($newArray);

            //Test with dynamic search
            $_GET['testing'] = array(
                'a' => null,
                'dynamicClauses' => array(array('b' => 'c')),
                'dynamicStructure' => '1 and 2',
            );
            $newArray    = SearchUtil::getDynamicSearchAttributesFromGetArray('testing');
            $compareData = array(array('b' => 'c'));
            $this->assertEquals($compareData, $newArray);

            //Test with dynamic search and an undefined sub-array
            $_GET['testing'] = array(
                'a' => null,
                'dynamicClauses' => array(array('b' => 'c'), 'undefined', array('d' => 'simpleDimple')),
                'dynamicStructure' => '1 and 2',
            );
            $newArray    = SearchUtil::getDynamicSearchAttributesFromGetArray('testing');
            $compareData = array(0 => array('b' => 'c'), 2 => array('d' => 'simpleDimple'));
            $this->assertEquals($compareData, $newArray);

            //Test with an empty value being converted to null, also tests nested empty values
            $_GET['testing'] = array(
                'a' => null,
                'dynamicClauses' => array(array('b' => 'c'), 'undefined', array('d' => ''),
                     array('e' => array('f' => array('g' => ''))),
                     array('e' => array('f' => '')),
                     ),
                'dynamicStructure' => '1 and 2',
            );
            $newArray    = SearchUtil::getDynamicSearchAttributesFromGetArray('testing');
            $compareData = array(0 => array('b' => 'c'), 2 => array('d' => null),
                                      array('e' => array('f' => array('g' => null))),
                                      array('e' => array('f' => null)));
            $this->assertEquals($compareData, $newArray);
            $this->assertTrue($newArray[2]['d'] === null);
            $this->assertTrue($newArray[3]['e']['f']['g'] === null);
            $this->assertTrue($newArray[4]['e']['f'] === null);
        }

        public function testSanitizeDynamicSearchAttributesByDesignerTypeForSavingModel()
        {
            $searchModel = new ASearchFormTestModel(new A());
            //Test without anything special sanitizing
            $dynamicSearchAttributes = array(
                                        0 => array('attributeIndexOrDerivedType' => 'a',
                                                    'structurePosition'          => '1',
                                                    'a'                          => 'something'),
                                        2 => array('attributeIndexOrDerivedType' => 'a',
                                                    'structurePosition'          => '2',
                                                    'a'                          => 'somethingElse'));
            $newArray = SearchUtil::sanitizeDynamicSearchAttributesByDesignerTypeForSavingModel($searchModel,
                                                                                                $dynamicSearchAttributes);
            $this->assertEquals($dynamicSearchAttributes, $newArray);
        }

        public function testSanitizeDynamicSearchAttributesByDesignerTypeForSavingModelWithNestedAttributes()
        {
            $searchModel = new IIISearchFormTestModel(new III());
            //Test without anything special sanitizing
            $dynamicSearchAttributes = array(
                                        0 => array('attributeIndexOrDerivedType' => 'iiiMember',
                                                    'structurePosition'          => '1',
                                                    'iiiMember'                  => 'something'),
                                        1 => array('attributeIndexOrDerivedType' => 'ccc' . DynamicSearchUtil::RELATION_DELIMITER . 'cccMember',
                                                    'structurePosition'          => '2',
                                                    'ccc'                        => array(
                                                        'relatedModelData' => true,
                                                        'cccMember'        => 'somethingElse',
                                                    )),
                                        2 => array('attributeIndexOrDerivedType' => 'ccc' . DynamicSearchUtil::RELATION_DELIMITER .
                                                   'bbb' . DynamicSearchUtil::RELATION_DELIMITER . 'bbbMember',
                                                    'structurePosition'          => '2',
                                                    'ccc'                        => array(
                                                        'relatedModelData' => true,
                                                        'bbb'                        => array(
                                                            'relatedModelData' => true,
                                                            'bbbMember'        => 'bbbValue',
                                                        )
                                                    )));
            $newArray = SearchUtil::sanitizeDynamicSearchAttributesByDesignerTypeForSavingModel($searchModel,
                                                                                                $dynamicSearchAttributes);
            $this->assertEquals($dynamicSearchAttributes, $newArray);
        }

        public function testSanitizeDynamicSearchAttributesByDesignerTypeForSavingModelWithSanitizableItems()
        {
            $language    = Yii::app()->getLanguage();
            $this->assertEquals($language, 'en');
            $searchModel = new IIISearchFormTestModel(new III());
            $dynamicSearchAttributes = array(
                                        0 => array('attributeIndexOrDerivedType' => 'date__Date',
                                                    'structurePosition'          => '1',
                                                    'date__Date'                 =>
                                                        array('firstDate' => '5/4/11',
                                                              'type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER)),
                                        2 => array('attributeIndexOrDerivedType' => 'date2__Date',
                                                    'structurePosition'          => '2',
                                                    'date2__Date'                =>
                                                        array('firstDate' => '5/6/11',
                                                              'type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER)),
                                        3 => array('attributeIndexOrDerivedType' => 'dateTime__DateTime',
                                                    'structurePosition'          => '1',
                                                    'dateTime__DateTime'         =>
                                                        array('firstDate' => '5/7/11',
                                                              'type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER)),
                                        5 => array('attributeIndexOrDerivedType' => 'dateTime2__DateTime',
                                                    'structurePosition'          => '2',
                                                    'dateTime2__DateTime'        =>
                                                        array('firstDate' => '5/8/11',
                                                              'type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER)),
                                        );
            $newArray = SearchUtil::sanitizeDynamicSearchAttributesByDesignerTypeForSavingModel($searchModel,
                                                                                                $dynamicSearchAttributes);
            $compareData = array(
                                        0 => array('attributeIndexOrDerivedType' => 'date__Date',
                                                    'structurePosition'          => '1',
                                                    'date__Date'                 =>
                                                        array('firstDate' => '2011-05-04',
                                                              'type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER)),
                                        2 => array('attributeIndexOrDerivedType' => 'date2__Date',
                                                    'structurePosition'          => '2',
                                                    'date2__Date'                =>
                                                        array('firstDate' => '2011-05-06',
                                                              'type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER)),
                                        3 => array('attributeIndexOrDerivedType' => 'dateTime__DateTime',
                                                    'structurePosition'          => '1',
                                                    'dateTime__DateTime'         =>
                                                        array('firstDate' => '2011-05-07',
                                                              'type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER)),
                                        5 => array('attributeIndexOrDerivedType' => 'dateTime2__DateTime',
                                                    'structurePosition'          => '2',
                                                    'dateTime2__DateTime'        =>
                                                        array('firstDate' => '2011-05-08',
                                                              'type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER)),
                                        );
            $this->assertEquals($compareData, $newArray);
        }

        public function testSanitizeDynamicSearchAttributesByDesignerTypeForSavingModelWithSanitizableItemsNestedSingleLevel()
        {
            $language    = Yii::app()->getLanguage();
            $this->assertEquals($language, 'en');
            $searchModel = new CCCSearchFormTestModel(new CCC());
            $dynamicSearchAttributes = array(
                                        0 => array('attributeIndexOrDerivedType' => 'iii' . DynamicSearchUtil::RELATION_DELIMITER . 'date__Date',
                                                    'structurePosition'          => '1',
                                                    'iii'                        => array(
                                                    'relatedModelData'           => true,
                                                    'date__Date'                 =>
                                                        array('firstDate' => '5/4/11',
                                                              'type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER))),
                                        1 => array('attributeIndexOrDerivedType' => 'iii' . DynamicSearchUtil::RELATION_DELIMITER . 'dateTime__DateTime',
                                                    'structurePosition'          => '1',
                                                    'iii'                        => array(
                                                    'relatedModelData'           => true,
                                                    'dateTime__DateTime'         =>
                                                        array('firstDate' => '5/7/11',
                                                              'type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER))),
                                        );

            $newArray = SearchUtil::sanitizeDynamicSearchAttributesByDesignerTypeForSavingModel($searchModel,
                                                                                                $dynamicSearchAttributes);
            $compareData = array(
                                        0 => array('attributeIndexOrDerivedType' => 'iii' . DynamicSearchUtil::RELATION_DELIMITER . 'date__Date',
                                                    'structurePosition'          => '1',
                                                    'iii'                        => array(
                                                    'relatedModelData'           => true,
                                                    'date__Date'                 =>
                                                        array('firstDate' => '2011-05-04',
                                                              'type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER))),
                                        1 => array('attributeIndexOrDerivedType' => 'iii' . DynamicSearchUtil::RELATION_DELIMITER . 'dateTime__DateTime',
                                                    'structurePosition'          => '1',
                                                    'iii'                        => array(
                                                    'relatedModelData'           => true,
                                                    'dateTime__DateTime'         =>
                                                        array('firstDate' => '2011-05-07',
                                                              'type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER))),
                                        );
            $this->assertEquals($compareData, $newArray);
        }

        public function testSanitizeDynamicSearchAttributesByDesignerTypeForSavingModelWithSanitizableItemsNestedNMultipleLevelsDeep()
        {
            $language    = Yii::app()->getLanguage();
            $this->assertEquals($language, 'en');
            $searchModel = new AAASearchFormTestModel(new AAA());
            $dynamicSearchAttributes = array(
                                        0 => array('attributeIndexOrDerivedType' => 'bbb' .
                                                    DynamicSearchUtil::RELATION_DELIMITER . 'ccc' .
                                                    DynamicSearchUtil::RELATION_DELIMITER . 'iii' .
                                                    DynamicSearchUtil::RELATION_DELIMITER . 'date__Date',
                                                    'structurePosition'          => '1',
                                                    'bbb'                        => array(
                                                    'relatedModelData'           => true,
                                                    'ccc'                        => array(
                                                    'relatedModelData'           => true,
                                                    'iii'                        => array(
                                                    'relatedModelData'           => true,
                                                    'date__Date'                 =>
                                                        array('firstDate' => '5/4/11',
                                                              'type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER))))),
                                        1 => array('attributeIndexOrDerivedType' => 'bbb' .
                                                    DynamicSearchUtil::RELATION_DELIMITER . 'ccc' .
                                                    DynamicSearchUtil::RELATION_DELIMITER . 'iii' .
                                                    DynamicSearchUtil::RELATION_DELIMITER . 'dateTime__DateTime',
                                                    'structurePosition'          => '1',
                                                    'bbb'                        => array(
                                                    'relatedModelData'           => true,
                                                    'ccc'                        => array(
                                                    'relatedModelData'           => true,
                                                    'iii'                        => array(
                                                    'relatedModelData'           => true,
                                                    'date__Date'                 =>
                                                        array('firstDate' => '5/7/11',
                                                              'type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER))))),
                                        );

            $newArray = SearchUtil::sanitizeDynamicSearchAttributesByDesignerTypeForSavingModel($searchModel,
                                                                                                $dynamicSearchAttributes);
            $compareData = array(
                                        0 => array('attributeIndexOrDerivedType' => 'bbb' .
                                                    DynamicSearchUtil::RELATION_DELIMITER . 'ccc' .
                                                    DynamicSearchUtil::RELATION_DELIMITER . 'iii' .
                                                    DynamicSearchUtil::RELATION_DELIMITER . 'date__Date',
                                                    'structurePosition'          => '1',
                                                    'bbb'                        => array(
                                                    'relatedModelData'           => true,
                                                    'ccc'                        => array(
                                                    'relatedModelData'           => true,
                                                    'iii'                        => array(
                                                    'relatedModelData'           => true,
                                                    'date__Date'                 =>
                                                        array('firstDate' => '2011-05-04',
                                                              'type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER))))),
                                        1 => array('attributeIndexOrDerivedType' => 'bbb' .
                                                    DynamicSearchUtil::RELATION_DELIMITER . 'ccc' .
                                                    DynamicSearchUtil::RELATION_DELIMITER . 'iii' .
                                                    DynamicSearchUtil::RELATION_DELIMITER . 'dateTime__DateTime',
                                                    'structurePosition'          => '1',
                                                    'bbb'                        => array(
                                                    'relatedModelData'           => true,
                                                    'ccc'                        => array(
                                                    'relatedModelData'           => true,
                                                    'iii'                        => array(
                                                    'relatedModelData'           => true,
                                                    'date__Date'                 =>
                                                        array('firstDate' => '2011-05-07',
                                                              'type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER))))),
                                        );
            $this->assertEquals($compareData, $newArray);
        }

        public function testGetDynamicSearchStructureFromGetArray()
        {
            $_GET['testing'] = array(
                'a' => '',
            );
            $newString = SearchUtil::getDynamicSearchStructureFromGetArray('testing');
            $this->assertNull($newString);
            $_GET['testing'] = array(
                'a' => null,
                'dynamicStructure' => '1 and 2',
            );
            $newString = SearchUtil::getDynamicSearchStructureFromGetArray('testing');
            $this->assertEquals('1 and 2', $newString);
        }

        /**
         * Checks if the empty values are properly converted to null when nested
         */
        public function testGetSearchAttributesFromSearchArrayWithRecursiveNullResolution()
        {
            $searchArray = array(
                'a' => 'apple',
                'b' => array(
                    'relatedModelData' => true,
                    'bMember' => '',
                ),
                'c' => array(
                    'relatedModelData' => true,
                    'd' => array(
                        'relatedModelData' => true,
                        'dMember' => '',
                    ),
                ),
            );
            $testArray = array(
                'a' => 'apple',
                'b' => array(
                    'relatedModelData' => true,
                    'bMember' => null,
                ),
                'c' => array(
                    'relatedModelData' => true,
                    'd' => array(
                        'relatedModelData' => true,
                        'dMember' => null,
                    ),
                ),
            );
            $newArray = SearchUtil::getSearchAttributesFromSearchArray($searchArray);
            $this->assertEquals($testArray, $newArray);
        }
    }
?>
