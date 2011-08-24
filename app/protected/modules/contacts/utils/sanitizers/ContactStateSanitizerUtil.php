<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

   /**
     * Sanitizer for handling contact state. These are states that are the starting state or after.
     */
    class ContactStateSanitizerUtil extends SanitizerUtil
    {
        public static function getSqlAttributeValueDataAnalyzerType()
        {
            return 'ContactState';
        }

        public static function getBatchAttributeValueDataAnalyzerType()
        {
            return 'ContactState';
        }

        /**
         * If a state is invalid, then skip the entire row during import.
         */
        public static function shouldNotSaveModelOnSanitizingValueFailure()
        {
            return true;
        }

        /**
         * Given a contact state id, attempt to get and return a contact state object. If the id is invalid, then an
         * InvalidValueToSanitizeException will be thrown.
         * @param string $modelClassName
         * @param string $attributeName
         * @param mixed $value
         * @param array $mappingRuleData
         */
        public static function sanitizeValue($modelClassName, $attributeName, $value, $mappingRuleData)
        {
            assert('is_string($modelClassName)');
            assert('$attributeName == null');
            assert('$mappingRuleData == null');
            if($value == null)
            {
                return $value;
            }
            try
            {
                if((int)$value <= 0)
                {
                    throw new NotFoundException();
                }
                $state = ContactState::getById($value);
                $startingState = ContactsUtil::getStartingState();
                if(!static::resolvesValidStateByOrder($state->order, $startingState->order))
                {
                    throw new InvalidValueToSanitizeException(Yii::t('Default', 'The status specified is invalid.'));
                }
                return $state;
            }
            catch(NotFoundException $e)
            {
                throw new InvalidValueToSanitizeException(Yii::t('Default', 'The status specified does not exist.'));
            }
        }

        protected static function resolvesValidStateByOrder($stateOrder, $startingOrder)
        {
            if($stateOrder >= $startingOrder)
            {
                return true;
            }
            return false;
        }
    }
?>