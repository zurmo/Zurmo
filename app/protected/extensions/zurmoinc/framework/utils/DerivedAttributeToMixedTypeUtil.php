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
     * Helper functionality for finding the element or
     * form type associated with a derived attribute
     */
    class DerivedAttributeToMixedTypeUtil
    {
        /**
         * Returns the a type that is derived by looking at several different components of an attribute.  This
         * includes the metadata element data, validator data, and relation information.  The type returned can
         * be utilized by different aspects of the application where an attribute type is needed.
         * @return string type
         */
        public static function getType($modelClassName, $attributeName)
        {
            assert('is_string($modelClassName) && $modelClassName != ""');
            assert('is_string($attributeName) && $attributeName != ""');
            try
            {
                $models = CalculatedDerivedAttributeMetadata::
                          getByNameAndModelClassName($attributeName, $modelClassName);
                if (count($models) == 1)
                {
                    return 'CalculatedNumber';
                }
            }
            catch(NotFoundException $e)
            {
            }
            try
            {
                $models = DropdownDependencyDerivedAttributeMetadata::
                          getByNameAndModelClassName($attributeName, $modelClassName);
                if (count($models) == 1)
                {
                    return 'DropDownDependency';
                }
            }
            catch(NotFoundException $e)
            {
                throw new NotImplementedException($attributeName . 'M' . $modelClassName);
            }
        }
    }
?>
