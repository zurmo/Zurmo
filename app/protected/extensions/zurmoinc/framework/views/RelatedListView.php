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
     * The base View for a module's related list view.
     */
    abstract class RelatedListView extends ListView implements PortletViewInterface
    {
        protected $params;
        protected $viewData;
        protected $uniqueLayoutId;

        /**
         * Signal to use ExtendedGridView
         * @var integer
         */
        const GRID_VIEW_TYPE_NORMAL  = 1;

        /**
         * Signal to use StackedExtendedGridView
         * @var integer
         */
        const GRID_VIEW_TYPE_STACKED = 2;

        public function __construct($viewData, $params, $uniqueLayoutId)
        {
            assert('isset($params["controllerId"])');
            assert('isset($params["relationModuleId"])');
            assert('$params["relationModel"] instanceof RedBeanModel || $params["relationModel"] instanceof ModelForm');
            assert('isset($params["portletId"])');
            assert('isset($params["redirectUrl"])');
            assert('$this->getRelationAttributeName() != null');
            $this->modelClassName    = $this->getModelClassName();
            $this->viewData          = $viewData;
            $this->params            = $params;
            $this->uniqueLayoutId    = $uniqueLayoutId;
            $this->gridIdSuffix      = $uniqueLayoutId;
            $this->rowsAreSelectable = false;
            $this->gridId            = 'list-view';
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
            return Yii::t('Default', 'No {moduleLabelPluralLowerCase} found', array('{moduleLabelPluralLowerCase}' => $moduleLabel));
        }

        protected function getGridViewWidgetPath()
        {
            $resolvedMetadata = $this->getResolvedMetadata();
            if (isset($resolvedMetadata['global']['gridViewType']) &&
                     $resolvedMetadata['global']['gridViewType'] == RelatedListView::GRID_VIEW_TYPE_STACKED)
             {
                 return 'ext.zurmoinc.framework.widgets.StackedExtendedGridView';
             }

            return parent::getGridViewWidgetPath();
        }

        protected function makeSearchAttributeData()
        {
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => $this->getRelationAttributeName(),
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => (int)$this->params['relationModel']->id,
                )
            );
            $searchAttributeData['structure'] = '1';
            return $searchAttributeData;
        }

        protected function makeDataProviderBySearchAttributeData($searchAttributeData)
        {
            assert('is_array($searchAttributeData)');
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType('subListPageSize');
            return new RedBeanModelDataProvider( $this->modelClassName, null, false,
                                                                $searchAttributeData, array(
                                                                    'pagination' => array(
                                                                        'pageSize' => $pageSize,
                                                                    )
                                                                ));
        }

        public function isUniqueToAPage()
        {
            return false;
        }

        protected function getCreateLinkRouteParameters()
        {
            return array(
                'relationAttributeName' => $this->getRelationAttributeName(),
                'relationModelId'       => $this->params['relationModel']->id,
                'relationModuleId'      => $this->params['relationModuleId'],
                'redirectUrl'           => $this->params['redirectUrl'],
            );
        }

        protected function getCGridViewPagerParams()
        {
            return array(
                    'cssFile' => Yii::app()->baseUrl . '/themes/' . Yii::app()->theme->name . '/css/cgrid-view.css',
                    'prevPageLabel' => '<span>previous</span>',
                    'nextPageLabel' => '<span>next</span>',
                    'class'          => 'SimpleListLinkPager',
                    'paginationParams' => array_merge(GetUtil::getData(), array('portletId' => $this->params['portletId'])),
                    'route'         => 'defaultPortlet/details',
                );
        }

        /**
         * Override to not run global eval, since it causes doubling up of ajax requests on the pager.
         * (non-PHPdoc)
         * @see ListView::getCGridViewAfterAjaxUpdate()
         */
        protected function getCGridViewAfterAjaxUpdate()
        {
            // Begin Not Coding Standard
            return 'js:function(id, data) {
                        processAjaxSuccessError(id, data);
                    }';
            // End Not Coding Standard
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

        public static function canUserConfigure()
        {
            return false;
        }

        public static function getDesignerRulesType()
        {
            return 'RelatedListView';
        }

        /**
         * Override to add a display description.  An example would be 'Contacts for Account'.  This display description
         * can then be used by external classes interfacing with the view in order to display information to the user in
         * the user interface.
         */
        public static function getDisplayDescription()
        {
            return null;
        }

        public function getModelClassName()
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
            return 'RelatedList';
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

        abstract protected function getRelationAttributeName();
    }
?>
