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
     * Activities Modules such as Meetings, Notes, and tasks
     * should extend this class to provide generic functionality
     * that is applicable to all activity modules.
     */
    abstract class ActivitiesModuleController extends ZurmoModuleController
    {
        /**
         * Override to handle the ActivityItemForm processing from POST.
         * @see ZurmoBaseController->attemptToSaveModelFromPost
         */
        protected function attemptToSaveModelFromPost($model, $redirectUrlParams = null)
        {
            assert('$model instanceof Activity');
            assert('$redirectUrlParams == null || is_array($redirectUrlParams) || is_string($redirectUrlParams)');
            $postVariableName = get_class($model);
            if (isset($_POST[$postVariableName]))
            {
                $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                     resolveByPostDataAndModelThenMake($_POST[$postVariableName], $model);
                $readyToUsePostData                = ExplicitReadWriteModelPermissionsUtil::
                                                     removeIfExistsFromPostData($_POST[$postVariableName]);
                $sanitizedPostData                 = PostUtil::sanitizePostByDesignerTypeForSavingModel($model,
                                                                $readyToUsePostData);
                $sanitizedOwnerPostData            = PostUtil::sanitizePostDataToJustHavingElementForSavingModel(
                                                                $sanitizedPostData, 'owner');
                $sanitizedPostDataWithoutOwner     = PostUtil::removeElementFromPostDataForSavingModel(
                                                                $sanitizedPostData, 'owner');
                $model->setAttributes($sanitizedPostData);
                $this->resolveModelsHasManyRelationsFromPost($model);
                if ($model->validate())
                {
                    $modelToStringValue = strval($model);
                    if ($sanitizedOwnerPostData != null)
                    {
                        $model->setAttributes($sanitizedOwnerPostData);
                    }
                    if ($model->validate(array('owner')) && $model->save(false))
                    {
                        if ($explicitReadWriteModelPermissions != null)
                        {
                            $success = ExplicitReadWriteModelPermissionsUtil::
                            resolveExplicitReadWriteModelPermissions($model, $explicitReadWriteModelPermissions);
                            //todo: handle if success is false, means adding/removing permissions save failed.
                        }
                        $this->actionAfterSuccessfulModelSave($model, $modelToStringValue, $redirectUrlParams);
                    }
                }
            }
            return $model;
        }

        protected function resolveModelsHasManyRelationsFromPost(& $model)
        {
            assert('$model instanceof Activity');
            $this->resolveModelsActivityItemsFromPost($model);
        }

        /**
         * Passing in a $model, process any activityItems that have to be removed, added, or changed.
         */
        protected function resolveModelsActivityItemsFromPost(& $model)
        {
            assert('$model instanceof Activity');
            if (isset($_POST['ActivityItemForm']))
            {
                $newRelationModelsIndexedByItemId = array();
                foreach ($_POST['ActivityItemForm'] as $modelClassName => $relationData)
                {
                    if (!empty($relationData['id']))
                    {
                        $aModel = $modelClassName::getById((int)$relationData['id']);
                        $newRelationModelsIndexedByItemId[$aModel->getClassId('Item')] = $aModel;
                    }
                    elseif (!empty($relationData['ids']))
                    {
                        $relationIds = explode(",", $relationData['ids']);  // Not Coding Standard
                        foreach ($relationIds as $relationIdFromElementStoringMultiples)
                        {
                            $aModel = $modelClassName::getById((int)$relationIdFromElementStoringMultiples);
                            $newRelationModelsIndexedByItemId[$aModel->getClassId('Item')] = $aModel;
                        }
                    }
                }
                if ($model->activityItems->count() > 0)
                {
                    $itemsToRemove = array();
                    foreach ($model->activityItems as $index => $existingItem)
                    {
                        if (!isset($newRelationModelsIndexedByItemId[$existingItem->getClassId('Item')]))
                        {
                            $itemsToRemove[] = $existingItem;
                        }
                        else
                        {
                            unset($newRelationModelsIndexedByItemId[$existingItem->getClassId('Item')]);
                        }
                    }
                    foreach ($itemsToRemove as $itemToRemove)
                    {
                        $model->activityItems->remove($itemToRemove);
                    }
                }
                //Now add missing activityItems.
                foreach ($newRelationModelsIndexedByItemId as $modelToAdd)
                {
                    $model->activityItems->add($modelToAdd);
                }
            }
        }

        /**
         * Override to handle the special scenario of relations for an activity. Since relations are done in the
         * ActivityItems, the relation information needs to handled in a specific way.
         * @see ZurmoModuleController->resolveNewModelByRelationInformation
         */
        protected function resolveNewModelByRelationInformation(    $model, $relationModelClassName,
                                                                    $relationModelId, $relationModuleId)
        {
            assert('$model instanceof Activity');
            assert('is_string($relationModelClassName)');
            assert('is_int($relationModelId)');
            assert('is_string($relationModuleId)');

            $metadata = Activity::getMetadata();
            if (in_array($relationModelClassName, $metadata['Activity']['activityItemsModelClassNames']))
            {
                $model->activityItems->add($relationModelClassName::getById((int)$relationModelId));
            }
            else
            {
                throw new NotSupportedException();
            }
            return $model;
        }
    }
?>