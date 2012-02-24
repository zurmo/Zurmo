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
     * The base View for a module's my list view which is a customizable list view by the end user for use in the
     * dashboard.
     */
    abstract class MyListView extends ListView implements PortletViewInterface
    {
        protected $params;
        protected $viewData;
        protected $uniqueLayoutId;

        public function __construct($viewData, $params, $uniqueLayoutId)
        {
            assert('isset($params["portletId"])');
            $this->viewData          = $viewData;
            $this->params            = $params;
            $this->uniqueLayoutId    = $uniqueLayoutId;
            $this->gridIdSuffix      = $uniqueLayoutId;
            $this->rowsAreSelectable = false;
            $this->gridId            = 'list-view';
            $this->modelClassName    = $this->getModelClassName();
            $this->controllerId      = $this->resolveControllerId();
            $this->moduleId          = $this->resolveModuleId();
        }

        protected function getShowTableOnEmpty()
        {
            return false;
        }

        protected function getEmptyText()
        {
            $moduleClassName = static::getModuleClassName();
            $moduleLabel     = $moduleClassName::getModuleLabelByTypeAndLanguage('PluralLowerCase');
            return Yii::t('Default', 'No {moduleLabel} found', array('{moduleLabel}' => $moduleLabel));
        }

        protected function makeSearchAttributeData()
        {
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                $this->getSearchModel(),
                Yii::app()->user->userModel->id,
                $this->getSearchAttributes()
            );
            $metadata = $metadataAdapter->getAdaptedMetadata();
            $this->resolveSearchAttributesMetadataAgainstStateAdapter($metadata);
            return $metadata;
        }

        protected function resolveSearchAttributesMetadataAgainstStateAdapter(& $searchAttributesMetadata)
        {
            assert('is_array($searchAttributesMetadata)');
            $moduleClassName              = $this->getActionModuleClassName();
            if (null != $stateMetadataAdapterClassName = $moduleClassName::getStateMetadataAdapterClassName())
            {
                $stateMetadataAdapter     = new $stateMetadataAdapterClassName($searchAttributesMetadata);
                $searchAttributesMetadata = $stateMetadataAdapter->getAdaptedDataProviderMetadata();
            }
        }

        protected function makeDataProviderBySearchAttributeData($searchAttributeData)
        {
            assert('is_array($searchAttributeData)');
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType('dashboardListPageSize');
            return new RedBeanModelDataProvider($this->modelClassName, $this->getSortAttributeForDataProvider(), false,
                                                                $searchAttributeData, array(
                                                                    'pagination' => array(
                                                                        'pageSize' => $pageSize,
                                                                    )
                                                                ));
        }

        protected function getSortAttributeForDataProvider()
        {
            return null;
        }

        public function isUniqueToAPage()
        {
            return false;
        }

        protected function getSearchAttributes()
        {
            if ($this->viewData != null && isset($this->viewData['searchAttributes']))
            {
                return $this->viewData['searchAttributes'];
            }
            return static::getDefaultSearchAttributes();
        }

        public function getConfigurationView()
        {
            $searchForm   = $this->getSearchModel();
            $formModel    = new MyListForm();
            if ($this->viewData != null)
            {
                if (isset($this->viewData['searchAttributes']))
                {
                    $searchForm->setAttributes($this->viewData['searchAttributes']);
                }
                if (isset($this->viewData['title']))
                {
                    $formModel->setAttributes(array('title' => $this->viewData['title']));
                }
            }
            else
            {
                $searchForm->setAttributes(static::getDefaultSearchAttributes());
                $formModel->setAttributes(array('title' => static::getDefaultTitle()));
            }
            $configViewClassName = static::getConfigViewClassName();
            return new $configViewClassName($formModel, $searchForm, $this->params);
        }

        protected function getCGridViewPagerParams()
        {
            return array(
                    'cssFile' => Yii::app()->baseUrl . '/themes/' . Yii::app()->theme->name . '/css/cgrid-view.css',
                    'firstPageLabel' => '&lt;&lt;',
                    'prevPageLabel'  => '&lt;',
                    'nextPageLabel'  => '&gt;',
                    'lastPageLabel'  => '&gt;&gt;',
                    'class'          => 'LinkPager',
                    'paginationParams' => array_merge(GetUtil::getData(), array('portletId' => $this->params['portletId'])),
                    'route'         => 'defaultPortlet/myListDetails',
                );
        }

        public function getTitle()
        {
            if (!empty($this->viewData['title']))
            {
                return $this->viewData['title'];
            }
            else
            {
                return static::getDefaultTitle();
            }
        }

        public static function getDefaultTitle()
        {
            $metadata = self::getMetadata();
            $title    = $metadata['perUser']['title'];
            MetadataUtil::resolveEvaluateSubString($title);
            return $title;
        }

        public static function getDefaultSearchAttributes()
        {
            $metadata = self::getMetadata();
            if (isset($metadata['perUser']['searchAttributes']))
            {
                return $metadata['perUser']['searchAttributes'];
            }
            return array();
        }

        public static function canUserConfigure()
        {
            return true;
        }

        public static function getDesignerRulesType()
        {
            return 'MyListView';
        }

        /**
         * Override to add a display description.  An example would be 'My Contacts'.  This display description
         * can then be used by external classes interfacing with the view in order to display information to the user in
         * the user interface.
         */
        public static function getDisplayDescription()
        {
            return null;
        }

        private function getModelClassName()
        {
            $moduleClassName = $this->getActionModuleClassName();
            return $moduleClassName::getPrimaryModelName();
        }

        /**
         * What kind of PortletRules this view follows.
         * @return PortletRulesType as string.
         */
        public static function getPortletRulesType()
        {
            return 'MyList';
        }

        /**
         * Controller Id for the link to models from rows in the grid view.
         */
        private function resolveControllerId()
        {
            return 'default';
        }

        /**
         * Module Id for the link to models from rows in the grid view.
         */
        private function resolveModuleId()
        {
            $moduleClassName = $this->getActionModuleClassName();
            return $moduleClassName::getDirectoryName();
        }

        /**
         * Module class name for models linked from rows in the grid view.
         */
        protected function getActionModuleClassName()
        {
            $calledClass = get_called_class();
            return $calledClass::getModuleClassName();
        }

        protected function getDataProvider()
        {
            if ($this->dataProvider == null)
            {
                $this->dataProvider = $this->makeDataProviderBySearchAttributeData($this->makeSearchAttributeData());
            }
            return $this->dataProvider;
        }

        /**
         * Override in non-abstract class to return the proper search model object.
         * @throws NotImplementedException
         */
        protected function getSearchModel()
        {
            throw new NotImplementedException();
        }

        /**
         * Override in non-abstract class to return the proper config view class name.
         * @throws NotImplementedException
         */
        protected static function getConfigViewClassName()
        {
            throw new NotImplementedException();
        }
    }
?>
