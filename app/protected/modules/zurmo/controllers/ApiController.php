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
    * ZurmoAPiController is responsible for login and logout actions.
    */
    class ZurmoApiController extends ZurmoModuleApiController
    {
        public function actionLogin()
        {
            try
            {
                $identity = new UserIdentity(Yii::app()->apiRequest->getUsername(), Yii::app()->apiRequest->getPassword());
                $identity->authenticate();
            }
            catch (Exception $e)
            {
                $message = Yii::t('Default', 'An error occured during login. Please try again.');
                throw new ApiException($message);
            }
            if ($identity->errorCode == UserIdentity::ERROR_NONE)
            {
                Yii::app()->user->login($identity);
                $data['sessionId'] = Yii::app()->getSession()->getSessionID();
                $data['token'] = Yii::app()->session['token'];
                $session = Yii::app()->getSession();
                $result = new ApiResult(ApiResponse::STATUS_SUCCESS, $data, null, null);
                Yii::app()->apiHelper->sendResponse($result);
            }
            else
            {
                $message = Yii::t('Default', 'Invalid username or password.');
                throw new ApiException($message);
            }
        }

        public function actionLogout()
        {
            Yii::app()->user->logout();
            if (Yii::app()->user->isGuest)
            {
                $result = new ApiResult(ApiResponse::STATUS_SUCCESS, null, null, null);
                Yii::app()->apiHelper->sendResponse($result);
            }
            else
            {
                $message = Yii::t('Default', 'Logout failed.');
                throw new ApiException($message);
            }
        }

        public function actionError()
        {
            if ($error = Yii::app()->errorHandler->error)
            {
                throw new ApiException($error);
            }
        }
    }
?>
