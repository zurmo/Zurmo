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

    class UrlBatchAttributeValueDataAnalyzer extends BatchAttributeValueDataAnalyzer
                                                  implements DataAnalyzerInterface
    {
        const FULL_NAME_TOO_LONG = 'Full name too long';

        protected $firstNameMaxLength;

        protected $lastNameMaxLength;

        public function __construct($modelClassName, $attributeNameOrNames)
        {
            parent:: __construct($modelClassName, $attributeNameOrNames);
            assert('count($this->attributeNameOrNames) == 2');
            assert('$this->attributeNameOrNames[0] == "firstName"');
            assert('$this->attributeNameOrNames[1] == "lastName"');
            $this->messageCountData[static::FULL_NAME_TOO_LONG] = 0;

            $model                    = new $modelClassName(false);
            $this->firstNameMaxLength = StringValidatorHelper::
                                        getMaxLengthByModelAndAttributeName($model, $attributeNameOrNames[0]);
            $this->lastNameMaxLength  = StringValidatorHelper::
                                        getMaxLengthByModelAndAttributeName($model, $attributeNameOrNames[1]);
        }

        public function runAndMakeMessages(AnalyzerSupportedDataProvider $dataProvider, $columnName)
        {
            assert('is_string($columnName)');
            $this->processAndMakeMessage($dataProvider, $columnName);
        }

        protected function analyzeByValue($value)
        {
            if($value == null)
            {
                return;
            }
            list($firstName, $lastName) = explode(' ', trim($value));
            if($firstName == null)
            {
                $lastName  = $firstName;
                $firstName = null;
            }
            if(strlen($lastName) > $this->lastNameMaxLength || strlen($firstName) > $this->firstNameMaxLength)
            {
                $this->messageCountData[static::FULL_NAME_TOO_LONG] ++;
            }
        }

        protected function makeMessages()
        {
            $tooLarge = $this->messageCountData[static::URL_TOO_LONG];
            if($tooLarge > 0)
            {
                $label   = '{count} value(s) are too large for this field. ';
                $label  .= 'These values will be truncated to a length of {length} upon import.';
                $this->addMessage(Yii::t('Default', $label,
                                  array('{count}' => $tooLarge, '{length}' => $this->maxLength)));
            }
        }
    }
?>