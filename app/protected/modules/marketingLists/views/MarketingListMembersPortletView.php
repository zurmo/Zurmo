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

    class MarketingListMembersPortletView extends ConfigurableMetadataView
                                                                  implements PortletViewInterface
    {
        // TODO: @Shoaibi: Low: refactor this and LatestActivitiesForPortletView, create a parent PortletView Class
        /**
         * Portlet parameters passed in from the portlet.
         * @var array
         */
        protected $cssClasses = array('portlet-with-toolbar');

        protected $params;

        protected $controllerId;

        protected $moduleId;

        protected $model;

        protected $uniqueLayoutId;

        protected $viewData;

        protected $listView;

        protected $dataProvider;

        protected $uniquePageId;

        protected $configurationForm;

        protected static $persistentUserPortletConfigs = array(
                'filteredBySubscriptionType',
            );

        /**
         * Some extra assertions are made to ensure this view is used in a way that it supports.
         */
        public function __construct($viewData, $params, $uniqueLayoutId)
        {
            assert('is_array($viewData) || $viewData == null');
            assert('isset($params["relationModuleId"])');
            assert('isset($params["relationModel"])');
            assert('isset($params["portletId"])');
            assert('is_string($uniqueLayoutId)');
            $this->moduleId       = $params['relationModuleId'];
            $this->model          = $params['relationModel'];
            $this->modelId        = $this->model->id;
            $this->controllerId   = $params['controllerId'];
            $this->viewData       = $viewData;
            $this->params         = $params;
            $this->uniqueLayoutId = $uniqueLayoutId;
            $this->uniquePageId   = get_called_class();
        }

        public function getPortletParams()
        {
            return array();
        }

        public function renderPortletHeadContent()
        {
            return null;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type'            => 'MarketingListAddSubscriberLink',
                                'htmlOptions'       => array('class' => 'icon-edit'),
                                'pageVarName'       => 'eval:$this->getPageVarName()',
                                'listViewGridId'    => 'eval:$this->getListGridId()'),
                            array('type'            => 'MarketingListMembersSubscribeLink',
                                'htmlOptions'       => array('class' => 'icon-edit'),
                                'controllerId'      => 'eval:$this->getMassActionsControllerId()',
                                'pageVarName'       => 'eval:$this->getPageVarName()',
                                'listViewGridId'    => 'eval:$this->getListGridId()'),
                            array('type'            => 'MarketingListMembersUnsubscribeLink',
                                'htmlOptions'       => array('class' => 'icon-edit'),
                                'controllerId'      => 'eval:$this->getMassActionsControllerId()',
                                'pageVarName'       => 'eval:$this->getPageVarName()',
                                'listViewGridId'    => 'eval:$this->getListGridId()'),
                            array('type'            => 'MassDeleteLink',
                                'htmlOptions'       => array('class' => 'icon-delete'),
                                'controllerId'      => 'eval:$this->getMassActionsControllerId()',
                                'pageVarName'       => 'eval:$this->getPageVarName()',
                                'listViewGridId'    => 'eval:$this->getListGridId()'),
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        public function getTitle()
        {
            return Zurmo::t('LeadsModule', 'ContactsModulePluralLabel/LeadsModulePluralLabel',
                                                                LabelUtil::getTranslationParamsForAllModules());
        }

        public function renderContent()
        {
            if ($this->shouldRenderViewToolBar())
            {
                $actionElementBar = $this->renderViewToolBar();
            }
            elseif ($this->shouldRenderActionElementBar())
            {
                $actionElementBar = ZurmoHtml::tag('div', array('class' => 'portlet-view-toolbar view-toolbar'),
                                                                                $this->renderActionElementBar(false));
            }
            else
            {
                $actionElementBar = null;
            }
            $content = null;
            if ($actionElementBar != null)
            {
                $content .= $actionElementBar;
            }
            $content .= $this->renderSearchFormAndListContent();
            return ZurmoHtml::tag('div', array('class' => $this->getWrapperDivClass()), $content);
        }

        protected function shouldRenderViewToolBar()
        {
            return false;
        }

        protected function shouldRenderActionElementBar()
        {
            return true;
        }

        public static function canUserConfigure()
        {
            return false;
        }

        /**
         * What kind of PortletRules this view follows
         * @return PortletRulesType as string.
         */
        public static function getPortletRulesType()
        {
            return 'ModelDetails';
        }

        /**
         * The view's module class name.
         */
        public static function getModuleClassName()
        {
            return 'MarketingListsModule';
        }

        /**
         * After a portlet action is completed, the portlet must be refreshed. This is the url to correctly
         * refresh the portlet content.
         */
        protected function getPortletDetailsUrl()
        {
            return Yii::app()->createUrl('/' . $this->moduleId . '/defaultPortlet/details',
                                                array_merge($_GET, array( 'portletId' => $this->getPortletId(),
                                                                            'uniqueLayoutId' => $this->uniqueLayoutId)));
        }

        protected function getPortletId()
        {
            return ArrayUtil::getArrayValueWithExceptionIfNotFound($this->params, 'portletId');
        }

        /**
         * Url to go to after an action is completed. Typically returns user to either a model's detail view or
         * the home page dashboard.
         */
        protected function getNonAjaxRedirectUrl()
        {
            $redirectUrl = ArrayUtil::getArrayValue($this->params, 'redirectUrl');
            if ($redirectUrl && strpos($redirectUrl, 'defaultPortlet') === false)
            {
                return $redirectUrl;
            }
            else
            {
                return Yii::app()->createUrl('/' . $this->moduleId . '/' . $this->controllerId . '/details',
                                                                                    array( 'id' => $this->modelId));
            }
        }

        protected function renderSearchFormAndListContent()
        {
            $listContent = $this->getListView()->render();
            return ZurmoHtml::tag('div', array('class' => $this->getListContentWrapperDivClass()), $listContent);
        }

        protected function makeListView()
        {
            $listViewClassName = $this->getListViewClassName();
            $this->getDataProvider(); // no need to save return value as we don't need it.
            return new $listViewClassName(
                                                            $this->dataProvider,
                                                            $this->configurationForm,
                                                            $this->controllerId,
                                                            $this->moduleId,
                                                            $this->getPortletDetailsUrl(),
                                                            $this->getNonAjaxRedirectUrl(),
                                                            $this->uniquePageId,
                                                            $this->params,
                                                            get_class(Yii::app()->findModule($this->moduleId))
                                                        );
        }

        protected function getListView()
        {
            if ($this->listView === null)
            {
                $this->listView = $this->makeListView();
            }
            return $this->listView;
        }

        protected function resolveConfigFormFromRequest()
        {
            $excludeFromRestore = array();
            if (isset($_GET[get_class($this->configurationForm)]))
            {
                $this->configurationForm->setAttributes($_GET[get_class($this->configurationForm)]);
                $excludeFromRestore = $this->saveUserSettingsFromConfigForm();
            }
            $this->restoreUserSettingsToConfigFrom($excludeFromRestore);
        }

        protected function saveUserSettingsFromConfigForm()
        {
            $savedConfigs   = array();
            $configUtil     = $this->getConfigUtilClassName();
            foreach (static::$persistentUserPortletConfigs as $persistentUserConfigItem)
            {
                if ($this->configurationForm->$persistentUserConfigItem !==
                    $configUtil::getForCurrentUserByPortletIdAndKey( $this->getPortletId(), $persistentUserConfigItem))
                {
                    $configUtil::setForCurrentUserByPortletIdAndKey( $this->getPortletId(), $persistentUserConfigItem,
                                                        $this->configurationForm->$persistentUserConfigItem);
                    $savedConfigs[] = $persistentUserConfigItem;
                }
            }
            return $savedConfigs;
        }

        protected function restoreUserSettingsToConfigFrom($excludeFromRestore)
        {
            foreach (static::$persistentUserPortletConfigs as $persistentUserConfigItem)
            {
                if (in_array($persistentUserConfigItem, $excludeFromRestore))
                {
                    continue;
                }
                else
                {
                    $configUtil                     = $this->getConfigUtilClassName();
                    $persistentUserConfigItemValue  = $configUtil::getForCurrentUserByPortletIdAndKey(
                                                                                            $this->getPortletId(),
                                                                                            $persistentUserConfigItem
                                                                                            );
                    if (isset($persistentUserConfigItemValue))
                    {
                        $this->configurationForm->$persistentUserConfigItem = $persistentUserConfigItemValue;
                    }
                }
            }
        }

        protected function makeConfigurationForm()
        {
            $this->configurationForm = null;
            $configFormClass         = $this->getConfigurationFormClassName();
            if ($configFormClass)
            {
                $this->configurationForm = new $configFormClass();
                $this->resolveConfigFormFromRequest();
            }
        }

        protected function getConfigurationFormClassName()
        {
            return 'MarketingListMembersConfigurationForm';
        }

        protected function getConfigurationForm()
        {
            if ($this->configurationForm === null)
            {
                $this->makeConfigurationForm();
            }
            return $this->configurationForm;
        }

        protected function getDataProvider()
        {
            if ($this->dataProvider === null)
            {
                $this->dataProvider = $this->makeDataProvider();
            }
            return $this->dataProvider;
        }

        protected function getPageVarName()
        {
            return $this->getDataProvider()->getPagination()->pageVar;
        }

        protected function getListViewClassName()
        {
            return 'MarketingListMembersListView';
        }

        protected function getListGridId()
        {
            return $this->getListView()->getGridViewId();
        }

        protected function getWrapperDivClass()
        {
            return MarketingListDetailsAndRelationsView::MEMBERS_PORTLET_CLASS;
        }

        protected function getListContentWrapperDivClass()
        {
            return 'marketing-list-members-list';
        }

        protected function getConfigUtilClassName()
        {
            return 'MarketingListMembersPortletPersistentConfigUtil';
        }

        protected function getMassActionsControllerId()
        {
            return 'member';
        }

        protected function makeDataProvider()
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType('subListPageSize');
            $searchAttributes   = $this->getSearchAttributes();
            $sortAttributes     = $this->getSortAttributes();
            $sortDescending     = $this->getIsSortDescending();
            return new RedBeanModelsDataProvider($this->uniquePageId,
                                                    $sortAttributes,
                                                    $sortDescending,
                                                    $searchAttributes,
                                                    array('pagination' => array('pageSize' => $pageSize))
                                                );
        }

        protected function getSearchAttributes()
        {
            $form = $this->getConfigurationForm();
            return  MarketingListMembersUtil::makeSearchAttributeData($this->modelId,
                                                                        $form->filteredBySubscriptionType,
                                                                        $form->filteredBySearchTerm);
        }

        protected function getSortAttributes()
        {
            return MarketingListMembersUtil::makeSortAttributeData();
        }

        protected function getIsSortDescending()
        {
            return MarketingListMembersUtil::getIsSortDescending();
        }
    }
?>