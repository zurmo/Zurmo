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
     * Helper functionality for finding the trigger value element
     * associated with a model's attribute
     */
    class ModeAttributeToWorkflowTriggerValueElementTypeUtil
    {
        /**
         * @param $model
         * @param string $attributeName
         * @return string
         * @throws NotSupportedException if the attributeName is a relation on the model
         */
        public static function getType($model, $attributeName)
        {
            assert('is_string($attributeName)');
            if ($attributeName == 'id')
            {
                return 'Text';
            }
            if ($model->$attributeName instanceof CustomField)
            {
                return 'StaticDropDownForWorkflow';
            }
            if ($model->$attributeName instanceof MultipleValuesCustomField)
            {
                return 'StaticMultiSelectDropDownForWorkflow';
            }
            $metadata = $model->getMetadata();
            foreach ($metadata as $className => $perClassMetadata)
            {
                if (isset($perClassMetadata['elements'][$attributeName]))
                {
                    $operatorType = self::getAvailableOperatorsTypeFromModelMetadataElement(
                                                $perClassMetadata['elements'][$attributeName]);
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
                throw new NotSupportedException('Unsupported type for Model Class: ' . get_class($model) .
                                                ' with attribute: ' . $attributeName);
            }
            else
            {
                $validators = $model->getValidators($attributeName);
                foreach ($validators as $validator)
                {
                    switch(get_class($validator))
                    {
                        case 'CBooleanValidator':
                            return 'BooleanForWizardStaticDropDown';

                        case 'CEmailValidator':
                            return 'Text';

                        case 'RedBeanModelTypeValidator':
                        case 'TypeValidator':
                            switch ($validator->type)
                            {
                                case 'date':
                                    return 'MixedDateTypesForWorkflow';

                                case 'datetime':
                                    return 'MixedDateTypesForWorkflow';

                                case 'integer':
                                    return 'MixedNumberTypes';

                                case 'float':
                                    return 'MixedNumberTypes';

                                case 'time':
                                    throw new NotSupportedException();

                                case 'array':
                                    throw new NotSupportedException();
                                case 'string':
                                    return 'Text';
                            }
                            break;

                        case 'CUrlValidator':
                            return 'Text';
                    }
                }
            }
            throw new NotSupportedException();
        }

        /**
         * @param string $elementType
         * @return null|string
         */
        protected static function getAvailableOperatorsTypeFromModelMetadataElement($elementType)
        {
            assert('is_string($elementType)');
            switch ($elementType)
            {
                case 'CurrencyValue':
                    return 'MixedCurrencyValueTypes';
                case 'Phone':
                    return 'Text';
                case 'TextArea':
                    return 'Text';
                default :
                    return null;
            }
        }
    }
?>
