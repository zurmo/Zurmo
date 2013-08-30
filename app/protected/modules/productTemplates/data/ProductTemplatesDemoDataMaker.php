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
     * Class that builds demo product templates.
     */
    class ProductTemplatesDemoDataMaker extends DemoDataMaker
    {
        protected $ratioToLoad = 1;

        /**
         * @return array
         */
        public static function getDependencies()
        {
            return array();
        }

        /**
         * @param Object $demoDataHelper
         */
        public function makeAll(& $demoDataHelper)
        {
            assert('$demoDataHelper instanceof DemoDataHelper');
            $currencies = Currency::getAll('id');
            $productTemplates = array();
            $productTemplateRandomData = self::getProductTemplatesRandomData();
            for ($i = 0; $i < count($productTemplateRandomData['names']); $i++)
            {
                $productTemplate = new ProductTemplate();
                $currencyIndex                   = array_rand($currencies);
                $currencyValue                   = new CurrencyValue();
                $currencyValue->currency         = $currencies[$currencyIndex];
                $productTemplate->cost           = $currencyValue;
                $currencyValue                   = new CurrencyValue();
                $currencyValue->currency         = $currencies[$currencyIndex];
                $productTemplate->listPrice      = $currencyValue;
                $currencyValue                   = new CurrencyValue();
                $currencyValue->currency         = $currencies[$currencyIndex];
                $productTemplate->sellPrice      = $currencyValue;
                $this->populateModelData($productTemplate, $i);
                $saved               = $productTemplate->save();
                assert('$saved');
                $productTemplates[]      = $productTemplate->id;
            }
            $demoDataHelper->setRangeByModelName('ProductTemplate', $productTemplates[0], $productTemplates[count($productTemplates)-1]);
        }

        /**
         * Populate Product Template Model with data
         * @param Product Template object $model
         * @param int $counter
         */
        public function populateModelData(& $model, $counter)
        {
            assert('$model instanceof ProductTemplate');
            parent::populateModel($model);
            $productTemplateRandomData = self::getProductTemplatesRandomData();
            $name                      = $productTemplateRandomData['names'][$counter];
            $productCategoryName       = self::getProductCategoryForTemplate($name);
            $allCats = ProductCategory::getAll();
            foreach ($allCats as $category)
            {
                if ($category->name == $productCategoryName)
                {
                    $categoryId = $category->id;
                }
            }
            $productCategory           = ProductCategory::getById($categoryId);

            $model->name               = $name;
            $model->productCategories->add($productCategory);
            $model->priceFrequency     = 2;
            $model->cost->value        = 200;
            $model->listPrice->value   = 200;
            $model->sellPrice->value   = 200;
            $model->status             = ProductTemplate::STATUS_ACTIVE;
            $model->type               = ProductTemplate::TYPE_PRODUCT;
            $sellPriceFormula          = new SellPriceFormula();
            $sellPriceFormula->type    = SellPriceFormula::TYPE_EDITABLE;
            $model->sellPriceFormula   = $sellPriceFormula;
        }

        /**
         * Get product category based on template
         * @param string $template
         * @return string
         */
        private static function getProductCategoryForTemplate($template)
        {
            $templateCategoryMapping = array(
                                                'Amazing Kid'           => 'CD-DVD',
                                                'You Can Do Anything'   => 'CD-DVD',
                                                'A Bend in the River'   => 'Books',
                                                'A Gift of Monotheists' => 'Books',
                                                'Once in a Lifetime'    => 'Music'
                                            );
            if (!array_key_exists($template, $templateCategoryMapping))
            {
                if (strpos($template, 'Laptop Inc - Model') !== false)
                {
                    return 'Laptops';
                }
                if (strpos($template, 'Camera Inc') !== false)
                {
                    return 'Camera';
                }
                if (strpos($template, 'Handycam Inc - Model') !== false)
                {
                    return 'Handycam';
                }
            }
            return $templateCategoryMapping[$template];
        }

        /**
         * Gets the product templates random data
         * @return array
         */
        public static function getProductTemplatesRandomData()
        {
            $templateNames = array(
                                    'names' => array(
                                        'Amazing Kid',
                                        'You Can Do Anything',
                                        'A Bend in the River',
                                        'A Gift of Monotheists',
                                        'Once in a Lifetime'
                                    )
                                   );
            for ($i = 1; $i < 10; $i++)
            {
               $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
               $templateNames['names'][] = 'Laptop Inc - Model ' . $randomString;
            }

            for ($i = 1; $i < 10; $i++)
            {
               $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
               $templateNames['names'][] = 'Camera Inc 2 MegaPixel - Model ' . $randomString;
            }

            for ($i = 1; $i < 10; $i++)
            {
               $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
               $templateNames['names'][] = 'Handycam Inc - Model ' . $randomString;
            }
            return $templateNames;
        }
    }
?>