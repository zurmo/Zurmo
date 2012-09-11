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

    class MixedModelsSearchResultsDataCollectionTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            ContactsModule::loadStartingData();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testGetListView()
        {
            $term = "green";
            $pageSize = 5;
            $collection = new MixedModelsSearchResultsDataCollection($term, $pageSize, Yii::app()->user->userModel);
            $accountsView = $collection->getListView('contacts');
            $this->assertInstanceOf('View', $accountsView);
            $this->assertAttributeInstanceOf('RedBeanModelDataProvider', 'dataProvider', $accountsView);
            //Get a listView with no empty results
            $accountsView = $collection->getListView('accounts', true);
            $this->assertInstanceOf('View', $accountsView);
            $this->assertAttributeInstanceOf('EmptyRedBeanModelDataProvider', 'dataProvider', $accountsView);
        }

        public function testGetViews()
        {
            $term = "green";
            $pageSize = 5;
            $collection = new MixedModelsSearchResultsDataCollection($term, $pageSize, Yii::app()->user->userModel);
            $testViews = $collection->getViews();
            $i = 1;
            $oldModuleName = '';
            foreach ($testViews as $moduleName => $view)
            {
                if (($i++ % 2) === 1)
                {
                    $oldModuleName = str_replace('titleBar-', '', $moduleName);
                    $this->assertInstanceOf('TitleBarView', $view);
                }
                else
                {
                    $this->assertEquals($oldModuleName, $moduleName);
                    $this->assertInstanceOf('View', $view);
                    $this->assertAttributeInstanceOf('EmptyRedBeanModelDataProvider', 'dataProvider', $view);
                }
            }
        }
    }
?>