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
     * Helper class for processing actions that trigger
     */
    class WorkflowActionProcessingHelper
    {
        /**
         * @var ActionForWorkflowForm
         */
        protected $action;

        /**
         * @var RedBeanModel
         */
        protected $triggeredModel;

        /**
         * @var User
         */
        protected $triggeredByUser;

        /**
         * @var bool
         */
        protected $canSaveTriggeredModel;

        /**
         * @param ActionForWorkflowForm $action
         * @param RedBeanModel $triggeredModel
         * @param User $triggeredByUser
         * @param bool $canSaveTriggeredModel - when processing a @see ByTimeWorkflowInQueueJob the job will handle
         * saving the model, so when creating new models as a result of an action, there is no need here to save the
         * triggered model.  This parameter then would be set to false in that scenario.  Otherwise the triggered model
         * will be saved when necessary since it is assumed it will not be saved after this execution.
         */
        public function __construct(ActionForWorkflowForm $action, RedBeanModel $triggeredModel, User $triggeredByUser,
                                    $canSaveTriggeredModel = true)
        {
            assert('is_bool($canSaveTriggeredModel)');
            $this->action                = $action;
            $this->triggeredModel        = $triggeredModel;
            $this->triggeredByUser       = $triggeredByUser;
            $this->canSaveTriggeredModel = $canSaveTriggeredModel;
        }

        public function processUpdateSelfAction()
        {
            if ($this->action->type == ActionForWorkflowForm::TYPE_UPDATE_SELF)
            {
                self::processActionAttributesForActionBeforeSave($this->action, $this->triggeredModel,
                                                                 $this->triggeredByUser, $this->triggeredModel);
            }
        }

        public function processNonUpdateSelfAction()
        {
            if ($this->action->type == ActionForWorkflowForm::TYPE_UPDATE_RELATED)
            {
                self::processUpdateRelatedAction();
            }
            elseif ($this->action->type == ActionForWorkflowForm::TYPE_CREATE)
            {
                self::processCreateAction();
            }
            elseif ($this->action->type == ActionForWorkflowForm::TYPE_CREATE_RELATED)
            {
                self::processCreateRelatedAction();
            }
            else
            {
                throw new NotSupportedException('Invalid action type: ' . $this->action->type);
            }
        }

        /**
         * @param ActionForWorkflowForm $action
         * @param RedBeanModel $model
         * @param User $triggeredByUser
         * @param RedBeanModel $triggeredModel
         * @param boolean $create
         */
        protected static function processActionAttributesForActionBeforeSave(ActionForWorkflowForm $action,
                                                                   RedBeanModel $model,
                                                                   User $triggeredByUser,
                                                                   RedBeanModel $triggeredModel,
                                                                   $create = false)
        {
            assert('is_bool($create)');
            $processedAttributes = array();
            foreach ($action->getActionAttributes() as $attribute => $actionAttribute)
            {
                if ($actionAttribute->resolveValueBeforeSave() && $actionAttribute->shouldSetValue)
                {
                    if (null === $relation = ActionForWorkflowForm::resolveFirstRelationName($attribute))
                    {
                        $resolvedModel     = $model;
                        $resolvedAttribute = ActionForWorkflowForm::resolveRealAttributeName($attribute);
                    }
                    else
                    {
                        $resolvedModel     = $model->{$relation};
                        $resolvedAttribute = ActionForWorkflowForm::resolveRealAttributeName($attribute);
                    }
                    $adapter = new WorkflowActionProcessingModelAdapter($resolvedModel, $triggeredByUser, $triggeredModel);
                    $actionAttribute->resolveValueAndSetToModel($adapter, $resolvedAttribute);
                    $processedAttributes[] = $attribute;
                }
            }
            if ($create)
            {
                foreach ($action->resolveAllActionAttributeFormsAndLabelsAndSort() as $attribute => $actionAttribute)
                {
                    if (!in_array($attribute, $processedAttributes) && $actionAttribute->resolveValueBeforeSave() &&
                       $actionAttribute->shouldSetNullAlternativeValue())
                    {
                        if (null === $relation = ActionForWorkflowForm::resolveFirstRelationName($attribute))
                        {
                            $resolvedModel     = $model;
                            $resolvedAttribute = ActionForWorkflowForm::resolveRealAttributeName($attribute);
                            $adapter = new WorkflowActionProcessingModelAdapter($resolvedModel, $triggeredByUser, $triggeredModel);
                            $actionAttribute->resolveNullAlternativeValueAndSetToModel($adapter, $resolvedAttribute);
                        }
                    }
                }
            }
        }

        /**
         * @param ActionForWorkflowForm $action
         * @param RedBeanModel $model
         * @param User $triggeredByUser
         * @param RedBeanModel $triggeredModel
         */
        protected static function processActionAttributesForActionAfterSave(ActionForWorkflowForm $action,
                                                                            RedBeanModel $model,
                                                                            User $triggeredByUser,
                                                                            RedBeanModel $triggeredModel)
        {
            foreach ($action->getActionAttributes() as $attribute => $actionAttribute)
            {
                if (!$actionAttribute->resolveValueBeforeSave() && $actionAttribute->shouldSetValue)
                {
                    if (null === $relation = ActionForWorkflowForm::resolveFirstRelationName($attribute))
                    {
                        $resolvedModel     = $model;
                        $resolvedAttribute = ActionForWorkflowForm::resolveRealAttributeName($attribute);
                    }
                    else
                    {
                        $resolvedModel     = $model->{$relation};
                        $resolvedAttribute = ActionForWorkflowForm::resolveRealAttributeName($attribute);
                    }
                    $adapter = new WorkflowActionProcessingModelAdapter($resolvedModel, $triggeredByUser, $triggeredModel);
                    $actionAttribute->resolveValueAndSetToModel($adapter, $resolvedAttribute);
                }
            }
        }

        protected function processUpdateRelatedAction()
        {
            if ($this->action->relationFilter != ActionForWorkflowForm::RELATION_FILTER_ALL)
            {
                throw new NotSupportedException();
            }
            $modelClassName = get_class($this->triggeredModel);
            if ($this->triggeredModel->isADerivedRelationViaCastedUpModel($this->action->relation) &&
               $this->triggeredModel->getDerivedRelationType($this->action->relation) == RedBeanModel::MANY_MANY)
            {
                foreach (WorkflowUtil::resolveDerivedModels($this->triggeredModel, $this->action->relation) as $relatedModel)
                {
                    self::processActionAttributesForActionBeforeSave($this->action, $relatedModel, $this->triggeredByUser, $this->triggeredModel);
                    $saved = $relatedModel->save();
                    if (!$saved)
                    {
                        throw new FailedToSaveModelException();
                    }
                }
            }
            elseif ($this->triggeredModel->getInferredRelationModelClassNamesForRelation(
                        ModelRelationsAndAttributesToWorkflowAdapter::resolveRealAttributeName($this->action->relation)) !=  null)
            {
                foreach (WorkflowUtil::getInferredModelsByAtrributeAndModel($this->action->relation, $this->triggeredModel) as $relatedModel)
                {
                    self::processActionAttributesForActionBeforeSave($this->action, $relatedModel, $this->triggeredByUser, $this->triggeredModel);
                    $saved = $relatedModel->save();
                    if (!$saved)
                    {
                        throw new FailedToSaveModelException();
                    }
                }
            }
            elseif ($this->triggeredModel->{$this->action->relation} instanceof RedBeanMutableRelatedModels)
            {
                foreach ($this->triggeredModel->{$this->action->relation} as $relatedModel)
                {
                    self::processActionAttributesForActionBeforeSave($this->action, $relatedModel, $this->triggeredByUser, $this->triggeredModel);
                    $saved = $relatedModel->save();
                    if (!$saved)
                    {
                        throw new FailedToSaveModelException();
                    }
                }
            }
            elseif ($modelClassName::isRelationTypeAHasOneVariant($this->action->relation) &&
                  !$modelClassName::isOwnedRelation($this->action->relation))
            {
                $relatedModel = $this->triggeredModel->{$this->action->relation};
                self::processActionAttributesForActionBeforeSave($this->action, $relatedModel, $this->triggeredByUser, $this->triggeredModel);
                $saved = $relatedModel->save();
                if (!$saved)
                {
                    throw new FailedToSaveModelException();
                }
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        protected function processCreateAction()
        {
            if ($this->resolveCreateModel($this->triggeredModel, $this->action->relation) && $this->canSaveTriggeredModel)
            {
                $saved = $this->triggeredModel->save();
                if (!$saved)
                {
                    throw new FailedToSaveModelException();
                }
            }
        }

        /**
         * @param RedBeanModel $model
         * @param $relation
         * @param null $modelToForgetCache
         * @return bool true if the $model passed in needs to be saved again. Otherwise false if not.
         * @throws NotSupportedException
         * @throws FailedToSaveModelException
         */
        protected function resolveCreateModel(RedBeanModel $model, $relation, & $modelToForgetCache = null)

        {
            assert('is_string($relation)');
            $modelClassName = get_class($model);
            if ($model->isADerivedRelationViaCastedUpModel($relation) &&
                $model->getDerivedRelationType($relation) == RedBeanModel::MANY_MANY)
            {
                $relationModelClassName = $model->getDerivedRelationModelClassName($relation);
                $inferredRelationName   = $model->getDerivedRelationViaCastedUpModelOpposingRelationName($relation);
                $newModel               = new $relationModelClassName();
                self::processActionAttributesForActionBeforeSave($this->action, $newModel, $this->triggeredByUser, $this->triggeredModel, true);
                $newModel->{$inferredRelationName}->add($model);
                $saved = $newModel->save();
                if (!$saved)
                {
                    throw new FailedToSaveModelException();
                }
                self::processActionAttributesForActionAfterSave($this->action, $newModel, $this->triggeredByUser, $this->triggeredModel);
                return false;
            }
            elseif ($model->getInferredRelationModelClassNamesForRelation(
                ModelRelationsAndAttributesToWorkflowAdapter::resolveRealAttributeName($relation)) !=  null)
            {
                $relationModelClassName = ModelRelationsAndAttributesToWorkflowAdapter::
                                          getInferredRelationModelClassName($relation);
                $newModel               = new $relationModelClassName();
                self::processActionAttributesForActionBeforeSave($this->action, $newModel, $this->triggeredByUser, $this->triggeredModel, true);
                $saved = $newModel->save();
                if (!$saved)
                {
                    throw new FailedToSaveModelException();
                }
                self::processActionAttributesForActionAfterSave($this->action, $newModel, $this->triggeredByUser, $this->triggeredModel);
                $model->{ModelRelationsAndAttributesToWorkflowAdapter::resolveRealAttributeName($relation)}->add($newModel);
                return true;
            }
            elseif ($model->$relation instanceof RedBeanMutableRelatedModels)
            {
                $relationModelClassName = $model->getRelationModelClassName($relation);
                $newModel               = new $relationModelClassName();
                self::processActionAttributesForActionBeforeSave($this->action, $newModel, $this->triggeredByUser, $this->triggeredModel, true);
                $saved = $newModel->save();
                if (!$saved)
                {
                    throw new FailedToSaveModelException();
                }
                self::processActionAttributesForActionAfterSave($this->action, $newModel, $this->triggeredByUser, $this->triggeredModel);
                $model->{$relation}->add($newModel);
                $modelToForgetCache = $newModel;
                return true;
            }
            elseif ($modelClassName::isRelationTypeAHasOneVariant($relation) &&
                   !$modelClassName::isOwnedRelation($relation))
            {
                $relatedModel = $model->{$relation};
                if ($relatedModel->id > 0)
                {
                    return;
                }
                self::processActionAttributesForActionBeforeSave($this->action, $relatedModel, $this->triggeredByUser, $this->triggeredModel, true);
                if (!$relatedModel->save())
                {
                    throw new FailedToSaveModelException();
                }
                self::processActionAttributesForActionAfterSave($this->action, $relatedModel, $this->triggeredByUser, $this->triggeredModel);
                return true;
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        /**
         * Notice the use of $modelToForgetCache. This was needed to avoid a caching issue with the following example.
         * If an opportunity fires, and a related account's opportunity is created. This new opportunity had a cached
         * model for account that was null.  So this is fixed by forgetting the new model after it is added to the account.
         * @throws FailedToSaveModelException
         * @throws NotSupportedException
         */
        protected function processCreateRelatedAction()
        {
            if ($this->action->relationFilter != ActionForWorkflowForm::RELATION_FILTER_ALL)
            {
                throw new NotSupportedException();
            }
            $modelClassName = get_class($this->triggeredModel);
            if ($this->triggeredModel->isADerivedRelationViaCastedUpModel($this->action->relation) &&
                $this->triggeredModel->getDerivedRelationType($this->action->relation) == RedBeanModel::MANY_MANY)
            {
                foreach (WorkflowUtil::resolveDerivedModels($this->triggeredModel, $this->action->relation) as $relatedModel)
                {
                    if ($this->resolveCreateModel($relatedModel, $this->action->relatedModelRelation))
                    {
                        $saved = $relatedModel->save();
                        if (!$saved)
                        {
                            throw new FailedToSaveModelException();
                        }
                    }
                }
            }
            elseif ($this->triggeredModel->getInferredRelationModelClassNamesForRelation(
                ModelRelationsAndAttributesToWorkflowAdapter::resolveRealAttributeName($this->action->relation)) !=  null)
            {
                foreach (WorkflowUtil::getInferredModelsByAtrributeAndModel($this->action->relation, $this->triggeredModel) as $relatedModel)
                {
                    if ($this->resolveCreateModel($relatedModel, $this->action->relatedModelRelation))
                    {
                        $saved = $relatedModel->save();
                        if (!$saved)
                        {
                            throw new FailedToSaveModelException();
                        }
                    }
                }
            }
            elseif ($this->triggeredModel->{$this->action->relation} instanceof RedBeanMutableRelatedModels)
            {
                foreach ($this->triggeredModel->{$this->action->relation} as $relatedModel)
                {
                    if ($this->resolveCreateModel($relatedModel, $this->action->relatedModelRelation))
                    {
                        $saved = $relatedModel->save();
                        if (!$saved)
                        {
                            throw new FailedToSaveModelException();
                        }
                    }
                }
            }
            elseif ($modelClassName::isRelationTypeAHasOneVariant($this->action->relation) &&
                   !$modelClassName::isOwnedRelation($this->action->relation))
            {
                $relatedModel = $this->triggeredModel->{$this->action->relation};
                $modelToForgetCache = null;
                if ($this->resolveCreateModel($relatedModel, $this->action->relatedModelRelation, $modelToForgetCache))
                {
                    $saved = $relatedModel->save();
                    if (!$saved)
                    {
                        throw new FailedToSaveModelException();
                    }
                    if($modelToForgetCache instanceof RedBeanModel)
                    {
                        $modelToForgetCache->forget();
                    }
                }
            }
            else
            {
                throw new NotSupportedException();
            }
        }
    }
?>