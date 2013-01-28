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
     * Base class used for wrapping a view of social items
     */
    abstract class SocialItemsForPortletView extends ConfigurableMetadataView
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

        /**
         * Some extra assertions are made to ensure this view is used in a way that it supports.
         */
        public function __construct($viewData, $params, $uniqueLayoutId)
        {
            assert('is_array($viewData) || $viewData == null');
            assert('isset($params["relationModuleId"]) && $params["relationModuleId"] == "users"');
            assert('isset($params["relationModel"]) && get_class($params["relationModel"]) == "User"');
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
                    'title' => "eval:Zurmo::t('SocialItemsModule', 'Social Items')",
                ),
            );
            return $metadata;
        }

        public function getTitle()
        {
            $title  = Zurmo::t('SocialItemsModule', 'What\'s going on?');
            return $title;
        }

        public function renderContent()
        {
            $content  = $this->renderActionContent();
            $content .= $this->renderNewSocialItemContent();
            $content .= $this->renderSocialItemsContent();
            return $content;
        }

        /**
         * If this is an ajax call, it is most likely a paging request which means we should not show the view for
         * posting a new social item.
         */
        protected function renderNewSocialItemContent()
        {
            if (ArrayUtil::getArrayValue(GetUtil::getData(), 'ajax') != null)
            {
                return;
            }
            $socialItem = new  SocialItem();
            $urlParameters = array('relatedUserId'            => $this->params['relationModel']->id,
                                   'redirectUrl'              => $this->getPortletDetailsUrl()); //After save, the url to go to.
            $uniquePageId  = get_called_class();
            $inlineView    = new SocialItemInlineEditView($socialItem, 'default', 'socialItems', 'inlineCreateSave',
                                                      $urlParameters, $uniquePageId);
            return $inlineView->render();
        }

        protected function renderSocialItemsContent()
        {
            $uniquePageId  = get_called_class();
            $dataProvider  = $this->getDataProvider($uniquePageId);
            $view          = new SocialItemsListView($dataProvider, 'default', 'socialItems',
                                                      $this->resolveAndGetPaginationRoute(),
                                                      $this->resolveAndGetPaginationParams(),
                                                      $this->getNonAjaxRedirectUrl(),
                                                      $uniquePageId,
                                                      $this->params);
            return $view->render();
        }

        protected function resolveAndGetPaginationRoute()
        {
            return 'defaultPortlet/myListDetails';
        }

        protected function resolveAndGetPaginationParams()
        {
            return array_merge(GetUtil::getData(), array('portletId' => $this->params['portletId']));
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
            return 'SocialItemsModule';
        }

        protected function renderActionContent()
        {
            $actionElementContent = $this->renderActionElementMenu(Zurmo::t('SocialItemsModule', 'Create'));
            $content              = null;
            if ($actionElementContent != null)
            {
                $content .= '<div class="view-toolbar-container toolbar-mbmenu clearfix"><div class="view-toolbar">';
                $content .= $actionElementContent;
                $content .= '</div></div>';
            }
            return $content;
        }
    }
?>