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
            foreach ($sanitizerUtilTypes as $sanitizerUtilType)
            {
                $sanitizerUtilClassName = $sanitizerUtilType . 'SanitizerUtil';
                //For extra columns, only process sanitization for 'required' since that will add the default values.
                //Other sanitization is not required since extra columns are not fed from external data.
                if ($columnMappingData["type"] == 'extraColumn' &&
                   !is_subclass_of($sanitizerUtilClassName, 'RequiredSanitizerUtil') &&
                   $sanitizerUtilClassName != 'RequiredSanitizerUtil')
                {
                   continue;
                }
                $mappingRuleType = $sanitizerUtilClassName::getLinkedMappingRuleType();
                if ($mappingRuleType != null)
                {
                    assert('$mappingRuleType != null');
                    $mappingRuleFormClassName = $mappingRuleType .'MappingRuleForm';
                    if (!isset($columnMappingData['mappingRulesData'][$mappingRuleFormClassName]))
                    {
                        assert('$columnMappingData["type"] = "extraColumn"');
                        $mappingRuleData = null;
                    }
                    else
                    {
                        $mappingRuleData = $columnMappingData['mappingRulesData'][$mappingRuleFormClassName];
                    }
                }
                else
                {
                    $mappingRuleData = null;
                }
                try
                {
                    if ($sanitizerUtilClassName::supportsSanitizingWithInstructions())
                    {
                        if (!empty($columnMappingData['importInstructionsData']))
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
                catch (InvalidValueToSanitizeException $e)
                {
                    if ($e->getMessage() != null)
                    {
                        if ($attributeName != null)
                        {
                            $label = LabelUtil::makeModelAndAttributeNameCombinationLabel($modelClassName, $attributeName);
                        }
                        else
                        {
                            $label = $modelClassName::getModelLabelByTypeAndLanguage('Singular') . ' -';
                        }
                        $importSanitizeResultsUtil->addMessage($label . ' ' . $e->getMessage());
                    }
                    $value = null;
                    if ($sanitizerUtilClassName::shouldNotSaveModelOnSanitizingValueFailure())
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