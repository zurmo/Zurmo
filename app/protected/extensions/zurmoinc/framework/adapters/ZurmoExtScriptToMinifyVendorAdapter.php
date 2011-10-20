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
     * Adapts minify group configs from common.php file to format that is required by minify script
     * @see groupsConfig.php
     */
    class ZurmoExtScriptToMinifyVendorAdapter
    {
        /**
         * Return group config array, used in groupsConfig.php
         * @return array $groupMap
         * @see common.php
         */
        public static function getConfig()
        {
            //Replace THEME_NAME string used in common config, with real theme path, and return modified array
            $groupMap = Yii::app()->minScript->getGroupMap();
            foreach ($groupMap as $groupId => $group)
            {
                foreach ($group as $fileId => $filePath)
                {
                    $groupMap[$groupId][$fileId] = str_replace('THEME_NAME', Yii::app()->theme->name, $filePath);
                }
            }
            return $groupMap;
        }
    }
?>
