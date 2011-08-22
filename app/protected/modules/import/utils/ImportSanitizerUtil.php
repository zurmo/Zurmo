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
     * Helper class for working with sanitizers.
     */
    class ImportSanitizerUtil
    {
        /**
         * Given an array of sanitizer util types, a value, as well as several other parameters, run through each
         * sanitizer type on the value and process any sanitization messages or errors into the ImportSanitizeResultsUtil
         * provided.
         * @param array $sanitizerUtilTypes
         * @param string $modelClassName
         * @param string $attributeName
         * @param mixed $value
         * @param array $columnMappingData
         * @param ImportSanitizeResultsUtil $importSanitizeResultsUtil
         */
        public static function sanitizeValueBySanitizerTypes($sanitizerUtilTypes, $modelClassName,
                                                             $attributeName, $value, $columnMappingData,
                                                             ImportSanitizeResultsUtil $importSanitizeResultsUtil)
        {
            assert('is_array($sanitizerUtilTypes)');
            assert('is_string($modelClassName)');
            assert('is_string($attributeName) || $attributeName == null');
            assert('is_array($columnMappingData)');
            foreach($sanitizerUtilTypes as $sanitizerUtilType)
            {
                $sanitizerUtilClassName = $sanitizerUtilType . 'SanitizerUtil';
                $mappingRuleType = $sanitizerUtilClassName::getLinkedMappingRuleType();
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
                            assert('isset($columnMappingData["importInstructionsData"])');
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
                      if($e->getMessage() != null)
                      {
                          $label = LabelUtil::makeModelAndAttributeNameCombinationLabel($modelClassName, $attributeName);
                          $importSanitizeResultsUtil->addMessage($label . ' ' . $e->getMessage());
                      }
                      $value = null;
                      if($sanitizerUtilClassName::shouldNotSaveModelOnSanitizingValueFailure())
                      {
                          $importSanitizeResultsUtil->setModelShouldNotBeSaved();
                      }
                      break;
                  }
            }
            return $value;
        }
    }
?>