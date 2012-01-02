<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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

    class ZurmoDefaultController extends ZurmoBaseController
    {
        public function filters()
        {
            $moduleClassName = get_class($this->getModule());
            return array(
                array(
                    ZurmoBaseController::RIGHTS_FILTER_PATH . ' + logout, index, about',
                    'moduleClassName' => $moduleClassName,
                    'rightName'       => $moduleClassName::getAccessRight(),
               ),
                array(
                    ZurmoBaseController::RIGHTS_FILTER_PATH . ' + configurationEdit',
                    'moduleClassName' => $moduleClassName,
                    'rightName'       => ZurmoModule::RIGHT_ACCESS_GLOBAL_CONFIGURATION,
               ),
            );
        }

        public function actionIndex()
        {
            $this->redirect(Yii::app()->homeUrl);
        }

        public function actionLogin()
        {
            $formModel = new LoginForm();
            if (isset($_POST['ajax']) && $_POST['ajax'] == 'login-form')
            {
                echo ZurmoActiveForm::validate($formModel);
                Yii::app()->end(0, false);
            }
            elseif (isset($_POST['LoginForm']))
            {
                $formModel->attributes = $_POST['LoginForm'];
                if ($formModel->validate() && $formModel->login())
                {
                    $this->redirect(Yii::app()->user->returnUrl);
                }
            }
            $extraHeaderContent = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'loginViewExtraHeaderContent');
            $view = new LoginPageView($this, $formModel, $extraHeaderContent);
            echo $view->render();
        }

        public function actionLogout()
        {
            Yii::app()->user->logout();
            $this->redirect(Yii::app()->homeUrl);
        }

        public function actionError()
        {
            if ($error = Yii::app()->errorHandler->error)
            {
                if (Yii::app()->request->isAjaxRequest)
                {
                    echo $error['message'];
                }
                else
                {
                    $view = new ErrorPageView($error['message']);
                    echo $view->render();
                }
            }
        }

        public function actionUnsupportedBrowser($name)
        {
            if ($name == '')
            {
                $name = 'not detected';
            }
            $view = new UnsupportedBrowserPageView($name);
            echo $view->render();
        }

        public function actionAbout()
        {
            $view = new AboutPageView($this);
            echo $view->render();
        }

        public function actionConfigurationEdit()
        {
            $configurationForm = ZurmoConfigurationFormAdapter::makeFormFromGlobalConfiguration();
            $postVariableName   = get_class($configurationForm);
            if (isset($_POST[$postVariableName]))
            {
                $configurationForm->setAttributes($_POST[$postVariableName]);
                if ($configurationForm->validate())
                {
                    ZurmoConfigurationFormAdapter::setConfigurationFromForm($configurationForm);
                    Yii::app()->user->setFlash('notification',
                        Yii::t('Default', 'Global configuration saved successfully.')
                    );
                    $this->redirect(Yii::app()->createUrl('configuration/default/index'));
                }
            }
            $titleBarAndEditView = new TitleBarAndConfigurationEditAndDetailsView(
                                    $this->getId(),
                                    $this->getModule()->getId(),
                                    $configurationForm,
                                    'ZurmoConfigurationEditAndDetailsView',
                                    'Edit',
                                    Yii::t('Default', 'Global Configuration')
            );
            $view = new ZurmoConfigurationPageView($this, $titleBarAndEditView);
            echo $view->render();
        }

        public function actionRecentlyViewed()
        {
            echo AuditEventsRecentlyViewedUtil::getRecentlyViewedAjaxContentByUser(Yii::app()->user->userModel, 10);
        }

        public function actionGlobalSearchAutoComplete($term)
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'autoCompleteListPageSize', get_class($this->getModule()));
            $autoCompleteResults = ModelAutoCompleteUtil::
                                   getGlobalSearchResultsByPartialTerm($term, $pageSize, Yii::app()->user->userModel);
            echo CJSON::encode($autoCompleteResults);
        }

        /**
         * Given a name of a customFieldData object and a term to search on return a JSON encoded
         * array of autocomplete search results.
         * @param string $name - Name of CustomFieldData
         * @param string $term - term to search on
         */
        public function actionAutoCompleteCustomFieldData($name, $term)
        {
            assert('is_string($name)');
            assert('is_string($term)');
            $autoCompleteResults = ModelAutoCompleteUtil::getCustomFieldDataByPartialName(
                                       $name, $term);
            echo CJSON::encode($autoCompleteResults);
        }
    }
?>
