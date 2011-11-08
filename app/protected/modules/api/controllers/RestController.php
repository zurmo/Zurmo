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
            $this->apiRequest         = new ApiRestRequest();
            $this->apiResponse        = new ApiRestResponse();
            $this->serviceType        = $this->apiRequest->getServiceType();
            $this->serviceContentType = $this->apiRequest->getServiceContentType();
        }

        public function actionList()
        {
            print_r(unserialize(Yii::app()->session->readSession('bdekl6vbb4it74fl026nig6o0')));
            echo "aa";
            exit;

            $sessId = Yii::app()->getSession()->getSessionID();
            echo $sessId . "<br />";
            Yii::app()->getSession()->setSessionID('bdekl6vbb4it74fl026nig6o0');
            $sessId = Yii::app()->getSession()->getSessionID();
            echo $sessId . "<br />";
            print_r(Yii::app());
            exit;
            $baseControllerName = $this->getBaseController();
            if ($baseControllerName != null)
            {
                $baseController = new $baseControllerName($baseControllerName, 'accounts');
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
            $credentials = $this->apiRequest->getCredentials();
            $identity = new UserIdentity('super', 'super');
            $identity->authenticate();
            if ($identity->errorCode == UserIdentity::ERROR_NONE)
            {
                Yii::app()->user->login($identity);
                echo Yii::app()->getSession()->getSessionID();
                print_r($_SESSION);
                return true;
                //returm tokenId
            }
            else
            {
                return false;
            }
        }

        protected function getBaseController()
        {
            $model = $_GET['model'];
            switch($_GET['model'])
            {
                case 'account':
                    $controllerName = 'AccountsApiController';
                    break;
                default:
                    $controllerName = null;
                    break;
            }
            return $controllerName;
        }
    }
?>
