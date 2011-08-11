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
     * Data analyzer for columns mapped to email type attributes.
     */
    class EmailBatchAttributeValueDataAnalyzer extends BatchAttributeValueDataAnalyzer
                                                  implements DataAnalyzerInterface
    {
        const EMAIL_TOO_LONG = 'Email too long';

        protected $maxLength;

        public function __construct($modelClassName, $attributeNameOrNames)
        {
            parent:: __construct($modelClassName, $attributeNameOrNames);
            assert('count($this->attributeNameOrNames) == 1');
            $this->maxLength = DatabaseCompatibilityUtil::getMaxVarCharLength();
            $this->messageCountData[static::EMAIL_TOO_LONG] = 0;
        }

        /**
         * @see DataAnalyzerInterface::runAndMakeMessages()
         */
        public function runAndMakeMessages(AnalyzerSupportedDataProvider $dataProvider, $columnName)
        {
            assert('is_string($columnName)');
            $this->processAndMakeMessage($dataProvider, $columnName);
        }

        /**
         * @see BatchAttributeValueDataAnalyzer::analyzeByValue()
         */
        protected function analyzeByValue($value)
        {
            if($value == null)
            {
                return;
            }
            $validator = new CEmailValidator();
            $validator->defaultScheme = 'http';
            $validatedEmail = $validator->validateValue($value);
            if($validatedEmail === false)
            {
                $this->messageCountData[static::INVALID] ++;
                return;
            }
            if(strlen($validatedEmail) > $this->maxLength)
            {
                $this->messageCountData[static::EMAIL_TOO_LONG] ++;
            }
        }

        /**
         * @see BatchAttributeValueDataAnalyzer::makeMessages()
         */
        protected function makeMessages()
        {
            $invalid  = $this->messageCountData[static::INVALID];
            $tooLarge = $this->messageCountData[static::EMAIL_TOO_LONG];
            if($invalid > 0)
            {
                $label   = '{count} value(s) have emails that are invalid. ';
                $label  .= 'These rows will be skipped during import.';
                $this->addMessage(Yii::t('Default', $label, array('{count}' => $invalid)));
            }
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