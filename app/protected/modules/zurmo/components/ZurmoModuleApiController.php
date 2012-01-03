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
        public function filters()
        {
            $filters = array(
                'apiRequest'
            );
            return array_merge($filters, parent::filters());
        }

        public function filterApiRequest($filterChain)
        {
            try
            {
                $filterChain->run();
            }
            catch(ApiException $e)
            {
                $result = new ApiResult(ApiResponse::STATUS_FAILURE, null, $e->getMessage(), null);
                Yii::app()->apiHelper->sendResponse($result);
            }
        }

        public function actionRead()
        {
            $params = Yii::app()->apiHelper->getRequestParams();
            if(!isset($params['id']))
            {
                $message = Yii::t('Default', 'The id specified was invalid.');
                throw new ApiException($message);
            }
            $result    =  $this->processRead((int)$params['id']);
            Yii::app()->apiHelper->sendResponse($result);
        }

        public function actionList()
        {
            $params = Yii::app()->apiHelper->getRequestParams();
            //if(!isset($params['data']))
            //{
            //    $message = Yii::t('Default', 'Data are empty.');
            //    throw new ApiException($message);
            //}
            $result    =  $this->processList($params);
            Yii::app()->apiHelper->sendResponse($result);
        }

        public function actionCreate()
        {
            $params = Yii::app()->apiHelper->getRequestParams();
            if(!isset($params['data']))
            {
                $message = Yii::t('Default', 'Please provide data.');
                throw new ApiException($message);
            }
            $result    =  $this->processCreate($params['data']);
            Yii::app()->apiHelper->sendResponse($result);
        }

        public function actionUpdate()
        {
            $params = Yii::app()->apiHelper->getRequestParams();
            if(!isset($params['id']))
            {
                $message = Yii::t('Default', 'The id specified was invalid.');
                throw new ApiException($message);
            }
            $result    =  $this->processUpdate((int)$params['id'], $params['data']);
            Yii::app()->apiHelper->sendResponse($result);
        }

        public function actionDelete()
        {
            $params = Yii::app()->apiHelper->getRequestParams();
            if(!isset($params['id']))
            {
                $message = Yii::t('Default', 'The id specified was invalid.');
                throw new ApiException($message);
            }
            $result    =  $this->processDelete((int)$params['id']);
            Yii::app()->apiHelper->sendResponse($result);
        }

        protected function getModelName()
        {
            return $this->getModule()->getPrimaryModelName();
        }

        protected function processRead($id)
        {
            assert('is_int($id)');
            $modelClassName = $this->getModelName();

            try
            {
                $model = $modelClassName::getById($id);
            }
            catch (NotFoundException $e)
            {
                $message = Yii::t('Default', 'The id specified was invalid.');
                throw new ApiException($message);
            }

            try
            {
                ApiControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($model);
            }
            catch(SecurityException $e)
            {
                $message = $e->getMessage();
                throw new ApiException($message);
            }

            try
            {
                $redBeanModelToApiDataUtil   = new RedBeanModelToApiDataUtil($model);
                $data   = $redBeanModelToApiDataUtil->getData();
                $result = new ApiResult(ApiResponse::STATUS_SUCCESS, $data, null, null);
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();
                throw new ApiException($message);
            }
            return $result;
        }

        protected function getStateMetadataAdapterClassName()
        {
            return null;
        }

        protected function getSearchFormClassName()
        {
            return null;
        }

        protected function processList()
        {
            $modelClassName = $this->getModelName();
            $searchFormClassName = $this->getSearchFormClassName();

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

                $model= new $modelClassName(false);
                if (isset($searchFormClassName))
                {
                    $searchForm = new $searchFormClassName($model);
                }
                else
                {
                    $searchForm = null;
                }
                $stateMetadataAdapterClassName = $this->getStateMetadataAdapterClassName();

                $dataProvider = $this->makeRedBeanDataProviderFromGet(
                                    $searchForm,
                                    $modelClassName,
                                    $pageSize,
                                    Yii::app()->user->userModel->id,
                                    $stateMetadataAdapterClassName
                                );

                $totalItems = $dataProvider->getTotalItemCount();
                $data = array();
                $data['total'] = $totalItems;
                if ($totalItems > 0)
                {
                    $formattedData = $dataProvider->getData();
                    foreach ($formattedData as $model)
                    {
                        $redBeanModelToApiDataUtil  = new RedBeanModelToApiDataUtil($model);
                        $data['array'][] = $redBeanModelToApiDataUtil->getData();
                    }
                    $result = new ApiResult(ApiResponse::STATUS_SUCCESS, $data, null, null);
                }
                else
                {
                    $result = new ApiResult(ApiResponse::STATUS_SUCCESS, $data, null, null);
                }
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();
                throw new ApiException($message);
            }
            return $result;
        }

        protected function processCreate($data)
        {
            $modelClassName = $this->getModelName();
            try
            {
                $model = $this->attemptToSaveModelFromData(new $modelClassName, $data, null, false);
                $id = $model->id;
                $model->forget();
                if (!count($model->getErrors()))
                {
                    $model = $modelClassName::getById($id);
                    $redBeanModelToApiDataUtil  = new RedBeanModelToApiDataUtil($model);
                    $data  = $redBeanModelToApiDataUtil->getData();
                    $result = new ApiResult(ApiResponse::STATUS_SUCCESS, $data, null, null);
                }
                else
                {
                    $errors = $model->getErrors();
                    $message = Yii::t('Default', 'Model was not created.');
                    // To-Do: How to pass $errors and $message to exception
                    //throw new ApiException($message);
                    $result = new ApiResult(ApiResponse::STATUS_FAILURE, null, $message, $errors);
                }
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();
                throw new ApiException($message);
            }
            return $result;
        }



        protected function processUpdate($id, $data)
        {
            assert('is_int($id)');
            $modelClassName = $this->getModelName();

            try
            {
                $model = $modelClassName::getById($id);
            }
            catch (NotFoundException $e)
            {
                $message = Yii::t('Default', 'The id specified was invalid.');
                throw new ApiException($message);
            }

            try
            {
                ApiControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($model);
            }
            catch(SecurityException $e)
            {
                $message = $e->getMessage();
                throw new ApiException($message);
            }

            try
            {
                $model = $this->attemptToSaveModelFromData($model, $data, null, false);
                $id = $model->id;
                if (!count($model->getErrors()))
                {
                    $model = $modelClassName::getById($id);
                    $redBeanModelToApiDataUtil  = new RedBeanModelToApiDataUtil($model);
                    $data  = $redBeanModelToApiDataUtil->getData();
                    $result = new ApiResult(ApiResponse::STATUS_SUCCESS, $data, null, null);
                }
                else
                {
                    $errors = $model->getErrors();
                    $message = Yii::t('Default', 'Model was not updated.');
                    // To-Do: How to pass $errors and $message to exception
                    //throw new ApiException($message);
                    $result = new ApiResult(ApiResponse::STATUS_FAILURE, null, $message, $errors);
                }
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();
                throw new ApiException($message);
            }
            return $result;
        }

        protected function processDelete($id)
        {
            assert('is_int($id)');
            $modelClassName = $this->getModelName();

            try
            {
                $model = $modelClassName::getById($id);
            }
            catch (NotFoundException $e)
            {
                $message = Yii::t('Default', 'The id specified was invalid.');
                throw new ApiException($message);
            }

            try
            {
                ApiControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($model);
            }
            catch(SecurityException $e)
            {
                $message = $e->getMessage();
                throw new ApiException($message);
            }

            try
            {
                $model->delete();
                $result = new ApiResult(ApiResponse::STATUS_SUCCESS, null);
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();
                throw new ApiException($message);
            }
            return $result;
        }

        /**
         * Instead of saving from post, we are saving from the API data.
         * @see attemptToSaveModelFromPost
         */
        protected function attemptToSaveModelFromData($model, $data, $redirectUrlParams = null, $redirect = true)
        {
            assert('is_array($data)');
            assert('$redirectUrlParams == null || is_array($redirectUrlParams) || is_string($redirectUrlParams)');
            $savedSucessfully   = false;
            $modelToStringValue = null;

            if (isset($data))
            {
                $model            = ZurmoControllerUtil::
                                    saveModelFromSanitizedData($data, $model, $savedSucessfully, $modelToStringValue);
            }
            if ($savedSucessfully && $redirect)
            {
                $this->actionAfterSuccessfulModelSave($model, $modelToStringValue, $redirectUrlParams);
            }
            return $model;
        }

        /**
         *
         * Enter description here ...
         * @param string $status
         * @param string $message
         * @param array || boolean $data
         */
        protected function generateOutput($status, $message, $data=null, $errors = null)
        {
            assert('is_string($status) && $status !=""');
            assert('is_string($message)');

            $output = array();
            $output['data'] = $data;
            $output['status'] = $status;
            $output['message'] = $message;
            $output['errors'] = $errors;

            return $output;
        }
    }
?>