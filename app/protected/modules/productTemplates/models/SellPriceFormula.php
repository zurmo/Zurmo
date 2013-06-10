<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    class SellPriceFormula extends OwnedModel
    {
        /*
         * Constants for sell price formula type
         */
        const TYPE_EDITABLE           = 1;

        const TYPE_PROFIT_MARGIN      = 2;

        const TYPE_MARKUP_OVER_COST   = 3;

        const TYPE_DISCOUNT_FROM_LIST = 4;

        const TYPE_SAME_AS_LIST       = 5;

        /**
         * @return string
         */
        public function __toString()
        {
            if (trim($this->name) == '')
            {
                return Zurmo::t('ProductTemplatesModule', '(None)');
            }
            return $this->name;
        }

        /**
         * @return array
         */
        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'type',
                    'discountOrMarkupPercentage',
                ),
                'relations' => array(
                    'productTemplate' => array(RedBeanModel::HAS_ONE, 'ProductTemplate'),
                ),
                'rules' => array(
                    array('type',                        'required'),
                    array('type',                        'type',    'type' => 'integer'),
                    array('discountOrMarkupPercentage',  'type',    'type' => 'float'),
                ),
                'elements' => array(
                    'type'  => 'SellPriceFormulaTypeDropDown'
                ),
                'defaultSortAttribute' => 'type',
                'customFields' => array(
                ),
            );
            return $metadata;
        }

        /**
         * @return bool
         */
        public static function isTypeDeletable()
        {
            return true;
        }

        /**
         * @return bool
         */
        public static function canSaveMetadata()
        {
            return true;
        }

        /**
         * @return array of sellpriceformula values and labels
         */
        public static function getTypeDropDownArray()
        {
            return array(
                SellPriceFormula::TYPE_EDITABLE            => EditableSellPriceFormulaRules::getDisplayLabel(),
                SellPriceFormula::TYPE_DISCOUNT_FROM_LIST  => DiscountFromListSellPriceFormulaRules::getDisplayLabel(),
                SellPriceFormula::TYPE_MARKUP_OVER_COST    => MarkupOverCostSellPriceFormulaRules::getDisplayLabel(),
                SellPriceFormula::TYPE_PROFIT_MARGIN       => ProfitMarginSellPriceFormulaRules::getDisplayLabel(),
                SellPriceFormula::TYPE_SAME_AS_LIST        => SameAsListSellPriceFormulaRules::getDisplayLabel(),
            );
        }

        /**
         * @return array of sellprice formula displayable labels and values
         */
        public static function getDisplayedSellPriceFormulaArray()
        {
            return array(
                SellPriceFormula::TYPE_EDITABLE            => EditableSellPriceFormulaRules::getDisplaySellPriceFormula(),
                SellPriceFormula::TYPE_DISCOUNT_FROM_LIST  => DiscountFromListSellPriceFormulaRules::getDisplaySellPriceFormula(),
                SellPriceFormula::TYPE_MARKUP_OVER_COST    => MarkupOverCostSellPriceFormulaRules::getDisplaySellPriceFormula(),
                SellPriceFormula::TYPE_PROFIT_MARGIN       => ProfitMarginSellPriceFormulaRules::getDisplaySellPriceFormula(),
                SellPriceFormula::TYPE_SAME_AS_LIST        => SameAsListSellPriceFormulaRules::getDisplaySellPriceFormula(),
            );
        }

        /**
         * @return string
         */
        public static function getModuleClassName()
        {
            return 'ProductTemplatesModule';
        }

        /**
         * Gets the report list view column adapater class name
         * @param string $attribute
         * @return string or null value
         */
        public static function getAttributeToReportListViewColumnAdapterClassName($attribute)
        {
            switch ($attribute)
            {
                case 'type':
                    return 'SellPriceFormulaTypeReportListViewColumnAdapter';
                default:
                    break;
            }

            return null;
        }
    }
?>