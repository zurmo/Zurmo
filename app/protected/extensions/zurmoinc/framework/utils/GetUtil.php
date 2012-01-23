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
     * Helper class for handling GET
     * arrays.
     */
    class GetUtil extends DataUtil
    {
        public static function getData()
        {
            $getData = array();
            if (isset($_GET))
            {
                $getData = $_GET;
            }
            return $getData;
        }

         /**
         * Resets page to 1 for a grid view.
         * @param $pageVariableName - typically the model class name.
         */
        public static function resetGetPageVariableToNull($pageVariableName)
        {
            $_GET[$pageVariableName . '_page'] = null;
        }

        /**
         * Resolve selectedIds value based on $_GET['selectedIds'].
         */
        public static function resolveSelectedIdsFromGet()
        {
            if (!empty($_GET['selectedIds']))
            {
                return explode(",", $_GET['selectedIds']); // Not Coding Standard
            }
            else
            {
                return array();
            }
        }

        /**
         * Resolve selectAll value based on $_GET['selectAll'].
         */
        public static function resolveSelectAllFromGet()
        {
            if (!empty($_GET['selectAll']))
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        /**
         * Sanitizes get data for date and date time attributes by converting them to the proper
         * format and timezone for saving.  Wrapper for the method with the logic in PostUtil which completes this
         * task.
         * @return - array sanitized get data
         */
        public static function sanitizePostByDesignerTypeForSavingModel($model, $postData)
        {
            return PostUtil::sanitizePostByDesignerTypeForSavingModel($model, $postData);
        }
    }
?>