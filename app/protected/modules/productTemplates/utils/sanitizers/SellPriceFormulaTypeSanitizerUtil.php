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
     * Sanitizer for handling price frequency.
     */
    class SellPriceFormulaTypeSanitizerUtil extends SanitizerUtil
    {
        /**
         * @param RedBean_OODBBean $rowBean
         */
        public function analyzeByRow(RedBean_OODBBean $rowBean)
        {
            $resolvedAcceptableValues = ArrayUtil::resolveArrayToLowerCase(static::getAcceptableValues());
            if (!in_array(strtolower($rowBean->{$this->columnName}), $resolvedAcceptableValues))
            {
                $label = Zurmo::t('ImportModule',
                                  '{attributeLabel} specified is invalid and this row will be skipped during import.',
                                  array('{attributeLabel}' => ProductTemplate::getAnAttributeLabel('sellPriceFormula')));
                $this->shouldSkipRow      = true;
                $this->analysisMessages[] = $label;
            }
        }

        /**
         * @param mixed $value
         * @return sanitized value
         * @throws InvalidValueToSanitizeException
         */
        public function sanitizeValue($value)
        {
            if ($value == null)
            {
                return $value;
            }
            try
            {
                if (strtolower($value) == strtolower(SellPriceFormula::TYPE_EDITABLE) ||
                    strtolower($value) == strtolower('Editable'))
                {
                    return SellPriceFormula::TYPE_EDITABLE;
                }
                elseif (strtolower($value) == strtolower(SellPriceFormula::TYPE_DISCOUNT_FROM_LIST) ||
                    strtolower($value) == strtolower('Discount From List Percent'))
                {
                    return SellPriceFormula::TYPE_DISCOUNT_FROM_LIST;
                }
                elseif (strtolower($value) == strtolower(SellPriceFormula::TYPE_MARKUP_OVER_COST) ||
                    strtolower($value) == strtolower('Markup Over Cost Percent'))
                {
                    return SellPriceFormula::TYPE_MARKUP_OVER_COST;
                }
                elseif (strtolower($value) == strtolower(SellPriceFormula::TYPE_PROFIT_MARGIN) ||
                    strtolower($value) == strtolower('Profit Margin Percent'))
                {
                    return SellPriceFormula::TYPE_PROFIT_MARGIN;
                }
                elseif (strtolower($value) == strtolower(SellPriceFormula::TYPE_MARKUP_OVER_COST) ||
                    strtolower($value) == strtolower('Same As List'))
                {
                    return SellPriceFormula::TYPE_MARKUP_OVER_COST;
                }
                else
                {
                    throw new InvalidValueToSanitizeException();
                }
            }
            catch (NotFoundException $e)
            {
                throw new InvalidValueToSanitizeException(Zurmo::t('ProductTemplatesModule',
                                                                   'Sell Price Formula type specified is invalid.'));
            }
        }

        protected static function getAcceptableValues()
        {
            return array(SellPriceFormula::TYPE_EDITABLE,
                         SellPriceFormula::TYPE_DISCOUNT_FROM_LIST,
                         SellPriceFormula::TYPE_MARKUP_OVER_COST,
                         SellPriceFormula::TYPE_PROFIT_MARGIN,
                         SellPriceFormula::TYPE_SAME_AS_LIST,
                        'Editable',
                        'Discount From List Percent',
                        'Markup Over Cost Percent',
                        'Profit Margin Percent',
                        'Same As List');
        }
    }
?>