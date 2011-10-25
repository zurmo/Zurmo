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
     * Rule used in search form to define how the different datetimes types are proceesed.
     */
    class MixedDateTimeTypesSearchFormAttributeMappingRules extends MixedDateTypesSearchFormAttributeMappingRules
    {
        /**
         * The value['type'] determines how the attributeAndRelations is structured.
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
                    $dateValue             = static::resolveValueDataIntoUsableValue($value);
                    $greaterThanValue      = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($dateValue);
                    $lessThanValue         = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($dateValue);
                    $attributeAndRelations = array(array($realAttributeName, null, 'greaterThanOrEqualTo', $greaterThanValue, true),
                                                   array($realAttributeName, null, 'lessThanOrEqualTo',    $lessThanValue, true));
                }
                elseif ($value['type'] == self::TYPE_AFTER)
                {
                    $dateValue             = static::resolveValueDataIntoUsableValue($value);
                    $greaterThanValue      = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($dateValue);
                    $attributeAndRelations = array(array($realAttributeName, null, 'greaterThanOrEqualTo', $greaterThanValue));
                }
                elseif ($value['type'] == self::TYPE_BEFORE)
                {
                    $dateValue             = static::resolveValueDataIntoUsableValue($value);
                    $lessThanValue         = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($dateValue);
                    $attributeAndRelations = array(array($realAttributeName, null, 'lessThanOrEqualTo', $lessThanValue));
                }
                elseif ($value['type'] == self::TYPE_NEXT_7_DAYS)
                {
                    $today                 = static::calculateNewDateByDaysFromNow(0);
                    $todayPlusSevenDays    = static::calculateNewDateByDaysFromNow(7);
                    $greaterThanValue      = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($today);
                    $lessThanValue         = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($todayPlusSevenDays);
                    $attributeAndRelations = array(array($realAttributeName, null, 'greaterThanOrEqualTo', $greaterThanValue, true),
                                                   array($realAttributeName, null, 'lessThanOrEqualTo',    $lessThanValue, true));
                }
                elseif ($value['type'] == self::TYPE_LAST_7_DAYS)
                {
                    $today                 = static::calculateNewDateByDaysFromNow(0);
                    $todayMinusSevenDays   = static::calculateNewDateByDaysFromNow(-7);
                    $greaterThanValue      = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($todayMinusSevenDays);
                    $lessThanValue         = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($today);
                    $attributeAndRelations = array(array($realAttributeName, null, 'greaterThanOrEqualTo', $greaterThanValue, true),
                                                   array($realAttributeName, null, 'lessThanOrEqualTo',    $lessThanValue, true));
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
    }
?>