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
     * Component form for a time trigger definition
     */
    class TimeTriggerForWorkflowForm extends TriggerForWorkflowForm
    {
        /**
         * @var integer.  Example: Account name is xyz for 1 hour.  The duration seconds would be set to 3600
         */
        public $durationSeconds;

        /**
         * @return string component type
         */
        public static function getType()
        {
            return static::TYPE_TIME_TRIGGER;
        }

        /**
         * @return array
         */
        public function rules()
        {
            return array_merge(parent::rules(), array(
                array('durationSeconds', 'type', 'type' => 'integer'),
            ));
        }

        /**
         * @return array
         * @throws NotSupportedException if the attributeIndexOrDerivedType has not been populated yet
         */
        public function getOperatorValuesAndLabels()
        {
            if ($this->attributeIndexOrDerivedType == null)
            {
                throw new NotSupportedException();
            }
            $type = $this->getAvailableOperatorsType();
            $data = array();
            ModelAttributeToWorkflowOperatorTypeUtil::resolveOperatorsToIncludeByType($data, $type);
            $data[OperatorRules::TYPE_DOES_NOT_CHANGE] = OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_DOES_NOT_CHANGE);
            if ($type != ModelAttributeToWorkflowOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_BOOLEAN &&
               $type != ModelAttributeToWorkflowOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_HAS_ONE)
            {
                $data[OperatorRules::TYPE_IS_EMPTY]      = OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_IS_EMPTY);
                $data[OperatorRules::TYPE_IS_NOT_EMPTY]  = OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_IS_NOT_EMPTY);
            }
            return $data;
        }

        /**
         * @return array
         * @throws NotSupportedException
         */
        public function getDurationValuesAndLabels()
        {
            if ($this->attributeIndexOrDerivedType == null)
            {
                throw new NotSupportedException();
            }

            $modelToWorkflowAdapter = $this->makeResolvedAttributeModelRelationsAndAttributesToWorkflowAdapter();
            $type = $modelToWorkflowAdapter->getDisplayElementType($this->getResolvedAttribute());
            $data = array();
            if ($type == 'DateTime')
            {
                return $this->makeDurationValuesAndLabels(true, true, true, true);
            }
            elseif ($type == 'Date')
            {
                return $this->makeDurationValuesAndLabels(true, true, true, false);
            }
            else
            {
                return $this->makeDurationValuesAndLabels(true, false, false, true);
            }

            return $data;
            ModelAttributeToWorkflowOperatorTypeUtil::resolveOperatorsToIncludeByType($data, $type);
            $data[OperatorRules::TYPE_DOES_NOT_CHANGE] = OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_DOES_NOT_CHANGE);
            if ($type != ModelAttributeToWorkflowOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_BOOLEAN &&
                $type != ModelAttributeToWorkflowOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_HAS_ONE)
            {
                $data[OperatorRules::TYPE_IS_EMPTY]      = OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_IS_EMPTY);
                $data[OperatorRules::TYPE_IS_NOT_EMPTY]  = OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_IS_NOT_EMPTY);
            }
            return $data;
        }

        /**
         * @param bool $includePositiveDuration
         * @param bool $includeNegativeDuration
         * @param bool $isTimeBased
         * @param bool $includeHours
         * @return array
         * @throws NotSupportedException
         */
        protected function makeDurationValuesAndLabels($includePositiveDuration = false,
                                                       $includeNegativeDuration = false,
                                                       $isTimeBased             = false,
                                                       $includeHours            = true)
        {
            assert('is_bool($includePositiveDuration)');
            assert('is_bool($includeNegativeDuration)');
            assert('is_bool($isTimeBased)');
            assert('is_bool($includeHours)');
            $data = array();
            if ($includeNegativeDuration)
            {
                if ($isTimeBased)
                {
                    WorkflowUtil::resolveNegativeDurationAsDistanceFromPointData($data, $includeHours);
                }
                else
                {
                    throw new NotSupportedException();
                }
            }
            if ($includePositiveDuration)
            {
                if ($isTimeBased)
                {
                    WorkflowUtil::resolvePositiveDurationAsDistanceFromPointData($data, $includeHours);
                }
                else
                {
                    WorkflowUtil::resolvePositiveDurationData($data, $includeHours);
                }
            }
            return $data;
        }
    }
?>