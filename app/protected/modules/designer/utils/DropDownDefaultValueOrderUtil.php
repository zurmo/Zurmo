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
     * This class exists to support the adaption between the drop down defaultValue
     * and the user interface for administering the defaultValue.  The user interface must
     * rely on the ordering of the pick list items in the array by their key.  This is in order
     * to support adding pick list items in the user interface on the fly and then making one of those
     * new pick list items the default value before posting any information to be processed.
     * This class has utilities to switch between the defaultValue which is the pick list item 'name' to/from
     * the defaultValueOrder which is the 'order' or 'key' of the array that has the items.
     */
    class DropDownDefaultValueOrderUtil
    {
        public static function getDefaultValueOrderFromDefaultValue(
            $defaultValue, $customFieldDataData)
        {
            assert('$customFieldDataData == null || is_array($customFieldDataData)');
            if ($defaultValue != null)
            {
                return array_search($defaultValue, $customFieldDataData);
            }
            return null;
        }

        public static function getDefaultValueFromDefaultValueOrder(
            $defaultValueOrder, $customFieldDataData)
        {
            assert('$customFieldDataData == null || is_array($customFieldDataData)');
            if ($defaultValueOrder != null && $defaultValueOrder >= 0)
            {
                $defaultValue = $customFieldDataData[$defaultValueOrder];
            }
            else
            {
                $defaultValue = null;
            }
            return $defaultValue;
        }
    }
?>