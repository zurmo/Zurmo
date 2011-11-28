<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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
            $newArray = SearchUtil::resolveSearchAttributesFromGetArray('testing');
            $this->assertEquals($testArray, $newArray);

            //Now test various empty and 0 combinations
            $_GET['testing'] = array(
                'a' => null,
            );
            $newArray = SearchUtil::resolveSearchAttributesFromGetArray('testing');
            $this->assertEquals(array('a' => null), $newArray);

            $_GET['testing'] = array(
                'a' => '',
            );
            $newArray = SearchUtil::resolveSearchAttributesFromGetArray('testing');
            $this->assertEquals(array('a' => null), $newArray);

            $_GET['testing'] = array(
                'a' => 0,
            );
            $newArray = SearchUtil::resolveSearchAttributesFromGetArray('testing');
            $this->assertEquals(array('a' => null), $newArray);

            $_GET['testing'] = array(
                'a' => '0',
            );
            $newArray = SearchUtil::resolveSearchAttributesFromGetArray('testing');
            $this->assertEquals(array('a' => null), $newArray);
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
        }

        public function testAdaptSearchAttributesToSetInRedBeanModel()
        {
            $model = new ASearchFormTestModel(new A(false));
            $searchAttributes = array(
                'differentOperatorB' => array('value' => 'thiswillstay'),
                'a'				     => array('value' => 'thiswillgo'),
                'differentOperatorB' => 'something',
                'name'				 => array('value' => 'thiswillstay'),
            );
            $adaptedSearchAttributes = SearchUtil::adaptSearchAttributesToSetInRedBeanModel($searchAttributes, $model);
            $compareData = array(
                'differentOperatorB' => array('value' => 'thiswillstay'),
                'a'				     => 'thiswillgo',
                'differentOperatorB' => 'something',
                'name'				 => array('value' => 'thiswillstay'),
            );
            $this->assertEquals($compareData, $adaptedSearchAttributes);
        }
    }
?>
