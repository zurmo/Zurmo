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
     * Base class used for wrapping a latest activity view.
     */
    abstract class LatestActivtiesForPortletView extends ConfigurableMetadataView
                                                                  implements PortletViewInterface
    {
        /**
         * Portlet parameters passed in from the portlet.
         * @var array
         */
        protected $params;

        protected $controllerId;

        protected $moduleId;

        protected $model;

        protected $uniqueLayoutId;

        protected $viewData;

        abstract protected function getLatestActivitiesViewClassName();

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
            $this->viewData       = $viewData;
            $this->params         = $params;
            $this->uniqueLayoutId = $uniqueLayoutId;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'perUser' => array(
                    'title' => "eval:Zurmo::t('ActivitiesModule', 'Latest Activities')",
                ),
            );
            return $metadata;
        }

        public function getTitle()
        {
            $title  = Zurmo::t('ActivitiesModule', 'Latest Activities');
            return $title;
        }

        public function renderContent()
        {
            $content  = $this->renderActionContent();
            $content .= $this->renderLatestActivitiesContent();
            return $content;
        }

        protected function renderLatestActivitiesContent()
        {
            $mashableModelClassNamesAndDisplayLabels = LatestActivitiesUtil::
                                                            getMashableModelDataForCurrentUser(
                                                            static::includeHavingRelatedItemsWhenRenderingMashableModels());
            if (count($mashableModelClassNamesAndDisplayLabels) > 0)
            {
                $uniquePageId  = get_called_class();
                $latestActivitiesConfigurationForm = $this->makeLatestActivitiesConfigurationForm();
                $latestActivitiesConfigurationForm->mashableModelClassNamesAndDisplayLabels =
                    $mashableModelClassNamesAndDisplayLabels;
                if (isset($_GET[get_class($latestActivitiesConfigurationForm)]))
                {
                    $latestActivitiesConfigurationForm->setAttributes($_GET[get_class($latestActivitiesConfigurationForm)]);
                }
                $latestActivitiesViewClassName = $this->getLatestActivitiesViewClassName();
                $dataProvider = $this->getDataProvider($uniquePageId, $latestActivitiesConfigurationForm);
                $latestView = new $latestActivitiesViewClassName($dataProvider,
                                                                 $latestActivitiesConfigurationForm,
                                                                 'default', 'activities',
                                                                 $this->getPortletDetailsUrl(),
                                                                 $this->getNonAjaxRedirectUrl(),
                                                                 $uniquePageId,
                                                                 $this->params,
                                                                 get_class(Yii::app()->findModule($this->moduleId)));
                return $latestView->render();
            }
        }

        protected static function includeHavingRelatedItemsWhenRenderingMashableModels()
        {
            return false;
        }

        protected function makeLatestActivitiesConfigurationForm()
        {
            return new LatestActivitiesConfigurationForm();
        }

        /**
         * After a portlet action is completed, the portlet must be refreshed. This is the url to correctly
         * refresh the portlet content.
         */
        protected function getPortletDetailsUrl()
        {
            return Yii::app()->createUrl('/' . $this->moduleId . '/defaultPortlet/details',
                                                        array_merge($_GET, array( 'portletId' =>
                                                                                    $this->params['portletId'],
                                                            'uniqueLayoutId' => $this->uniqueLayoutId)));
        }

        /**
         * Url to go to after an action is completed. Typically returns user to either a model's detail view or
         * the home page dashboard.
         */
        protected function getNonAjaxRedirectUrl()
        {
            return Yii::app()->createUrl('/' . $this->moduleId . '/default/details',
                                                        array( 'id' => $this->params['relationModel']->id));
        }

        protected function getDataProvider($uniquePageId, $form)
        {
            assert('is_string($uniquePageId)');
            assert('$form instanceOf LatestActivitiesConfigurationForm');
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType('subListPageSize');
            $filteredMashableModelClassNames = LatestActivitiesUtil::resolveMashableModelClassNamesByFilteredBy(
                                                    array_keys($form->mashableModelClassNamesAndDisplayLabels),
                                                    $form->filteredByModelName);
            $relationItemId = (int)$this->params['relationModel']->getClassId('Item');
            if ($form->rollup)
            {
                $relationItemsIds = ModelRollUpUtil::getItemIdsByModelAndUser($this->params['relationModel'],
                                                                              Yii::app()->user->userModel);
            }
            else
            {
                $relationItemsIds = array($relationItemId);
            }
            $modelClassNamesAndSearchAttributeData = // Not Coding Standard
                LatestActivitiesUtil::
                    getSearchAttributesDataByModelClassNamesAndRelatedItemIds($filteredMashableModelClassNames,
                                                                              $relationItemsIds, $form->ownedByFilter);
            $modelClassNamesAndSortAttributes =      // Not Coding Standard
                LatestActivitiesUtil::getSortAttributesByMashableModelClassNames($filteredMashableModelClassNames);
            return new RedBeanModelsDataProvider($uniquePageId, $modelClassNamesAndSortAttributes,
                                                          true, $modelClassNamesAndSearchAttributeData,
                                                          array('pagination' => array('pageSize' => $pageSize)));
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
            return 'ActivitiesModule';
        }

        protected function renderActionContent()
        {
            $actionElementContent = $this->renderActionElementMenu(Zurmo::t('ActivitiesModule', 'Create'));
            $content              = null;
            if ($actionElementContent != null)
            {
                $content .= '<div class="view-toolbar-container toolbar-mbmenu clearfix"><div class="view-toolbar">';
                $content .= $actionElementContent;
                $content .= '</div></div>';
            }
            return $content;
        }

        /**
         * Expects there to be a modelClassName parameter passed through the elementInformation that can be used
         * to compare the Rights security on based on the actionType of the element.
         * (non-PHPdoc)
         * @see MetadataView::shouldRenderToolBarElement()
         */
        protected function shouldRenderToolBarElement($element, $elementInformation)
        {
            assert('$element instanceof ActionElement');
            assert('is_array($elementInformation)');
            if (!parent::shouldRenderToolBarElement($element, $elementInformation))
            {
                return false;
            }
            if (isset($elementInformation['modelClassName']))
            {
                $modelClassName = $elementInformation['modelClassName'];
                //Todo: figure out how to not need to new up a new model.
                return ActionSecurityUtil::canCurrentUserPerformAction( $element->getActionType(),
                                                                        new $modelClassName(false));
            }
            return true;
        }
    }
?>