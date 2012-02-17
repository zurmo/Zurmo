<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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
     * Helper class to work with calculated numbers.
     */
    class CalculatedNumberUtil
    {
        /**
         * Given a formula string and a model, calculate the number using the formula and values from the attributes
         * on the model.
         * @param string $formula
         * @param RedBeanModel $model
         * @return calculated value as number.
         */
        public static function calculateByFormulaAndModel($formula, RedBeanModel $model)
        {
            assert('is_string($formula)');
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
                $formula = str_replace($attribute, $replacementValue, $formula);
            }
            $result = static::matheval($formula);
            if ($result === false)
            {
                return Yii::t('Default', 'Invalid');
            }
            return $result;
        }

        /**
         * Given a formula string and a model, determine if the formula is correctly formed and is using valid
         * attributes from the given model.
         * @param string $formula
         * @param RedBeanModel $model
         * @return boolean true/false
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
            if (static::matheval($formula) === false)
            {
                return false;
            }
            return true;
        }

        protected static function matheval($equation)
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
    }
?>