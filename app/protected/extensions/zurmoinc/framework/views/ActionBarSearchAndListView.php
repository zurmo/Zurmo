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

    /**
     * Renders an action bar, search view, and list view.
     */
    class ActionBarSearchAndListView extends GridView
    {
        public function __construct($controllerId, $moduleId, ModelForm $searchModel, RedBeanModel $listModel,
                                    $moduleName, CDataProvider $dataProvider, $selectedIds, $selectAll,
                                    $actionBarViewClassName)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_string($actionBarViewClassName)');
            parent::__construct(3, 1);

            $searchViewClassName = $moduleName . 'SearchView';
            $searchView          = new $searchViewClassName($searchModel, get_class($listModel));
            $listViewClassName   = $moduleName . 'ListView';
            $listView            = new $listViewClassName($controllerId, $moduleId,
                                       get_class($listModel), $dataProvider, $selectedIds, $selectAll);
            $actionBarView       = new $actionBarViewClassName($controllerId, $moduleId, $listModel,
                                                               $listView->getGridViewId(),
                                                               $dataProvider->getPagination()->pageVar,
                                                               $listView->getRowsAreSelectable());
            $this->setView($actionBarView, 0, 0);
            $this->setView($searchView, 1, 0);
            $this->setView($listView, 2, 0);
        }

        public function isUniqueToAPage()
        {
            return true;
        }
    }
?>