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

    class ProductCategoryTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testDemoDataMaker()
        {
            $productCategory           = new ProductCategory();
            $productTemplateRandomData = ZurmoRandomDataUtil::getRandomDataByModuleAndModelClassNames(
                                            'ProductTemplatesModule', 'ProductCategory');
            $name                      = RandomDataUtil::getRandomValueFromArray($productTemplateRandomData['names']);
            $productCategory->name     = $name;
            $this->assertTrue($productCategory->save());
            $productCategories[]       = $productCategory->id;
        }

        public function testCreateAndGetProductCategoryById()
        {
            $user                  = UserTestHelper::createBasicUser('Steven');

            $productCategory       = new ProductCategory();
            $productCategory->name = "My Test Category";

            $productCatalog        = new ProductCatalog();
            $productCatalog->name  = ProductCatalog::DEFAULT_NAME;
            $productCatalog->save();
            $productCategory->productCatalogs->add($productCatalog);

            $this->assertTrue($productCategory->save());
            $id                    = $productCategory->id;
            $productCategory->forget();
            unset($productCategory);
            $productCategory       = ProductCategory::getById($id);
            $this->assertEquals('My Test Category', $productCategory->name);
            $this->assertEquals(ProductCatalog::DEFAULT_NAME, $productCategory->productCatalogs[0]->name);
        }

        /**
         * @depends testCreateAndGetProductCategoryById
         */
        public function testGetProductCategoriesByName()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $productCategories = ProductCategory::getByName('My Test Category');
            $this->assertEquals(1, count($productCategories));
            $this->assertEquals('My Test Category', $productCategories[0]->name);
        }

        /**
         * @depends testCreateAndGetProductCategoryById
         */
        public function testGetLabel()
        {
            $productCategories          = ProductCategory::getByName('My Test Category');
            $this->assertEquals(1, count($productCategories));
            $this->assertEquals('Product Category',   $productCategories[0]::getModelLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Product Categories', $productCategories[0]::getModelLabelByTypeAndLanguage('Plural'));
        }

        /**
         * @depends testGetProductCategoriesByName
         */
        public function testGetProductCategoriesByNameForNonExistentName()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $productCategories = ProductCategory::getByName('Red Widget 1');
            $this->assertEquals(0, count($productCategories));
        }

        /**
         * @depends testCreateAndGetProductCategoryById
         */
        public function testGetAll()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $productCategories          = ProductCategory::getAll();
            $this->assertEquals(2, count($productCategories));
            $this->assertEquals('My Test Category', $productCategories[1]->name);
        }

        /**
         * @depends testCreateAndGetProductCategoryById
         */
        public function testUpdateProductCategoryFromForm()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $user                       = User::getByUsername('Steven');
            $productCategories          = ProductCategory::getByName('My Test Category');
            $productCategory            = $productCategories[0];
            $postData                   = array(
                                                    'name'        => 'Second Category'
                                                );
            $productCategory->setAttributes($postData);
            $this->assertTrue($productCategory->save());
            $id                         = $productCategory->id;
            unset($productCategory);
            $productCategory            = ProductCategory::getById($id);
            $this->assertEquals('Second Category', $productCategory->name);
        }

        public function testDeleteProductCategory()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $productCategories          = ProductCategory::getAll();
            $this->assertEquals(2, count($productCategories));
            $productCategories[0]->delete();
            $productCategories          = ProductCategory::getAll();
            $this->assertEquals(1, count($productCategories));
            $productCategories[0]->delete();
            $productCategories          = ProductCategory::getAll();
            $this->assertEquals(0, count($productCategories));
        }

        public function testGetAllWhenThereAreNone()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $productCategories          = ProductCategory::getAll();
            $this->assertEquals(0, count($productCategories));
        }

        public function testCategoryProductTemplates()
        {
            $user                   = UserTestHelper::createBasicUser('Steven 1');
            $productCategory        = new ProductCategory();
            $productCategory->name  = "My Test Category";
            $productTemplate        = ProductTemplateTestHelper::createProductTemplateByName("Test Template");

            $productCategory->productTemplates->add($productTemplate);
            $this->assertTrue($productCategory->save());
            $id                     = $productCategory->id;
            unset($productCategory);
            $productCategory        = ProductCategory::getById($id);
            $this->assertEquals(1, count($productCategory->productTemplates));
            $this->assertEquals($productTemplate, $productCategory->productTemplates[0]);
        }

        public function testChildProductCategories()
        {
            $childProductCategory                   = new ProductCategory();
            $childProductCategory->name             = "My Test Category Child";
            $existingCats                           = ProductCategory::getByName('My Test Category');
            $childProductCategory->productCategory  = $existingCats[0];
            $this->assertTrue($childProductCategory->save());
            $id                                     = $childProductCategory->id;
            unset($childProductCategory);
            $childProductCategory                   = ProductCategory::getById($id);
            $this->assertEquals("My Test Category Child", $childProductCategory->name);
            $this->assertEquals("My Test Category", $existingCats[0]->name);
        }
    }
?>