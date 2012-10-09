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

    class SavedSearchUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testMakeSavedSearchBySearchForm()
        {
            $searchForm                   = new AAASavedDynamicSearchFormTestModel(new AAA());
            $searchForm->savedSearchName  = 'myTest';
            $searchForm->dynamicStructure = '1 or 6';
            $savedSearch = SavedSearchUtil::makeSavedSearchBySearchForm($searchForm, 'someView');
            $this->assertTrue($savedSearch->id < 0);
            $unserializedData = unserialize($savedSearch->serializedData);
            $this->assertEquals('1 or 6', $unserializedData['dynamicStructure']);
            $saved = $savedSearch->save();
            $savedSearchId = $savedSearch->id;
            $this->assertTrue($saved);
            $this->assertEquals('myTest', $savedSearch->name);

            $savedSearch = SavedSearchUtil::makeSavedSearchBySearchForm($searchForm, 'someView');

            $searchForm                  = new AAASavedDynamicSearchFormTestModel(new AAA());
            $searchForm->savedSearchId   = $savedSearchId;
            $searchForm->savedSearchName = 'myTest';
            $savedSearch = SavedSearchUtil::makeSavedSearchBySearchForm($searchForm, 'someView');
            $this->assertEquals($savedSearchId, $savedSearch->id);
            $this->assertTrue($savedSearch->id > 0);
            $savedSearch->forget();
        }

        /**
         * @depends testMakeSavedSearchBySearchForm
         */
        public function testResolveSearchFormByGetData()
        {
            $savedSearches = SavedSearch::getByName('myTest');
            $this->assertEquals(1, count($savedSearches));
            $getData = array(
                'savedSearchId'           => $savedSearches[0]->id,
                'anyMixedAttributes'      => 'a search',
                'anyMixedAttributesScope' => 'some',
                'dynamicStructure'        => '1 or 5',
                'dynamicClauses'          => array('a', 'b'),
                SearchForm::SELECTED_LIST_ATTRIBUTES => array('aaaMember', 'aaaMember2')
            );
            $searchForm = new AAASavedDynamicSearchFormTestModel(new AAA());
            $listAttributesSelector         = new ListAttributesSelector('AListView', 'TestModule');
            $searchForm->setListAttributesSelector($listAttributesSelector);
            SavedSearchUtil::resolveSearchFormByGetData($getData, $searchForm);
            $this->assertEquals('myTest',      $searchForm->savedSearchName);
            $this->assertEquals(null,          $searchForm->anyMixedAttributes);
            $this->assertEquals(null,          $searchForm->getAnyMixedAttributesScope());
            $this->assertEquals('1 or 6',      $searchForm->dynamicStructure);
            $this->assertEquals(array(),       $searchForm->dynamicClauses);
            $this->assertEquals(array('name'), $searchForm->getListAttributesSelector()->getSelected());
        }

        public function testSetGetClearStickySearchByKey()
        {
            StickySearchUtil::clearDataByKey('abc');
            $value = StickySearchUtil::getDataByKey('abc');
            $this->assertNull($value);

            $savedSearch                     = new SavedSearch();
            $savedSearch->name               = 'something';
            $savedSearch->viewClassName      = 'view';
            $savedSearch->serializedData     = 'someString';
            $saved                           = $savedSearch->save();
            $this->assertTrue($saved);
            $this->assertTrue($savedSearch->id > 0);

            $searchModel                     = new AAASavedDynamicSearchFormTestModel(new AAA(false));
            $listAttributesSelector         = new ListAttributesSelector('AListView', 'TestModule');
            $searchModel->setListAttributesSelector($listAttributesSelector);
            $searchModel->dynamicStructure   = '1 and 5';
            $searchModel->dynamicClauses     = array('a', 'b');
            $searchModel->anyMixedAttributes = 'abcdef';
            $searchModel->savedSearchId      = $savedSearch->id;
            $searchModel->setAnyMixedAttributesScope('xyz');
            $searchModel->getListAttributesSelector()->setSelected(array('aaaMember', 'aaaMember2'));
            $dataCollection = new SavedSearchAttributesDataCollection($searchModel);
            SavedSearchUtil::setDataByKeyAndDataCollection('abc', $dataCollection);
            $stickyData = StickySearchUtil::getDataByKey('abc');
            $compareData = array(   'dynamicClauses'          => array('a', 'b'),
                                    'dynamicStructure'        => '1 and 5',
                                    'anyMixedAttributes'      => 'abcdef',
                                    'anyMixedAttributesScope' => 'xyz',
                                    'savedSearchId'           => $savedSearch->id,
                                    SearchForm::SELECTED_LIST_ATTRIBUTES => array('aaaMember', 'aaaMember2'));
            $this->assertEquals($compareData, $stickyData);
            $searchModel                     = new AAASavedDynamicSearchFormTestModel(new AAA(false));
            $listAttributesSelector          = new ListAttributesSelector('AListView', 'TestModule');
            $searchModel->setListAttributesSelector($listAttributesSelector);
            SavedSearchUtil::resolveSearchFormByStickyDataAndModel($stickyData, $searchModel);
            $this->assertEquals('something',        $searchModel->savedSearchName);
            $this->assertEquals($savedSearch->id,   $searchModel->savedSearchId);
            $this->assertEquals('abcdef',           $searchModel->anyMixedAttributes);
            $this->assertEquals('xyz',              $searchModel->getAnyMixedAttributesScope());
            $this->assertEquals('1 and 5',          $searchModel->dynamicStructure);
            $this->assertEquals(array('a', 'b'),    $searchModel->dynamicClauses);
            $this->assertEquals(array('aaaMember', 'aaaMember2'), $searchModel->getListAttributesSelector()->getSelected());
        }
    }
?>