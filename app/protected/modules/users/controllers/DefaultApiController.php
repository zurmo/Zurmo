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

    class UsersDefaultApiController extends ZurmoModuleApiController
    {
        public function getById($modelClassName, $id)
        {
            try
            {
                $model = User::getById($id);

                $isAllowed = ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($model);
                if ($isAllowed === false)
                {
                    throw new NotSupportedException(Yii::t('Default', 'This action is not allowed.'));
                }

                if ($this->resolveCanCurrentUserAccessAction($id))
                {
                    $util  = new RedBeanModelToApiDataUtil($model);
                    $data  = $util->getData();
                    $outputArray = array();
                    $outputArray['status'] = 'SUCCESS';
                    $outputArray['data']   = $data;
                    $outputArray['message'] = '';
                }
                else
                {
                    $outputArray['data'] = null;
                    $outputArray['status'] = 'FAILURE';
                    $outputArray['message'] = Yii::t('Default', 'This action is not allowed.');
                }

            }
            catch (Exception $e)
            {
                $outputArray['data'] = null;
                $outputArray['status'] = 'FAILURE';
                $outputArray['message'] = $e->getMessage();
            }
            return $outputArray;
        }

        public function create($modelClassName, $data)
        {
            try
            {
                $model = $this->attemptToSaveModelFromData(new $modelClassName, $data, null, false);
                $id = $model->id;

                if (!count($model->getErrors()))
                {
                    $model = $modelClassName::getById($id);
                    $util  = new RedBeanModelToApiDataUtil($model);
                    $data  = $util->getData();
                    $output = $this->generateOutput('SUCCESS', '', $data);
                }
                else
                {
                    $errors = $model->getErrors();
                    $message = Yii::t('Default', 'Model could not be saved.');
                    $output = $this->generateOutput('FAILURE', $message, null, $errors);
                }
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();
                $output = $this->generateOutput('FAILURE', $message, null);
            }
            return $output;
        }

        public function update($modelClassName, $id, $data)
        {
            try
            {
                $model = $modelClassName::getById($id);
                $isAllowed = ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($model);
                if ($isAllowed === false || !$this->resolveCanCurrentUserAccessAction($id))
                {
                    throw new NotSupportedException(Yii::t('Default', 'This action is not allowed.'));
                }

                $model = $this->attemptToSaveModelFromData($model, $data, null, false);
                $id = $model->id;

                if (!count($model->getErrors()))
                {
                    $model = $modelClassName::getById($id);
                    $util  = new RedBeanModelToApiDataUtil($model);
                    $data  = $util->getData();

                    $output = $this->generateOutput('SUCCESS', '', $data);
                }
                else
                {
                    $errors = $model->getErrors();
                    $message = Yii::t('Default', 'Model could not be saved.');
                    $output = $this->generateOutput('FAILURE', $message, null, $errors);
                }
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();
                $output = $this->generateOutput('FAILURE', $message, null);
            }
            return $output;
        }

        public function delete($modelClassName, $id)
        {
            try
            {
                $model = $modelClassName::getById($id);
                $isAllowed = ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($model);
                if ($isAllowed === false || !$this->resolveCanCurrentUserAccessAction($id))
                {
                    throw new NotSupportedException(Yii::t('Default', 'This action is not allowed.'));
                }
                $model->delete();
                $output = $this->generateOutput('SUCCESS', '');
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();
                $output = $this->generateOutput('FAILURE', $message, null);
            }
            return $output;
        }

        protected function resolveCanCurrentUserAccessAction($userId)
        {
            if (Yii::app()->user->userModel->id == $userId ||
            RightsUtil::canUserAccessModule('UsersModule', Yii::app()->user->userModel))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }
?>
