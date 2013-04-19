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
     * Helper class to adapt a SavedWorkflow object to a Workflow object and vice-versa
     */
    class SavedWorkflowToWorkflowAdapter
    {
        /**
         * @param SavedWorkflow $savedWorkflow
         * @return Workflow
         */
        public static function makeWorkflowBySavedWorkflow(SavedWorkflow $savedWorkflow)
        {
            $workflow = new Workflow();
            if ($savedWorkflow->id > 0)
            {
                $workflow->setId((int)$savedWorkflow->id);
            }
            $workflow->setDescription       ($savedWorkflow->description);
            $workflow->setIsActive          ((bool)$savedWorkflow->isActive);
            $workflow->setModuleClassName   ($savedWorkflow->moduleClassName);
            $workflow->setName              ($savedWorkflow->name);
            $workflow->setOrder             ((int)$savedWorkflow->order);
            $workflow->setType              ($savedWorkflow->type);
            $workflow->setTriggerOn         ($savedWorkflow->triggerOn);
            if ($savedWorkflow->serializedData != null)
            {
                $unserializedData = unserialize($savedWorkflow->serializedData);
                if (isset($unserializedData['triggersStructure']))
                {
                    $workflow->setTriggersStructure($unserializedData['triggersStructure']);
                }
                self::makeComponentFormAndPopulateWorkflowFromData(
                            $unserializedData[ComponentForWorkflowForm::TYPE_TRIGGERS], $workflow, 'Trigger');
                self::makeActionForWorkflowFormAndPopulateWorkflowFromData(
                            $unserializedData[ComponentForWorkflowForm::TYPE_ACTIONS],  $workflow);
                self::makeEmailMessageForWorkflowFormAndPopulateWorkflowFromData(
                    $unserializedData[ComponentForWorkflowForm::TYPE_EMAIL_MESSAGES],  $workflow);
                if (isset($unserializedData[ComponentForWorkflowForm::TYPE_TIME_TRIGGER]))
                {
                    $moduleClassName = $workflow->getModuleClassName();
                    $timeTrigger     = new TimeTriggerForWorkflowForm($moduleClassName,
                                                                      $moduleClassName::getPrimaryModelName(),
                                                                      $workflow->getType());
                    $timeTrigger->setAttributes($unserializedData[ComponentForWorkflowForm::TYPE_TIME_TRIGGER]);
                    $workflow->setTimeTrigger($timeTrigger);
                    $workflow->setTimeTriggerAttribute($timeTrigger->getAttributeIndexOrDerivedType());
                }
            }
            return $workflow;
        }

        /**
         * @param Workflow $workflow
         * @param SavedWorkflow $savedWorkflow
         */
        public static function resolveWorkflowToSavedWorkflow(Workflow $workflow, SavedWorkflow $savedWorkflow)
        {
            $savedWorkflow->description     = $workflow->getDescription();
            $savedWorkflow->isActive        = $workflow->getIsActive();
            $savedWorkflow->moduleClassName = $workflow->getModuleClassName();
            $savedWorkflow->name            = $workflow->getName();
            $savedWorkflow->order           = $workflow->getOrder();
            $savedWorkflow->triggerOn       = $workflow->getTriggerOn();
            $savedWorkflow->type            = $workflow->getType();
            $data = array();
            $data['triggersStructure']      = $workflow->getTriggersStructure();
            $data[ComponentForWorkflowForm::TYPE_TRIGGERS]                     =
                  self::makeArrayFromComponentFormsAttributesData($workflow->getTriggers());
            $data[ComponentForWorkflowForm::TYPE_ACTIONS]                      =
                  self::makeArrayFromActionForWorkflowFormAttributesData($workflow->getActions());
            $data[ComponentForWorkflowForm::TYPE_EMAIL_MESSAGES]                      =
                self::makeArrayFromEmailMessageForWorkflowFormAttributesData($workflow->getEmailMessages());
            if ($workflow->getTimeTrigger() != null)
            {
                $data[ComponentForWorkflowForm::TYPE_TIME_TRIGGER] = self::makeArrayFromTimeTriggerForWorkflowFormAttributesData(
                                       $workflow->getTimeTrigger());
            }
            $savedWorkflow->serializedData   = serialize($data);
        }

        /**
         * @param array $componentFormsData
         * @return array
         */
        public static function makeArrayFromEmailMessageForWorkflowFormAttributesData(Array $componentFormsData)
        {
            $data = array();
            foreach ($componentFormsData as $key => $emailMessageForWorkflowForm)
            {
                foreach ($emailMessageForWorkflowForm->getAttributes() as $attribute => $value)
                {
                    $data[$key][$attribute] = $value;
                }
                foreach ($emailMessageForWorkflowForm->getEmailMessageRecipients() as
                        $emailMessageRecipientKey => $workflowEmailMessageRecipientForm)
                {
                    foreach ($workflowEmailMessageRecipientForm->getAttributes() as $attribute => $value)
                    {
                        $data[$key][EmailMessageForWorkflowForm::EMAIL_MESSAGE_RECIPIENTS]
                             [$emailMessageRecipientKey][$attribute] = $value;
                    }
                }
            }
            return $data;
        }

        /**
         * @param TimeTriggerForWorkflowForm $timeTriggerForWorkflowForm
         * @return array
         */
        protected static function makeArrayFromTimeTriggerForWorkflowFormAttributesData(
                                  TimeTriggerForWorkflowForm $timeTriggerForWorkflowForm)
        {
            $data = array();
            foreach ($timeTriggerForWorkflowForm->getAttributes() as $attribute => $value)
            {
                if ($attribute != 'stringifiedModelForValue')
                {
                    $data[$attribute] = $value;
                }
            }
            return $data;
        }

        /**
         * @param array $componentFormsData
         * @return array
         */
        protected static function makeArrayFromComponentFormsAttributesData(Array $componentFormsData)
        {
            $data = array();
            foreach ($componentFormsData as $key => $componentForm)
            {
                foreach ($componentForm->getAttributes() as $attribute => $value)
                {
                    $data[$key][$attribute] = $value;
                }
            }
            return $data;
        }

        /**
         * @param array $componentFormsData
         * @return array
         */
        protected static function makeArrayFromActionForWorkflowFormAttributesData(Array $componentFormsData)
        {
            $data = array();
            foreach ($componentFormsData as $key => $actionForWorkflowForm)
            {
                foreach ($actionForWorkflowForm->getAttributes() as $attribute => $value)
                {
                    $data[$key][$attribute] = $value;
                }
                foreach ($actionForWorkflowForm->getActionAttributes() as $actionAttribute => $workflowActionAttributeForm)
                {
                    foreach ($workflowActionAttributeForm->getAttributes() as $attribute => $value)
                    {
                        $data[$key][ActionForWorkflowForm::ACTION_ATTRIBUTES][$actionAttribute][$attribute] = $value;
                    }
                }
            }
            return $data;
        }

        /**
         * @param array $componentFormsData
         * @param Workflow $workflow
         * @param string $componentPrefix
         */
        protected static function makeComponentFormAndPopulateWorkflowFromData($componentFormsData, Workflow $workflow,
                                                                               $componentPrefix)
        {
            assert('is_array($componentFormsData)');
            assert('is_string($componentPrefix) || $componentPrefix == null');
            $moduleClassName    = $workflow->getModuleClassName();
            $addMethodName      = 'add' . $componentPrefix;
            $componentClassName = $componentPrefix . 'ForWorkflowForm';
            $rowKey             = 0;
            foreach ($componentFormsData as $componentFormData)
            {
                $component      = new $componentClassName($moduleClassName,
                                                          $moduleClassName::getPrimaryModelName(),
                                                          $workflow->getType(), $rowKey);
                $component->setAttributes($componentFormData);
                $workflow->{$addMethodName}($component);
                $rowKey++;
            }
        }

        /**
         * @param array $componentFormsData
         * @param Workflow $workflow
         */
        protected static function makeActionForWorkflowFormAndPopulateWorkflowFromData($componentFormsData,
                                                                                       Workflow $workflow)
        {
            assert('is_array($componentFormsData)');
            $moduleClassName    = $workflow->getModuleClassName();
            $rowKey             = 0;
            foreach ($componentFormsData as $componentFormData)
            {
                $component      = new ActionForWorkflowForm($moduleClassName::getPrimaryModelName(),
                                                            $workflow->getType(), $rowKey);
                $component->setAttributes($componentFormData);
                $workflow->addAction($component);
                $rowKey++;
            }
        }

        /**
         * @param array $componentFormsData
         * @param Workflow $workflow
         */
        protected static function makeEmailMessageForWorkflowFormAndPopulateWorkflowFromData($componentFormsData,
                                                                                             Workflow $workflow)
        {
            assert('is_array($componentFormsData)');
            $moduleClassName    = $workflow->getModuleClassName();
            $rowKey             = 0;
            foreach ($componentFormsData as $componentFormData)
            {
                $component      = new EmailMessageForWorkflowForm($moduleClassName::getPrimaryModelName(),
                                                                  $workflow->getType(), $rowKey);
                $component->setAttributes($componentFormData);
                $workflow->addEmailMessage($component);
                $rowKey++;
            }
        }
    }
?>