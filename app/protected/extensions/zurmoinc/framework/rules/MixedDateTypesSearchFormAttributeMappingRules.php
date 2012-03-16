<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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
     * Rule used in search form to define how the different date types are proceesed.
     */
    class MixedDateTypesSearchFormAttributeMappingRules extends SearchFormAttributeMappingRules
    {
        const TYPE_YESTERDAY      = 'Yesterday';

        const TYPE_TODAY          = 'Today';

        const TYPE_TOMORROW       = 'Tomorrow';

        const TYPE_BEFORE         = 'Before';

        const TYPE_AFTER          = 'After';

        const TYPE_ON             = 'On';

        const TYPE_BETWEEN        = 'Between';

        const TYPE_NEXT_7_DAYS    = 'Next 7 Days';

        const TYPE_LAST_7_DAYS    = 'Last 7 Days';

        /**
         * In the event that the type is BEFORE or AFTER, and the firstDate value is not populated, it will be treated
         * as null, and the search on this attribute will be ignored.  At some point in the future the search form
         * could have validation added, so that the empty firstDate combined with a type of BEFORE or AFTER would not
         * get this far, but for now this is the easiest approach to ensuring a valid BEFORE or AFTER value.
         * @param mixed $value
         */
        public static function resolveValueDataIntoUsableValue($value)
        {
            if (isset($value['type']) && $value['type'] != null)
            {
                $validValueTypesAndLabels = static::getValidValueTypesAndLabels();
                if (!isset($validValueTypesAndLabels[$value['type']]))
                {
                    throw new NotSupportedException();
                }
                if ($value['type'] == self::TYPE_TODAY)
                {
                    return   DateTimeCalculatorUtil::
                             calculateNew(DateTimeCalculatorUtil::TODAY,
                             new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser())));
                }
                elseif ($value['type'] == self::TYPE_TOMORROW)
                {
                    return   DateTimeCalculatorUtil::
                             calculateNew(DateTimeCalculatorUtil::TOMORROW,
                             new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser())));
                }
                elseif ($value['type'] == self::TYPE_YESTERDAY)
                {
                    return   DateTimeCalculatorUtil::
                             calculateNew(DateTimeCalculatorUtil::YESTERDAY,
                             new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser())));
                }
                elseif ($value['type'] == self::TYPE_BEFORE || $value['type'] == self::TYPE_AFTER ||
                        $value['type'] == self::TYPE_ON)
                {
                    if ($value["firstDate"] == null)
                    {
                        return null;
                    }
                    return $value['firstDate'];
                }
                else
                {
                    throw new NotSupportedException();
                }
            }
            return null;
        }

        /**
         * When the value type is between, returns the firstDate value.
         * @param unknown_type $value
         */
        public static function resolveValueDataForBetweenIntoUsableFirstDateValue($value)
        {
            if ($value['type'] != self::TYPE_BETWEEN)
            {
                throw new NotSupportedException();
            }
            if ($value["firstDate"] == null)
            {
                return null;
            }
            return $value['firstDate'];
        }

        /**
         * When the value type is between, returns the secondDate value.
         * @param unknown_type $value
         */
        public static function resolveValueDataForBetweenIntoUsableSecondDateValue($value)
        {
            if ($value['type'] != self::TYPE_BETWEEN)
            {
                throw new NotSupportedException();
            }
            if ($value["secondDate"] == null)
            {
                return null;
            }
            return $value['secondDate'];
        }

        public static function getValidValueTypesAndLabels()
        {
            return array(self::TYPE_YESTERDAY   => Yii::t('Default', 'Yesterday'),
                         self::TYPE_TODAY       => Yii::t('Default', 'Today'),
                         self::TYPE_TOMORROW    => Yii::t('Default', 'Tomorrow'),
                         self::TYPE_BEFORE      => Yii::t('Default', 'Before'),
                         self::TYPE_AFTER       => Yii::t('Default', 'After'),
                         self::TYPE_ON          => Yii::t('Default', 'On'),
                         self::TYPE_BETWEEN     => Yii::t('Default', 'Between'),
                         self::TYPE_NEXT_7_DAYS => Yii::t('Default', 'Next 7 Days'),
                         self::TYPE_LAST_7_DAYS => Yii::t('Default', 'Last 7 Days'),
            );
        }

        public static function getValueTypesRequiringFirstDateInput()
        {
            return array(self::TYPE_BEFORE, self::TYPE_AFTER, self::TYPE_ON, self::TYPE_BETWEEN);
        }

            public static function getValueTypesRequiringSecondDateInput()
        {
            return array(self::TYPE_BETWEEN);
        }

        /**
         * The value['type'] deterimines how the attributeAndRelations is structured.
         * @param string $attributeName
         * @param array $attributeAndRelations
         * @param mixed $value
         */
        public static function resolveAttributesAndRelations($attributeName, & $attributeAndRelations, $value)
        {
            assert('is_string($attributeName)');
            assert('$attributeAndRelations == "resolveEntireMappingByRules"');
            assert('empty($value) || $value == null || is_array($value)');
            $delimiter                      = FormModelUtil::DELIMITER;
            $parts = explode($delimiter, $attributeName);
            if (count($parts) != 2)
            {
                throw new NotSupportedException();
            }
            list($realAttributeName, $type) = $parts;
            if (isset($value['type']) && $value['type'] != null)
            {
                if ($value['type'] == self::TYPE_YESTERDAY ||
                   $value['type'] == self::TYPE_TODAY ||
                   $value['type'] == self::TYPE_TOMORROW)
                {
                    $attributeAndRelations = array(array($realAttributeName, null, 'equals', 'resolveValueByRules'));
                }
                elseif ($value['type'] == self::TYPE_AFTER)
                {
                    $attributeAndRelations = array(array($realAttributeName, null, 'greaterThanOrEqualTo', 'resolveValueByRules'));
                }
                elseif ($value['type'] == self::TYPE_BEFORE)
                {
                    $attributeAndRelations = array(array($realAttributeName, null, 'lessThanOrEqualTo', 'resolveValueByRules'));
                }
                elseif ($value['type'] == self::TYPE_ON)
                {
                    $attributeAndRelations = array(array($realAttributeName, null, 'equals', 'resolveValueByRules'));
                }
                elseif ($value['type'] == self::TYPE_BETWEEN)
                {
                    $firstDateValue = static::resolveValueDataForBetweenIntoUsableFirstDateValue($value);
                    $secondDateValue = static::resolveValueDataForBetweenIntoUsableSecondDateValue($value);
                    $attributeAndRelations = array(array($realAttributeName, null, 'greaterThanOrEqualTo', $firstDateValue, true),
                                                   array($realAttributeName, null, 'lessThanOrEqualTo',    $secondDateValue, true));
                }
                elseif ($value['type'] == self::TYPE_NEXT_7_DAYS)
                {
                    $today                 = static::calculateNewDateByDaysFromNow(0);
                    $todayPlusSevenDays    = static::calculateNewDateByDaysFromNow(7);
                    $attributeAndRelations = array(array($realAttributeName, null, 'greaterThanOrEqualTo', $today, true),
                                                   array($realAttributeName, null, 'lessThanOrEqualTo',    $todayPlusSevenDays, true));
                }
                elseif ($value['type'] == self::TYPE_LAST_7_DAYS)
                {
                    $today                 = static::calculateNewDateByDaysFromNow(0);
                    $todayMinusSevenDays   = static::calculateNewDateByDaysFromNow(-7);
                    $attributeAndRelations = array(array($realAttributeName, null, 'greaterThanOrEqualTo', $todayMinusSevenDays, true),
                                                   array($realAttributeName, null, 'lessThanOrEqualTo',    $today, true));
                }
                else
                {
                    throw new NotSupportedException();
                }
            }
            else
            {
                $attributeAndRelations = array(array($realAttributeName, null, null, 'resolveValueByRules'));
            }
        }

        /**
         * Given an integer representing a count of days from the present day, returns a DB formatted date stamp based
         * on that calculation. This is a wrapper method for @see DateTimeCalculatorUtil::calculateNewByDaysFromNow
         * @param integer $daysFromNow
         */
        public static function calculateNewDateByDaysFromNow($daysFromNow)
        {
            assert('is_int($daysFromNow)');
            return   DateTimeCalculatorUtil::calculateNewByDaysFromNow($daysFromNow,
                     new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser())));
        }
    }
?>