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
     * Base sanitization utility to be overriden as needed for specific sanitizers. Sanitizer utils provide information
     * on the data analyzers to utilize and if sql and batch analysis are supported. Also provides method to perform
     * actual sanitization prior to setting the value in a model to import.
     */
    abstract class SanitizerUtil
    {
        /**
         * @return string - type of sanitizer
         */
        public static function getType()
        {
            $type = get_called_class();
            $type = substr($type, 0, strlen($type) - strlen('SanitizerUtil'));
            return $type;
        }

        /**
         * @return boolean - whether this sanitizer supports sql analysis which does an analysis using a sql query.
         */
        public static function supportsSqlAttributeValuesDataAnalysis()
        {
            return true;
        }

        /**
         * @return boolean - whether this sanitizer supports batch analysis which does analysis looping over rows
         * of data using a data provider.
         */
        public static function supportsBatchAttributeValuesDataAnalysis()
        {
            return true;
        }

        /**
         *
         * Given a model class name and attribute name or names, make a sql analyzer and return it.
         * @param string $modelClassName
         * @param array $attributeName
         * @throws NotSupportedException
         */
        public static function makeSqlAttributeValueDataAnalyzer($modelClassName, $attributeName)
        {
            assert('is_string($modelClassName)');
            assert('is_string($attributeName) || $attributeName == null');
            $sqlAttributeValueDataAnalyzerType = static::getSqlAttributeValueDataAnalyzerType();
            if($sqlAttributeValueDataAnalyzerType == null)
            {
                throw new NotSupportedException();
            }
            $sqlAttributeValueDataAnalyzerClassName = $sqlAttributeValueDataAnalyzerType .
                                                      'SqlAttributeValueDataAnalyzer';
            return new $sqlAttributeValueDataAnalyzerClassName($modelClassName, $attributeName);
        }

        /**
         * Given a model class name and attribute name or names, make a batch analyzer and return it.
         * @param string $modelClassName
         * @param array $attributeName
         */
        public static function makeBatchAttributeValueDataAnalyzer($modelClassName, $attributeName)
        {
            assert('is_string($modelClassName)');
            assert('is_string($attributeName) || $attributeName == null');
            $batchAttributeValueDataAnalyzerType = static::getBatchAttributeValueDataAnalyzerType();
            if($batchAttributeValueDataAnalyzerType == null)
            {
                throw new NotSupportedException();
            }
            $batchAttributeValueDataAnalyzerClassName = $batchAttributeValueDataAnalyzerType .
                                                        'BatchAttributeValueDataAnalyzer';
            return new $batchAttributeValueDataAnalyzerClassName($modelClassName, $attributeName);
        }

        /**
         * @return string - the type of sql attribute value data analyzer or null if none available.
         */
        public static function getSqlAttributeValueDataAnalyzerType()
        {
            return null;
        }

        /**
         *
         * @return string - the type of batch attribute value data analyzer or null if none available.
         */
        public static function getBatchAttributeValueDataAnalyzerType()
        {
            return null;
        }

        /**
         *
         * @return string - the type of linked mapping rule  or null if none available.  Some sanitizers and
         * data analyzers need information from a mapping rule form in order to perform their job. This method
         * returns the type of mapping rule form.
         */
        public static function getLinkedMappingRuleType()
        {
            return null;
        }

        /**
         * @return boolean - whether this sanitizer supports the use of instructions data in the form of an array
         * during sanitization. An example is drop downs, which has possible instructional information regarding
         * what to do with missing drop down values.
         * @see self::sanitizeValueWithInstructions()
         */
        public static function supportsSanitizingWithInstructions()
        {
            return false;
        }

        /**
         * Sanitize a value with possible instructions data that was developed during data anlysis. This is the final
         * step for importing a row, this method is called as the value from the import column for a row is ready to
         * be populated into a model to save.  This method can also be called in a chain of sanitizers based on what
         * sanitizers are set for a given attribute import rule.
         * @see self::supportsSanitizingWithInstructions()
         * @param mixed $value
         * @param null or array $mappingRuleData
         * @param array $importInstructionsData
         */
        public static function sanitizeValueWithInstructions($modelClassName, $attributeName,
                                                             $value, $mappingRuleData, $importInstructionsData)
        {
            throw new NotImplementedException();
        }

        /**
         * Sanitize a value, returning a sanitized value either as the same cast or different cast. This is the final
         * step for importing a row, this method is called as the value from the import column for a row is ready to
         * be populated into a model to save.  This method can also be called in a chain of sanitizers based on what
         * sanitizers are set for a given attribute import rule.
         * depending on the circumstances.
         * @param mixed $value
         * @param array $mappingRuleData
         */
        public static function sanitizeValue($modelClassName, $attributeName,
                                             $value, $mappingRuleData)
        {
            throw new NotImplementedException();
        }

        /**
         * If the sanitization of a value fails, should the entire row that is trying to be imported be ignored?
         * Override if you want to change this value.  Returning false means save the import row anyways regardless
         * of if a sanitization of a value failed.  If the model has other validation errors, these will block saving
         * the model regardless of what value is returned here.
         * @return boolean
         */
        public static function shouldNotSaveModelOnSanitizingValueFailure()
        {
            return false;
        }
    }
?>