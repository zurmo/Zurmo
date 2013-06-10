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
     * Helper class to work with calculated numbers.
     */
    class CalculatedNumberUtil
    {
        const FORMAT_TYPE_INTEGER        = 1;

        const FORMAT_TYPE_DECIMAL        = 2;

        const FORMAT_TYPE_CURRENCY_VALUE = 3;

        /**
         * Calculate the formula and resolve the correct formula based on the attributes included in the formula
         * @param $formula
         * @param RedBeanModel $model
         * @return mixed
         * @throws NotSupportedException
         */
        public static function calculateByFormulaAndModelAndResolveFormat($formula, RedBeanModel $model)
        {
            $formatType   = self::FORMAT_TYPE_INTEGER;
            $currencyCode = null;
            $value = static::calculateByFormulaAndModel($formula, $model, $formatType, $currencyCode);
            if ($formatType == self::FORMAT_TYPE_INTEGER)
            {
                return Yii::app()->format->formatNumber((int)$value);
            }
            elseif ($formatType == self::FORMAT_TYPE_DECIMAL)
            {
                return Yii::app()->numberFormatter->formatDecimal((float)$value);
            }
            elseif ($formatType == self::FORMAT_TYPE_CURRENCY_VALUE && $currencyCode != null)
            {
                return Yii::app()->numberFormatter->formatCurrency((float)$value, $currencyCode);
            }
            elseif ($formatType == self::FORMAT_TYPE_CURRENCY_VALUE)
            {
                return Yii::app()->numberFormatter->formatDecimal((float)$value);
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        /**
         * Given a formula string and a model, calculate the number using the formula and values from the attributes
         * on the model.
         * @param string $formula
         * @param RedBeanModel $model
         * @param $formatType
         * @param $currencyCode
         * @return bool|int|string calculated value as number.
         */
        public static function calculateByFormulaAndModel($formula, RedBeanModel $model, & $formatType, & $currencyCode)
        {
            assert('is_string($formula)');
            assert('is_int($formatType)');
            assert('is_string($currencyCode) || $currencyCode === null');
            $adapter = new ModelNumberOrCurrencyAttributesAdapter($model);
            foreach ($adapter->getAttributes() as $attribute => $data)
            {
                if (($model->{$attribute} instanceof CurrencyValue && $model->{$attribute}->value == null) ||
                   $model->{$attribute} == null)
                {
                    $replacementValue = 0;
                }
                else
                {
                    if ($model->{$attribute} instanceof CurrencyValue)
                    {
                        $replacementValue = $model->{$attribute}->value;
                    }
                    else
                    {
                        $replacementValue = $model->{$attribute};
                    }
                }
                $oldFormula = $formula;
                $formula = str_replace($attribute, $replacementValue, $formula);
                if ($formula !== $oldFormula)
                {
                    self::resolveFormatTypeAndCurrencyCode($formatType, $currencyCode, $model, $attribute);
                }
            }
            $result = static::mathEval($formula);
            if ($result === false)
            {
                return Zurmo::t('ZurmoModule', 'Invalid');
            }
            return $result;
        }

        /**
         * Given a formula string and a model, determine if the formula is correctly formed and is using valid
         * attributes from the given model.
         * @param $formula
         * @param ModelNumberOrCurrencyAttributesAdapter $adapter
         * @return bool
         */
        public static function isFormulaValid($formula, ModelNumberOrCurrencyAttributesAdapter $adapter)
        {
            assert('is_string($formula)');
            foreach ($adapter->getAttributes() as $attribute => $data)
            {
                $formula = str_replace($attribute, 1, $formula);
            }
            if ($formula != strtoupper($formula) || $formula != strtolower($formula))
            {
                return false;
            }
            if (static::mathEval($formula) === false)
            {
                return false;
            }
            return true;
        }

        protected static function mathEval($equation)
        {
            $equation = preg_replace("/[^0-9+\-.*\/()%]/","",$equation); // Not Coding Standard
            $equation = preg_replace("/([+-])([0-9]{1})(%)/","*(1\$1.0\$2)",$equation); // Not Coding Standard
            $equation = preg_replace("/([+-])([0-9]+)(%)/","*(1\$1.\$2)",$equation); // Not Coding Standard
            $equation = preg_replace("/([0-9]+)(%)/",".\$1",$equation); // Not Coding Standard
            if ($equation == "")
            {
              $return = 0;
            }
            else
            {
              $success = @eval("\$return=" . $equation . ";" ); // Not Coding Standard
              if ($success === false)
              {
                  return false;
              }
            }
            return $return;
        }

        protected static function resolveFormatTypeAndCurrencyCode(& $formatType, & $currencyCode, $model, $attribute)
        {
            assert('is_int($formatType)');
            assert('is_string($currencyCode) || $currencyCode === null');
            $attributeType = ModelAttributeToMixedTypeUtil::getType($model, $attribute);
            if ($attributeType == 'Decimal' && $formatType == self::FORMAT_TYPE_INTEGER)
            {
                $formatType = self::FORMAT_TYPE_DECIMAL;
            }
            if ($attributeType == 'CurrencyValue' &&
               ($formatType == self::FORMAT_TYPE_INTEGER || $formatType == self::FORMAT_TYPE_DECIMAL))
            {
                $formatType = self::FORMAT_TYPE_CURRENCY_VALUE;
            }
            if ($attributeType == 'CurrencyValue' && $currencyCode === null)
            {
                $currencyCode = $model->{$attribute}->currency->code;
            }
            elseif ($attributeType == 'CurrencyValue' && $currencyCode != null &&
                   $model->{$attribute}->currency->code != $currencyCode)
            {
                //An empty value, not null, indicates there is mixed currencies
                $currencyCode = '';
            }
        }
    }
?>