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

    class MarketingListsDefaultController extends ZurmoModuleController
    {
        const ZERO_MODELS_CHECK_FILTER_PATH =
            'application.modules.marketingLists.controllers.filters.MarketingListsZeroModelsCheckControllerFilter';

        public static function getListBreadcrumbLinks()
        {
            $title = Zurmo::t('MarketingListsModule', 'Lists');
            return array($title);
        }

        public static function getDetailsAndEditBreadcrumbLinks()
        {
            return array(Zurmo::t('MarketingListsModule', 'Lists') => array('default/list'));
        }

        public function filters()
        {
            return array_merge(parent::filters(),
                array(
                    array(
                        static::ZERO_MODELS_CHECK_FILTER_PATH . ' + list',
                        'controller'                    => $this,
                        'activeActionElementType'       => 'MarketingListsLink',
                        'breadcrumbLinks'               => static::getListBreadcrumbLinks(),
                    ),
                )
            );
        }

        public function actionIndex()
        {
            $this->actionList();
        }

        public function actionList()
        {
            $pageSize                       = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                                                        'listPageSize', get_class($this->getModule()));
            $marketingList                  = new MarketingList(false);
            $searchForm                     = new MarketingListsSearchForm($marketingList);
            $listAttributesSelector         = new ListAttributesSelector('MarketingListsListView',
                                                                                get_class($this->getModule()));
            $searchForm->setListAttributesSelector($listAttributesSelector);
            $dataProvider = $this->resolveSearchDataProvider(
                $searchForm,
                $pageSize,
                null,
                'MarketingListsSearchView'
            );
            if (isset($_GET['ajax']) && $_GET['ajax'] == 'list-view')
            {
                $mixedView = $this->makeListView(
                    $searchForm,
                    $dataProvider
                );
                $view = new MarketingListsPageView($mixedView);
            }
            else
            {
                $mixedView = $this->makeActionBarSearchAndListView($searchForm, $dataProvider,
                             'SecuredActionBarForMarketingSearchAndListView', null, 'MarketingListsLink');
                $breadcrumbLinks = static::getListBreadcrumbLinks();
                $view      = new MarketingListsPageView(MarketingDefaultViewUtil::
                                 makeViewWithBreadcrumbsForCurrentUser($this, $mixedView, $breadcrumbLinks,
                                 'MarketingBreadCrumbView'));
            }
            echo $view->render();
        }

        public function actionCreate()
        {
           $breadcrumbLinks    = static::getDetailsAndEditBreadcrumbLinks();
           $breadcrumbLinks[]  = Zurmo::t('MarketingListsModule', 'Create');
           $editView = new MarketingListEditView($this->getId(), $this->getModule()->getId(),
                                                 $this->attemptToSaveModelFromPost(new MarketingList()),
                                                 Zurmo::t('Default', 'Create Marketing List'));
            $view               = new MarketingListsPageView(MarketingDefaultViewUtil::
                                  makeViewWithBreadcrumbsForCurrentUser($this, $editView,
                                  $breadcrumbLinks, 'MarketingBreadCrumbView'));
            echo $view->render();
        }

        public function actionDetails($id)
        {
            $marketingList = static::getModelAndCatchNotFoundAndDisplayError('MarketingList', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($marketingList);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED,
                                            array(strval($marketingList), 'MarketingListsModule'), $marketingList);
            $breadCrumbView             = MarketingListsStickySearchUtil::
                                          resolveBreadCrumbViewForDetailsControllerAction($this,
                                          'MarketingListsSearchView', $marketingList);
            $detailsAndRelationsView    = $this->makeDetailsAndRelationsView($marketingList, 'MarketingListsModule',
                                                                                'MarketingListDetailsAndRelationsView',
                                                                                Yii::app()->request->getRequestUri(),
                                                                                $breadCrumbView);
            $view                       = new MarketingListsPageView(MarketingDefaultViewUtil::
                                                makeStandardViewForCurrentUser($this, $detailsAndRelationsView));
            echo $view->render();
        }

        public function actionEdit($id)
        {
            $marketingList = MarketingList::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($marketingList);
            $breadcrumbLinks    = static::getDetailsAndEditBreadcrumbLinks();
            $breadcrumbLinks[]  = StringUtil::getChoppedStringContent(strval($marketingList), 25);
            $editView = new MarketingListEditView($this->getId(), $this->getModule()->getId(),
                                                 $this->attemptToSaveModelFromPost($marketingList),
                                                 strval($marketingList));
            $view               = new MarketingListsPageView(MarketingDefaultViewUtil::
                                  makeViewWithBreadcrumbsForCurrentUser($this, $editView,
                                  $breadcrumbLinks, 'MarketingBreadCrumbView'));
            echo $view->render();
        }

        public function actionDelete($id)
        {
            $marketingList = static::getModelAndCatchNotFoundAndDisplayError('MarketingList', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($marketingList);
            $marketingList->delete();
            $this->redirect(array($this->getId() . '/index'));
        }

        public function actionModalList()
        {
            $modalListLinkProvider = new SelectFromRelatedEditModalListLinkProvider(
                                            $_GET['modalTransferInformation']['sourceIdFieldId'],
                                            $_GET['modalTransferInformation']['sourceNameFieldId']
            );
            echo ModalSearchListControllerUtil::setAjaxModeAndRenderModalSearchList($this, $modalListLinkProvider,
                                                'ContactsStateMetadataAdapter');
        }

        protected static function getSearchFormClassName()
        {
            return 'MarketingListsSearchForm';
        }
  }
?>