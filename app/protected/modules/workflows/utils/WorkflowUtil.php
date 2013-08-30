<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Helper class for working with Workflow objects
     */
    class WorkflowUtil
    {
        /**
         * @param $type
         * @return null | string
         */
        public static function renderNonEditableTypeStringContent($type)
        {
            assert('is_string($type)');
            $typesAndLabels = Workflow::getTypeDropDownArray();
            if (isset($typesAndLabels[$type]))
            {
                return $typesAndLabels[$type];
            }
        }

        /**
         * @param $moduleClassName
         * @return null | string
         */
        public static function renderNonEditableModuleStringContent($moduleClassName)
        {
            assert('is_string($moduleClassName)');
            $modulesAndLabels = Workflow::getWorkflowSupportedModulesAndLabelsForCurrentUser();
            if (isset($modulesAndLabels[$moduleClassName]))
            {
                return $modulesAndLabels[$moduleClassName];
            }
        }

        /**
         * @param string $moduleClassName
         * @param string $modelClassName
         * @param string $workflowType
         * @return array
         * @throws NotSupportedException
         */
        public static function resolveDataAndLabelsForTimeTriggerAvailableAttributes($moduleClassName, $modelClassName,
                                                                                     $workflowType)
        {
            assert('is_string($moduleClassName)');
            assert('is_string($modelClassName)');
            assert('is_string($workflowType)');
            $modelToWorkflowAdapter             = ModelRelationsAndAttributesToWorkflowAdapter::
                make($moduleClassName, $modelClassName, $workflowType);
            if (!$modelToWorkflowAdapter instanceof ModelRelationsAndAttributesToByTimeWorkflowAdapter)
            {
                throw new NotSupportedException();
            }
            $attributes     = $modelToWorkflowAdapter->getAttributesForTimeTrigger();
            $dataAndLabels  = array('' => Zurmo::t('Core', '(None)'));
            return array_merge($dataAndLabels, WorkflowUtil::renderDataAndLabelsFromAdaptedAttributes($attributes));
        }

        /**
         * Given an array of attributes generated from $modelToWorkflowAdapter->getAttributesForTimeTrigger()
         * return an array indexed by the attribute and the value is the label
         * @param array $attributes
         * @return array
         */
        public static function renderDataAndLabelsFromAdaptedAttributes($attributes)
        {
            assert('is_array($attributes)');
            $dataAndLabels = array();
            foreach ($attributes as $attribute => $data)
            {
                $dataAndLabels[$attribute] = $data['label'];
            }
            return $dataAndLabels;
        }

        /**
         * @param string $modelClassName
         * @param string $inferredRelationName
         * @param integer $inferredModelItemId
         * @return Array of models
         */
        public static function getModelsFilteredByInferredModel($modelClassName, $inferredRelationName, $inferredModelItemId)
        {
            assert('is_string($modelClassName)');
            assert('is_string($inferredRelationName)');
            assert('is_int($inferredModelItemId)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => $inferredRelationName,
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => $inferredModelItemId,
                ),
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter($modelClassName);
            $where = RedBeanModelDataProvider::makeWhere($modelClassName, $searchAttributeData, $joinTablesAdapter);
            return $modelClassName::getSubset($joinTablesAdapter, null, null, $where, null);
        }

        /**
         * @param string $relation
         * @param $model
         * @return array of models
         */
        public static function getInferredModelsByAtrributeAndModel($relation, $model)
        {
            assert('is_string($relation)');
            $realAttributeName      = ModelRelationsAndAttributesToWorkflowAdapter::
                                      resolveRealAttributeName($relation);
            $relationModelClassName = ModelRelationsAndAttributesToWorkflowAdapter::
                                      getInferredRelationModelClassName($relation);
            $relatedModels          = array();
            foreach ($model->{$realAttributeName} as $item)
            {
                try
                {
                    $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem($relationModelClassName);
                    $relatedModels[]           = $item->castDown(array($modelDerivationPathToItem));
                }
                catch (NotFoundException $e)
                {
                }
            }
            return $relatedModels;
        }

        /**
         * @param RedBeanModel $model
         * @param string $relation
         * @return Array of models
         */
        public static function resolveDerivedModels(RedBeanModel $model, $relation)
        {
            assert('is_string($relation)');
            $modelClassName       = $model->getDerivedRelationModelClassName($relation);
            $inferredRelationName = $model->getDerivedRelationViaCastedUpModelOpposingRelationName($relation);
            return                  WorkflowUtil::getModelsFilteredByInferredModel($modelClassName, $inferredRelationName,
                                    (int)$model->getClassId('Item'));
        }

        /**
         * Utilize this method when processing workflow triggers, actions, and alerts.  Sometimes an exception could be
         * thrown, but we don't want to stop execution. So we will just throw that exception into the log for now.
         * Some exceptions can just be because an action exists against a model that no longer exists, which can happen
         * if you are dealing with a by-time queue item for example.  In the future if we decide we need a better way
         * to handle this type of occurrence, we can alert this method.
         * @param Exception exception
         * @param string $category
         */
        public static function handleProcessingException(Exception $exception, $category)
        {
            assert('is_string($category)');
            $content = 'Exception class: ' . get_class($exception);
            if ($exception->getMessage() != null)
            {
                $content .= ' Thrown with message: ' . $exception->getMessage();
            }
            $content .= "\n" . $exception->getTraceAsString();
            Yii::log($content, CLogger::LEVEL_WARNING, $category, true);
        }
    }
?>