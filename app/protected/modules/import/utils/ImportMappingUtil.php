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
     * Helper utility for working with import mapping.
     */
    class ImportMappingUtil
    {
        /**
         * Given an import data's table name, create a basic mapping data array that has the correct starting
         * elements set as null.  This will ensure the mapping data array is always structured correctly.  Each key
         * will be a column name from the table.  Throws an exception if the table is missing rows.
         * @param string $tableName
         * @return array $mappingData
         */
        public static function makeMappingDataByTableName($tableName)
        {
            assert('is_string($tableName)');
            $firstRowData = ImportDatabaseUtil::getFirstRowByTableName($tableName);
            if(count($firstRowData) == 1 || count($firstRowData) == 0)
            {
                throw new NoRowsInTableException();
            }
            //handles scenario where there is no data in the file, but because there are a few bytes,
            //it creates a single row.
            if(count($firstRowData) == 2)
            {
                foreach($firstRowData as $columnName => $value)
                {
                    if($columnName != 'id' && $value == null)
                    {
                        if(ImportDatabaseUtil::getCount($tableName) == 1)
                        {
                            throw new NoRowsInTableException();
                        }
                    }
                }
            }
            $mappingData = array();
            foreach($firstRowData as $columnName => $notUsed)
            {
                if($columnName != 'id')
                {
                    $mappingData[$columnName] = array('type'                        => 'importColumn',
                                                      'attributeIndexOrDerivedType' => null,
                                                      'mappingRulesData'            => null);
                }
            }
            return $mappingData;
        }

        /**
         * Given an array of mapping data, extract the 'attributeIndexOrDerivedType' from each sub array
         * in the mapping data and return an array of the attributeIndexOrDerivedType.  This is useful if you
         * just need a single dimension array of this information based on the mapping data.
         * @param array $mappingData
         * @return array
         */
        public static function getMappedAttributeIndicesOrDerivedAttributeTypesByMappingData($mappingData)
        {
            assert('is_array($mappingData)');
            $mappedAttributeIndicesOrDerivedAttributeTypes = null;
            foreach($mappingData as $data)
            {
                if($data['attributeIndexOrDerivedType'] != null)
                {
                    $mappedAttributeIndicesOrDerivedAttributeTypes[] = $data['attributeIndexOrDerivedType'];
                }
            }
            return $mappedAttributeIndicesOrDerivedAttributeTypes;
        }

        /**
         * Given a column count, create a suitable column name. An example would be column_5, where 5 would have been
         * the column count that was passed in.  This pattern column_x matches the pattern used by redbean to generate
         * column names.
         * @param integer $columnCount
         */
        public static function makeExtraColumnNameByColumnCount($columnCount)
        {
            assert('is_int($columnCount)');
            return 'column_' . ($columnCount + 1);
        }

        /**
         * Given an array of post data, re-index the column names that are of type 'extraColumn'. This method is
         * needed since it is possible that a user can add, remove extra columns in such a way that produces column
         * names that are missing index orders.  This will fix the extra column column names and return the data.
         * @param array $postData
         */
        public static function reIndexExtraColumnNamesByPostData($postData)
        {
            assert('is_array($postData)');
            $reIndexedData     = array();
            $importColumnCount = 0;
            $tempData          = array();
            foreach($postData as $columnName => $data)
            {
                assert('$data["type"] == "importColumn" || $data["type"] == "extraColumn"');
                if($data['type'] == 'extraColumn')
                {
                    $tempData[] = $data;
                }
                else
                {
                    $reIndexedData[$columnName] = $data;
                    $importColumnCount ++;
                }
            }
            $extraColumnStartingCount = $importColumnCount - 1;
            foreach($tempData as $data)
            {
                $reIndexedData[self::makeExtraColumnNameByColumnCount($extraColumnStartingCount)] = $data;
                $extraColumnStartingCount ++;
            }
            return $reIndexedData;
        }

        /**
         * Given an array of import instructions data, merge this data into the mapping data.
         * @param array $mappingData
         * @param array $importInstructionsData
         */
        public static function resolveImportInstructionsDataIntoMappingData($mappingData, $importInstructionsData)
        {
            assert('is_array($mappingData)');
            assert('is_array($importInstructionsData) || $importInstructionsData == null');
            foreach($mappingData as $columnName => $columnMappingData)
            {
                if($importInstructionsData == null && isset($columnMappingData['importInstructionsData']))
                {
                    unset($mappingData[$columnName]['importInstructionsData']);
                }
                elseif($importInstructionsData != null && isset($importInstructionsData[$columnName]))
                {
                    $mappingData[$columnName]['importInstructionsData'] = $importInstructionsData[$columnName];
                }
            }
            return $mappingData;
        }

        /**
         *
         * Make an array of index/values that are the column names and their respective labels.
         * @param array $mappingData
         * @param array $importRulesType
         */
        public static function makeColumnNamesAndAttributeIndexOrDerivedTypeLabels($mappingData, $importRulesType)
        {
            assert('is_array($mappingData)');
            assert('is_string($importRulesType)');
            $columnNamesAndAttributeIndexOrDerivedTypeLabels = array();
            foreach($mappingData as $columnName => $columnData)
            {
                if($columnData['attributeIndexOrDerivedType'] != null)
                {
                    $attributeImportRules = AttributeImportRulesFactory::
                                            makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                            $importRulesType, $columnData['attributeIndexOrDerivedType']);
                    $columnNamesAndAttributeIndexOrDerivedTypeLabels[$columnName] = $attributeImportRules->getDisplayLabel();
                }
                else
                {
                    $columnNamesAndAttributeIndexOrDerivedTypeLabels[$columnName] = null;
                }
            }
            return $columnNamesAndAttributeIndexOrDerivedTypeLabels;
        }

    }
?>