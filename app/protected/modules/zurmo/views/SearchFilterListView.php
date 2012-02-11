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

    class SearchFilterListView extends GridView
    {
        public function __construct(
            $controllerId,
            $moduleId,
            ModelForm $searchModel,
            RedBeanModel $listModel,
            $moduleName,
            CDataProvider $dataProvider,
            $selectedIds,
            $selectAll,
            $filteredList,
            $filteredListId,
            $title
            )
        {
            parent::__construct(4, 1);
            $moduleClassName = $moduleName . 'Module';
            $menuItems = MenuUtil::getAccessibleShortcutsMenuByCurrentUser($moduleClassName);
            $shortcutsMenu = new DropDownShortcutsMenuView(
                $controllerId,
                $moduleId,
                $menuItems
            );
            $this->setView(new TitleBarView($title, Yii::t('Default', 'Home'), 1, $shortcutsMenu->render()), 0, 0);
            $searchViewClassName = $moduleName . 'SearchView';
            $this->setView(new $searchViewClassName($searchModel, get_class($listModel), null, true, !empty($filteredListId)), 1, 0);
            $this->setView(new FilteredListView($controllerId, $moduleId, $filteredList, $filteredListId, get_class($listModel)), 2, 0);
            $listViewClassName = $moduleName . 'ListView';
            $this->setView(new $listViewClassName($controllerId, $moduleId, get_class($listModel), $dataProvider, $selectedIds, $selectAll), 3, 0);
        }

        public function isUniqueToAPage()
        {
            return true;
        }
    }
?>