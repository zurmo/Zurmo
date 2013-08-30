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
     * Helper class for working with customFields during import.  Organizes which values are missing and should be
     * added and what values are missing and should be mapped.
     */
    class CustomFieldsInstructionData
    {
        /**
         * Variable used to indicate a drop down value is missing from zurmo and will need to be added during import.
         * @var string
         */
        const ADD_MISSING_VALUES = 'Add missing value';

        /**
         * Variable used to indicate a drop down value is missing from zurmo and will need to map to an existing value
         * based on what is provided.
         * @var string
         */
        const MAP_MISSING_VALUES = 'Map missing value';

        /**
         * @var array
         */
        protected $missingValuesToAdd = array();

        /**
         * @var array
         */
        protected $missingValuesToMap = array();

        /**
         * @return array
         */
        public function getMissingValuesToAdd()
        {
            return $this->missingValuesToAdd;
        }

        /**
         * @return array
         */
        public function getMissingValuesToMap()
        {
            return $this->missingValuesToMap;
        }

        /**
         * @param $columnName
         * @return boolean
         */
        public function hasDataByColumnName($columnName)
        {
            assert('is_string($columnName)');
            if ((isset($this->missingValuesToAdd[$columnName]) && !empty($this->missingValuesToAdd[$columnName])) ||
               (isset($this->missingValuesToMap[$columnName]) && !empty($this->missingValuesToMap[$columnName])))
            {
                return true;
            }
            return false;
        }

        /**
         * @param $columnName
         * @return array of data specific to the column specified
         */
        public function getDataByColumnName($columnName)
        {
            assert('is_string($columnName)');
            $data = array();
            if (isset($this->missingValuesToAdd[$columnName]))
            {
                $data[static::ADD_MISSING_VALUES] = $this->missingValuesToAdd[$columnName];
            }
            if (isset($this->missingValuesToMap[$columnName]))
            {
                $data[static::MAP_MISSING_VALUES] = $this->missingValuesToMap[$columnName];
            }
            return $data;
        }

        /**
         * @param array $missingCustomFieldValues
         * @param string $columnName
         */
        public function addMissingValuesByColumnName(array $missingCustomFieldValues, $columnName)
        {
            assert('is_array($missingCustomFieldValues)');
            assert('is_string($columnName)');
            foreach ($missingCustomFieldValues as $missingCustomFieldValue)
            {
                $this->resolveMissingValueToAdd($missingCustomFieldValue, $columnName);
            }
        }

        /**
         *
         * @param $instructionsData is the 'customFieldsInstructionData' array element in the mappingData
         * @param string $columnName
         */
        public function addByInstructionsDataAndColumnName($instructionsData, $columnName)
        {
            assert('is_string($columnName)');
            if (isset($instructionsData[static::ADD_MISSING_VALUES]))
            {
                foreach ($instructionsData[static::ADD_MISSING_VALUES] as $missingValueToAdd)
                {
                    $this->resolveMissingValueToAdd($missingValueToAdd, $columnName);
                }
            }
            if (isset($instructionsData[static::MAP_MISSING_VALUES]))
            {
                foreach ($instructionsData[static::MAP_MISSING_VALUES] as $missingValueToMap => $mapToValue)
                {
                    $this->resolveMissingValueToMap($missingValueToMap, $mapToValue, $columnName);
                }
            }
        }

        /**
         * Appends missingValuesToAdd and missingValuesToMap with new data
         * @param CustomFieldsInstructionData $newInstructionsData
         */
        public function resolveForNewData(CustomFieldsInstructionData $newInstructionsData)
        {
            foreach ($newInstructionsData->getMissingValuesToAdd() as $columnName => $columnData)
            {
                foreach ($columnData as $missingValueToAdd)
                {
                    $this->resolveMissingValueToAdd($missingValueToAdd, $columnName);
                }
            }
            foreach ($newInstructionsData->getMissingValuesToMap() as $columnName => $columnData)
            {
                foreach ($columnData as $missingValueToMap  => $mapToValue)
                {
                    $this->resolveMissingValueToMap($missingValueToMap, $mapToValue, $columnName);
                }
            }
        }

        protected function resolveMissingValueToAdd($missingCustomFieldValue, $columnName)
        {
            assert('is_string($missingCustomFieldValue) || is_numeric($missingCustomFieldValue)');
            assert('is_string($columnName)');
            if (!isset($this->missingValuesToAdd[$columnName]))
            {
                $this->missingValuesToAdd[$columnName] = array();
            }
            if (!in_array($missingCustomFieldValue, $this->missingValuesToAdd[$columnName]))
            {
                $this->missingValuesToAdd[$columnName][] = $missingCustomFieldValue;
            }
        }

        /**
         * @param string $missingCustomFieldValue
         * @param string $mapToValue
         * @param string $columnName
         */
        protected function resolveMissingValueToMap($missingCustomFieldValue, $mapToValue, $columnName)
        {
            assert('is_string($missingCustomFieldValue) || is_numeric($missingCustomFieldValue)');
            assert('is_string($mapToValue)');
            assert('is_string($columnName)');
            if (!isset($this->missingValuesToMap[$columnName]))
            {
                $this->missingValuesToMap[$columnName] = array();
            }
            $this->missingValuesToMap[$columnName][$missingCustomFieldValue] = $mapToValue;
        }
    }
?>