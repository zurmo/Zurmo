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
     * Class to work with POST data and adapting that into a Workflow object
     */
    class DataToWorkflowUtil
    {
        /**
         * @param Workflow $workflow
         * @param array $postData
         * @param string$wizardFormClassName
         */
        public static function resolveWorkflowByWizardPostData(Workflow $workflow, $postData, $wizardFormClassName)
        {
            assert('is_array($postData)');
            assert('is_string($wizardFormClassName)');
            $data = ArrayUtil::getArrayValue($postData, $wizardFormClassName);
            if (isset($data['description']))
            {
                $workflow->setDescription($data['description']);
            }
            if (isset($data['isActive']))
            {
                $workflow->setIsActive((bool)$data['isActive']);
            }
            if (isset($data['moduleClassName']))
            {
                $workflow->setModuleClassName($data['moduleClassName']);
            }
            if (isset($data['name']))
            {
                $workflow->setName($data['name']);
            }
            if (isset($data['triggerOn']))
            {
                $workflow->setTriggerOn($data['triggerOn']);
            }
            if (isset($data['triggersStructure']))
            {
                $workflow->setTriggersStructure($data['triggersStructure']);
            }
            if (isset($data['timeTriggerAttribute']))
            {
                $workflow->setTimeTriggerAttribute($data['timeTriggerAttribute']);
            }
            self::resolveTriggers                   ($data, $workflow);
            self::resolveActions                    ($data, $workflow);
            self::resolveEmailMessages                ($data, $workflow);
            self::resolveTimeTrigger                ($data, $workflow);
        }

        /**
         * @param array $data
         * @param Workflow $workflow
         */
        public static function resolveTriggers($data, Workflow $workflow)
        {
            assert('is_array($data)');
            $workflow->removeAllTriggers();
            $moduleClassName = $workflow->getModuleClassName();
            if (count($triggersData = ArrayUtil::getArrayValue($data, ComponentForWorkflowForm::TYPE_TRIGGERS)) > 0)
            {
                $sanitizedTriggersData = self::sanitizeTriggersData($moduleClassName, $workflow->getType(), $triggersData);
                foreach ($sanitizedTriggersData as $key => $triggerData)
                {
                    $trigger = new TriggerForWorkflowForm($moduleClassName, $moduleClassName::getPrimaryModelName(),
                                                          $workflow->getType(), $key);
                    $trigger->setAttributes($triggerData);
                    $workflow->addTrigger($trigger);
                }
            }
            else
            {
                $workflow->removeAllTriggers();
            }
        }

        /**
         * @param string $moduleClassName
         * @param string $workflowType
         * @param array $triggersData
         * @return array
         */
        public static function sanitizeTriggersData($moduleClassName, $workflowType, array $triggersData)
        {
            assert('is_string($moduleClassName)');
            assert('is_string($workflowType)');
            $sanitizedTriggersData = array();
            foreach ($triggersData as $key => $triggerData)
            {
                $sanitizedTriggersData[$key] = static::sanitizeTriggerData($moduleClassName, $workflowType, $triggerData);
            }
            return $sanitizedTriggersData;
        }

        /**
         * Public for testing purposes
         * @param array $data
         * @param Workflow $workflow
         */
        public static function resolveActions($data, Workflow $workflow)
        {
            assert('is_array($data)');
            $workflow->removeAllActions();
            $moduleClassName = $workflow->getModuleClassName();
            if (count($actionsData = ArrayUtil::getArrayValue($data, ComponentForWorkflowForm::TYPE_ACTIONS)) > 0)
            {
                foreach ($actionsData as $key => $actionData)
                {
                    $sanitizedActionData = static::sanitizeActionData($moduleClassName::getPrimaryModelName(),
                                                                      $actionData, $workflow->type);
                    $action              = new ActionForWorkflowForm ($moduleClassName::getPrimaryModelName(),
                                                                      $workflow->type, $key);
                    $action->setAttributes($sanitizedActionData);
                    $workflow->addAction($action);
                }
            }
            else
            {
                $workflow->removeAllActions();
            }
        }

        /**
         * @param string $modelClassName
         * @param array $actionData
         * @param string $workflowType
         * @return array
         */
        public static function sanitizeActionData($modelClassName, $actionData, $workflowType)
        {
            assert('is_string($modelClassName)');
            assert('is_array($actionData)');
            assert('is_string($workflowType)');
            if (!isset($actionData[ActionForWorkflowForm::ACTION_ATTRIBUTES]))
            {
                return $actionData;
            }
            $actionForSanitizing = new ActionForWorkflowForm($modelClassName, $workflowType);
            $actionForSanitizing->setAttributes($actionData);
            foreach ($actionData[ActionForWorkflowForm::ACTION_ATTRIBUTES] as $attribute => $attributeData)
            {
                if (isset($attributeData['value']))
                {
                    $type = $actionForSanitizing->getActionAttributesAttributeFormType($attribute);
                    if ($type == 'Date' && $attributeData['type'] == DateWorkflowActionAttributeForm::TYPE_STATIC)
                    {
                        $actionData[ActionForWorkflowForm::ACTION_ATTRIBUTES][$attribute]['value'] =
                            DateTimeUtil::resolveValueForDateDBFormatted($attributeData['value']);
                    }
                    elseif ($type == 'DateTime' && $attributeData['type'] == DateTimeWorkflowActionAttributeForm::TYPE_STATIC)
                    {
                        $actionData[ActionForWorkflowForm::ACTION_ATTRIBUTES][$attribute]['value'] =
                            DateTimeUtil::convertDateTimeLocaleFormattedDisplayToDbFormattedDateTimeWithSecondsAsZero($attributeData['value']);
                    }
                }
            }
            return $actionData;
        }

        /**
         * Public for testing purposes
         * @param array $data
         * @param Workflow $workflow
         */
        public static function resolveEmailMessages($data, Workflow $workflow)
        {
            assert('is_array($data)');
            $workflow->removeAllEmailMessages();
            $moduleClassName = $workflow->getModuleClassName();
            if (count($emailMessagesData = ArrayUtil::getArrayValue($data, ComponentForWorkflowForm::TYPE_EMAIL_MESSAGES)) > 0)
            {
                foreach ($emailMessagesData as $key => $emailMessageData)
                {
                    $emailMessage = new EmailMessageForWorkflowForm($moduleClassName::getPrimaryModelName(),
                                  $workflow->type, $key);
                    $emailMessage->setAttributes($emailMessageData);
                    $workflow->addEmailMessage($emailMessage);
                }
            }
            else
            {
                $workflow->removeAllEmailMessages();
            }
        }

        /**
         * No need to sanitize for Date and DateTime since those attributes utilize integers for time-based triggers
         * @param array $data
         * @param Workflow $workflow
         */
        public static function resolveTimeTrigger($data, Workflow $workflow)
        {
            assert('is_array($data)');
            if ($workflow->getType() != Workflow::TYPE_BY_TIME)
            {
                return;
            }
            $workflow->removeTimeTrigger();
            $moduleClassName = $workflow->getModuleClassName();
            $timeTrigger     = new TimeTriggerForWorkflowForm($moduleClassName, $moduleClassName::getPrimaryModelName(),
                                                              $workflow->getType());
            if (null != $timeTriggerData = ArrayUtil::getArrayValue($data, ComponentForWorkflowForm::TYPE_TIME_TRIGGER))
            {
                $timeTrigger->setAttributes($timeTriggerData);
            }
            $workflow->setTimeTrigger($timeTrigger);
        }

        /**
         * @param string $moduleClassName
         * @param string $workflowType
         * @param array $triggerData
         * @return mixed
         */
        protected static function sanitizeTriggerData($moduleClassName, $workflowType, $triggerData)
        {
            assert('is_string($moduleClassName)');
            assert('is_string($workflowType)');
            assert('is_array($triggerData)');
            $triggerForSanitizing = new TriggerForWorkflowForm($moduleClassName, $moduleClassName::getPrimaryModelName(),
                $workflowType);
            $triggerForSanitizing->setAttributes($triggerData);
            $valueElementType = null;
            $valueElementType    = $triggerForSanitizing->getValueElementType();
            if ($valueElementType == 'MixedDateTypesForWorkflow')
            {
                if (isset($triggerData['value']) && $triggerData['value'] !== null)
                {
                    $triggerData['value']       = DateTimeUtil::resolveValueForDateDBFormatted($triggerData['value']);
                }
                if (isset($triggerData['secondValue']) && $triggerData['secondValue'] !== null)
                {
                    $triggerData['secondValue'] = DateTimeUtil::resolveValueForDateDBFormatted($triggerData['secondValue']);
                }
            }
            return $triggerData;
        }
    }
?>