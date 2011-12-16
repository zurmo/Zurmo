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

    class TaskApiController extends ZurmoModuleApiController
    {
        public function create($data)
        {
            try
            {
                $model= new Task();

                if (isset($data['name']))
                {
                    $model->name     = $data['name'];
                }

                if (isset($data['dueDateTime']))
                {
                    $model->dueDateTime       = $data['dueDateTime'];
                }

                if (isset($data['completedDateTime']))
                {
                    $model->completedDateTime       = $data['completedDateTime'];
                }

                if (isset($data['completed']))
                {
                    $model->completed       = $data['completed'];
                }

                if (isset($data['description']))
                {
                    $model->description       = $data['description'];
                }

                $saved = $model->save();
                $id = $model->id;
                $model->forget();
                unset($model);
                $outputArray = array();
                if ($saved)
                {
                    $model = Task::getById($id);
                    $util  = new RedBeanModelToApiDataUtil($model);
                    $data  = $util->getData();

                    $outputArray['status']  = 'SUCCESS';
                    $outputArray['data']    = $data;
                    $outputArray['message'] = '';
                }
                else
                {
                    $outputArray['data'] = null;
                    $outputArray['status'] = 'FAILURE';
                    $outputArray['message'] = Yii::t('Default', 'Model could not be saved.');
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

        public function update($id, $data)
        {
            try
            {
                $model = Task::getById($id);
                $isAllowed = ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($model);
                if ($isAllowed === false)
                {
                    throw new Exception('This action is not allowed.');
                }
                if (isset($data['name']))
                {
                    $model->name     = $data['name'];
                }
                else
                {
                    $model->name = null;
                }

                if (isset($data['dueDateTime']))
                {
                    $model->dueDateTime       = $data['dueDateTime'];
                }
                else
                {
                    $model->dueDateTime = null;
                }

                if (isset($data['completedDateTime']))
                {
                    $model->completedDateTime       = $data['completedDateTime'];
                }
                else
                {
                    $model->completedDateTime = null;
                }

                if (isset($data['completed']))
                {
                    $model->completed       = $data['completed'];
                }
                else
                {
                    $model->completed = null;
                }

                if (isset($data['description']))
                {
                    $model->description       = $data['description'];
                }
                else
                {
                    $model->description = null;
                }

                $saved = $model->save();
                $outputArray = array();
                if ($saved)
                {
                    $model = Task::getById($id);
                    $util  = new RedBeanModelToApiDataUtil($model);
                    $data  = $util->getData();

                    $outputArray['status']  = 'SUCCESS';
                    $outputArray['data']    = $data;
                    $outputArray['message'] = '';
                }
                else
                {
                    $outputArray['data'] = null;
                    $outputArray['status'] = 'FAILURE';
                    $outputArray['message'] = Yii::t('Default', 'Model could not be saved.');
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
    }
?>
