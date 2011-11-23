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
     * Data analyzer for columns mapped to drop down type attributes.  Values found in the import data but not
     * in the zurmo CustomFieldData will be added to the instructionsData array.
     */
    class DropDownSqlAttributeValueDataAnalyzer extends SqlAttributeValueDataAnalyzer
                                                implements DataAnalyzerInterface
    {
        /**
         * @see DataAnalyzerInterface::runAndMakeMessages()
         */
        public function runAndMakeMessages(AnalyzerSupportedDataProvider $dataProvider, $columnName)
        {
            assert('is_string($columnName)');
            $customFieldData  = CustomFieldDataModelUtil::
                                getDataByModelClassNameAndAttributeName($this->modelClassName,
                                                                       $this->attributeName);
            $dropDownValues   = unserialize($customFieldData->serializedData);
            $dropDownValues   = ArrayUtil::resolveArrayToLowerCase($dropDownValues);
            $data             = $dataProvider->getCountDataByGroupByColumnName($columnName);
            $count            = 0;
            $missingDropDowns = null;
            foreach ($data as $valueCountData)
            {
                if ($valueCountData[$columnName] == null)
                {
                    continue;
                }
                if($missingDropDowns != null)
                {
                    $lowerCaseMissingValuesToMap = ArrayUtil::resolveArrayToLowerCase($missingDropDowns);
                }
                else
                {
                    $lowerCaseMissingValuesToMap = array();
                }
                if(!in_array(strtolower($valueCountData[$columnName]), $dropDownValues) &&
                   !in_array(strtolower($valueCountData[$columnName]), $lowerCaseMissingValuesToMap))
                {
                    $missingDropDowns[] = $valueCountData[$columnName];
                    $count++;
                }
            }
            if ($count > 0)
            {
                $label   = '{count} dropdown value(s) are missing from the field. ';
                $label  .= 'These values will be added upon import.';
                $this->addMessage(Yii::t('Default', $label, array('{count}' => $count)));
            }

            if ($missingDropDowns != null)
            {
                $instructionsData = array(DropDownSanitizerUtil::ADD_MISSING_VALUE => $missingDropDowns);
                $this->setInstructionsData($instructionsData);
            }
        }
    }
?>