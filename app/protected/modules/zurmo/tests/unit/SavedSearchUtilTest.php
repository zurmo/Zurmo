<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
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
            $searchForm                     = new AAASavedDynamicSearchFormTestModel(new AAA());
            $listAttributesSelector         = new ListAttributesSelector('AListView', 'TestModule');
            $searchForm->setListAttributesSelector($listAttributesSelector);
            $searchForm->savedSearchName    = 'myTest';
            $searchForm->dynamicStructure   = '1 or 6';
            $searchForm->dynamicClauses     = array('a', 'b');
            $searchForm->anyMixedAttributes = 'abcdef';
            $searchForm->setAnyMixedAttributesScope('xyz');
            $searchForm->getListAttributesSelector()->setSelected(array('aaaMember', 'aaaMember2'));
            $dataCollection                 = new SavedSearchAttributesDataCollection($searchForm);
            SavedSearchUtil::setDataByKeyAndDataCollection('abc', $dataCollection, array());
            $stickyData                     = StickySearchUtil::getDataByKey('abc');
            $kanbanBoard                  = new KanbanBoard(new AAA(), 'industry');
            $kanbanBoard->setGroupByAttributeVisibleValues(array('c', 'd'));
            $kanbanBoard->setSelectedTheme('red');
            $searchForm->setKanbanBoard($kanbanBoard);
            $savedSearch                    = SavedSearchUtil::makeSavedSearchBySearchForm($searchForm, 'someView', $stickyData);
            $this->assertTrue($savedSearch->id < 0);
            $unserializedData = unserialize($savedSearch->serializedData);
            $this->assertEquals('1 or 6',        $unserializedData['dynamicStructure']);
            $this->assertEquals(array('c', 'd'), $unserializedData['groupByAttributeVisibleValues']);
            $this->assertEquals('red',           $unserializedData['selectedTheme']);
            $saved = $savedSearch->save();
            $savedSearchId = $savedSearch->id;
            $searchForm->savedSearchId      = $savedSearch->id;
            $this->assertTrue($saved);
            $this->assertEquals('myTest', $savedSearch->name);

            $dataCollection                 = new SavedSearchAttributesDataCollection($searchForm);
            SavedSearchUtil::setDataByKeyAndDataCollection('abc', $dataCollection, array());
            $stickyData                     = StickySearchUtil::getDataByKey('abc');
            $savedSearch                    = SavedSearchUtil::makeSavedSearchBySearchForm($searchForm, 'someView');

            $searchForm                  = new AAASavedDynamicSearchFormTestModel(new AAA());
            $searchForm->savedSearchId   = $savedSearchId;
            $searchForm->savedSearchName = 'myTest';
            $savedSearch = SavedSearchUtil::makeSavedSearchBySearchForm($searchForm, 'someView');
            $unserializedData = unserialize($savedSearch->serializedData);
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
            $searchForm                     = new AAASavedDynamicSearchFormTestModel(new AAA());
            $listAttributesSelector         = new ListAttributesSelector('AListView', 'TestModule');
            $searchForm->setListAttributesSelector($listAttributesSelector);
            $searchForm->setKanbanBoard(new KanbanBoard(new AAA(), 'industry'));
            SavedSearchUtil::resolveSearchFormByGetData($getData, $searchForm);
            $this->assertEquals('myTest',                                   $searchForm->savedSearchName);
            $this->assertEquals('abcdef',                                   $searchForm->anyMixedAttributes);
            $this->assertEquals('xyz',                                      $searchForm->getAnyMixedAttributesScope());
            $this->assertEquals('1 or 6',                                   $searchForm->dynamicStructure);
            $this->assertEquals(array('a', 'b'),                            $searchForm->dynamicClauses);
            $this->assertEquals(array('aaaMember', 'aaaMember2'),           $searchForm->getListAttributesSelector()->getSelected());
            $this->assertEquals(array('c', 'd'), $searchForm->getKanbanBoard()->getGroupByAttributeVisibleValues());
            $this->assertEquals('red',           $searchForm->getKanbanBoard()->getSelectedTheme());
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
            $listAttributesSelector          = new ListAttributesSelector('AListView', 'TestModule');
            $searchModel->setListAttributesSelector($listAttributesSelector);
            $searchModel->dynamicStructure   = '1 and 5';
            $searchModel->dynamicClauses     = array('a', 'b');
            $searchModel->anyMixedAttributes = 'abcdef';
            $searchModel->savedSearchId      = $savedSearch->id;
            $searchModel->setAnyMixedAttributesScope('xyz');
            $kanbanBoard                     = new KanbanBoard(new AAA(), 'industry');
            $kanbanBoard->setIsActive();
            $kanbanBoard->setGroupByAttributeVisibleValues(array('c', 'd'));
            $kanbanBoard->setSelectedTheme('red');
            $searchModel->setKanbanBoard($kanbanBoard);
            $searchModel->getListAttributesSelector()->setSelected(array('aaaMember', 'aaaMember2'));
            $dataCollection = new SavedSearchAttributesDataCollection($searchModel);
            SavedSearchUtil::setDataByKeyAndDataCollection('abc', $dataCollection, array());
            $stickyData = StickySearchUtil::getDataByKey('abc');
            $compareData = array(   'dynamicClauses'          => array('a', 'b'),
                                    'dynamicStructure'        => '1 and 5',
                                    'anyMixedAttributes'      => 'abcdef',
                                    'anyMixedAttributesScope' => 'xyz',
                                    'savedSearchId'           => $savedSearch->id,
                                    SearchForm::SELECTED_LIST_ATTRIBUTES => array('aaaMember', 'aaaMember2'),
                                    'groupByAttributeVisibleValues' => array('c', 'd'),
                                    'selectedTheme' => 'red');
            $this->assertEquals($compareData, $stickyData);
            $searchModel                     = new AAASavedDynamicSearchFormTestModel(new AAA(false));
            $kanbanBoard                     = new KanbanBoard(new AAA(), 'industry');
            $searchModel->setKanbanBoard($kanbanBoard);
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
            $this->assertEquals(array('c', 'd'),    $searchModel->getKanbanBoard()->getGroupByAttributeVisibleValues());
            $this->assertEquals('red',              $searchModel->getKanbanBoard()->getSelectedTheme());
        }

        public function testStickySearchByKeyWithSortAsc()
        {
            $dataArray = $this->getSearchDataForSort();
            $dataCollection = $dataArray[ 'dataCollection' ];
            $savedSearch = $dataArray[ 'savedSearch' ];
            //Set the sort in $_GET to set the sticky key for it.
            $_GET['AAA_sort'] = 'aaaMember';
            SavedSearchUtil::setDataByKeyAndDataCollection('abc', $dataCollection, array());
            $stickyData = StickySearchUtil::getDataByKey('abc');
            $compareData = array(   'dynamicClauses'                     => array('a', 'b'),
                                    'dynamicStructure'                   => '1 and 5',
                                    'anyMixedAttributes'                 => 'abcdef',
                                    'anyMixedAttributesScope'            => 'xyz',
                                    'savedSearchId'                      => $savedSearch->id,
                                    SearchForm::SELECTED_LIST_ATTRIBUTES => array('aaaMember', 'aaaMember2'),
                                    'sortAttribute'                      => 'aaaMember',
                                    'sortDescending'                     => false);
            $this->assertEquals($compareData, $stickyData);

            $searchModel                     = new AAASavedDynamicSearchFormTestModel(new AAA(false));
            $listAttributesSelector          = new ListAttributesSelector('AListView', 'TestModule');
            $searchModel->setListAttributesSelector($listAttributesSelector);
            unset($_GET['AAA_sort']);
            SavedSearchUtil::resolveSearchFormByStickyDataAndModel($stickyData, $searchModel);
            $this->assertEquals('aaaMember', $searchModel->sortAttribute);
            $this->assertNull($searchModel->sortDescending);
        }

        public function testStickySearchByKeyWithSortDesc()
        {
            $dataArray = $this->getSearchDataForSort();
            $dataCollection = $dataArray[ 'dataCollection' ];
            $savedSearch = $dataArray[ 'savedSearch' ];

            //Set the sort in $_GET to set the sticky key for it.
            $_GET['AAA_sort'] = 'aaaMember2.desc';
            SavedSearchUtil::setDataByKeyAndDataCollection('abc', $dataCollection, array());
            $stickyData = StickySearchUtil::getDataByKey('abc');
            $compareData = array(   'dynamicClauses'                     => array('a', 'b'),
                                    'dynamicStructure'                   => '1 and 5',
                                    'anyMixedAttributes'                 => 'abcdef',
                                    'anyMixedAttributesScope'            => 'xyz',
                                    'savedSearchId'                      => $savedSearch->id,
                                    SearchForm::SELECTED_LIST_ATTRIBUTES => array('aaaMember', 'aaaMember2'),
                                    'sortAttribute'                      => 'aaaMember2',
                                    'sortDescending'                     => true);
            $this->assertEquals($compareData, $stickyData);

            $searchModel                     = new AAASavedDynamicSearchFormTestModel(new AAA(false));
            $listAttributesSelector          = new ListAttributesSelector('AListView', 'TestModule');
            $searchModel->setListAttributesSelector($listAttributesSelector);
            unset($_GET['AAA_sort']);
            SavedSearchUtil::resolveSearchFormByStickyDataAndModel($stickyData, $searchModel);
            $this->assertEquals('aaaMember2', $searchModel->sortAttribute);
            $this->assertNotNull($searchModel->sortDescending);
        }

        private function getSearchDataForSort()
        {
            $savedSearch                     = new SavedSearch();
            $savedSearch->name               = 'something';
            $savedSearch->viewClassName      = 'view';
            $savedSearch->serializedData     = 'someString';
            $saved                           = $savedSearch->save();

            $searchModel                     = new AAASavedDynamicSearchFormTestModel(new AAA(false));
            $listAttributesSelector          = new ListAttributesSelector('AListView', 'TestModule');
            $searchModel->setListAttributesSelector($listAttributesSelector);
            $searchModel->dynamicStructure   = '1 and 5';
            $searchModel->dynamicClauses     = array('a', 'b');
            $searchModel->anyMixedAttributes = 'abcdef';
            $searchModel->savedSearchId      = $savedSearch->id;
            $searchModel->setAnyMixedAttributesScope('xyz');

            $searchModel->getListAttributesSelector()->setSelected(array('aaaMember', 'aaaMember2'));
            $dataCollection = new SavedSearchAttributesDataCollection($searchModel);
            return array('dataCollection' => $dataCollection, 'savedSearch' => $savedSearch);
        }
    }
?>