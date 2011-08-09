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

    class DropDownBatchAttributeValueDataAnalyzer extends BatchAttributeValueDataAnalyzer
                                                  implements DataAnalyzerInterface
    {
        protected $dropDownValues;

        protected $missingDropDownInstructions;

        public function __construct($modelClassName, $attributeNameOrNames)
        {
            parent:: __construct($modelClassName, $attributeNameOrNames);
            assert('count($this->attributeNameOrNames) == 1');
            $customFieldData      = CustomFieldDataModelUtil::
                                    getDataByModelClassNameAndAttributeName($this->modelClassName,
                                                                       $this->attributeNameOrNames[0]);
            $dropDownValues       = unserialize($customFieldData->serializedData);
            $this->dropDownValues = ArrayUtil::resolveArrayToLowerCase($dropDownValues);
        }

        public function runAndMakeMessages(AnalyzerSupportedDataProvider $dataProvider, $columnName)
        {
            assert('is_string($columnName)');
            $this->processAndMakeMessage($dataProvider, $columnName);
            if($this->missingDropDownInstructions != null)
            {
                $this->setInstructionsData($this->missingDropDownInstructions);
            }
        }

        protected function analyzeByValue($value)
        {

            if($value != null && !in_array(strtolower($value), $this->dropDownValues))
            {
                $this->missingDropDownInstructions[DropDownSanitizerUtil::ADD_MISSING_VALUE][] = $value;
                $this->messageCountData[static::INVALID] ++;
            }
        }

        protected function makeMessages()
        {
            $invalid  = $this->messageCountData[static::INVALID];
            if($invalid > 0)
            {
                $label   = '{count} dropdown value(s) are missing from the field. ';
                $label  .= 'These values will be added upon import.';
                $this->addMessage(Yii::t('Default', $label, array('{count}' => $invalid)));
            }
        }
    }
?>