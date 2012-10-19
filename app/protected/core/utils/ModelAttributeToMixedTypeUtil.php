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
     * Helper functionality for finding the element or
     * form type associated with a model's attribute.
     */
    class ModelAttributeToMixedTypeUtil
    {
        /**
         * Returns the a type that is derived by looking at several different components of an attribute.  This
         * includes the metadata element data, validator data, and relation information.  The type returned can
         * be utilized by different aspects of the application where an attribute type is needed.
         * @return string type
         */
        public static function getType($model, $attributeName)
        {
            assert('$model instanceof RedBeanModel || $model instanceof ModelForm');
            assert('is_string($attributeName) && $attributeName != ""');
            $metadata = $model->getMetadata();
            foreach ($metadata as $className => $perClassMetadata)
            {
                if (isset($perClassMetadata['elements'][$attributeName]))
                {
                    return $perClassMetadata['elements'][$attributeName];
                }
            }
            if ($model->isRelation($attributeName))
            {
                if ($model->getRelationModelClassName($attributeName) == 'User')
                {
                    return 'User';
                }
                return 'DropDown';
            }
            else
            {
                $typeByValidator = static::getTypeByModelUsingValidator($model, $attributeName);
                if ($typeByValidator != null)
                {
                    return $typeByValidator;
                }
            }
            return 'Text';
        }

        public static function getTypeByModelUsingValidator($model, $attributeName)
        {
            assert('$model instanceof RedBeanModel || $model instanceof ModelForm ||
                    $model instanceof ConfigurableMetadataModel');
            assert('is_string($attributeName) && $attributeName != ""');
            $validators = $model->getValidators($attributeName);
            foreach ($validators as $validator)
            {
                switch(get_class($validator))
                {
                    case 'CBooleanValidator':
                        return 'CheckBox';

                    case 'CEmailValidator':
                        return 'Email';

                    case 'RedBeanModelTypeValidator':
                    case 'TypeValidator':
                        switch ($validator->type)
                        {
                            case 'date':
                                return 'Date';

                            case 'datetime':
                                return 'DateTime';

                            case 'integer':
                                return 'Integer';

                            case 'float':
                                return 'Decimal';

                            case 'time':
                                return 'Time';

                            case 'array':
                                throw new NotSupportedException();
                        }
                        break;

                    case 'CUrlValidator':
                        return 'Url';
                }
            }
            return null;
        }
    }
?>
