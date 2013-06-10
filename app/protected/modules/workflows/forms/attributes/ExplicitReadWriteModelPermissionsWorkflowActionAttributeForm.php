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
     * Form to work with the explicit permissions on a model
     */
    class ExplicitReadWriteModelPermissionsWorkflowActionAttributeForm extends WorkflowActionAttributeForm
    {
        const TYPE_DYNAMIC_SAME_AS_TRIGGERED_MODEL  = 'SameAsTriggeredModel';

        const TYPE_DYNAMIC_OWNER                    = 'Owner';

        const TYPE_DYNAMIC_EVERYONE_GROUP           = 'EveryoneGroup';

        public function getValueElementType()
        {
            return null;
        }

        /**
         * Returns false so it resolves value afterSave
         * @return bool
         */
        public static function resolveValueBeforeSave()
        {
            return false;
        }

        /**
         * Utilized to create or update model attribute values after a workflow's triggers are fired as true.
         * Currently only works with creating new and creating new related models. Not designed to support updating
         * existing models.
         * @param WorkflowActionProcessingModelAdapter $adapter
         * @param $attribute
         * @throws FailedToResolveExplicitReadWriteModelPermissionsException
         * @throws NotSupportedException
         */
        public function resolveValueAndSetToModel(WorkflowActionProcessingModelAdapter $adapter, $attribute)
        {
            assert('is_string($attribute)');
            if ($adapter->getModel()->id < 0)
            {
                throw new NotSupportedException();
            }
            if ($this->type == self::TYPE_DYNAMIC_SAME_AS_TRIGGERED_MODEL)
            {
                $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($adapter->getTriggeredModel());
            }
            elseif ($this->type == self::TYPE_DYNAMIC_OWNER)
            {
                //Do nothing, by default this will take.
                return;
            }
            elseif ($this->type == self::TYPE_DYNAMIC_EVERYONE_GROUP)
            {
                $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($adapter->getModel());
                $explicitReadWriteModelPermissions->addReadWritePermitable(Group::getByName(Group::EVERYONE_GROUP_NAME));
            }
            else
            {
                $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($adapter->getModel());
                try
                {
                    $group = Group::getById((int)$this->type);
                    $explicitReadWriteModelPermissions->addReadWritePermitable($group);
                }
                catch (NotFoundException $e)
                {
                    //todo: handle exception better
                    return;
                }
            }

            $success = ExplicitReadWriteModelPermissionsUtil::
                       resolveExplicitReadWriteModelPermissions($adapter->getModel(), $explicitReadWriteModelPermissions);
            if (!$success)
            {
                throw new FailedToResolveExplicitReadWriteModelPermissionsException();
            }
        }

        protected function makeTypeValuesAndLabels($isCreatingNewModel, $isRequired)
        {
            $data                      = array();
            if (!$isCreatingNewModel)
            {
                throw new NotSupportedException();
            }
            $data[self::TYPE_DYNAMIC_SAME_AS_TRIGGERED_MODEL] = Zurmo::t('WorkflowsModule', 'Same as triggered record');
            $data[self::TYPE_DYNAMIC_OWNER]                   = Zurmo::t('ZurmoModule', 'Owner');
            $groups = ExplicitReadWriteModelPermissionsElement::getSelectableGroupsData();
            foreach ($groups as $id => $name)
            {
                $data[$id]  = Zurmo::t('Zurmo', 'Owner and users in {groupName}', array('{groupName}' => $name));
            }
            $data[self::TYPE_DYNAMIC_EVERYONE_GROUP]          = Zurmo::t('ZurmoModule', 'Everyone');
            return $data;
        }
    }
?>