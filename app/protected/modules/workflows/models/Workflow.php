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
     * Class for interacting with Workflow definitions.  Gets information from either a SavedWorkflow or via a POST.
     * Contains information about how a workflow should be constructed including how it looks in the user interface
     * when run.  The components of a workflow are a time trigger, triggers, actions, and email messages
     *
     * There are 2 different types of workflows: TYPE_ON_SAVE and TYPE_BY_TIME
     */
    class Workflow extends CComponent
    {
        const TYPE_ON_SAVE                    = 'OnSave';

        const TYPE_BY_TIME                    = 'ByTime';

        const TRIGGER_ON_NEW                  = 'New';

        const TRIGGER_ON_NEW_AND_EXISTING     = 'NewAndExisting';

        const TRIGGER_ON_EXISTING             = 'Existing';

        private $description;

        /**
         * Id of the saved workflow if it has already been saved
         * @var integer
         */
        private $id;

        /**
         * If the workflow is active or not, if not it will not be fired during processing
         * @var boolean
         */
        private $isActive;

        /**
         * @var string
         */
        private $moduleClassName;

        /**
         * @var string
         */
        private $name;

        /**
         * The firing order of workflow when processing.
         * @var integer
         */
        private $order;

        /**
         * Workflows can be fired either only when a new model exists, only an existing model, or both cases
         * @var string
         */
        private $triggerOn;

        /**
         * TYPE_ON_SAVE or TYPE_BY_TIME
         * @var string
         */
        private $type;

        /**
         * @var string
         */
        private $triggersStructure;

        /**
         * Defines the attribute that the time trigger fires on.
         * @var string
         */
        private $timeTriggerAttribute;

        /**
         * @var TimeTriggerForWorkflowForm, used when the type is TYPE_BY_TIME
         */
        private $timeTrigger;

        /**
         * @var array of TriggerForWorkflowForm models
         */
        private $triggers                          = array();

        /**
         * @var array of ActionForWorkflowForm models
         */
        private $actions                           = array();

        /**
         * @var array of EmailMessageForWorkflowForm models
         */
        private $emailMessages                       = array();

        /**
         * @var bool
         */
        private $timeTriggerRequireChangeToProcess   = true;

        /**
         * @return array
         */
        public static function getTypeDropDownArray()
        {
            return array(self::TYPE_ON_SAVE  => Yii::t('Default', 'On-Save'),
                         self::TYPE_BY_TIME  => Yii::t('Default', 'Time-Based'));
        }

        /**
         * Based on the current user, return the workflow supported modules and their display labels.  Only include modules
         * that the user has a right to access.
         * @return array of module class names and display labels.
         */
        public static function getWorkflowSupportedModulesAndLabelsForCurrentUser()
        {
            $moduleClassNamesAndLabels = array();
            $modules = Module::getModuleObjects();
            foreach (self::getWorkflowSupportedModulesClassNamesCurrentUserHasAccessTo() as $moduleClassName)
            {
                if ($moduleClassName::getStateMetadataAdapterClassName() != null)
                {
                    $workflowRules = WorkflowRules::makeByModuleClassName($moduleClassName);
                    $label         = $workflowRules->getVariableStateModuleLabel(Yii::app()->user->userModel);
                }
                else
                {
                    $label = $moduleClassName::getModuleLabelByTypeAndLanguage('Plural');
                }
                if ($label != null)
                {
                    $moduleClassNamesAndLabels[$moduleClassName] = $label;
                }
            }
            return $moduleClassNamesAndLabels;
        }

        /**
         * @return array
         */
        public static function getWorkflowSupportedModulesClassNamesCurrentUserHasAccessTo()
        {
            $moduleClassNames = array();
            $modules = Module::getModuleObjects();
            foreach ($modules as $module)
            {
                if ($module::canHaveWorkflow())
                {
                    if (WorkflowSecurityUtil::canCurrentUserCanAccessModule(get_class($module)))
                    {
                        $moduleClassNames[] = get_class($module);
                    }
                }
            }
            return $moduleClassNames;
        }

        /**
         * @return string
         */
        public function __toString()
        {
            if (trim($this->name) == '')
            {
                return Yii::t('Default', '(Unnamed)');
            }
            return $this->name;
        }

        /**
         * @return mixed
         */
        public function getDescription()
        {
            return $this->description;
        }

        /**
         * @param $description
         */
        public function setDescription($description)
        {
            assert('is_string($description)');
            $this->description = $description;
        }

        /**
         * @return bool
         */
        public function getIsActive()
        {
            return $this->isActive;
        }

        /**
         * @param bool $isActive
         */
        public function setIsActive($isActive)
        {
            assert('is_bool($isActive)');
            $this->isActive = $isActive;
        }

        /**
         * @return string
         */
        public function getModuleClassName()
        {
            return $this->moduleClassName;
        }

        /**
         * @param $moduleClassName
         */
        public function setModuleClassName($moduleClassName)
        {
            assert('is_string($moduleClassName)');
            $this->moduleClassName = $moduleClassName;
        }

        /**
         * @return int
         */
        public function getOrder()
        {
            return $this->order;
        }

        /**
         * @param integer $order
         */
        public function setOrder($order)
        {
            assert('is_int($order)');
            $this->order = $order;
        }

        /**
         * @return string
         */
        public function getTriggerOn()
        {
            return $this->triggerOn;
        }

        /**
         * @param string $triggerOn
         */
        public function setTriggerOn($triggerOn)
        {
            assert('$triggerOn == self::TRIGGER_ON_NEW || $triggerOn == self::TRIGGER_ON_NEW_AND_EXISTING ||
                    $triggerOn == self::TRIGGER_ON_EXISTING');
            $this->triggerOn = $triggerOn;
        }

        /**
         * @param string $triggersStructure
         */
        public function setTriggersStructure($triggersStructure)
        {
            assert('is_string($triggersStructure)');
            $this->triggersStructure = $triggersStructure;
        }

        /**
         * @return string
         */
        public function getTriggersStructure()
        {
            return $this->triggersStructure;
        }

        /**
         * @return int
         */
        public function getId()
        {
            return $this->id;
        }

        /**
         * @param integer $id
         */
        public function setId($id)
        {
            assert('is_int($id)');
            $this->id = $id;
        }

        /**
         * @return string
         */
        public function getName()
        {
            return $this->name;
        }

        /**
         * @param $name
         */
        public function setName($name)
        {
            assert('is_string($name)');
            $this->name = $name;
        }

        /**
         * @return string
         */
        public function getType()
        {
            return $this->type;
        }

        /**
         * @param $type
         */
        public function setType($type)
        {
            assert('$type == self::TYPE_ON_SAVE || $type == self::TYPE_BY_TIME');
            $this->type = $type;
        }

        /**
         * @return bool
         */
        public function isNew()
        {
            if ($this->id > 0)
            {
                return false;
            }
            return true;
        }

        /**
         * @param string $timeTriggerAttribute
         */
        public function setTimeTriggerAttribute($timeTriggerAttribute)
        {
            assert('is_string($timeTriggerAttribute)');
            $this->timeTriggerAttribute = $timeTriggerAttribute;
        }

        /**
         * @return string
         */
        public function getTimeTriggerAttribute()
        {
            return $this->timeTriggerAttribute;
        }

        /**
         * @return TimeTriggerForWorkflowForm
         */
        public function getTimeTrigger()
        {
            return $this->timeTrigger;
        }

        /**
         * @param TimeTriggerForWorkflowForm $timeTrigger
         */
        public function setTimeTrigger(TimeTriggerForWorkflowForm $timeTrigger)
        {
            $this->timeTrigger = $timeTrigger;
        }

        /**
         * Resets timeTrigger to null
         */
        public function removeTimeTrigger()
        {
            $this->timeTrigger = null;
        }

        /**
         * @return array
         */
        public function getTriggers()
        {
            return $this->triggers;
        }

        /**
         * @param TriggerForWorkflowForm $trigger
         */
        public function addTrigger(TriggerForWorkflowForm $trigger)
        {
            $this->triggers[] = $trigger;
        }

        /**
         * Resets triggers to an empty array
         */
        public function removeAllTriggers()
        {
            $this->triggers   = array();
        }

        /**
         * @return array
         */
        public function getActions()
        {
            return $this->actions;
        }

        /**
         * @param ActionForWorkflowForm $action
         */
        public function addAction(ActionForWorkflowForm $action)
        {
            $this->actions[] = $action;
        }

        /**
         * Resets actions to an empty array
         */
        public function removeAllActions()
        {
            $this->actions   = array();
        }

        /**
         * @return array
         */
        public function getEmailMessages()
        {
            return $this->emailMessages;
        }

        /**
         * @param EmailMessageForWorkflowForm $emailMessage
         */
        public function addEmailMessage(EmailMessageForWorkflowForm $emailMessage)
        {
            $this->emailMessages[] = $emailMessage;
        }

        /**
         * Resets emailMessages to an empty array
         */
        public function removeAllEmailMessages()
        {
            $this->emailMessages   = array();
        }

        /**
         * @return bool
         */
        public function doesTimeTriggerRequireChangeToProcess()
        {
            return $this->timeTriggerRequireChangeToProcess;
        }

        /**
         * When processing ByTime workflow in @see ByTimeWorkflowInQueueJob this should be changed to false
         * so the time trigger can be evaluated correctly.
         */
        public function setTimeTriggerRequireChangeToProcessToFalse()
        {
            $this->timeTriggerRequireChangeToProcess = false;
        }
    }
?>