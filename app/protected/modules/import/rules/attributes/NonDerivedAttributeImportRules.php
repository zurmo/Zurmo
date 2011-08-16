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
     * Base class for defining a non-derived attribute's import rules.
     */
    abstract class NonDerivedAttributeImportRules extends AttributeImportRules
    {
        public function resolveValueToImport($value, $columnMappingData, & $shouldSaveModel)
        {
            $attributeNames = $this->getModelAttributeNames();
            assert('count($attributeNames) == 1');
            $attributeName  = $attributeNames[0];
            $modelClassName =$this->getModelClassName();
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