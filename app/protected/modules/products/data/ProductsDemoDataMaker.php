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
     * Class that builds demo products.
     */
    class ProductsDemoDataMaker extends DemoDataMaker
    {
        protected $ratioToLoad = 1;

        /**
         * Gets the dependencies before creating products data
         * @return array
         */
        public static function getDependencies()
        {
            return array('productTemplates');
        }

        /**
         * @param object $demoDataHelper
         */
        public function makeAll(& $demoDataHelper)
        {
            assert('$demoDataHelper instanceof DemoDataHelper');
            assert('$demoDataHelper->isSetRange("Contact")');
            assert('$demoDataHelper->isSetRange("Account")');
            assert('$demoDataHelper->isSetRange("Opportunity")');
            assert('$demoDataHelper->isSetRange("User")');
            $products = array();
            $productRandomData = self::getProductsRandomData();
            for ($i = 0; $i < count($productRandomData['names']); $i++)
            {
                $product = new Product();
                $product->contact          = $demoDataHelper->getRandomByModelName('Contact');
                $product->account          = $demoDataHelper->getRandomByModelName('Account');
                $product->opportunity      = $demoDataHelper->getRandomByModelName('Opportunity');
                $product->owner            = $demoDataHelper->getRandomByModelName('User');
                $name                      = $productRandomData['names'][$i];
                $this->populateModelData($product, $i, $name);
                $saved                     = $product->save();
                assert('$saved');
                $products[]                = $product->id;
            }
            $demoDataHelper->setRangeByModelName('Product', $products[0], $products[count($products)-1]);
        }

        public function populateModelData(& $model, $counter, $name)
        {
            assert('$model instanceof Product');
            parent::populateModel($model);
            $productTemplateName    = self::getProductTemplateForProduct($name);
            $allTemplates = ProductTemplate::getAll();

            foreach ($allTemplates as $template)
            {
                if ($template->name == $productTemplateName)
                {
                    $templateId = $template->id;
                }
            }
            $productTemplate       = ProductTemplate::getById($templateId);
            $model->name            = $name;
            $model->quantity        = mt_rand(1, 95);
            $model->productTemplate = $productTemplate;
            $model->stage->value    = 'Open';
            $model->priceFrequency  = $productTemplate->priceFrequency;
            $model->sellPrice->value= $productTemplate->sellPrice->value;
            $model->type            = $productTemplate->type;
        }

        public static function getProductTemplateForProduct($product)
        {
            $productTemplateMapping = array(
                                                'Amazing Kid Sample'                        => 'Amazing Kid',
                                                'You Can Do Anything Sample'                => 'You Can Do Anything',
                                                'A Bend in the River November Issue'        => 'A Bend in the River',
                                                'A Gift of Monotheists October Issue'       => 'A Gift of Monotheists',
                                                'Enjoy Once in a Lifetime Music'            => 'Once in a Lifetime'
                                            );
            if (!array_key_exists($product, $productTemplateMapping))
            {
                $productNameSubstr = explode('-P', $product);
                if ((strpos($product, 'Laptop') !== false) ||
                    (strpos($product, 'Camera') !== false) ||
                    (strpos($product, 'Handycam') !== false))
                {
                    return $productNameSubstr[0];
                }
            }
            return $productTemplateMapping[$product];
        }

        /**
         * Gets the products random data
         * @return array
         */
        public static function getProductsRandomData()
        {
            $productNames = array(
                                    'names' => array(
                                        'Amazing Kid Sample',
                                        'You Can Do Anything Sample',
                                        'A Bend in the River November Issue',
                                        'A Gift of Monotheists October Issue',
                                        'Enjoy Once in a Lifetime Music'
                                    )
                                );

            $productTemplates = ProductTemplate::getAll();

            foreach ($productTemplates as $template)
            {
                if ((strpos($template->name, 'Laptop') !== false) ||
                    (strpos($template->name, 'Camera') !== false) ||
                    (strpos($template->name, 'Handycam') !== false))
                {
                    for ($i = 1; $i < 3; $i++)
                    {
                       $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 2);
                       $productNames['names'][] = $template->name . '-P' . $randomString;
                    }
                }
            }

            return $productNames;
        }
    }
?>