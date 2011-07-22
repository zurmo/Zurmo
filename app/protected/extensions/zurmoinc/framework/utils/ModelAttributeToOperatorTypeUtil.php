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
     * Given a model and its attribute, find the appropriate operator type.
     */
    class ModelAttributeToOperatorTypeUtil
    {
        /**
         * Returns the operator type
         * that should be used with the named attribute
         * of the given model.  If the model is a customField, it assumes some sort of dropdown and returns
         * 'equals'.
         * @param $model - instance of a RedBeanModel or RedBeanModels if the model is a HAS_MANY relation on the
         *                 original model.
         */
        public static function getOperatorType($model, $attributeName)
        {
            assert('$model instanceof RedBeanModel || $model instanceof RedBeanModels || $model instanceof ModelForm');
            assert('is_string($attributeName) && $attributeName != ""');
            if (get_class($model) == 'CustomField' || $attributeName == 'id')
            {
                return 'equals';
            }
            $metadata = $model->getMetadata();
            foreach ($metadata as $className => $perClassMetadata)
            {
                if (isset($perClassMetadata['elements'][$attributeName]))
                {
                    $operatorType = self::getOperatorTypeFromModelMetadataElement($perClassMetadata['elements'][$attributeName]);
                    if ($operatorType == null)
                    {
                        break;
                    }
                    else
                    {
                        return $operatorType;
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
                            return 'equals';

                        case 'CEmailValidator':
                            return 'startsWith';

                        case 'RedBeanModelTypeValidator':
                            switch ($validator->type)
                            {
                                case 'date':
                                    return 'equals';

                                case 'datetime':
                                    return 'equals';

                                case 'integer':
                                    return 'equals';

                                case 'float':
                                    return 'equals';

                                case 'time':
                                    return 'equals';

                                case 'array':
                                    throw new NotSupportedException();
                            }
                            break;

                        case 'CUrlValidator':
                            return 'contains';
                    }
                }
            }
            return 'startsWith';
        }

        protected static function getOperatorTypeFromModelMetadataElement($element)
        {
            assert('is_string($element)');
            switch ($element)
            {
                case 'CurrencyValue':        //todo: once currency has validation rules, this can be removed.
                    return 'equals';

                case 'DropDown':
                    return 'equals';

                case 'MultiSelectDropDown':        //tbd.
                    return 'equals';        //tbd.

                case 'Phone':
                    return 'startsWith';

                case 'RadioDropDown':
                    return 'equals';

                case 'TextArea':
                    return 'contains';

                default :
                    null;
            }
        }
    }
?>