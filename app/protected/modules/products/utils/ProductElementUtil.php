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

    /**
     * Helper class used by product elements.
     */
    class ProductElementUtil
    {
        /**
         * Product name length in portlet view
         */
        const PRODUCT_NAME_LENGTH_IN_PORTLET_VIEW = 19;

        /**
         * Gets sell price for product in portlet view
         * @param object $data
         * @param int $row
         * @return string
         */
        public static function getProductPortletSellPrice($data, $row)
        {
            assert('$data->sellPrice instanceof CurrencyValue');
            $currencyValueModel = $data->sellPrice;
            return Yii::app()->numberFormatter->formatCurrency( $currencyValueModel->value,
                                                                $currencyValueModel->currency->code);
        }

        /**
         * Gets total price for product in portlet view
         * @param object $data
         * @param int $row
         * @return string
         */
        public static function getProductPortletTotalPrice($data, $row)
        {
            assert('$data->sellPrice instanceof CurrencyValue');
            $currencyValueModel = $data->sellPrice;
            return Yii::app()->numberFormatter->formatCurrency( $currencyValueModel->value * $data->quantity,
                                                                $currencyValueModel->currency->code);
        }

        /**
         * Gets name for product in portlet view
         * @param object $data
         * @param int $row
         * @return string
         */
        public static function getProductNameLinkString($data, $row)
        {
            $productName = $data->name;
            if (strlen($productName) > (self::PRODUCT_NAME_LENGTH_IN_PORTLET_VIEW + 2))
            {
                $productName = substr($productName, 0, self::PRODUCT_NAME_LENGTH_IN_PORTLET_VIEW);
                $productName .= '..';
            }
            $url         = Yii::app()->createUrl('products/default/details', array('id' => $data->id));
            return ZurmoHtml::link($productName, $url);
        }
    }
?>