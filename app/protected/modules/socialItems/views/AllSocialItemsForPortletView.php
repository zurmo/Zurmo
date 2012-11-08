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
     * Wrapper view for displaying a feed of all social items on a dashboard.
     */
    class AllSocialItemsForPortletView extends SocialItemsForPortletView
    {
        /**
         * Some extra assertions are made to ensure this view is used in a way that it supports.
         */
        public function __construct($viewData, $params, $uniqueLayoutId)
        {
            assert('is_array($viewData) || $viewData == null');
            assert('isset($params["portletId"])');
            assert('is_string($uniqueLayoutId)');
            $this->moduleId       = 'home';
            $this->viewData       = $viewData;
            $this->params         = $params;
            $this->uniqueLayoutId = $uniqueLayoutId;
        }

        protected function renderNewSocialItemContent()
        {
            if (ArrayUtil::getArrayValue(GetUtil::getData(), 'ajax') != null)
            {
                return;
            }
            $socialItem    = new  SocialItem();
            $urlParameters = array('redirectUrl'              => $this->getPortletDetailsUrl()); //After save, the url to go to.
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
                                                      $this->params,
                                                      true);
            return $view->render();
        }

        /**
         * Override to properly use myListDetails instead of just details as the action.
         * (non-PHPdoc)
         * @see SocialItemsForPortletView::getPortletDetailsUrl()
         */
        protected function getPortletDetailsUrl()
        {
            return Yii::app()->createUrl('/' . $this->moduleId . '/defaultPortlet/myListDetails',
                                                        array_merge(GetUtil::getData(), array( 'portletId' =>
                                                                                    $this->params['portletId'],
                                                            'uniqueLayoutId' => $this->uniqueLayoutId)));
        }

       /**
         * Url to go to after an action is completed. Typically returns user to either a model's detail view or
         * the home page dashboard.
         */
        protected function getNonAjaxRedirectUrl()
        {
            return Yii::app()->createUrl('/' . $this->moduleId . '/default/index');
        }

        protected function getDataProvider()
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType('subListPageSize');
            $searchAttributeData              = array();
            $searchAttributeData['clauses']   = array();
            $searchAttributeData['structure'] = '';
            return new RedBeanModelDataProvider('SocialItem', 'latestDateTime', true, $searchAttributeData,
                                                   array(
                                                        'pagination' => array(
                                                            'pageSize' => $pageSize,
                                                        )
                                                    ));
        }

        /**
         * What kind of PortletRules this view follows
         * @return PortletRulesType as string.
         */
        public static function getPortletRulesType()
        {
            return 'AllSocialItemsList';
        }
    }
?>