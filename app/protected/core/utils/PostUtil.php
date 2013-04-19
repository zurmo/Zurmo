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
     * Helper class for handling POST
     * arrays.
     */
    class PostUtil extends DataUtil
    {
        public static function getData()
        {
            $getData = array();
            if (isset($_POST))
            {
                $getData = $_POST;
            }
            return $getData;
        }

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

        public static function sanitizePostForMassDelete($postVariableName)
        {
            foreach ($_POST[$postVariableName] as $attributeName => $values)
            {
                if (empty($_POST['MassDelete'][$attributeName]))
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