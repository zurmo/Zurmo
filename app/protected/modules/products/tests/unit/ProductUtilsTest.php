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

    class ProductUtilsTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            //Setup test data owned by the super user.
            $account                = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
            $opportunity            = OpportunityTestHelper::createOpportunityByNameForOwner('superOpportunity', $super);
            $productTemplate        = ProductTemplateTestHelper::createProductTemplateByName('superProductTemplate');
            $contactWithNoAccount   = ContactTestHelper::createContactByNameForOwner('noAccountContact', $super);
        }

        public function testGetProductPortletSellPrice()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $product = ProductTestHelper::createProductByNameForOwner('My Product', $super);
            $id                       = $product->id;
            $product->forget();
            unset($product);
            $product                  = Product::getById($id);
            $sellPrice = ProductElementUtil::getProductPortletSellPrice($product, 0);
            $this->assertEquals($sellPrice, "$500.54");
        }

        public function testGetProductPortletTotalPrice()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $product = ProductTestHelper::createProductByNameForOwner('My Product 1', $super);
            $id                       = $product->id;
            $product->forget();
            unset($product);
            $product                  = Product::getById($id);
            $totalPrice = ProductElementUtil::getProductPortletTotalPrice($product, 0);
            $this->assertEquals($totalPrice, "$1,001.08"); // Not Coding Standard
        }
        
        public function testResolveProductHasManyProductCategoriesFromPost()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $category1 = ProductCategoryTestHelper::createProductCategoryByName('Test Product Category');
            $category2 = ProductCategoryTestHelper::createProductCategoryByName('Test Product Category2');
            $product = ProductTestHelper::createProductByNameForOwner('I am testing products', $super);
            $postData = array('categoryIds' => $category1->id . ',' . $category2->id); // Not Coding Standard
            $id                       = $product->id;
            $product->forget();
            unset($product);
            $product                  = Product::getById($id);
            $categories = ProductCategoriesUtil::resolveProductHasManyProductCategoriesFromPost($product, $postData);
            $this->assertEquals(count($categories), 2);
            $this->assertEquals($categories[$category1->id]->id, $category1->id);
        }
    }
?>