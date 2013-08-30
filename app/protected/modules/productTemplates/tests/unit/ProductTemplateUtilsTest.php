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

    class ProductTemplateUtilsTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            SecurityTestHelper::createUsers();
        }

        public function testGetProductTemplateTypeDisplayedGridValue()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $productTemplate = ProductTemplateTestHelper::createProductTemplateByName('My Product Template');
            $id                       = $productTemplate->id;
            $productTemplate->forget();
            unset($productTemplate);
            $productTemplate          = ProductTemplate::getById($id);
            $sellPrice = ProductTemplateElementUtil::getProductTemplateTypeDisplayedGridValue($productTemplate, 0);
            $this->assertEquals($sellPrice, "Product");
        }

        public function testGetProductTemplateStatusDisplayedGridValue()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $productTemplate = ProductTemplateTestHelper::createProductTemplateByName('My Product Template 1');
            $id                       = $productTemplate->id;
            $productTemplate->forget();
            unset($productTemplate);
            $productTemplate          = ProductTemplate::getById($id);
            $status = ProductTemplateElementUtil::getProductTemplateStatusDisplayedGridValue($productTemplate, 0);
            $this->assertEquals($status, "Active");
        }

        public function testGetProductTemplatePriceFrequencyDisplayedGridValue()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $productTemplate = ProductTemplateTestHelper::createProductTemplateByName('My Product Template 2');
            $id                       = $productTemplate->id;
            $productTemplate->forget();
            unset($productTemplate);
            $productTemplate          = ProductTemplate::getById($id);
            $priceFrequency = ProductTemplateElementUtil::getProductTemplatePriceFrequencyDisplayedGridValue($productTemplate, 0);
            $this->assertEquals($priceFrequency, "Monthly");
        }

        public function testResolveProductTemplateHasManyProductCategoriesFromPost()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $category1 = ProductCategoryTestHelper::createProductCategoryByName('Test Product Category');
            $category2 = ProductCategoryTestHelper::createProductCategoryByName('Test Product Category2');
            $productTemplate   = ProductTemplateTestHelper::createProductTemplateByName('PT1');
            $postData  = array('categoryIds' => $category1->id . ',' . $category2->id); // Not Coding Standard
            $id                       = $productTemplate->id;
            $productTemplate->forget();
            unset($productTemplate);
            $productTemplate                  = ProductTemplate::getById($id);
            $categories = ProductTemplateProductCategoriesUtil::resolveProductTemplateHasManyProductCategoriesFromPost($productTemplate, $postData);
            $this->assertEquals(count($categories), 2);
            $this->assertEquals($categories[$category1->id]->id, $category1->id);
        }
    }
?>