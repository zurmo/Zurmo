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
                            if ($designerType == 'Date' && !empty($value))
                            {
                                $data[$attributeName] = DateTimeUtil::resolveValueForDateDBFormatted($value);
                            }
                            if ($designerType == 'DateTime' && !empty($value))
                            {
                                $data[$attributeName] = DateTimeUtil::convertDateTimeLocaleFormattedDisplayToDbFormattedDateTimeWithSecondsAsZero($value);
                            }
                            $data[$attributeName] = self::purifyHtml($data[$attributeName]);
                        }
                    }
                    else
                    {
                        try
                        {
                            $designerType = ModelAttributeToDesignerTypeUtil::getDesignerType($model, $attributeName);
                        }
                        catch (NotImplementedException $e)
                        {
                            //In the event that a designer type does not exist.
                            $designerType = null;
                        }
                        if ($model->isAttributeSafe($attributeName) && $designerType != 'TagCloud')
                        {
                            if ($designerType == 'MixedDateTypesForSearch' && isset($value['firstDate']) &&
                                $value['firstDate'] != null)
                            {
                                $data[$attributeName]['firstDate'] = DateTimeUtil::
                                                                         resolveValueForDateDBFormatted(
                                                                         $value['firstDate']);
                            }
                            if ($designerType == 'MixedDateTypesForSearch' && isset($value['secondDate']) &&
                            $value['secondDate'] != null)
                            {
                                $data[$attributeName]['secondDate'] = DateTimeUtil::
                                                                     resolveValueForDateDBFormatted(
                                                                     $value['secondDate']);
                            }
                        }
                        elseif (isset($value['values']) && is_string($value['values']) && $designerType == 'TagCloud')
                        {
                            if ($data[$attributeName]['values'] == '')
                            {
                                $data[$attributeName]['values'] = array();
                            }
                            else
                            {
                                $data[$attributeName]['values'] = explode(',', $data[$attributeName]['values']); // Not Coding Standard
                            }
                        }
                        array_walk_recursive($data[$attributeName], array('DataUtil', 'purifyHtmlAndModifyInput'));
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

        /**
         * Purify string content
         * @param string $text
         * @return string
         */
        public static function purifyHtml($text)
        {
            if (is_string($text))
            {
                $safeCharacters     = array('&' => '&amp;');
                $purifier           = new CHtmlPurifier();
                $purifier->options  = array('Cache.SerializerPermissions' => 0777);
                $purifiedText       = $purifier->purify($text);
                foreach ($safeCharacters as $specialCharacter => $purifiedCode)
                {
                    if (strpos($text, $specialCharacter) !== false)
                    {
                        $purifiedText = str_replace($purifiedCode, $specialCharacter, $purifiedText);
                    }
                }
                $text = $purifiedText;
            }
            return $text;
        }

        /**
         * Purify string content
         * This function should be used in recurcive functions, like array_walk_recursive().
         * It doesn't return data, but instead modify input argument, so use it carefully.
         * As side effect it modify provided element
         * @param mixed $item
         */
        public static function purifyHtmlAndModifyInput(&$item)
        {
            assert('is_scalar($item) || empty($item)');
            if (!empty($item))
            {
                $item = self::purifyHtml($item);
            }
        }
    }
?>
