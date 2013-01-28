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

    class HomeDefaultController extends ZurmoBaseController
    {
        public function filters()
        {
            return array_merge(parent::filters(),
                array(
                    array(
                        ZurmoBaseController::RIGHTS_FILTER_PATH . ' - index, welcome, hideWelcome, getTip',
                        'moduleClassName' => 'HomeModule',
                        'rightName' => HomeModule::RIGHT_ACCESS_DASHBOARDS,
                   ),
                    array(
                        ZurmoBaseController::RIGHTS_FILTER_PATH . ' + createDashboard',
                        'moduleClassName' => 'HomeModule',
                        'rightName' => HomeModule::RIGHT_CREATE_DASHBOARDS,
                   ),
                    array(
                        ZurmoBaseController::RIGHTS_FILTER_PATH . ' + deleteDashboard',
                        'moduleClassName' => 'HomeModule',
                        'rightName' => HomeModule::RIGHT_DELETE_DASHBOARDS,
                   ),
               )
            );
        }

        public function actionIndex()
        {
            if (RightsUtil::doesUserHaveAllowByRightName(
                'HomeModule',
                HomeModule::RIGHT_ACCESS_DASHBOARDS,
                Yii::app()->user->userModel))
            {
                $this->actionDashboardDetails(-1);
            }
            else
            {
                $this->actionWelcome();
            }
        }

        public function actionWelcome()
        {
            $hasDashboardAccess = true;
            if (!RightsUtil::doesUserHaveAllowByRightName(
            'HomeModule',
            HomeModule::RIGHT_ACCESS_DASHBOARDS,
            Yii::app()->user->userModel))
            {
                $hasDashboardAccess = false;
            }
            if (UserConfigurationFormAdapter::resolveAndGetHideWelcomeViewValue(Yii::app()->user->userModel))
            {
                //If you can see dashboards, then go there, otherwise stay here since the user has limited access.
                if ($hasDashboardAccess)
                {
                    $this->redirect(array($this->getId() . '/index'));
                }
            }
            $tipContent                = ZurmoTipsUtil::getRandomTipResolvedForCurrentUser();
            $welcomeView               = new WelcomeView($tipContent, $hasDashboardAccess);
            $view                      = new HomePageView(ZurmoDefaultViewUtil::
                                             makeStandardViewForCurrentUser($this, $welcomeView));
            echo $view->render();
        }

        public function actionHideWelcome()
        {
            $configurationForm = UserConfigurationFormAdapter::
                                 makeFormFromUserConfigurationByUser(Yii::app()->user->userModel);
            $configurationForm->hideWelcomeView = true;
            UserConfigurationFormAdapter::setConfigurationFromForm($configurationForm, Yii::app()->user->userModel);
            $this->redirect(array($this->getId() . '/index'));
        }

        public function actionDashboardDetails($id)
        {
            if (intval($id) > 0)
            {
                $dashboard = Dashboard::getById(intval($id));
                $layoutId  = $dashboard->layoutId;
            }
            else
            {
                $dashboard = Dashboard::getByLayoutIdAndUser(Dashboard::DEFAULT_USER_LAYOUT_ID, Yii::app()->user->userModel);
                $layoutId  = $dashboard->layoutId;
            }
            $params = array(
                'controllerId' => $this->getId(),
                'moduleId'     => $this->getModule()->getId(),
            );
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($dashboard);
            $homeTitleBarAndDashboardView = new HomeTitleBarAndDashboardView(
                                                    $this->getId(),
                                                    $this->getModule()->getId(),
                                                    'HomeDashboard' . $layoutId,
                                                    $dashboard,
                                                    $params);
            $view = new HomePageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $homeTitleBarAndDashboardView));
            echo $view->render();
        }

        public function actionCreateDashboard()
        {
            $dashboard = new Dashboard();
            if (isset($_POST['Dashboard']))
            {
                $dashboard->owner = Yii::app()->user->userModel;
                $dashboard->layoutId = Dashboard::getNextLayoutId();
                $dashboard->setAttributes($_POST['Dashboard']);
                assert('in_array($dashboard->layoutType, array_keys(Dashboard::getLayoutTypesData()))');
                if ($dashboard->save())
                {
                    GeneralCache::forgetAll(); //Ensure menu refreshes
                    $this->redirect(array('default/dashboardDetails', 'id' => $dashboard->id));
                }
            }
            $editView = new DashboardEditView($this->getId(), $this->getModule()->getId(), $dashboard,
                                              Zurmo::t('HomeModule', 'Create Dashboard'));
            $view     = new HomePageView(ZurmoDefaultViewUtil::makeStandardViewForCurrentUser($this, $editView));
            echo $view->render();
        }

        /**
         * Only supports saving 4 layoutTypes (max 2 column)
         *
         */
        public function actionEditDashboard($id)
        {
            $id = intval($id);
            $dashboard = Dashboard::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($dashboard);
            if (isset($_POST['Dashboard']))
            {
                $oldLayoutType = $dashboard->layoutType;
                $dashboard->setAttributes($_POST['Dashboard']);
                assert('in_array($dashboard->layoutType, array_keys(Dashboard::getLayoutTypesData()))');
                if ($dashboard->save())
                {
                    if ($oldLayoutType != $dashboard->layoutType && $dashboard->layoutType == '100')
                    {
                        $uniqueLayoutId = 'HomeDashboard' . $dashboard->layoutId;
                        $portletCollection = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition($uniqueLayoutId, Yii::app()->user->userModel->id, array());
                        Portlet::shiftPositionsBasedOnColumnReduction($portletCollection, 1);
                    }
                    GeneralCache::forgetAll(); //Ensure menu refreshes
                    $this->redirect(array('default/dashboardDetails', 'id' => $dashboard->id));
                }
            }
            $editView = new DashboardEditView($this->getId(), $this->getModule()->getId(), $dashboard, strval($dashboard));
            $view     = new AccountsPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $editView));
            echo $view->render();
        }

        /**
         * Removes dashboard and related portlets
         *
         */
        public function actionDeleteDashboard()
        {
            $id = intval($_GET['dashboardId']);
            $dashboard = Dashboard::getById($id);
            ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($dashboard);
            if ($dashboard->isDefault)
            {
                //todo: make a specific exception or view for this situation.
                throw new NotSupportedException();
            }
            $portlets = Portlet::getByLayoutIdAndUserSortedById('HomeDashboard' . $id, Yii::app()->user->userModel->id);
            foreach ($portlets as $portlet)
            {
                $portlet->delete();
                unset($portlet);
            }
            $dashboard->delete();
            unset($dashboard);
            $this->redirect(array('default/index'));
        }

        /**
         * Retrieves a random tip
         */
        public function actionGetTip()
        {
            $tipContent = ZurmoTipsUtil::getRandomTipResolvedForCurrentUser();
            echo CJSON::encode($tipContent);
        }
    }
?>
