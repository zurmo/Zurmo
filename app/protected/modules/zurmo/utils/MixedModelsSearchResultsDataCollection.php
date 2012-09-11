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

    /**
     * MixedModelsSearchSearchResultsDataCollection
     * @param
     */
    class MixedModelsSearchResultsDataCollection
    {
        private $term;
        private $user;
        private $views = array();

        /**
         * @param   string
         * @param   integer
         * @param   User        User model
         * @param   array       Modules to be searched
         */
        public function __construct($term, $pageSize, User $user)
        {
            assert('is_string($term)');
            assert('is_int($pageSize)');
            $this->term      = $term;
            $this->pageSize  = $pageSize;
            $this->user      = $user;
        }

        /**
         * @param   string
         * @param   bollean     Return an empty listView
         * @return  View
         */
        public function getListView($moduleName, $forceEmptyResults = false)
        {
            assert('is_string($moduleName)');
            $pageSize = $this->pageSize;
            $module = Yii::app()->findModule($moduleName);
            $searchFormClassName = $module::getGlobalSearchFormClassName();
            $modelClassName = $module::getPrimaryModelName();
            $model = new $modelClassName(false);
            $searchForm = new $searchFormClassName($model);
            $sanitizedSearchAttributes = MixedTermSearchUtil::
                    getGlobalSearchAttributeByModuleAndPartialTerm($module, $this->term);
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                    $searchForm,
                    $this->user->id,
                    $sanitizedSearchAttributes
                 );
            $listViewClassName = $module::getPluralCamelCasedName() . 'ListView';
            $sortAttribute     = SearchUtil::resolveSortAttributeFromGetArray($modelClassName);
            $sortDescending    = SearchUtil::resolveSortDescendingFromGetArray($modelClassName);
            if ($forceEmptyResults)
            {
                $dataProviderClass = 'EmptyRedBeanModelDataProvider';
                $emptyText = '';
            }
            else
            {
                $dataProviderClass = 'RedBeanModelDataProvider';
                $emptyText = null;
            }
            $dataProvider = RedBeanModelDataProviderUtil::makeDataProvider(
                    $metadataAdapter->getAdaptedMetadata(false),
                    $modelClassName,
                    $dataProviderClass,
                    $sortAttribute,
                    $sortDescending,
                    $pageSize,
                    $module->getStateMetadataAdapterClassName()
                 );
            $listView = new $listViewClassName(
                    'default',
                    $module->getId(),
                    $modelClassName,
                    $dataProvider,
                    GetUtil::resolveSelectedIdsFromGet(),
                    '-' . $moduleName,
                    array(
                        'route' => '',
                        'class' => 'SimpleListLinkPager'
                      )
                 );
            $listView->setRowsAreSelectable(false);
            $listView->setEmptyText($emptyText);
            return $listView;
        }

        /**
         * makeViews
         * @return  array   moduleName => listView
         */
        private function makeViews()
        {
            $globalSearchModuleNamesAndLabelsData = GlobalSearchUtil::
                    getGlobalSearchScopingModuleNamesAndLabelsDataByUser($this->user);
            foreach ($globalSearchModuleNamesAndLabelsData as $moduleName => $label)
            {
                $titleView                              = new TitleBarView($label, null, 1);
                $iconClassName                          = Yii::app()->findModule($moduleName)->getSingularCamelCasedName();
                $titleView->setCssClasses(array($iconClassName));
                $this->views['titleBar-' . $moduleName] = $titleView;
                $this->views[$moduleName]               = $this->getListView($moduleName, true);
            }
        }

        public function getViews()
        {
            $this->makeViews();
            return $this->views;
        }
    }
?>