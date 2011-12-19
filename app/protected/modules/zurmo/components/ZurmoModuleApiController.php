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
     * Zurmo Modules api controllers
     * should extend this class to provide generic functionality
     * that is applicable to all standard api modules.
     */
    abstract class ZurmoModuleApiController extends ZurmoModuleController
    {
        public function getAll($modelClassName, $searchFormClassName, $stateMetadataAdapterClassName)
        {
            try
            {
                $filterParams = array();
                if (isset($_GET['filter']) && $_GET['filter'] != '')
                {
                    parse_str($_GET['filter'], $filterParams);
                }

                $pageSize    = Yii::app()->pagination->getGlobalValueByType('apiListPageSize');
                if (isset($filterParams['pagination']['pageSize']))
                {
                    $pageSize = $filterParams['pagination']['pageSize'];
                }

                if (isset($filterParams['pagination']['page']))
                {
                    $_GET[$modelClassName . '_page'] = $filterParams['pagination']['page'];
                }

                if (isset($filterParams['sort']))
                {
                    $_GET[$modelClassName . '_sort'] = $filterParams['sort'];
                }


                if (isset($filterParams['search']) && isset($searchFormClassName))
                {
                    $_GET[$searchFormClassName] = $filterParams['search'];
                }

                $stateMetadataAdapterClassName = null;
                $model= new $modelClassName(false);
                if (isset($searchFormClassName))
                {
                    $searchForm = new $searchFormClassName($model);
                }
                else
                {
                    $searchForm = null;
                }

                $dataProvider = $this->makeRedBeanDataProviderFromGet(
                    $searchForm,
                    $modelClassName,
                    $pageSize,
                    Yii::app()->user->userModel->id,
                    $stateMetadataAdapterClassName
                );

                $totalItems = $dataProvider->getTotalItemCount();
                $outputArray = array();
                $outputArray['data']['total'] = $totalItems;

                if ($totalItems > 0)
                {

                    $outputArray['status'] = 'SUCCESS';
                    $outputArray['message'] = '';

                    $data = $dataProvider->getData();
                    foreach ($data as $model)
                    {
                        $util  = new RedBeanModelToApiDataUtil($model);
                        $outputArray['data']['array'][] = $util->getData();
                    }
                }
                else
                {
                    $outputArray['data']['array'] = null;
                    $outputArray['status'] = 'FAILURE';
                    $outputArray['message'] = Yii::t('Default', 'Error');
                }
            }
            catch (Exception $e)
            {
                $outputArray['status'] = 'FAILURE';
                $outputArray['message'] = $e->getMessage();
            }
            return $outputArray;
        }

        public function getById($modelClassName, $id)
        {
            try
            {
                $model = $modelClassName::getById($id);
                $isAllowed = ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($model);
                if ($isAllowed === false)
                {
                    throw new Exception('This action is not allowed.');
                }
                $util  = new RedBeanModelToApiDataUtil($model);
                $data  = $util->getData();
                $outputArray = array();
                $outputArray['status'] = 'SUCCESS';
                $outputArray['data']   = $data;
                $outputArray['message'] = '';
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
                foreach($data as $k=>$v){
                    $_POST[$modelClassName][$k] = $v;
                }
                $model = $this->attemptToSaveModelFromPost(new $modelClassName, null, false);

                $id = $model->id;
                $model->forget();
                unset($model);
                $outputArray = array();
                if (isset($id))
                {
                    $model = $modelClassName::getById($id);
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

        public function update($modelClassName, $id, $data)
        {
            try
            {
                foreach($data as $k=>$v){
                    $_POST[$modelClassName][$k] = $v;
                }

                $model = $modelClassName::getById($id);
                $isAllowed = ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($model);
                if ($isAllowed === false)
                {
                    throw new Exception('This action is not allowed.');
                }

                $model = $this->attemptToSaveModelFromPost($model, null, false);

                $id = $model->id;
                $outputArray = array();
                if (isset($id))
                {
                    $model = $modelClassName::getById($id);
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

        public function delete($modelClassName, $id)
        {
            try
            {
                $model = $modelClassName::getById($id);
                $isAllowed = ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($model);
                if ($isAllowed === false)
                {
                    throw new Exception('This action is not allowed.');
                }
                $model->delete();
                $outputArray['status'] = 'SUCCESS';
                $outputArray['message'] = '';
            }
            catch (Exception $e)
            {
                $outputArray['status'] = 'FAILURE';
                $outputArray['message'] = $e->getMessage();
            }
            return $outputArray;
        }

        /**
         * Instead of saving from post, we are saving from the API data.
         * @see attemptToSaveModelFromPost
         */
        protected function attemptToSaveModelFromData($data, $model, $redirectUrlParams = null, $redirect = true)
        {
            assert('is_array($data)');
            assert('$redirectUrlParams == null || is_array($redirectUrlParams) || is_string($redirectUrlParams)');
            $savedSucessfully   = false;
            $modelToStringValue = null;
            //$postVariableName   = get_class($model);
           //if (isset($data[$postVariableName]))        //dont need this since our $data array can be exactly that sub array
           if(isset($data))
            {
               // $data = $data[$postVariableName];
                $model            = ZurmoControllerUtil::
                                    saveModelFromSanitizedData($data, $model, $savedSucessfully, $modelToStringValue);
            }
            if ($savedSucessfully && $redirect)
            {
                $this->actionAfterSuccessfulModelSave($model, $modelToStringValue, $redirectUrlParams);
            }
            return $model;
        }
    }
?>