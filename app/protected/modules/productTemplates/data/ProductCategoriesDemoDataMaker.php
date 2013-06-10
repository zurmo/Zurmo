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
     * Class that builds demo product categories.
     */
    class ProductCategoriesDemoDataMaker extends DemoDataMaker
    {
        protected $ratioToLoad = 1;

        public static function getDependencies()
        {
            return array();
        }

        public function makeAll(& $demoDataHelper)
        {
            assert('$demoDataHelper instanceof DemoDataHelper');
            $productCategories = array();
            $productCatalog    = ProductCatalog::resolveAndGetByName(ProductCatalog::DEFAULT_NAME);
            for ($i = 0; $i < 6; $i++)
            {
                $productCategory = new ProductCategory();
                $productCategory->productCatalogs->add($productCatalog);
                $this->populateModelData($productCategory, $i);
                $saved = $productCategory->save();
                assert('$saved');
                $productCategories[] = $productCategory->id;
            }
            $demoDataHelper->setRangeByModelName('ProductCategory', $productCategories[0], $productCategories[count($productCategories)-1]);
        }

        public function populateModelData(& $model, $counter)
        {
            assert('$model instanceof ProductCategory');
            parent::populateModel($model);
            $productCategoryRandomData = ZurmoRandomDataUtil::getRandomDataByModuleAndModelClassNames(
                                        'ProductTemplatesModule', 'ProductCategory');
            $name        = $productCategoryRandomData['names'][$counter];
            $model->name = $name;
        }
    }
?>