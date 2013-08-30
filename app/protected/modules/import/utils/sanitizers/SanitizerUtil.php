<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Base sanitization utility to be overriden as needed for specific sanitizers. Sanitizer utils provide information
     * on the data analyzers to utilize and if sql and batch analysis are supported. Also provides method to perform
     * actual sanitization prior to setting the value in a model to import.
     *
     * Sanitizers can inspect a value and based on rules, decide whether
     * that value is valid or invalid.  This information is then utilized to help users during the import process
     * ensure their import data is correct before finalizing the import.
     */
    abstract class SanitizerUtil
    {
        protected $modelClassName;

        protected $attributeName;

        protected $analysisMessages  = array();

        protected $shouldSkipRow     = false;

        protected $columnName;

        protected $columnMappingData = array();

        protected $mappingRuleData   = array();

        /**
         * Sanitize a value, returning a sanitized value either as the same cast or different cast. This is the final
         * step for importing a row, this method is called as the value from the import column for a row is ready to
         * be populated into a model to save.  This method can also be called in a chain of sanitizers based on what
         * sanitizers are set for a given attribute import rule.
         * depending on the circumstances.
         * @param mixed $value
         */
        abstract public function sanitizeValue($value);

        /**
         * @param $rowBean
         */
        abstract public function analyzeByRow(RedBean_OODBBean $rowBean);

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

        protected static function resolveMappingRuleData($columnMappingData)
        {
            assert('$columnMappingData == null || is_array($columnMappingData)');
            $mappingRuleType = static::getLinkedMappingRuleType();

            if ($mappingRuleType === null || empty($mappingRuleType))
            {
                return array();
            }

            $mappingRuleFormClassName = $mappingRuleType .'MappingRuleForm';
            if (!isset($columnMappingData['mappingRulesData']) ||
                !isset($columnMappingData['mappingRulesData'][$mappingRuleFormClassName]))
            {
                return array();
            }
            else
            {
                $mappingRuleData = $columnMappingData['mappingRulesData'][$mappingRuleFormClassName];
                assert('$mappingRuleData != null');
                return $mappingRuleData;
            }
        }

        /**
         * @param $modelClassName
         * @param $attributeName
         * @param $columnName
         * @param array $columnMappingData
         */
        public function __construct($modelClassName, $attributeName, $columnName, array $columnMappingData)
        {
            assert('is_string($modelClassName)');
            assert('is_string($attributeName) || $attributeName == null');
            assert('is_string($columnName)');
            $this->modelClassName    = $modelClassName;
            $this->attributeName     = $attributeName;
            $this->columnName        = $columnName;
            $this->columnMappingData = $columnMappingData;
            $this->mappingRuleData   = static::resolveMappingRuleData($this->columnMappingData);
            $this->assertMappingRuleDataIsValid();
            $this->init();
        }

        public function shouldSanitizeValue()
        {
            if ($this->columnMappingData["type"] == 'extraColumn')
            {
                return false;
            }
            return true;
        }

        public function getAnalysisMessages()
        {
            return $this->analysisMessages;
        }

        public function getShouldSkipRow()
        {
            return $this->shouldSkipRow;
        }

        /**
         * Override as needed
         */
        protected function init()
        {
        }

        /**
         * Override as needed
         */
        protected function assertMappingRuleDataIsValid()
        {
        }
    }
?>