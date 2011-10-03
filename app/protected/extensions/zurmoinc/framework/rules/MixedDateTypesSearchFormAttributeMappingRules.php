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
     * Rule used in search form to define how the different date types are proceesed.
     */
    class MixedDateTypesSearchFormAttributeMappingRules extends SearchFormAttributeMappingRules
    {
        const TYPE_YESTERDAY      = 'Yesterday';

        const TYPE_TODAY          = 'Today';

        const TYPE_TOMORROW       = 'Tomorrow';

        const TYPE_BEFORE         = 'Before';

        const TYPE_AFTER          = 'After';

        const TYPE_NEXT_7_DAYS    = 'Next 7 Days';

        const TYPE_LAST_7_DAYS    = 'Last 7 Days';

        public static function resolveValueDataIntoUsableValue($value)
        {
            if(isset($value['type']) && $value['type'] != null)
            {
                $validValueTypesAndLabels = static::getValidValueTypesAndLabels();
                if(!isset($validValueTypesAndLabels[$value['type']]))
                {
                    throw new NotSupportedException();
                }
                if($value['type'] == self::TYPE_TODAY)
                {
                    return   DateTimeCalculatorUtil::
                             calculateNew(DateTimeCalculatorUtil::TODAY,
                             new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser())));
                }
                elseif($value['type'] == self::TYPE_TOMORROW)
                {
                    return   DateTimeCalculatorUtil::
                             calculateNew(DateTimeCalculatorUtil::TOMORROW,
                             new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser())));
                }
                elseif($value['type'] == self::TYPE_YESTERDAY)
                {
                    return   DateTimeCalculatorUtil::
                             calculateNew(DateTimeCalculatorUtil::YESTERDAY,
                             new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser())));
                }
                elseif($value['type'] == self::TYPE_BEFORE || $value['type'] == self::TYPE_AFTER)
                {
                    assert('$value["firstDate"] != null && is_string($value["firstDate"])');
                    return $value['firstDate'];
                }
                else
                {
                    throw new NotSupportedException();
                }
            }
            return null;
        }

        public static function getValidValueTypesAndLabels()
        {
            return array(self::TYPE_YESTERDAY   => Yii::t('Default', self::TYPE_YESTERDAY),
                         self::TYPE_TODAY       => Yii::t('Default', self::TYPE_TODAY),
                         self::TYPE_TOMORROW    => Yii::t('Default', self::TYPE_TOMORROW),
                         self::TYPE_BEFORE      => Yii::t('Default', self::TYPE_BEFORE),
                         self::TYPE_AFTER       => Yii::t('Default', self::TYPE_AFTER),
                         self::TYPE_NEXT_7_DAYS => Yii::t('Default', self::TYPE_NEXT_7_DAYS),
                         self::TYPE_LAST_7_DAYS => Yii::t('Default', self::TYPE_LAST_7_DAYS),
            );
        }

        public static function getValueTypesRequiringFirstDateInput()
        {
            return array(self::TYPE_BEFORE, self::TYPE_AFTER);
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
            if(count($parts) != 2)
            {
                throw new NotSupportedException();
            }
            list($realAttributeName, $type) = $parts;
            if(isset($value['type']) && $value['type'] != null)
            {
                if($value['type'] == self::TYPE_YESTERDAY ||
                   $value['type'] == self::TYPE_TODAY ||
                   $value['type'] == self::TYPE_TOMORROW)
                {
                    $attributeAndRelations = array(array($realAttributeName, null, 'equals', 'resolveValueByRules'));
                }
                elseif($value['type'] == self::TYPE_AFTER)
                {
                    $attributeAndRelations = array(array($realAttributeName, null, 'greaterThanOrEqualTo', 'resolveValueByRules'));
                }
                elseif($value['type'] == self::TYPE_BEFORE)
                {
                    $attributeAndRelations = array(array($realAttributeName, null, 'lessThanOrEqualTo', 'resolveValueByRules'));
                }
                elseif($value['type'] == self::TYPE_NEXT_7_DAYS)
                {
                    $today                 = static::calculateNewDateByDaysFromNow(0);
                    $todayPlusSevenDays    = static::calculateNewDateByDaysFromNow(7);
                    $attributeAndRelations = array(array($realAttributeName, null, 'greaterThanOrEqualTo', $today, true),
                                                   array($realAttributeName, null, 'lessThanOrEqualTo',    $todayPlusSevenDays, true));
                }
                elseif($value['type'] == self::TYPE_LAST_7_DAYS)
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