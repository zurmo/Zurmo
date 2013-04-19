<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class MarketingListMembersForPortletView extends ConfigurableMetadataView
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

        protected $marketingListMembersListView;

        protected $dataProvider;

        protected $uniquePageId;

        protected $marketingListMembersConfigurationForm;

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

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type'  => 'MarketingListAddSubscriberLink',
                                'htmlOptions'       => array('class' => 'icon-edit'),
                                'pageVarName'       => 'eval:$this->getPageVarName()',
                                'listViewGridId'    => 'eval:$this->getMarketingListMembersListGridId()'),
                            array('type'  => 'MarketingListMembersSubscribeLink',
                                'htmlOptions'       => array('class' => 'icon-edit'),
                                'controllerId'      => 'member',
                                'pageVarName'       => 'eval:$this->getPageVarName()',
                                'listViewGridId'    => 'eval:$this->getMarketingListMembersListGridId()'),
                            array('type'  => 'MarketingListMembersUnsubscribeLink',
                                'htmlOptions'       => array('class' => 'icon-edit'),
                                'controllerId'      => 'member',
                                'pageVarName'       => 'eval:$this->getPageVarName()',
                                'listViewGridId'    => 'eval:$this->getMarketingListMembersListGridId()'),
                            array('type'            => 'MassDeleteLink',
                                'htmlOptions'       => array('class' => 'icon-delete'),
                                'controllerId'      => 'member',
                                'pageVarName'       => 'eval:$this->getPageVarName()',
                                'listViewGridId'    => 'eval:$this->getMarketingListMembersListGridId()'),
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        public function getTitle()
        {
            return Zurmo::t('MarketingListsModule', 'Members');
        }

        public function renderContent()
        {
            $actionElementBar       = ZurmoHtml::tag('div', array('class' => 'portlet-view-toolbar view-toolbar'),
                                                                                $this->renderActionElementBar(false));
            $memberSearchAndList    = $this->renderMembersSearchFormAndListContent();
            $content = ZurmoHtml::tag('div', array('class' => MarketingListDetailsAndRelationsView::MEMBERS_PORTLET_CLASS),
                                        $actionElementBar);
            $content .= $memberSearchAndList;
            return $content;
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
            if ($redirectUrl)
            {
                return $redirectUrl;
            }
            else
            {
            return Yii::app()->createUrl('/' . $this->moduleId . '/' . $this->controllerId . '/details',
                                                                                    array( 'id' => $this->modelId));
            }
        }

        protected function renderMembersSearchFormAndListContent()
        {
            $marketingListMembersListContent = $this->getMarketingListMembersListView()->render();
            return ZurmoHtml::tag('div', array('class' => 'marketing-list-members-list'), $marketingListMembersListContent);
        }

        protected function makeMarketingListMembersListView()
        {
            $marketingListMembersListViewClassName = $this->getMarketingListMembersListViewClassName();
            $this->getDataProvider(); // no need to save return value as we don't need it.
            return new $marketingListMembersListViewClassName(
                                                            $this->dataProvider,
                                                            $this->marketingListMembersConfigurationForm,
                                                            $this->controllerId,
                                                            $this->moduleId,
                                                            $this->getPortletDetailsUrl(),
                                                            $this->getNonAjaxRedirectUrl(),
                                                            $this->uniquePageId,
                                                            $this->params,
                                                            get_class(Yii::app()->findModule($this->moduleId))
                                                        );
        }

        protected function getMarketingListMembersListView()
        {
            if ($this->marketingListMembersListView === null)
            {
                $this->marketingListMembersListView = $this->makeMarketingListMembersListView();
            }
            return $this->marketingListMembersListView;
        }

        protected function resolveMarketingListMembersConfigFormFromRequest()
        {
            $excludeFromRestore = array();
            if (isset($_GET[get_class($this->marketingListMembersConfigurationForm)]))
            {
                $this->marketingListMembersConfigurationForm->setAttributes($_GET[get_class($this->marketingListMembersConfigurationForm)]);
                $excludeFromRestore = $this->saveUserSettingsFromConfigForm();
            }
            $this->restoreUserSettingsToConfigFrom($excludeFromRestore);
        }

        protected function saveUserSettingsFromConfigForm()
        {
            $savedConfigs = array();
            foreach (static::$persistentUserPortletConfigs as $persistentUserConfigItem)
            {
                if ($this->marketingListMembersConfigurationForm->$persistentUserConfigItem !==
                    MarketingListMembersPortletPersistentConfigUtil::getForCurrentUserByPortletIdAndKey(
                                                                                            $this->getPortletId(),
                                                                                            $persistentUserConfigItem)
                                                                                        )
                {
                    MarketingListMembersPortletPersistentConfigUtil::setForCurrentUserByPortletIdAndKey(
                                                        $this->getPortletId(),
                                                        $persistentUserConfigItem,
                                                        $this->marketingListMembersConfigurationForm->$persistentUserConfigItem
                                                        );
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
                    $persistentUserConfigItemValue = MarketingListMembersPortletPersistentConfigUtil::getForCurrentUserByPortletIdAndKey(
                                                                                            $this->getPortletId(),
                                                                                            $persistentUserConfigItem
                                                                                            );
                    if (isset($persistentUserConfigItemValue))
                    {
                        $this->marketingListMembersConfigurationForm->$persistentUserConfigItem = $persistentUserConfigItemValue;
                    }
                }
            }
        }

        protected function makeMarketingListMembersConfigurationForm()
        {
            $this->marketingListMembersConfigurationForm = new MarketingListMembersConfigurationForm();
            $this->resolveMarketingListMembersConfigFormFromRequest();
        }

        protected function getMarketingListMembersConfigurationForm()
        {
            if ($this->marketingListMembersConfigurationForm === null)
            {
                $this->makeMarketingListMembersConfigurationForm();
            }
            return $this->marketingListMembersConfigurationForm;
        }

        protected function getDataProvider()
        {
            if ($this->dataProvider === null)
            {
                $this->dataProvider = $this->makeDataProvider($this->uniquePageId, $this->getMarketingListMembersConfigurationForm());
            }
            return $this->dataProvider;
        }

        protected function getPageVarName()
        {
            return $this->getDataProvider()->getPagination()->pageVar;
        }

        protected function getMarketingListMembersListViewClassName()
        {
            return 'MarketingListMembersListView';
        }

        protected function getMarketingListMembersListGridId()
        {
            return $this->getMarketingListMembersListView()->getGridViewId();
        }

        protected function makeDataProvider($uniquePageId, $form)
        {
            assert('is_string($uniquePageId)');
            assert('$form instanceOf MarketingListMembersConfigurationForm');
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType('subListPageSize');
            $searchAttributes   = MarketingListMembersUtil::makeSearchAttributeData($this->modelId,
                                                                                $form->filteredBySubscriptionType,
                                                                                $form->filteredBySearchTerm);
            $sortAttributes     = MarketingListMembersUtil::makeSortAttributeData();
            return new RedBeanModelsDataProvider($uniquePageId,
                                                    $sortAttributes,
                                                    true,
                                                    $searchAttributes,
                                                    array('pagination' => array('pageSize' => $pageSize))
                                                );
        }
    }
?>