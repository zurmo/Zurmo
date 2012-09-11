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

    class MixedModelsSearchAndListView extends View
    {
        private $views;
        private $term;
        private $scopeData;

        public function __construct(Array $views, $term, $scopeData)
        {
            $this->views     = $views;
            $this->term      = $term;
            $this->scopeData = $scopeData;
        }

        protected function renderContent()
        {
            $content = '';
            $content = $this->renderSearchView();
            $content .= $this->renderListViews();
            $this->renderScripts();
            return $content;
        }

        protected function renderSearchView()
        {
            $moduleNamesAndLabels     = GlobalSearchUtil::
                                        getGlobalSearchScopingModuleNamesAndLabelsDataByUser(Yii::app()->user->userModel);
            $sourceUrl                = Yii::app()->createUrl('zurmo/default/globallist');
            GlobalSearchUtil::resolveModuleNamesAndLabelsDataWithAllOption(
                                        $moduleNamesAndLabels);
            $searchView = new MixedModelsSearchView($moduleNamesAndLabels, $sourceUrl, $this->term, $this->scopeData);
            return $searchView->render();
        }

        /**
         * Render a group of lists that contais the search result from GlobalList
         *
         */
        protected function renderListViews()
        {
            $rows = count($this->views);
            $gridView = new GridView($rows, 1);
            $row = 0;
            foreach ($this->views as $view)
            {
                $gridView->setView($view, $row++, 0);
            }
            return ZurmoHtml::tag('div', array('id' => 'MixedModelsMultipleListsView'), $gridView->render());
        }

        protected function renderScripts()
        {
            // Begin Not Coding Standard
            //On page ready load all the List View with data
            $script = "$(document).ready(function () {
                            $('#MixedModelsSearchView').find('a').click();
                       });";
            Yii::app()->clientScript->registerScript('LoadListViews', $script);
            // End Not Coding Standard
        }
    }
?>