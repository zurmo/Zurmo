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
     * Zurmo Modules api controllers
     * should extend this class to provide generic functionality
     * that is applicable to all standard api modules.
     */
    abstract class ZurmoModuleApiController extends ZurmoBaseController
    {
        const RIGHTS_FILTER_PATH = 'application.modules.api.utils.ApiRightsControllerFilter';

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
            catch (Exception $e)
            {
                $result = new ApiResult(ApiResponse::STATUS_FAILURE, null, $e->getMessage(), null);
                Yii::app()->apiHelper->sendResponse($result);
            }
        }

        /**
         * Get model and send response
         * @throws ApiException
         */
        public function actionRead()
        {
            $params = Yii::app()->apiHelper->getRequestParams();
            if (!isset($params['id']))
            {
                $message = Yii::t('Default', 'The ID specified was invalid.');
                throw new ApiException($message);
            }
            $result    =  $this->processRead((int)$params['id']);
            Yii::app()->apiHelper->sendResponse($result);
        }

        /**
         * Get array or models and send response
         */
        public function actionList()
        {
            $params = Yii::app()->apiHelper->getRequestParams();
            $result    =  $this->processList($params);
            Yii::app()->apiHelper->sendResponse($result);
        }

        /**
         * Create new model, and send response
         * @throws ApiException
         */
        public function actionCreate()
        {
            $params = Yii::app()->apiHelper->getRequestParams();
            if (!isset($params['data']))
            {
                $message = Yii::t('Default', 'Please provide data.');
                throw new ApiException($message);
            }
            $result    =  $this->processCreate($params['data']);
            Yii::app()->apiHelper->sendResponse($result);
        }

        /**
         * Update model and send response
         * @throws ApiException
         */
        public function actionUpdate()
        {
            $params = Yii::app()->apiHelper->getRequestParams();
            if (!isset($params['id']))
            {
                $message = Yii::t('Default', 'The ID specified was invalid.');
                throw new ApiException($message);
            }
            $result    =  $this->processUpdate((int)$params['id'], $params['data']);
            Yii::app()->apiHelper->sendResponse($result);
        }

        /**
         * Delete model and send response
         * @throws ApiException
         */
        public function actionDelete()
        {
            $params = Yii::app()->apiHelper->getRequestParams();
            if (!isset($params['id']))
            {
                $message = Yii::t('Default', 'The ID specified was invalid.');
                throw new ApiException($message);
            }
            $result    =  $this->processDelete((int)$params['id']);
            Yii::app()->apiHelper->sendResponse($result);
        }

        /**
         * Add related model to model's relations
         */
        public function actionAddRelation()
        {
            $params = Yii::app()->apiHelper->getRequestParams();
            $result    =  $this->processAddRelation($params);
            Yii::app()->apiHelper->sendResponse($result);
        }

        /**
         * Remove related model from model's relations
         */
        public function actionRemoveRelation()
        {
            $params = Yii::app()->apiHelper->getRequestParams();
            $result    =  $this->processRemoveRelation($params);
            Yii::app()->apiHelper->sendResponse($result);
        }

        /**
         * Get module primary model name
         */
        protected function getModelName()
        {
            return $this->getModule()->getPrimaryModelName();
        }

        /**
         * Get model by id
         * @param int $id
         * @throws ApiException
         * @return ApiResult
         */
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
                $message = Yii::t('Default', 'The ID specified was invalid.');
                throw new ApiException($message);
            }

            try
            {
                ApiControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($model);
            }
            catch (SecurityException $e)
            {
                $message = $e->getMessage();
                throw new ApiException($message);
            }

            try
            {
                $redBeanModelToApiDataUtil = new RedBeanModelToApiDataUtil($model);
                $data                      = $redBeanModelToApiDataUtil->getData();
                $result                    = new ApiResult(ApiResponse::STATUS_SUCCESS, $data, null, null);
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();
                throw new ApiException($message);
            }
            return $result;
        }

        protected static function getSearchFormClassName()
        {
            return null;
        }

        /**
         * List all models that satisfy provided criteria
         * @param array $params
         * @throws ApiException
         * @return ApiResult
         */
        protected function processList($params)
        {
            $modelClassName = $this->getModelName();
            $searchFormClassName = static::getSearchFormClassName();

            try
            {
                $filterParams = array();

                if (strtolower($_SERVER['REQUEST_METHOD']) != 'post')
                {
                    if (isset($params['filter']) && $params['filter'] != '')
                    {
                        parse_str($params['filter'], $filterParams);
                    }
                }
                else
                {
                    $filterParams = $params['data'];
                }

                $pageSize    = Yii::app()->pagination->getGlobalValueByType('apiListPageSize');

                if (isset($filterParams['pagination']['pageSize']))
                {
                    $pageSize = (int)$filterParams['pagination']['pageSize'];
                }

                if (isset($filterParams['pagination']['page']))
                {
                    $_GET[$modelClassName . '_page'] = (int)$filterParams['pagination']['page'];
                }

                if (isset($filterParams['sort']))
                {
                    $_GET[$modelClassName . '_sort'] = $filterParams['sort'];
                }

                if (isset($filterParams['search']) && isset($searchFormClassName))
                {
                    $_GET[$searchFormClassName] = $filterParams['search'];
                }
                if (isset($filterParams['dynamicSearch']) &&
                    isset($searchFormClassName) &&
                    !empty($filterParams['dynamicSearch']['dynamicClauses']) &&
                    !empty($filterParams['dynamicSearch']['dynamicStructure']))
                {
                    // Convert model ids into item ids, so we can perform dynamic search
                    DynamicSearchUtil::resolveDynamicSearchClausesForModelIdsNeedingToBeItemIds($modelClassName, $filterParams['dynamicSearch']['dynamicClauses']);
                    $_GET[$searchFormClassName]['dynamicClauses'] = $filterParams['dynamicSearch']['dynamicClauses'];
                    $_GET[$searchFormClassName]['dynamicStructure'] = $filterParams['dynamicSearch']['dynamicStructure'];
                }

                $model = new $modelClassName(false);
                if (isset($searchFormClassName))
                {
                    $searchForm = new $searchFormClassName($model);
                }
                else
                {
                    throw new NotSupportedException();
                }

                // In case of ContactState model, we can't use Module::getStateMetadataAdapterClassName() function,
                // because it references to Contact model, so we defined new function
                // ContactsContactStateApiController::getStateMetadataAdapterClassName() which return null.
                if (method_exists($this, 'getStateMetadataAdapterClassName'))
                {
                    $stateMetadataAdapterClassName = $this->getStateMetadataAdapterClassName();
                }
                else
                {
                    $stateMetadataAdapterClassName = $this->getModule()->getStateMetadataAdapterClassName();
                }

                $dataProvider = $this->makeRedBeanDataProviderByDataCollection(
                    $searchForm,
                    $pageSize,
                    $stateMetadataAdapterClassName
                );

                if (isset($filterParams['pagination']['page']) && (int)$filterParams['pagination']['page'] > 0)
                {
                    $currentPage = (int)$filterParams['pagination']['page'];
                }
                else
                {
                    $currentPage = 1;
                }

                $totalItems = $dataProvider->getTotalItemCount();
                $data = array();
                $data['totalCount'] = $totalItems;
                $data['currentPage'] = $currentPage;
                if ($totalItems > 0)
                {
                    $formattedData = $dataProvider->getData();
                    foreach ($formattedData as $model)
                    {
                        $redBeanModelToApiDataUtil  = new RedBeanModelToApiDataUtil($model);
                        $data['items'][] = $redBeanModelToApiDataUtil->getData();
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

        /**
         * Add model relation
         * @param array $params
         * @throws ApiException
         * @return ApiResult
         */
        protected function processAddRelation($params)
        {
            $modelClassName = $this->getModelName();
            try
            {
                $data = array();
                if (isset($params['data']) && $params['data'] != '')
                {
                    parse_str($params['data'], $data);
                }
                $relationName = $data['relationName'];
                $modelId = $data['id'];
                $relatedId = $data['relatedId'];

                $model = $modelClassName::getById(intval($modelId));
                $relatedModelClassName = $model->getRelationModelClassName($relationName);
                $relatedModel = $relatedModelClassName::getById(intval($relatedId));

                if ($model->getRelationType($relationName) == RedBeanModel::HAS_MANY ||
                    $model->getRelationType($relationName) == RedBeanModel::MANY_MANY)
                {
                    $model->{$relationName}->add($relatedModel);

                    if ($model->save())
                    {
                        $result = new ApiResult(ApiResponse::STATUS_SUCCESS, null);
                    }
                    else
                    {
                        $message = Yii::t('Default', 'Could not save relation.');
                        throw new ApiException($message);
                    }
                }
                else
                {
                    $message = Yii::t('Default', 'Could not use this API call for HAS_ONE relationships.');
                    throw new ApiException($message);
                }
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();
                throw new ApiException($message);
            }
            return $result;
        }

        /**
         * Remove model relation
         * @param array $params
         * @throws ApiException
         * @return ApiResult
         */
        protected function processRemoveRelation($params)
        {
            $modelClassName = $this->getModelName();
            try
            {
                $data = array();
                if (isset($params['data']) && $params['data'] != '')
                {
                    parse_str($params['data'], $data);
                }
                $relationName = $data['relationName'];
                $modelId = $data['id'];
                $relatedId = $data['relatedId'];

                $model = $modelClassName::getById(intval($modelId));
                $relatedModelClassName = $model->getRelationModelClassName($relationName);
                $relatedModel = $relatedModelClassName::getById(intval($relatedId));
                if ($model->getRelationType($relationName) == RedBeanModel::HAS_MANY ||
                    $model->getRelationType($relationName) == RedBeanModel::MANY_MANY)
                {
                    $model->{$relationName}->remove($relatedModel);
                    if ($model->save())
                    {
                        $result = new ApiResult(ApiResponse::STATUS_SUCCESS, null);
                    }
                    else
                    {
                        $message = Yii::t('Default', 'Could not remove relation.');
                        throw new ApiException($message);
                    }
                }
                else
                {
                    $message = Yii::t('Default', 'Could not use this API call for HAS_ONE relationships.');
                    throw new ApiException($message);
                }
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();
                throw new ApiException($message);
            }
            return $result;
        }

        /**
         * Create new model
         * @param array $data
         * @throws ApiException
         */
        protected function processCreate($data)
        {
            $modelClassName = $this->getModelName();
            try
            {
                if (isset($data['modelRelations']))
                {
                    $modelRelations = $data['modelRelations'];
                    unset($data['modelRelations']);
                }
                $model = $this->attemptToSaveModelFromData(new $modelClassName, $data, null, false);
                $id = $model->id;
                $model->forget();
                if (!count($model->getErrors()))
                {
                    if (isset($modelRelations) && count($modelRelations))
                    {
                        try
                        {
                            $this->manageModelRelations($model, $modelRelations);
                            $model->save();
                        }
                        catch (Exception $e)
                        {
                            $model->delete();
                            $message = $e->getMessage();
                            throw new ApiException($message);
                        }
                    }
                    $model = $modelClassName::getById($id);
                    $redBeanModelToApiDataUtil  = new RedBeanModelToApiDataUtil($model);
                    $data  = $redBeanModelToApiDataUtil->getData();
                    $result = new ApiResult(ApiResponse::STATUS_SUCCESS, $data, null, null);
                }
                else
                {
                    $errors = $model->getErrors();
                    $message = Yii::t('Default', 'Model was not created.');
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

        /**
         * Update model
         * @param int $id
         * @param array $data
         * @throws ApiException
         * @return ApiResult
         */
        protected function processUpdate($id, $data)
        {
            assert('is_int($id)');
            $modelClassName = $this->getModelName();

            if (isset($data['modelRelations']))
            {
                $modelRelations = $data['modelRelations'];
                unset($data['modelRelations']);
            }

            try
            {
                $model = $modelClassName::getById($id);
            }
            catch (NotFoundException $e)
            {
                $message = Yii::t('Default', 'The ID specified was invalid.');
                throw new ApiException($message);
            }

            try
            {
                ApiControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($model);
            }
            catch (SecurityException $e)
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
                    if (isset($modelRelations) && count($modelRelations))
                    {
                        try
                        {
                            $this->manageModelRelations($model, $modelRelations);
                            $model->save();
                        }
                        catch (Exception $e)
                        {
                            $message = Yii::t('Default', 'Model was updated, but there were issues with relations.');
                            $message .= ' ' . $e->getMessage();
                            throw new ApiException($message);
                        }
                    }

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

        /**
         *
         * @param RedBeanModel $model
         * @param array $modelRelations
         * @throws NotSupportedException
         * @throws FailedToSaveModelException
         * @throws ApiException
         */
        protected function manageModelRelations($model, $modelRelations)
        {
            try
            {
                if (isset($modelRelations) && !empty($modelRelations))
                {
                    foreach ($modelRelations as $modelRelation => $relations)
                    {
                        if ($model->isAttribute($modelRelation) &&
                            ($model->getRelationType($modelRelation) == RedBeanModel::HAS_MANY ||
                            $model->getRelationType($modelRelation) == RedBeanModel::MANY_MANY))
                        {
                            foreach ($relations as $relation)
                            {
                                $relatedModelClassName = $relation['modelClassName'];
                                try
                                {
                                    $relatedModel = $relatedModelClassName::getById(intval($relation['modelId']));
                                }
                                catch (Exception $e)
                                {
                                    $message = Yii::t('Default', 'The related model ID specified was invalid.');
                                    throw new NotFoundException($message);
                                }

                                if ($relation['action'] == 'add')
                                {
                                    $model->{$modelRelation}->add($relatedModel);
                                }
                                elseif ($relation['action'] == 'remove')
                                {
                                    $model->{$modelRelation}->remove($relatedModel);
                                }
                                else
                                {
                                    $message = Yii::t('Default', 'Unsupported action.');
                                    throw new NotSupportedException($message);
                                }
                            }
                        }
                        else
                        {
                            $message = Yii::t('Default', 'You can add relations only for HAS_MANY and MANY_MANY relations.');
                            throw new NotSupportedException($message);
                        }
                    }
                }
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();
                throw new ApiException($message);
            }
            return true;
        }

        /**
         * Delete model
         * @param int $id
         * @throws ApiException
         * @return ApiResult
         */
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
                $message = Yii::t('Default', 'The ID specified was invalid.');
                throw new ApiException($message);
            }

            try
            {
                ApiControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($model);
            }
            catch (SecurityException $e)
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
                $controllerUtil   = new ZurmoControllerUtil();
                $model            = $controllerUtil->saveModelFromSanitizedData($data, $model,
                                                                                $savedSucessfully, $modelToStringValue);
            }
            if ($savedSucessfully && $redirect)
            {
                $this->actionAfterSuccessfulModelSave($model, $modelToStringValue, $redirectUrlParams);
            }
            return $model;
        }
    }
?>