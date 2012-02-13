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
     * Helper functionality for use in accessing type information at runtime.
     */
    class RuntimeUtil
    {
        private static $classNamesToHierarchies = array();

        /**
         * Returns an array of the name of the class and each of its
         * parent classes up to but not including the given class.
         * @param $className The class name to start with.
         * @param $upToAndNotIncludingClassName The class name to end with, and which
         *        is not included in the returned array.
         * @returns An array of strings, starting with $className, and
         *          containing each of the class names in its inheritance
         *          hierachy.
         */
        public static function getClassHierarchy($className, $upToAndNotIncludingClassName)
        {
            assert('is_string($className) && $className != ""');
            assert('is_string($upToAndNotIncludingClassName) && $upToAndNotIncludingClassName != ""');
            $key = "$className/$upToAndNotIncludingClassName";
            if (!array_key_exists($key, RuntimeUtil::$classNamesToHierarchies))
            {
                $modelClassNames = array();
                $modelClassName = $className;
                do
                {
                    $modelClassNames[] = $modelClassName;
                    $modelClassName = get_parent_class($modelClassName);
                } while ($modelClassName != $upToAndNotIncludingClassName);
                RuntimeUtil::$classNamesToHierarchies[$key] = $modelClassNames;
            }
            return RuntimeUtil::$classNamesToHierarchies[$key];
        }
    }
?>
