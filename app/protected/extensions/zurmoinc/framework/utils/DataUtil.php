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
     * Helper class for handling data.
     */
    class DataUtil
    {
        /**
        * Sanitizes data for date and date time attributes by converting them to the proper
        * format and timezone for saving.
        * @return - array sanitized data
        */
        public static function sanitizeDataByDesignerTypeForSavingModel($model, $data)
        {
            assert('$model instanceof RedBeanModel || $model instanceof ModelForm');
            assert('is_array($data)');
            foreach ($data as $attributeName => $value)
            {
                if ($value !== null)
                {
                    if (!is_array($value))
                    {
                        if ($model->isAttribute($attributeName) && $model->isAttributeSafe($attributeName))
                        {
                            $designerType = ModelAttributeToDesignerTypeUtil::getDesignerType(
                                                $model, $attributeName);
                            if ($designerType == 'Date')
                            {
                                $data[$attributeName] = DateTimeUtil::resolveValueForDateDBFormatted($value);
                            }
                            if ($designerType == 'DateTime' && !empty($value))
                            {
                                $data[$attributeName] = DateTimeUtil::convertDateTimeLocaleFormattedDisplayToDbFormattedDateTimeWithSecondsAsZero($value);
                            }
                        }
                    }
                    else
                    {
                        if ($model->isAttribute($attributeName) && $model->isAttributeSafe($attributeName))
                        {
                            $designerType = ModelAttributeToDesignerTypeUtil::getDesignerType(
                                                $model, $attributeName);
                            if ($designerType == 'MixedDateTypesForSearch' && isset($value['firstDate']) &&
                            $value['firstDate'] != null)
                            {
                                $data[$attributeName]['firstDate'] = DateTimeUtil::
                                                                     resolveValueForDateDBFormatted(
                                                                     $value['firstDate']);
                            }
                        }
                    }
                }
            }
            return $data;
        }

        /**
         * Given an array of data, filter out all elements but the specified element and return array.
         * If the specified element does not exist, then return null.
         * @param array $sanitizedData
         * @param string $elementName
         */
        public static function sanitizeDataToJustHavingElementForSavingModel($sanitizedData, $elementName)
        {
            assert('is_array($sanitizedData)');
            assert('is_string($elementName) || is_int($elementName)');
            if (!isset($sanitizedData[$elementName]))
            {
                return null;
            }
            return array($elementName => $sanitizedData[$elementName]);
        }

        /**
         * Given an array of data, filter out the specified element from the data if it exists.
         * @param array $sanitizedData
         * @param string $elementName
         */
        public static function removeElementFromDataForSavingModel($sanitizedData, $elementName)
        {
            assert('is_array($sanitizedData)');
            assert('is_string($elementName) || is_int($elementName)');
            if (isset($sanitizedData[$elementName]))
            {
                unset($sanitizedData[$elementName]);
            }
            return $sanitizedData;
        }
    }
?>