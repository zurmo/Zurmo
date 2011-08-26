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
     * form type associated with a model's attribute.
     */
    class ModelAttributeToDesignerTypeUtil extends ModelAttributeToMixedTypeUtil
    {
        private static $availableDesignerTypes;
        /**
         * Returns the element or attribute form type
         * that should be used with the named attribute
         * of the given model, (the name minus the Element
         * or AttributeForm suffix).
         */
        public static function getDesignerType($model, $attributeName)
        {
            return ModelAttributeToMixedTypeUtil::getType($model, $attributeName);
        }

        /**
         * Returns list of available designer types including
         * attribute types available for creating custom fields
         * and designer types for standard collection attributes that
         * are not necessarily available types for custom fields.
         *
         */
        public static function getAvailableDesignerTypes()
        {
            if (self::$availableDesignerTypes != null)
            {
                return self::$availableDesignerTypes;
            }
            $modules = Module::getModuleObjects();
            $designerTypes = array();
            foreach ($modules as $module)
            {
                $formsClassNames = $module::getAllClassNamesByPathFolder('forms');
                foreach ($formsClassNames as $formClassName)
                {
                    $classToEvaluate     = new ReflectionClass($formClassName);
                    if (is_subclass_of($formClassName, 'AttributeForm') && !$classToEvaluate->isAbstract())
                    {
$designerTypes[] = substr($formClassName, 0, strlen($formClassName) - strlen('AttributeForm'));
                    }
                }
            }
            self::$availableDesignerTypes = $designerTypes;
            return self::$availableDesignerTypes;
        }

        /**
         * Returns list of available attribute types for
         * creating new custom fields
         */
        public static function getAvailableCustomAttributeTypes()
        {
            return array(
                'CheckBox',
                'CurrencyValue',
                'Date',
                'DateTime',
                'Decimal',
                'DropDown',
                'Integer',
                //'MultiSelectDropDown', Turn on once this feature is finished.
                'Phone',
                'RadioDropDown',
                'Text',
                'TextArea',
                'Url',
            );
        }
    }
?>
