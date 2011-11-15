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
    Yii::import("application.modules.api.tests.unit.controllers.ApiTestController");
    Yii::import("application.modules.api.tests.unit.models.ApiModelTestItem");
    /**
     *
     * All requests to api will go to this controller.
     * UrlManager must be configured to determine which action is requested.
     * We should be able to catch all actions, even invalid one, and to provide error to user in that case.
     *
     */
    class ApiRestController extends ZurmoModuleController
    {
        protected $serviceType;
        protected $serviceContentType;
        protected $apiRequest;
        protected $apiResponse;
        protected $params;

        public function __construct($id, $module = null)
        {
            parent::__construct($id, $module);
        }

        public function actionList()
        {
            $baseControllerName = $this->getBaseController();

            if ($baseControllerName != null)
            {
                $baseController = new $baseControllerName($baseControllerName, 'api');
                $res = $baseController->getAll();
                print_r($res);
            }
            else
            {
                // Send error.
            }
        }

        public function actionView()
        {

        }

        public function actionCreate()
        {

        }

        public function actionUpdate()
        {

        }

        public function actionDelete()
        {

        }

        public function actionLogin()
        {
            $identity = new UserIdentity(Yii::app()->apiRequest->getUsername(), Yii::app()->apiRequest->getPassword());
            $identity->authenticate();
            if ($identity->errorCode == UserIdentity::ERROR_NONE)
            {
                Yii::app()->user->login($identity);
                echo 'SessionId:' . Yii::app()->getSession()->getSessionID() . "<br />";
                return true;
                //returm tokenId
            }
            else
            {
                return false;
            }
            exit;
        }

        protected function getBaseController()
        {
            $model = $_GET['model'];
            switch($_GET['model'])
            {
                case 'accounts':
                    $controllerName = 'AccountsApiController';
                    break;
                case 'apiTest':
                    $controllerName = 'ApiTestController';
                    break;
                default:
                    $controllerName = null;
                    break;
            }
            return $controllerName;
        }
    }
?>
