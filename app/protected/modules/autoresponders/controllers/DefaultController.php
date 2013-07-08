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

    class AutorespondersDefaultController extends ZurmoModuleController
    {
        const USER_MARKETING_LIST_ACCESS_FILTER_PATH =
            'application.modules.autoresponders.controllers.filters.UserCanAccessMarketingListControllerFilter';

        const JOBS_CHECK_FILTER_PATH =
            'application.modules.autoresponders.controllers.filters.AutoresponderJobsCheckControllerFilter';

        public function filters()
        {
            return array_merge(parent::filters(),
                array(
                    array(
                        static::USER_MARKETING_LIST_ACCESS_FILTER_PATH . ' + create, details, edit, delete',
                        'controller' => $this,
                    ),
                    array(
                        static::JOBS_CHECK_FILTER_PATH . ' + create, details, edit',
                    ),
                )
            );
        }

        public static function getDetailsAndEditBreadcrumbLinks($marketingList)
        {
            $listsTitle                         = Zurmo::t('MarketingListsModule', 'Lists');
            $marketingListTitle                 = StringUtil::getChoppedStringContent(strval($marketingList), 25);
            $breadcrumbs                        = array();
            $breadcrumbs[$listsTitle]           = array('/marketingLists/default/list');
            $breadcrumbs[$marketingListTitle]   = array('/marketingLists/default/details', 'id' => $marketingList->id);
            return $breadcrumbs;
        }

        public function actionCreate($marketingListId, $redirectUrl)
        {
            $autoresponder                  = new Autoresponder();
            $autoresponder->marketingList   = MarketingList::getById(intval($marketingListId));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($autoresponder->marketingList);
            $model                          = $this->attemptToSaveModelFromPost($autoresponder, $redirectUrl);
            $editAndDetailsView             = $this->makeEditAndDetailsView($model, 'Edit');
            $breadcrumbLinks                = static::getDetailsAndEditBreadcrumbLinks($autoresponder->marketingList);
            $breadcrumbLinks[]              = Zurmo::t('AutorespondersModule', 'Create');
            $view                           = new AutorespondersPageView(MarketingDefaultViewUtil::
                                                        makeViewWithBreadcrumbsForCurrentUser($this, $editAndDetailsView,
                                                        $breadcrumbLinks, 'MarketingBreadCrumbView'));
            echo $view->render();
        }

        public function actionDetails($id, $redirectUrl)
        {
            $autoresponder      = static::getModelAndCatchNotFoundAndDisplayError('Autoresponder', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($autoresponder->marketingList);
            $breadcrumbLinks    = static::getDetailsAndEditBreadcrumbLinks($autoresponder->marketingList);
            $breadcrumbLinks[]  = StringUtil::getChoppedStringContent(strval($autoresponder), 25);
            $detailsView        = new AutoresponderEditAndDetailsView('Details', $this->getId(),
                                                                        $this->getModule()->getId(), $autoresponder);
            $view               = new AutorespondersPageView(MarketingDefaultViewUtil::
                                                            makeViewWithBreadcrumbsForCurrentUser($this, $detailsView,
                                                            $breadcrumbLinks, 'MarketingBreadCrumbView'));
            echo $view->render();
        }

        public function actionEdit($id, $redirectUrl)
        {
            $autoresponder      = Autoresponder::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($autoresponder->marketingList);
            $model              = $this->attemptToSaveModelFromPost($autoresponder, $redirectUrl);
            $editAndDetailsView = $this->makeEditAndDetailsView($model, 'Edit');
            $breadcrumbLinks    = static::getDetailsAndEditBreadcrumbLinks($autoresponder->marketingList);
            $breadcrumbLinks[]  = StringUtil::getChoppedStringContent(strval($autoresponder), 25);
            $view               = new AutorespondersPageView(MarketingDefaultViewUtil::
                                                    makeViewWithBreadcrumbsForCurrentUser($this, $editAndDetailsView,
                                                    $breadcrumbLinks, 'MarketingBreadCrumbView'));
            echo $view->render();
        }

        public function actionDelete($id, $redirectUrl = null)
        {
            $autoresponder = Autoresponder::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($autoresponder->marketingList);
            $autoresponder->delete();
            if ($redirectUrl)
            {
                $this->redirect($redirectUrl);
            }
        }

        protected static function getZurmoControllerUtil()
        {
            return new AutoresponderZurmoControllerUtil();
        }
    }
?>