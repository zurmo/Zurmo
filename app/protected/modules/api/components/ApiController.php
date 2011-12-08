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
    /**
     *
     * All requests to api will go to this controller.
     * UrlManager must be configured to determine which action is requested.
     * We should be able to catch all actions, even invalid one, and to provide error to user in that case.
     *
     */
    class ApiController extends ZurmoModuleController
    {
        protected $serviceType;
        protected $serviceContentType;
        protected $apiRequest;
        protected $apiResponse;
        protected $params;

        public function __construct($id, $module = null)
        {
            parent::__construct($id, $module);
            Yii::app()->apiRequest->parseParams();
        }

        public function actionList()
        {
            //To be extended by children controllers
        }

        public function actionView()
        {
            //To be extended by children controllers
        }

        public function actionCreate()
        {
            //To be extended by children controllers
        }

        public function actionUpdate()
        {
            //To be extended by children controllers
        }

        public function actionDelete()
        {
            //To be extended by children controllers
        }

        public function actionGetCustomData()
        {
            //To be extended by children controllers
        }

        public function actionLogin()
        {
            $identity = new UserIdentity(Yii::app()->apiRequest->getUsername(), Yii::app()->apiRequest->getPassword());
            $identity->authenticate();
            if ($identity->errorCode == UserIdentity::ERROR_NONE)
            {
                Yii::app()->user->login($identity);
                $data['sessionId'] = Yii::app()->getSession()->getSessionID();
                ApiRestResponse::generateOutput(Yii::app()->apiRequest->getParamsFormat(),
                                                ApiResponse::STATUS_SUCCESS,
                                                $data);
            }
            else
            {
                $error = Yii::t('Default', 'Invalid username or password.');
                ApiRestResponse::generateOutput(Yii::app()->apiRequest->getParamsFormat(),
                                                ApiResponse::STATUS_FAILURE,
                                                null,
                                                $error);
            }
        }

        public function actionLogout()
        {
            Yii::app()->user->logout();
            if (Yii::app()->user->isGuest)
            {
                ApiRestResponse::generateOutput(Yii::app()->apiRequest->getParamsFormat(),
                                                ApiResponse::STATUS_SUCCESS);
            }
            else
            {
                $error = Yii::t('Default', 'Error. User is not logged out.');
                ApiRestResponse::generateOutput(Yii::app()->apiRequest->getParamsFormat(),
                                                ApiResponse::STATUS_FAILURE,
                                                null,
                                                $error);
            }
        }

        public function actionError()
        {
            if ($error = Yii::app()->errorHandler->error)
            {
                ApiRestResponse::generateOutput(Yii::app()->apiRequest->getParamsFormat(),
                                                ApiResponse::STATUS_FAILURE,
                                                null,
                                                $error);
            }
        }

        protected function getBaseController()
        {
            $model = $_GET['model'];
            switch($_GET['model'])
            {
                case 'apiTestModelItem':
                    $controllerName = 'ApiTestModelItemController';
                    break;
                case 'apiTestModelItem2':
                    $controllerName = 'ApiTestModelItem2Controller';
                    break;
                case 'account':
                    $controllerName = 'AccountApiController';
                    break;
                case 'contact':
                    $controllerName = 'ContactApiController';
                    break;
                case 'lead':
                    $controllerName = 'LeadApiController';
                    break;
                case 'meeting':
                    $controllerName = 'MeetingApiController';
                   break;
                case 'note':
                    $controllerName = 'NoteApiController';
                    break;
                case 'opportunity':
                    $controllerName = 'OpportunityApiController';
                    break;
                case 'task':
                    $controllerName = 'TaskApiController';
                    break;
                case 'user':
                    $controllerName = 'UserApiController';
                    break;
                case 'group':
                    $controllerName = 'GroupApiController';
                    break;
                case 'role':
                    $controllerName = 'RoleApiController';
                    break;
                case 'currency':
                    $controllerName = 'CurrencyApiController';
                    break;
                default:
                    $controllerName = null;
                    break;
            }
            return $controllerName;
        }
    }
?>
