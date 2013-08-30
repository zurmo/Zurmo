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
     * Helper class to adapt an import's serialized mappingData's customFieldsInstructionData into a model and allow
     * for manipulation and then setting it back into the serialized array
     */
    class ImportToCustomFieldsInstructionDataAdapter
    {
        protected $import;

        /**
         * Given an array of import instructions data, merge this data into the mapping data.
         * @param array $mappingData
         * @param CustomFieldsInstructionData $customFieldsInstructionData
         */
        protected static function resolveMappingData(& $mappingData, CustomFieldsInstructionData $customFieldsInstructionData)
        {
            assert('is_array($mappingData)');
            foreach ($customFieldsInstructionData->getMissingValuesToAdd() as $columnName => $columnData)
            {
                foreach ($columnData as $missingValueToAdd)
                {
                    if (!isset($mappingData[$columnName]))
                    {
                        $mappingData[$columnName] = array();
                    }
                    if (!isset($mappingData[$columnName]['customFieldsInstructionData']))
                    {
                        $mappingData[$columnName]['customFieldsInstructionData'] = array();
                    }
                    if (!isset($mappingData[$columnName]['customFieldsInstructionData']
                                          [CustomFieldsInstructionData::ADD_MISSING_VALUES]))
                    {
                        $mappingData[$columnName]['customFieldsInstructionData']
                                    [CustomFieldsInstructionData::ADD_MISSING_VALUES] = array();
                    }
                    if (!in_array($missingValueToAdd, $mappingData[$columnName]['customFieldsInstructionData']
                                                                 [CustomFieldsInstructionData::ADD_MISSING_VALUES]))
                    {
                        $mappingData[$columnName]['customFieldsInstructionData']
                                    [CustomFieldsInstructionData::ADD_MISSING_VALUES][] = $missingValueToAdd;
                    }
                }
            }
            foreach ($customFieldsInstructionData->getMissingValuesToMap() as $columnName => $columnData)
            {
                foreach ($columnData as $missingValueToMap  => $mapToValue)
                {
                    if (!isset($mappingData[$columnName]))
                    {
                        $mappingData[$columnName] = array();
                    }
                    if (!isset($mappingData[$columnName]['customFieldsInstructionData']))
                    {
                        $mappingData[$columnName]['customFieldsInstructionData'] = array();
                    }
                    if (!isset($mappingData[$columnName]['customFieldsInstructionData']
                                          [CustomFieldsInstructionData::MAP_MISSING_VALUES]))
                    {
                        $mappingData[$columnName]['customFieldsInstructionData']
                                    [CustomFieldsInstructionData::MAP_MISSING_VALUES] = array();
                    }
                    if (!isset($mappingData[$columnName]['customFieldsInstructionData']
                                          [CustomFieldsInstructionData::MAP_MISSING_VALUES][$missingValueToMap]))
                    {
                        $mappingData[$columnName]['customFieldsInstructionData']
                                    [CustomFieldsInstructionData::MAP_MISSING_VALUES][$missingValueToMap] = $mapToValue;
                    }
                }
            }
        }

        public function __construct(Import $import)
        {
            $this->import = $import;
        }

        public function appendCustomFieldsInstructionData(CustomFieldsInstructionData $newInstructionsData)
        {
            $unserializedData                = unserialize($this->import->serializedData);
            $existingInstructionsData        = new CustomFieldsInstructionData();
            if (isset($unserializedData['mappingData']))
            {
                $mappingData = $unserializedData['mappingData'];
                $this->populateCustomFieldsInstructionDataByMappingData($existingInstructionsData, $unserializedData['mappingData']);
            }
            else
            {
                $mappingData = array();
            }
            $existingInstructionsData->resolveForNewData($newInstructionsData);
            static::resolveMappingData($mappingData, $existingInstructionsData);
            $unserializedData['mappingData'] = $mappingData;
            $this->import->serializedData    = serialize($unserializedData);
        }

        protected function populateCustomFieldsInstructionDataByMappingData(
                           CustomFieldsInstructionData $instructionsData, array $mappingData)
        {
            foreach ($mappingData as $columnName => $columnMappingData)
            {
                if (isset($mappingData[$columnName]['customFieldsInstructionData']))
                {
                    $instructionsData->addByInstructionsDataAndColumnName($mappingData[$columnName]['customFieldsInstructionData'], $columnName);
                }
            }
        }
    }
?>