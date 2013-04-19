<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Helper class for adapting relation and attribute data into tree data
     */
    class WorkflowRelationsAndAttributesToTreeAdapter extends WizardModelRelationsAndAttributesToTreeAdapter
    {
        /**
         * @var Workflow
         */
        protected $workflow;

        /**
         * @param Workflow $workflow
         * @param string $treeType
         */
        public function __construct(Workflow $workflow, $treeType)
        {
            assert('is_string($treeType)');
            $this->workflow = $workflow;
            $this->treeType = $treeType;
        }

        /**
         * @param string $nodeId
         * @return array
         */
        public function getData($nodeId)
        {
            assert('is_string($nodeId)');
            $nodeId                   = $this->resolveNodeIdByRemovingTreeType($nodeId);
            $moduleClassName          = $this->workflow->getModuleClassName();
            $modelToWorkflowAdapter     = $this->makeModelRelationsAndAttributesToWorkflowAdapter(
                                        $moduleClassName, $moduleClassName::getPrimaryModelName());
            $nodeIdPrefix             = self::resolveNodeIdPrefixByNodeId($nodeId);
            $precedingModel           = null;
            $precedingRelation        = null;
            if ($nodeId != 'source')
            {
                self::resolvePrecedingModelRelationAndAdapterByNodeId($nodeId, $modelToWorkflowAdapter, $precedingModel,
                                                                      $precedingRelation);
            }
            else
            {
                $nodeIdPrefix = null;
            }
            if ($nodeIdPrefix == null)
            {
                $data                       = array();
                $data[0]                    = array('expanded' => true,
                                                    'text'      => $moduleClassName::getModuleLabelByTypeAndLanguage('Singular'));
            }
            $childrenNodeData               = $this->getChildrenNodeData($modelToWorkflowAdapter, $precedingModel,
                                                                         $precedingRelation, $nodeIdPrefix);
            if (!empty($childrenNodeData) && $nodeIdPrefix == null)
            {
                $data[0]['children'] = $childrenNodeData;
            }
            else
            {
                $data                = $childrenNodeData;
            }
            return $data;
        }

        /**
         * @param ModelRelationsAndAttributesToWorkflowAdapter $modelToWorkflowAdapter
         * @param RedBeanModel $precedingModel
         * @param null|string $precedingRelation
         * @param null|string $nodeIdPrefix
         * @return array
         * @throws NotSupportedException if one of the relations for the selectable data does not have a module class name
         * defined
         */
        protected function getChildrenNodeData(ModelRelationsAndAttributesToWorkflowAdapter $modelToWorkflowAdapter,
                                               RedBeanModel $precedingModel = null,
                                               $precedingRelation = null, $nodeIdPrefix = null)
        {
            $childrenNodeData        = array();
            $attributesData = $this->getAttributesData($modelToWorkflowAdapter, $precedingModel, $precedingRelation);
            foreach ($attributesData as $attribute => $attributeData)
            {
                $attributeNode      = array('id'           => self::makeNodeId($attribute, $nodeIdPrefix),
                                            'text'         => $attributeData['label'],
                                            'wrapperClass' => 'item-to-place');
                $childrenNodeData[] = $attributeNode;
            }
            $selectableRelationsData         = $modelToWorkflowAdapter->
                                               getSelectableRelationsData($precedingModel, $precedingRelation);
            $resolvedSelectableRelationsData = $modelToWorkflowAdapter->
                                                getSelectableRelationsDataResolvedForUserAccess(
                                                Yii::app()->user->userModel,
                                                $selectableRelationsData);
            foreach ($resolvedSelectableRelationsData as $relation => $relationData)
            {
                $relationModelClassName       = $modelToWorkflowAdapter->getRelationModelClassName($relation);
                $relationModuleClassName      = $relationModelClassName::getModuleClassName();
                if ($relationModuleClassName == null)
                {
                    throw new NotSupportedException($relationModelClassName);
                }
                $relationNode = array('id'          => self::makeNodeId($relation, $nodeIdPrefix),
                                     'text'         => $relationData['label'],
                                     'expanded'     => false,
                                     'hasChildren'  => true);
                $childrenNodeData[]           = $relationNode;
            }
            return $childrenNodeData;
        }

        /**
         * @param ModelRelationsAndAttributesToWorkflowAdapter $modelToWorkflowAdapter
         * @param RedBeanModel $precedingModel
         * @param null|string $precedingRelation
         * @return array
         * @throws NotSupportedException if the treeType is invalid or null
         */
        protected function getAttributesData(ModelRelationsAndAttributesToWorkflowAdapter $modelToWorkflowAdapter,
                                             RedBeanModel $precedingModel = null, $precedingRelation = null)
        {
            if ($this->treeType == ComponentForWorkflowForm::TYPE_TRIGGERS)
            {
                return $modelToWorkflowAdapter->getAttributesForTriggers($precedingModel, $precedingRelation);
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        /**
         * @param string $moduleClassName
         * @param string $modelClassName
         * @return ModelRelationsAndAttributesToWorkflowAdapter based object
         */
        protected function makeModelRelationsAndAttributesToWorkflowAdapter($moduleClassName, $modelClassName)
        {
            assert('is_string($moduleClassName)');
            assert('is_string($modelClassName)');
            return ModelRelationsAndAttributesToWorkflowAdapter::make($moduleClassName, $modelClassName,
                                                                    $this->workflow->getType());
        }

        /**
         * @param string $nodeId
         * @param ModelRelationsAndAttributesToWorkflowAdapter $modelToWorkflowAdapter
         * @param RedBeanModel $precedingModel
         * @param string $precedingRelation
         */
        protected function resolvePrecedingModelRelationAndAdapterByNodeId(
                           $nodeId, & $modelToWorkflowAdapter, & $precedingModel, & $precedingRelation)
        {
            assert('$modelToWorkflowAdapter instanceof ModelRelationsAndAttributesToWorkflowAdapter');
            if ($nodeId == 'source')
            {
                return;
            }
            $relations    = explode(FormModelUtil::RELATION_DELIMITER, $nodeId);
            $lastRelation = end($relations);
            foreach ($relations as $relation)
            {
                $relationModelClassName = $modelToWorkflowAdapter->getRelationModelClassName($relation);
                $precedingRelation      = $relation;
                if ($relation != $lastRelation)
                {
                    $precedingModel    = new $relationModelClassName(false);
                }
                elseif (count($relations) == 1)
                {
                    $precedingModel    = $modelToWorkflowAdapter->getModel();
                }
                $modelToWorkflowAdapter  = $this->makeModelRelationsAndAttributesToWorkflowAdapter(
                                           $relationModelClassName::getModuleClassName(), $relationModelClassName);
            }
        }
    }
?>