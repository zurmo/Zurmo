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
     * Form to work with the user attribute
     */
    class UserWorkflowActionAttributeForm extends WorkflowActionAttributeForm
    {
        const TYPE_DYNAMIC_CREATED_BY_USER          = 'DynamicCreatedByUser';

        const TYPE_DYNAMIC_MODIFIED_BY_USER         = 'DynamicModifiedByUser';

        const TYPE_DYNAMIC_TRIGGERED_BY_USER        = 'DynamicTriggeredByUser';

        const TYPE_DYNAMIC_OWNER_OF_TRIGGERED_MODEL = 'OwnerOfTriggeredModel';

        public function getValueElementType()
        {
            return 'UserNameId';
        }

        /**
         * Value can either be date or if dynamic, then it is an integer
         * @return bool
         */
        public function validateValue()
        {
            if (parent::validateValue())
            {
                if ($this->type == self::TYPE_STATIC)
                {
                    $validator             = CValidator::createValidator('type', $this, 'value', array('type' => 'integer'));
                    $validator->allowEmpty = false;
                    $validator->validate($this);
                    return !$this->hasErrors();
                }
                else
                {
                    if ($this->value != null)
                    {
                        $this->addError('value', Zurmo::t('WorkflowsModule', 'Value cannot be set'));
                        return false;
                    }
                    return true;
                }
            }
            return false;
        }

        public function getStringifiedModelForValue()
        {
            if ($this->value != null)
            {
                try
                {
                    return strval(User::getById((int)$this->value));
                }
                catch (NotFoundException $e)
                {
                }
            }
        }

        /**
         * Utilized to create or update model attribute values after a workflow's triggers are fired as true.
         * @param WorkflowActionProcessingModelAdapter $adapter
         * @param $attribute
         * @throws NotSupportedException
         */
        public function resolveValueAndSetToModel(WorkflowActionProcessingModelAdapter $adapter, $attribute)
        {
            assert('is_string($attribute)');
            if ($this->type == WorkflowActionAttributeForm::TYPE_STATIC)
            {
                $adapter->getModel()->{$attribute} = User::getById((int)$this->value);
            }
            elseif ($this->type == self::TYPE_DYNAMIC_OWNER_OF_TRIGGERED_MODEL)
            {
                if ($adapter->getTriggeredModel() instanceof OwnedSecurableItem)
                {
                    $adapter->getModel()->{$attribute} = $adapter->getTriggeredModel()->owner;
                }
            }
            elseif ($this->type == self::TYPE_DYNAMIC_CREATED_BY_USER)
            {
                $adapter->getModel()->{$attribute} = $adapter->getTriggeredModel()->createdByUser;
            }
            elseif ($this->type == self::TYPE_DYNAMIC_MODIFIED_BY_USER)
            {
                $adapter->getModel()->{$attribute} = $adapter->getTriggeredModel()->modifiedByUser;
            }
            elseif ($this->type == self::TYPE_DYNAMIC_TRIGGERED_BY_USER)
            {
                $adapter->getModel()->{$attribute} = $adapter->getTriggeredByUser();
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        protected function makeTypeValuesAndLabels($isCreatingNewModel, $isRequired)
        {
            $data                      = array();
            $data[static::TYPE_STATIC] = Zurmo::t('WorkflowsModule', 'As');
            $modelClassName            = $this->modelClassName;
            $modelLabel = $modelClassName::getModelLabelByTypeAndLanguage('SingularLowerCase');
            if ($isCreatingNewModel)
            {
                if (is_subclass_of($modelClassName, 'OwnedSecurableItem'))
                {
                    $data[self::TYPE_DYNAMIC_OWNER_OF_TRIGGERED_MODEL] =
                        Zurmo::t('WorkflowsModule', 'As user who owns triggered {modelLabel}',
                                                   array('{modelLabel}' => $modelLabel));
                }
            }
            else
            {
                $data[self::TYPE_DYNAMIC_CREATED_BY_USER]   =
                    Zurmo::t('WorkflowsModule', 'As user who created triggered {modelLabel}',
                                               array('{modelLabel}' => $modelLabel));
                $data[self::TYPE_DYNAMIC_MODIFIED_BY_USER]  =
                    Zurmo::t('WorkflowsModule', 'As user who last modified triggered {modelLabel}',
                                               array('{modelLabel}' => $modelLabel));
                $data[self::TYPE_DYNAMIC_TRIGGERED_BY_USER] =
                    Zurmo::t('WorkflowsModule', 'As user who triggered action',
                                               array('{modelLabel}' => $modelLabel));
            }
            return $data;
        }
    }
?>