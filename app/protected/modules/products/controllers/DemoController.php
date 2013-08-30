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

    Yii::import('application.modules.products.controllers.DefaultController', true);
    class ProductsDemoController extends ProductsDefaultController
    {
        /**
         * Special method to load each type of product.
         */
        public function actionLoadProductsSampler()
        {
            if (!Group::isUserASuperAdministrator(Yii::app()->user->userModel))
            {
                throw new NotSupportedException();
            }

            //Create test account for product functional test on related list sorting, product related view.
            $account        = new Account();
            $account->owner = Yii::app()->user->userModel;
            $account->name  = 'My Account For Product Test';
            $saved          = $account->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }

            //Load 6 so there is sufficient data for product related view pagination testing.
            for ($i = 0; $i < 8; $i++)
            {
                $product                            = new Product();
                if ($i < 6)
                {
                    $product->name                  = 'Product with open stage '. $i;
                    $product->stage->value          = 'Open';
                }
                elseif ($i == 6)
                {
                    $product->name                  = 'Product with lost stage '. $i;
                    $product->stage->value          = 'Lost';
                }
                elseif ($i == 7)
                {
                    $product->name                  = 'Product with won stage '. $i;
                    $product->stage->value          = 'Won';
                }
                $product->owner                     = Yii::app()->user->userModel;
                $product->quantity                  = mt_rand(1, 95);
                $product->account                   = $account;
                $product->type                      = 1;
                $product->sellPrice->value          = 786.0;
                $product->sellPrice->currency->code = 'USD';
                $product->priceFrequency            = 2;
                $saved                              = $product->save();
                if (!$saved)
                {
                    throw new NotSupportedException();
                }
            }
        }
    }
?>
