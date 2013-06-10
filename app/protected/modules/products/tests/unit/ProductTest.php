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

    class ProductTest extends ZurmoBaseTest
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

        public function testDemoDataMaker()
        {
            $model                    = new Product();
            $name                     = 'Amazing Kid Sample';
            $productTemplateName      = ProductsDemoDataMaker::getProductTemplateForProduct($name);
            $productTemplate          = ProductTemplateTestHelper::createProductTemplateByName($productTemplateName);
            $model->name              = $name;
            $model->quantity          = mt_rand(1, 95);
            $model->productTemplate   = $productTemplate;
            $model->stage->value      = 'Open';
            $model->priceFrequency    = $productTemplate->priceFrequency;
            $model->sellPrice->value  = $productTemplate->sellPrice->value;
            $model->type              = $productTemplate->type;
            $this->assertTrue($model->save());
            $id                       = $model->id;

            $product                  = Product::getById($id);
            $this->assertEquals($model->name, $product->name);
            $this->assertEquals($model->quantity, $product->quantity);
            $this->assertEquals($model->description, $product->description);
            $this->assertEquals($model->stage->value, $product->stage->value);
            $this->assertEquals($model->owner->id, $product->owner->id);
            $this->assertTrue($model->productTemplate->isSame($productTemplate));
            $this->assertEquals($model->priceFrequency, $product->priceFrequency);
            $this->assertEquals($model->sellPrice->value, $product->sellPrice->value);
            $this->assertEquals($model->type, $product->type);
        }

        public function testCreateAndGetProductById()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $contacts         = Contact::getAll();
            $accounts         = Account::getByName('superAccount');
            $opportunities    = Opportunity::getByName('superOpportunity');
            $productTemplates = ProductTemplate::getByName('superProductTemplate');
            $account          = $accounts[0];
            $user             = $account->owner;

            $product                  = new Product();
            $product->name            = 'Product 1';
            $product->owner           = $user;
            $product->description     = 'Description';
            $product->quantity        = 2;
            $product->stage->value    = 'Open';
            $product->account         = $accounts[0];
            $product->contact         = $contacts[0];
            $product->opportunity     = $opportunities[0];
            $product->productTemplate = $productTemplates[0];
            $product->priceFrequency  = ProductTemplate::PRICE_FREQUENCY_ONE_TIME;
            $product->sellPrice->value= 200;
            $product->type            = ProductTemplate::TYPE_PRODUCT;

            $this->assertTrue($product->save());
            $id                       = $product->id;
            $product->forget();
            unset($product);
            $product                  = Product::getById($id);
            $this->assertEquals('Product 1', $product->name);
            $this->assertEquals(2, $product->quantity);
            $this->assertEquals('Description', $product->description);
            $this->assertEquals('Open', $product->stage->value);
            $this->assertEquals($user->id, $product->owner->id);
            $this->assertTrue($product->contact->isSame($contacts[0]));
            $this->assertTrue($product->account->isSame($accounts[0]));
            $this->assertTrue($product->opportunity->isSame($opportunities[0]));
            $this->assertTrue($product->productTemplate->isSame($productTemplates[0]));
            $this->assertEquals(ProductTemplate::PRICE_FREQUENCY_ONE_TIME, $product->priceFrequency);
            $this->assertEquals(200, $product->sellPrice->value);
            $this->assertEquals(ProductTemplate::TYPE_PRODUCT, $product->type);
        }

        /**
         * @depends testCreateAndGetProductById
         */
        public function testGetProductsByName()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $products           = Product::getByName('Product 1');
            $this->assertEquals(1, count($products));
            $this->assertEquals('Product 1', $products[0]->name);
        }

        /**
         * @depends testCreateAndGetProductById
         */
        public function testGetLabel()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $products                   = Product::getByName('Product 1');
            $this->assertEquals(1, count($products));
            $this->assertEquals('Product',  $products[0]::getModelLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Products', $products[0]::getModelLabelByTypeAndLanguage('Plural'));
        }

        /**
         * @depends testGetProductsByName
         */
        public function testGetProductByNameForNonExistentName()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $products                   = Product::getByName('Red Widget 1');
            $this->assertEquals(0, count($products));
        }

        /**
         * @depends testCreateAndGetProductById
         */
        public function testGetAll()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $products                   = Product::getAll();
            $this->assertEquals(2, count($products));
            $this->assertEquals("superAccount", $products[1]->account->name);
        }

        public function testDeleteProduct()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $products                   = Product::getAll();
            $this->assertEquals(2, count($products));
            $products[0]->delete();
        }

        public function testGetAllWhenThereAreNone()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $products                   = Product::getAll();
            $this->assertEquals(1, count($products));
        }
    }
?>