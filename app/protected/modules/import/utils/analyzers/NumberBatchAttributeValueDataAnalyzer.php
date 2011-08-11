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
     * Data analyzer for columns mapped to number type attributes. Includes decimal, currency, and integer.
     * Validates whether the value is a valid number.
     */
    class NumberBatchAttributeValueDataAnalyzer extends BatchAttributeValueDataAnalyzer
                                                  implements DataAnalyzerInterface
    {
        /**
         * Used to identify the type of mixed type the attribute is. Integer, Currency, Decimal etc.
         * @see ModelAttributeToMixedTypeUtil::getType()
         * @var string
         */
        protected $type;

        /**
         * Override to assert $attributeNameOrNames is only a single attribute as this is the only way this analyzer
         * supports it.
         * @param string $modelClassName
         * @param string $attributeNameOrNames
         */
        public function __construct($modelClassName, $attributeNameOrNames)
        {
            parent:: __construct($modelClassName, $attributeNameOrNames);
            assert('count($this->attributeNameOrNames) == 1');
        }

        /**
         * @see DataAnalyzerInterface::runAndMakeMessages()
         */
        public function runAndMakeMessages(AnalyzerSupportedDataProvider $dataProvider, $columnName)
        {
            assert('is_string($columnName)');
            assert('count($this->attributeNameOrNames) == 1');
            $this->processAndMakeMessage($dataProvider, $columnName);
            $modelClassName = $this->modelClassName;
            $model          = new $modelClassName(false);
            $this->type     = ModelAttributeToMixedTypeUtil::getType($model, $this->attributeNameOrNames[0]);
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
            $validator = new RedBeanModelNumberValidator();
            if($this->type == 'Integer')
            {
                if(!preg_match($validator->integerPattern, $value))
                {
                    $this->messageCountData[static::INVALID] ++;
                    return;
                }
            }
            else
            {
                if(!preg_match($validator->numberPattern, $value))
                {
                    $this->messageCountData[static::INVALID] ++;
                    return;
                }
            }

        }

        /**
         * @see BatchAttributeValueDataAnalyzer::makeMessages()
         */
        protected function makeMessages()
        {
            $invalid  = $this->messageCountData[static::INVALID];
            if($invalid > 0)
            {
                $label   = '{count} value(s) are invalid. ';
                $label  .= 'These rows will be skipped during import.';
                $this->addMessage(Yii::t('Default', $label, array('{count}' => $invalid)));
            }
        }
    }
?>