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
     * Class to help evaluate MultipleValuesCustomField triggers against model values.
     */
    class MultiSelectDropDownTriggerRules extends TriggerRules
    {
        public function evaluateBeforeSave(RedBeanModel $model, $attribute)
        {
            switch($this->trigger->getOperator())
            {
                case OperatorRules::TYPE_EQUALS:
                    return $this->isSetIdenticalToTriggerValues($model->{$attribute}->values);
                    break;
                case OperatorRules::TYPE_DOES_NOT_EQUAL:
                    return !$this->isSetIdenticalToTriggerValues($model->{$attribute}->values);
                    break;
                case OperatorRules::TYPE_ONE_OF:
                    return $this->doesSetContainAtLeastOneOfTheTriggerValues($model->{$attribute}->values);
                    break;
                case OperatorRules::TYPE_CHANGES:
                    if ($model->{$attribute}->resolveOriginalCustomFieldValuesDataForNewData() !== null)
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_DOES_NOT_CHANGE:
                    if (!($model->{$attribute}->resolveOriginalCustomFieldValuesDataForNewData() !== null))
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_BECOMES:
                    if ($model->{$attribute}->resolveOriginalCustomFieldValuesDataForNewData() !== null &&
                       $this->isSetIdenticalToTriggerValues($model->{$attribute}->values))
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_WAS:
                    if ($model->{$attribute}->resolveOriginalCustomFieldValuesDataForNewData() !== null &&
                       $this->isDataIdenticalToTriggerValues(
                           $model->{$attribute}->resolveOriginalCustomFieldValuesDataForNewData()))
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_IS_EMPTY:
                    if ($model->{$attribute}->values->count() == 0)
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_IS_NOT_EMPTY:
                    if ($model->{$attribute}->values->count() != 0)
                    {
                        return true;
                    }
                    break;
                default:
                    throw new NotSupportedException();
            }
            return false;
        }

        /**
         * @see parent::evaluateTimeTriggerBeforeSave for explanation of method
         * @param RedBeanModel $model
         * @param $attribute
         * @param $changeRequiredToProcess - if a change in value is required to confirm the time trigger is true
         * @return bool
         */
        public function evaluateTimeTriggerBeforeSave(RedBeanModel $model, $attribute, $changeRequiredToProcess = true)
        {
            if ($model->{$attribute}->resolveOriginalCustomFieldValuesDataForNewData() !== null  || !$changeRequiredToProcess)
            {
                if ($this->trigger->getOperator() == OperatorRules::TYPE_DOES_NOT_CHANGE)
                {
                    return true;
                }
                return $this->evaluateBeforeSave($model, $attribute);
            }
            return false;
        }

        protected function isDataIdenticalToTriggerValues(Array $values)
        {
            if (count($values) != count($this->trigger->value))
            {
                return false;
            }
            foreach ($values as $value)
            {
                if (!in_array($value, $this->trigger->value))
                {
                    return false;
                }
            }
            return true;
        }

        protected function isSetIdenticalToTriggerValues(RedBeanOneToManyRelatedModels $multipleCustomFieldValues)
        {
            if ($multipleCustomFieldValues->count() != count($this->trigger->value))
            {
                return false;
            }
            foreach ($multipleCustomFieldValues as $customFieldValue)
            {
                if (!in_array($customFieldValue->value, $this->trigger->value))
                {
                    return false;
                }
            }
            return true;
        }

        protected function doesDataContainAtLeastOneOfTheTriggerValues(Array $values)
        {
            if (!is_array($this->trigger->value)) //it should always be an array
            {
                return false;
            }
            foreach ($values as $value)
            {
                if (in_array($value, $this->trigger->value))
                {
                    return true;
                }
            }
            return false;
        }

        protected function doesSetContainAtLeastOneOfTheTriggerValues(RedBeanOneToManyRelatedModels $multipleCustomFieldValues)
        {
            if (!is_array($this->trigger->value)) //it should always be an array
            {
                return false;
            }
            foreach ($multipleCustomFieldValues as $customFieldValue)
            {
                if (in_array($customFieldValue->value, $this->trigger->value))
                {
                    return true;
                }
            }
            return false;
        }
    }
?>