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
     * Given a model and its attribute, find the appropriate cast.
     */
    class ModelAttributeToCastTypeUtil
    {
        /**
         * For a given $attributeName on a $model, set the cast of the $value based on what the castType should be for
         * that attribute.
         * @param $model
         * @param $attributeName
         * @param $value
         * @see ModelAttributeToCastTypeUtil::getCastType
         * @return casted $value
         */
        public static function resolveValueForCast($model, $attributeName, $value)
        {
            $castType = self::getCastType($model, $attributeName);
            settype($value, $castType);
            return $value;
        }

        /**
         * Returns the cast type that should be used with the named attribute
         * of the given model.  If the model is a customField, it assumes some sort of dropdown and returns
         * 'equals'.
         */
        public static function getCastType($model, $attributeName)
        {
            assert('$model instanceof RedBeanModel || $model instanceof RedBeanModels || $model instanceof ModelForm');
            assert('is_string($attributeName) && $attributeName != ""');
            if ($attributeName == 'id')
            {
                return 'int';
            }
            if ($model instanceof CustomField)
            {
                return 'string';
            }
            if ($model instanceof MultipleValuesCustomField)
            {
                return 'array';
            }
            $metadata = $model->getMetadata();
            foreach ($metadata as $className => $perClassMetadata)
            {
                if (isset($perClassMetadata['elements'][$attributeName]))
                {
                    $castType = self::getOperatorTypeFromModelMetadataElement($perClassMetadata['elements'][$attributeName]);
                    if ($castType == null)
                    {
                        break;
                    }
                    else
                    {
                        return $castType;
                    }
                }
            }
            if ($model->isRelation($attributeName))
            {
                throw new NotSupportedException();
            }
            else
            {
                $validators = $model->getValidators($attributeName);
                foreach ($validators as $validator)
                {
                    switch(get_class($validator))
                    {
                        case 'CBooleanValidator':
                            return 'bool';

                        case 'CEmailValidator':
                            return 'string';

                        case 'RedBeanModelTypeValidator':
                        case 'TypeValidator':
                            switch ($validator->type)
                            {
                                case 'date':
                                    return 'string';

                                case 'datetime':
                                    return 'string';

                                case 'integer':
                                    return 'int';

                                case 'float':
                                    return 'float';

                                case 'time':
                                    return 'string';

                                case 'array':
                                    throw new NotSupportedException();
                            }
                            break;

                        case 'CUrlValidator':
                            return 'string';
                    }
                }
            }
            return 'string';
        }

        protected static function getOperatorTypeFromModelMetadataElement($element)
        {
            assert('is_string($element)');
            switch ($element)
            {
                case 'CurrencyValue': //todo: once currency has validation rules, this can be removed.
                    return 'float';

                case 'DropDown':
                    return 'int';

                case 'MultiSelectDropDown':        //tbd.
                    return 'string';        //tbd.

                case 'Phone':
                    return 'string';

                case 'RadioDropDown':
                    return 'int';

                default :
                    return null;
            }
        }
    }
?>