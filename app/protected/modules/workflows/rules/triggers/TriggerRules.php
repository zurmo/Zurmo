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
     * Class to help the workflow engine understand how to evaluate various triggers
     */
    abstract class TriggerRules
    {
        /**
         * @var TriggerForWorkflowForm
         */
        protected $trigger;

        abstract public function evaluateBeforeSave(RedBeanModel $model, $attribute);

        /**
         * @param TriggerForWorkflowForm $trigger
         */
        public function __construct(TriggerForWorkflowForm $trigger)
        {
            $this->trigger = $trigger;
        }

        /**
         * For a time trigger, the value must first 'change'.  If the operator is TYPE_DOES_NOT_CHANGE, then we can
         * assume true since any 'change' pushes out the time expiration.  If the value does 'change', then the
         * operator can be evaluated normally.
         * @param RedBeanModel $model
         * @param $attribute
         * @param $changeRequiredToProcess - if a change in value is required to confirm the time trigger is true
         * @return bool
         */
        public function evaluateTimeTriggerBeforeSave(RedBeanModel $model, $attribute, $changeRequiredToProcess = true)
        {
            assert('is_string($attribute)');
            assert('is_bool($changeRequiredToProcess)');
            if (array_key_exists($attribute, $model->originalAttributeValues) || !$changeRequiredToProcess)
            {
                if ($this->trigger->getOperator() == OperatorRules::TYPE_DOES_NOT_CHANGE)
                {
                    return true;
                }
                return $this->evaluateBeforeSave($model, $attribute);
            }
            return false;
        }

        /**
         * Override as needed to add specific sanitization routines.  Text for example, has to use strtolower
         * @param $value
         * @return mixed
         */
        protected function sanitize($value)
        {
            return $value;
        }
    }
?>