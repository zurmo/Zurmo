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
     * Helper class used by product template elements.
     */
    class ProductTemplateElementUtil
    {
        /**
         * Script used by slle price formula elements to control the helper
         * dropdown and toggling disable and enable on the text field
         * @see SellPriceFormulaInformationElement
         */
        public static function getShowHideDiscountOrMarkupPercentageTextFieldScript()
        {
            return "
                function showHideDiscountOrMarkupPercentageTextField(helperValue, textFieldId)
                {
                    var typeProfitMargin = " . SellPriceFormula::TYPE_PROFIT_MARGIN . ";
                    var typeMarkOverCost = " . SellPriceFormula::TYPE_MARKUP_OVER_COST . ";
                    var typeDiscountFromList = " . SellPriceFormula::TYPE_DISCOUNT_FROM_LIST . ";
                    if (helperValue == typeProfitMargin || helperValue == typeMarkOverCost || helperValue == typeDiscountFromList)
                    {
                        $('#' + textFieldId).show();
                    }
                    else
                    {
                        $('#' + textFieldId).hide();
                    }
                }
            ";
        }

        /**
         * @return string
         */
        public static function getEnableDisableSellPriceElementBySellPriceFormulaScript()
        {
            return "
                function enableDisableSellPriceElementBySellPriceFormula(helperValue, elementId, attribute)
                {
                    var typeEditable = " . SellPriceFormula::TYPE_EDITABLE . ";
                    if (helperValue != typeEditable)
                    {
                        $('#' + elementId).attr('readonly', true);
                        $('#ProductTemplate_' + attribute + '_currency_id').attr('readonly', 'true');
                        $('#ProductTemplate_' + attribute + '_currency_id').addClass('disabled');
                        $('#' + elementId).addClass('disabled');
                    }
                    else
                    {
                        $('#' + elementId).removeAttr('readonly');
                        $('#ProductTemplate_' + attribute + '_currency_id').removeAttr('readonly');
                        $('#ProductTemplate_' + attribute + '_currency_id').removeClass('disabled');
                        $('#' + elementId).removeClass('disabled');
                    }
                }
            ";
        }

        /**
         * @return string
         */
        public static function getCalculatedSellPriceBySellPriceFormulaScript()
        {
            // Begin Not Coding Standard
            return "
                $('#ProductTemplate_cost_currency_id').change(function(){calculateSellPriceBySellPriceFormula()});
                $('#ProductTemplate_listPrice_currency_id').change(function(){calculateSellPriceBySellPriceFormula()});
                function calculateSellPriceBySellPriceFormula()
                {
                    var priceCurrency = '';
                    var typeEditable = " . SellPriceFormula::TYPE_EDITABLE . ";
                    var typeProfitMargin = " . SellPriceFormula::TYPE_PROFIT_MARGIN . ";
                    var typeMarkOverCost = " . SellPriceFormula::TYPE_MARKUP_OVER_COST . ";
                    var typeDiscountFromList = " . SellPriceFormula::TYPE_DISCOUNT_FROM_LIST . ";
                    var typeSameAsList = " . SellPriceFormula::TYPE_SAME_AS_LIST . ";
                    var helperValue = $('#ProductTemplate_sellPriceFormula_type').val();
                    var calculatedSellPrice = 0;
                    var discountOrMarkupPercentage = $('#ProductTemplate_sellPriceFormula_discountOrMarkupPercentage').val();
                    if (discountOrMarkupPercentage == '')
                    {
                        discountOrMarkupPercentage = 0;
                    }
                    else
                    {
                        discountOrMarkupPercentage = parseFloat(discountOrMarkupPercentage)/100;
                    }
                    if (helperValue == typeProfitMargin)
                    {
                        var cost = parseFloat($('#ProductTemplate_cost_value').val());
                        calculatedSellPrice = parseFloat(cost/(1-discountOrMarkupPercentage));
                        modCalculatesSellPrice = (Math.round(calculatedSellPrice * 100)/100).toFixed(2);
                        $('#ProductTemplate_sellPrice_value').val(modCalculatesSellPrice);
                        priceCurrency = $('#ProductTemplate_cost_currency_id').val();
                        $('#ProductTemplate_sellPrice_currency_id').val(priceCurrency);
                        $('#ProductTemplate_listPrice_currency_id').val(priceCurrency);
                        $('#ProductTemplate_listPrice_currency_id').attr('readonly','readonly');
                        $('#ProductTemplate_listPrice_currency_id').addClass('disabled');
                        $('#ProductTemplate_cost_currency_id').removeAttr('readonly');
                        $('#ProductTemplate_cost_currency_id').removeClass('disabled');
                    }

                    if (helperValue == typeMarkOverCost)
                    {
                        var cost = parseFloat($('#ProductTemplate_cost_value').val());
                        calculatedSellPrice = (discountOrMarkupPercentage * cost) + cost;
                        $('#ProductTemplate_sellPrice_value').val(calculatedSellPrice);
                        priceCurrency = $('#ProductTemplate_cost_currency_id').val();
                        $('#ProductTemplate_sellPrice_currency_id').val(priceCurrency);
                        $('#ProductTemplate_listPrice_currency_id').val(priceCurrency);
                        $('#ProductTemplate_listPrice_currency_id').attr('readonly','readonly');
                        $('#ProductTemplate_listPrice_currency_id').addClass('disabled');
                        $('#ProductTemplate_cost_currency_id').removeAttr('readonly');
                        $('#ProductTemplate_cost_currency_id').removeClass('disabled');
                    }

                    if (helperValue == typeDiscountFromList)
                    {
                        var listPrice = parseFloat($('#ProductTemplate_listPrice_value').val());
                        calculatedSellPrice = listPrice - (listPrice * discountOrMarkupPercentage);
                        $('#ProductTemplate_sellPrice_value').val(calculatedSellPrice);
                        priceCurrency = $('#ProductTemplate_listPrice_currency_id').val();
                        $('#ProductTemplate_sellPrice_currency_id').val(priceCurrency);
                        $('#ProductTemplate_cost_currency_id').val(priceCurrency);
                        $('#ProductTemplate_cost_currency_id').attr('readonly','readonly');
                        $('#ProductTemplate_cost_currency_id').addClass('disabled');
                        $('#ProductTemplate_listPrice_currency_id').removeAttr('readonly');
                        $('#ProductTemplate_listPrice_currency_id').removeClass('disabled');
                    }

                    if (helperValue == typeSameAsList)
                    {
                        var listPrice = parseFloat($('#ProductTemplate_listPrice_value').val());
                        $('#ProductTemplate_sellPrice_value').val(listPrice);
                        priceCurrency = $('#ProductTemplate_listPrice_currency_id').val();
                        $('#ProductTemplate_sellPrice_currency_id').val(priceCurrency);
                        $('#ProductTemplate_cost_currency_id').val(priceCurrency);
                        $('#ProductTemplate_cost_currency_id').attr('readonly','readonly');
                        $('#ProductTemplate_cost_currency_id').addClass('disabled');
                        $('#ProductTemplate_listPrice_currency_id').removeAttr('readonly');
                        $('#ProductTemplate_listPrice_currency_id').removeClass('disabled');
                    }

                    if (helperValue == typeEditable)
                    {
                        $('#ProductTemplate_cost_currency_id').removeAttr('readonly');
                        $('#ProductTemplate_listPrice_currency_id').removeAttr('readonly');
                        $('#ProductTemplate_listPrice_currency_id').removeClass('disabled');
                        $('#ProductTemplate_cost_currency_id').removeClass('disabled');
                    }
                }
            ";
            // End Not Coding Standard
        }

        /**
         * @return string
         */
        public static function bindActionsWithFormFieldsForSellPrice()
        {
            return "
                $(document).ready(function()
                {
                   $('#ProductTemplate_cost_value').bind('keyup', function()
                       {
                            calculateSellPriceBySellPriceFormula();
                       }
                   );
                   $('#ProductTemplate_listPrice_value').bind('keyup', function()
                       {
                            calculateSellPriceBySellPriceFormula();
                       }
                   );
                });
            ";
        }

        /**
         * @return array
         */
        public static function getProductTemplateStatusDropdownArray()
        {
            return array(
                ProductTemplate::STATUS_ACTIVE       => Yii::t('Default', 'Active'),
                ProductTemplate::STATUS_INACTIVE     => Yii::t('Default', 'Inactive'),
            );
        }

        /**
         * @return array
         */
        public static function getProductTemplateTypeDropdownArray()
        {
            return array(
                ProductTemplate::TYPE_PRODUCT       => Yii::t('Default', 'Product'),
                ProductTemplate::TYPE_SERVICE       => Yii::t('Default', 'Service'),
                ProductTemplate::TYPE_SUBSCRIPTION  => Yii::t('Default', 'Subscription'),
            );
        }

        /**
         * @return array
         */
        public static function getProductTemplatePriceFrequencyDropdownArray()
        {
            return array(
                ProductTemplate::PRICE_FREQUENCY_ONE_TIME  => Yii::t('Default', 'One Time'),
                ProductTemplate::PRICE_FREQUENCY_MONTHLY   => Yii::t('Default', 'Monthly'),
                ProductTemplate::PRICE_FREQUENCY_ANNUALLY  => Yii::t('Default', 'Annually'),
            );
        }

        /**
         * Get the value of type displayed in grid view for product template
         * @param RedBeanModel $data
         * @param int $row
         * @return null or string
         */
        public static function getProductTemplateTypeDisplayedGridValue($data, $row)
        {
            $typeDropdownData = self::getProductTemplateTypeDropdownArray();
            if (isset($typeDropdownData[$data->type]))
            {
                return $typeDropdownData[$data->type];
            }
            else
            {
                return null;
            }
        }

        /**
         * Get the value of status displayed in grid view for product template
         * @param RedBeanModel $data
         * @param int $row
         * @return null or string
         */
        public static function getProductTemplateStatusDisplayedGridValue($data, $row)
        {
            $statusDropdownData = self::getProductTemplateStatusDropdownArray();
            if (isset($statusDropdownData[$data->status]))
            {
                return $statusDropdownData[$data->status];
            }
            else
            {
                return null;
            }
        }

        /**
         * Get the value of price frequency displayed in grid view for product template
         * @param RedBeanModel $data
         * @param int $row
         * @return null or string
         */
        public static function getProductTemplatePriceFrequencyDisplayedGridValue($data, $row)
        {
            $frequencyDropdownData = self::getProductTemplatePriceFrequencyDropdownArray();
            if (isset($frequencyDropdownData[$data->priceFrequency]))
            {
                return $frequencyDropdownData[$data->priceFrequency];
            }
            else
            {
                return null;
            }
        }

        /**
         * Get the value of sell price formula displayed in grid view for product template
         * @param RedBeanModel $data
         * @param int $row
         * @return string
         */
        public static function getSellPriceFormulaDisplayedGridValue($data, $row)
        {
            $sellPriceFormulaModel = $data->sellPriceFormula;
            $type = $sellPriceFormulaModel->type;
            $discountOrMarkupPercentage = $sellPriceFormulaModel->discountOrMarkupPercentage;
            $displayedSellPriceFormulaList = SellPriceFormula::getDisplayedSellPriceFormulaArray();
            $content = '';
            if ($type != null)
            {
                $content = $displayedSellPriceFormulaList[$type];

                if ($type != SellPriceFormula::TYPE_EDITABLE)
                {
                    $content = str_replace('{discount}', $discountOrMarkupPercentage/100, $content);
                }
            }

            return $content;
        }

        /**
         * Get the attribute value for the report grid view based on the input
         * dropdown attribute
         * @param object $data
         * @param int $row
         * @param string $inputAttribute
         * @param array $dataArray
         * @return string or null value
         */
        public static function renderProductTemplateListViewAttributeForReports($model, $attribute)
        {
            assert('$model instanceof ReportResultsRowData');
            if (null === $displayAttributeKey = $model::resolveKeyByAttributeName($attribute))
            {
                return $model->{$attribute};
            }
            $displayAttributes = $model->getDisplayAttributes();
            $displayAttribute  = $displayAttributes[$displayAttributeKey];
            $realAttributeName = $displayAttribute->getResolvedAttribute();
            switch($realAttributeName)
            {
                case 'priceFrequency':
                    $dataArray = self::getProductTemplatePriceFrequencyDropdownArray();
                    break;
                case 'status'        :
                    $dataArray = self::getProductTemplateStatusDropdownArray();
                    break;
                case 'type'          :
                    $dataArray = self::getProductTemplateTypeDropdownArray();
                    break;
                default              :   break;
            }
            $value = $model->{$attribute};
            if (isset($dataArray[$value]))
            {
                return $dataArray[$value];
            }
            else
            {
                return null;
            }
        }
    }
?>