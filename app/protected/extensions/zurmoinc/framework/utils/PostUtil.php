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
     * Helper class for handling POST
     * arrays.
     */
    class PostUtil extends DataUtil
    {
        public static function sanitizePostForSavingMassEdit($postVariableName)
        {
            foreach ($_POST[$postVariableName] as $attributeName => $values)
            {
                if (empty($_POST['MassEdit'][$attributeName]))
                {
                    unset($_POST[$postVariableName][$attributeName]);
                }
                else
                {
                    if (is_array($values) && isset($values['values']) && is_string($values['values']))
                    {
                        if ($_POST[$postVariableName][$attributeName]['values'] == '')
                        {
                            $_POST[$postVariableName][$attributeName]['values'] = array();
                        }
                        else
                        {
                            $_POST[$postVariableName][$attributeName]['values'] =
                                explode(',', $_POST[$postVariableName][$attributeName]['values']); // Not Coding Standard
                        }
                    }
                }
            }
        }

        /**
         * Sanitizes post data for date and date time attributes by converting them to the proper
         * format and timezone for saving.
         * @return - array sanitized post data
         */
        public static function sanitizePostByDesignerTypeForSavingModel($model, $postData)
        {
            $postData = DataUtil::sanitizeDataByDesignerTypeForSavingModel($model, $postData);
            return $postData;
        }

        /**
         * Given an array of data, filter out all elements but the specified element and return array.
         * If the specified element does not exist, then return null.
         * @param array $sanitizedPostData
         * @param string $elementName
         */
        public static function sanitizePostDataToJustHavingElementForSavingModel($sanitizedPostData, $elementName)
        {
            return DataUtil::sanitizeDataToJustHavingElementForSavingModel($sanitizedPostData, $elementName);
        }

        /**
         * Given an array of data, filter out the specified element from the data if it exists.
         * @param array $sanitizedPostData
         * @param string $elementName
         */
        public static function removeElementFromPostDataForSavingModel($sanitizedPostData, $elementName)
        {
            return DataUtil::removeElementFromDataForSavingModel($sanitizedPostData, $elementName);
        }
    }
?>