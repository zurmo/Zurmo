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

    class SavedSearchAttributesDataCollectionTest extends ZurmoBaseTest
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

        public function testGetDynamicSearchAttributes()
        {
            //getDynamicSearchAttributes()
            $model = new AAASearchFormTestModel(new AAA());
            $model->dynamicClauses = array(array('aaaMember' => '5'));
            $dataCollection          = new SavedSearchAttributesDataCollection($model);
            $dynamicClauses        = $dataCollection->getDynamicSearchAttributes();
            $this->assertEquals(array(array('aaaMember' => '5')), $dynamicClauses);
            $_GET['AAASearchFormTestModel']['dynamicClauses'] = array(array('aaaMember' => '6'));
            $dynamicClauses        = $dataCollection->getDynamicSearchAttributes();
            $this->assertEquals(array(array('aaaMember' => '6')), $dynamicClauses);
        }

        public function testGetDynamicStructure()
        {
            $model = new AAASearchFormTestModel(new AAA());
            $model->dynamicStructure = '1 AND 2';
            $dataCollection          = new SavedSearchAttributesDataCollection($model);
            $dynamicStructure        = $dataCollection->getDynamicStructure();
            $this->assertEquals('1 AND 2', $dynamicStructure);
            $_GET['AAASearchFormTestModel']['dynamicStructure'] = '1 OR 2';
            $dynamicStructure        = $dataCollection->getDynamicStructure();
            $this->assertEquals('1 OR 2', $dynamicStructure);
        }

        public function testResolveSearchAttributesFromSourceData()
        {
            $model = new AAASearchFormTestModel(new AAA());
            $model->anyMixedAttributes = '47';
            $dataCollection          = new SavedSearchAttributesDataCollection($model);
            $searchAttributes        = $dataCollection->resolveSearchAttributesFromSourceData();
            $this->assertEquals(array('anyMixedAttributes' => '47'), $searchAttributes);
            $_GET['AAASearchFormTestModel']['anyMixedAttributes'] = '46';
            $searchAttributes        = $dataCollection->resolveSearchAttributesFromSourceData();
            $this->assertEquals(array('anyMixedAttributes' => '46'), $searchAttributes);
        }

        public function testResolveAnyMixedAttributesScopeForSearchModelFromSourceData()
        {
            $model = new AAASearchFormTestModel(new AAA());
            $dataCollection          = new SavedSearchAttributesDataCollection($model);
            $getArrayName = 'someArray';
            $dataCollection->resolveAnyMixedAttributesScopeForSearchModelFromSourceData();
            $this->assertNull($model->getAnyMixedAttributesScope());

            //Test passing a value in the GET
            $_GET['AAASearchFormTestModel'][SearchForm::ANY_MIXED_ATTRIBUTES_SCOPE_NAME] = 'notAnArray';
            $dataCollection->resolveAnyMixedAttributesScopeForSearchModelFromSourceData();
            $this->assertNull($model->getAnyMixedAttributesScope());

            $_GET['AAASearchFormTestModel'][SearchForm::ANY_MIXED_ATTRIBUTES_SCOPE_NAME] = array('All');
            $dataCollection->resolveAnyMixedAttributesScopeForSearchModelFromSourceData();
            $this->assertNull($model->getAnyMixedAttributesScope());

            $_GET['AAASearchFormTestModel'][SearchForm::ANY_MIXED_ATTRIBUTES_SCOPE_NAME] = array('A', 'B', 'C');
            $dataCollection->resolveAnyMixedAttributesScopeForSearchModelFromSourceData();
            $this->assertEquals(array('A', 'B', 'C'), $model->getAnyMixedAttributesScope());
        }

        public function testResolveSelectedListAttributesForSearchModelFromSourceData()
        {
            $model = new AAASearchFormTestModel(new A());
            $listAttributesSelector         = new ListAttributesSelector('AListView', 'TestModule');
            $model->setListAttributesSelector($listAttributesSelector);
            $dataCollection          = new SavedSearchAttributesDataCollection($model);
            $getArrayName = 'someArray';
            $dataCollection->resolveSelectedListAttributesForSearchModelFromSourceData();
            $this->assertEquals(array('name'), $model->getListAttributesSelector()->getSelected());

            //Test passing a value in the GET
            $_GET['AAASearchFormTestModel'][SearchForm::SELECTED_LIST_ATTRIBUTES] = 'notAnArray';
            $dataCollection->resolveSelectedListAttributesForSearchModelFromSourceData();
            $this->assertEquals(array('name'), $model->getListAttributesSelector()->getSelected());

            $_GET['AAASearchFormTestModel'][SearchForm::SELECTED_LIST_ATTRIBUTES] = array('All');
            $dataCollection->resolveSelectedListAttributesForSearchModelFromSourceData();
            $this->assertEquals(array('All'), $model->getListAttributesSelector()->getSelected());

            $_GET['AAASearchFormTestModel'][SearchForm::SELECTED_LIST_ATTRIBUTES] = array('name', 'a');
            $dataCollection->resolveSelectedListAttributesForSearchModelFromSourceData();
            $this->assertEquals(array('name', 'a'), $model->getListAttributesSelector()->getSelected());
        }
    }
?>