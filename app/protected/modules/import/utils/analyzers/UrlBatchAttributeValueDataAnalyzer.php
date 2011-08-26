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
     * Data analyzer for columns mapped to url type attributes.
     */
    class UrlBatchAttributeValueDataAnalyzer extends BatchAttributeValueDataAnalyzer
                                                  implements DataAnalyzerInterface
    {
        /**
         * Identifier for when a url is larger than the max length.
         * @var string
         */
        const URL_TOO_LONG = 'Url too long';

        /**
         * @var integer
         */
        protected $maxLength;

        /**
         * Override to resolve the max length.
         * @param string $modelClassName
         * @param string $attributeName
         */
        public function __construct($modelClassName, $attributeName)
        {
            parent:: __construct($modelClassName, $attributeName);
            assert('is_string($attributeName)');
            $this->maxLength = DatabaseCompatibilityUtil::getMaxVarCharLength();
            $this->messageCountData[static::URL_TOO_LONG] = 0;
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
            if ($value == null)
            {
                return;
            }
            $validator = new CUrlValidator();
            $validator->defaultScheme = 'http';
            $validatedUrl = $validator->validateValue($value);
            if ($validatedUrl === false)
            {
                $this->messageCountData[static::INVALID] ++;
                return;
            }
            if (strlen($validatedUrl) > $this->maxLength)
            {
                $this->messageCountData[static::URL_TOO_LONG] ++;
            }
        }

        /**
         * @see BatchAttributeValueDataAnalyzer::makeMessages()
         */
        protected function makeMessages()
        {
            $invalid  = $this->messageCountData[static::INVALID];
            $tooLarge = $this->messageCountData[static::URL_TOO_LONG];
            if ($invalid > 0)
            {
                $label   = '{count} value(s) have urls that are invalid. ';
                $label  .= 'These values will be cleared during import.';
                $this->addMessage(Yii::t('Default', $label, array('{count}' => $invalid)));
            }
            if ($tooLarge > 0)
            {
                $label   = '{count} value(s) are too large for this field. ';
                $label  .= 'These values will be cleared during import.';
                $this->addMessage(Yii::t('Default', $label,
                                  array('{count}' => $tooLarge, '{length}' => $this->maxLength)));
            }
        }
    }
?>