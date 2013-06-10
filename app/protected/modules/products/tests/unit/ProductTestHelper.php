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

    class ProductTestHelper
    {
        public static function createProductByNameForOwner($name, $owner)
        {
            $currencies                      = Currency::getAll('id');
            $currencyValue                   = new CurrencyValue();
            $currencyValue->currency         = $currencies[array_rand($currencies)];
            $currencyValue->value            = 500.54;
            $product                         = new Product();
            $product->name                   = $name;
            $product->owner                  = $owner;
            $product->description            = 'Description';
            $product->quantity               = 2;
            $product->type                   = ProductTemplate::TYPE_PRODUCT;
            $product->stage->value           = 'Open';
            $product->sellPrice              = $currencyValue;
            $product->priceFrequency         = ProductTemplate::PRICE_FREQUENCY_ONE_TIME;
            $saved                           = $product->save();
            assert('$saved');
            return $product;
        }
    }
?>