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
     * Helper class for product template product category logic.
     */
    class ProductTemplateProductCategoriesUtil
    {
        /**
         * Resolve the product categories from prost
         * @param ProductTemplate $productTemplate
         * @param array $postData
         * @return array
         */
        public static function resolveProductTemplateHasManyProductCategoriesFromPost(
                                    ProductTemplate $productTemplate, $postData)
        {
            $newCategory = array();
            if (isset($postData['categoryIds']) && strlen($postData['categoryIds']) > 0)
            {
                $categoryIds = explode(",", $postData['categoryIds']);  // Not Coding Standard
                foreach ($categoryIds as $categoryId)
                {
                    $newCategory[$categoryId] = ProductCategory::getById((int)$categoryId);
                }
                if ($productTemplate->productCategories->count() > 0)
                {
                    $categoriesToRemove = array();
                    foreach ($productTemplate->productCategories as $index => $existingCategory)
                    {
                        $categoriesToRemove[] = $existingCategory;
                    }
                    foreach ($categoriesToRemove as $categoryToRemove)
                    {
                        $productTemplate->productCategories->remove($categoryToRemove);
                    }
                }
                //Now add missing categories
                foreach ($newCategory as $category)
                {
                    $productTemplate->productCategories->add($category);
                }
            }
            else
            {
                //remove all categories
                $productTemplate->productCategories->removeAll();
            }
            return $newCategory;
        }
    }
?>