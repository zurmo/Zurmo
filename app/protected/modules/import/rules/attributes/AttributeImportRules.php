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
     * Base class for defining an attribute or derived attribute's import rules.
     */
    abstract class AttributeImportRules
    {
        protected $model;

        protected $attributeName;

        public function __construct($model, $attributeName = null)
        {
            assert('$model instanceof RedBeanModel');
            assert('is_string($attributeName) || $attributeName == null');
            $this->model         = $model;
            $this->attributeName = $attributeName;
        }

        public function getModelClassName()
        {
            return get_class($this->model);
        }

        public function getDisplayLabel()
        {
            return $this->model->getAttributeLabel($this->attributeName);
        }

        public function getDisplayLabelByAttributeName($attributeName)
        {
            assert('$attributeName == null || is_string($attributeName)');
            return $this->model->getAttributeLabel($attributeName);
        }

        public function getModelAttributeNames()
        {
            return array($this->attributeName);
        }

        /**
         * Returns mapping rule form and the associated element to use.  Override to specify as many
         * pairings as needed.
         * @return array of MappingRuleForm/Element pairings.
         */
        public static function getModelAttributeMappingRuleFormTypesAndElementTypes($type)
        {
            assert('$type == "importColumn" || $type == "extraColumn"');

            $forAllData = static::getAllModelAttributeMappingRuleFormTypesAndElementTypes();
            if($type == 'extraColumn')
            {
                $typeBasedData  = static::getExtraColumnOnlyModelAttributeMappingRuleFormTypesAndElementTypes();
            }
            else
            {
                $typeBasedData  = static::getImportColumnOnlyModelAttributeMappingRuleFormTypesAndElementTypes();
            }
            return array_merge($forAllData, $typeBasedData);
        }

        /**
         * Returns mapping rule form and the associated element to use.  Override to specify as many
         * pairings as needed. This method is used for mapping rule form/element pairings that are available for
         * both types of columns.
         * @return array of MappingRuleForm/Element pairings.
         */
        protected static function getAllModelAttributeMappingRuleFormTypesAndElementTypes()
        {
            return array();
        }

        /**
         * Override to place mapping rule forms / elements that are only for mapping extra columns.
         */
        protected static function getExtraColumnOnlyModelAttributeMappingRuleFormTypesAndElementTypes()
        {
            return array();
        }

        /**
         * Override to place mapping rule forms / elements that are only for mapping actual import columns.
         */
        protected static function getImportColumnOnlyModelAttributeMappingRuleFormTypesAndElementTypes()
        {
            return array();
        }

        /**
         * @return array of sanitizer util names. The sanitizer utils in the array are in the order that they will
         * be processed during the import.
         */
        public static function getSanitizerUtilTypesInProcessingOrder()
        {
            return array();
        }

        protected function sanitizeValueForImport($modelClassName, $attributeName, $value,
                                                              $columnMappingData, & $shouldSaveModel)
        {
            assert('is_string($modelClassName)');
            assert('is_string($attributeName) || $attributeName == null');
            assert('is_array($columnMappingData)');
            assert('is_bool($shouldSaveModel)');
            foreach(static::getSanitizerUtilTypesInProcessingOrder() as $sanitizerUtilType)
            {
                $sanitizerUtilClassName = $sanitizerUtilType . 'SanitizerUtil';
                $mappingRuleType = $attributeValueSanitizerUtilClassName::getLinkedMappingRuleType();
                if($mappingRuleType != null)
                {
                    assert('$mappingRuleType != null');
                    $mappingRuleFormClassName = $mappingRuleType .'MappingRuleForm';
                    $mappingRuleData = $columnMappingData['mappingRulesData'][$mappingRuleFormClassName];
                    assert('$mappingRuleData != null');
                }
                else
                {
                    $mappingRuleData = null;
                }
                  try
                  {
                      if($sanitizerUtilClassName::supportsSanitizingWithInstructions())
                      {
                        if($columnMappingData['importInstructionsData'] != null)
                        {
                            $importInstructionsData = $columnMappingData['importInstructionsData'];
                        }
                        else
                        {
                            $importInstructionsData = null;
                        }
                          $value = $sanitizerUtilClassName::
                                   sanitizeValueWithInstructions($modelClassName, $attributeName,
                                                                 $value, $mappingRuleData, $importInstructionsData);
                      }
                      else
                      {
                          $value = $sanitizerUtilClassName::
                                   sanitizeValue($modelClassName, $attributeName, $value, $mappingRuleData);
                      }
                  }
                  catch(InvalidValueToSanitizeException $e)
                  {
                      $value = null;
                      if($sanitizerUtilClassName::shouldNotSaveModelOnSanitizingValueFailure())
                      {
                          $shouldSaveModel = false;
                      }
                  }
            }
            return $value;
        }
    }
?>