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

    class ProductsSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            ProductTestHelper::createProductByNameForOwner("My Product 1", $super);
            ProductTestHelper::createProductByNameForOwner("My Product 2", $super);
            ProductTestHelper::createProductByNameForOwner("My Product 3", $super);
            ProductTestHelper::createProductByNameForOwner("My Product 4", $super);
            ProductTestHelper::createProductByNameForOwner("My Product 5", $super);
            ProductTestHelper::createProductByNameForOwner("My Product 6", $super);
            ProductTestHelper::createProductByNameForOwner("My Product 7", $super);
            ProductTestHelper::createProductByNameForOwner("My Product 8", $super);
            ProductTestHelper::createProductByNameForOwner("My Product 9", $super);
            ProductTestHelper::createProductByNameForOwner("My Product 10", $super);
            ProductTestHelper::createProductByNameForOwner("My Product 11", $super);
            ProductTestHelper::createProductByNameForOwner("My Product 12", $super);
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test all default controller actions that do not require any POST/GET variables to be passed.
            //This does not include portlet controller actions.
            $this->runControllerWithNoExceptionsAndGetContent('products/default');
            $this->runControllerWithNoExceptionsAndGetContent('products/default/index');
            $this->runControllerWithNoExceptionsAndGetContent('products/default/create');

            $content = $this->runControllerWithNoExceptionsAndGetContent('products/default/list');
            $this->assertFalse(strpos($content, 'anyMixedAttributes') === false);
            //Test the search or paging of the listview.
            Yii::app()->clientScript->reset(); //to make sure old js doesn't make it to the UI
            $this->setGetArray(array('ajax' => 'list-view'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('products/default/list');
            $this->assertTrue(strpos($content, 'anyMixedAttributes') === false);
            $this->resetGetArray();

            //Default Controller actions requiring some sort of parameter via POST or GET
            //Load Model Edit Views
            $products            = Product::getAll();
            $this->assertEquals(12, count($products));
            $superProductId     = self::getModelIdByModelNameAndName('Product', 'My Product 1');
            $superProductId2    = self::getModelIdByModelNameAndName('Product', 'My Product 2');
            $superProductId3    = self::getModelIdByModelNameAndName('Product', 'My Product 3');
            $superProductId4    = self::getModelIdByModelNameAndName('Product', 'My Product 4');
            $superProductId5    = self::getModelIdByModelNameAndName('Product', 'My Product 5');
            $superProductId6    = self::getModelIdByModelNameAndName('Product', 'My Product 6');
            $superProductId7    = self::getModelIdByModelNameAndName('Product', 'My Product 7');
            $superProductId8    = self::getModelIdByModelNameAndName('Product', 'My Product 8');
            $superProductId9    = self::getModelIdByModelNameAndName('Product', 'My Product 9');
            $superProductId10   = self::getModelIdByModelNameAndName('Product', 'My Product 10');
            $superProductId11   = self::getModelIdByModelNameAndName('Product', 'My Product 11');
            $superProductId12   = self::getModelIdByModelNameAndName('Product', 'My Product 12');

            $this->setGetArray(array('id' => $superProductId));
            $this->runControllerWithNoExceptionsAndGetContent('products/default/edit');
            //Save product.
            $superProduct       = Product::getById($superProductId);
            $this->setPostArray(array('Product' => array('name' => 'My New Product 1')));

            //Test having a failed validation on the product during save.
            $this->setGetArray (array('id'      => $superProductId));
            $this->setPostArray(array('Product' => array('name' => '')));
            $content = $this->runControllerWithNoExceptionsAndGetContent('products/default/edit');
            $this->assertFalse(strpos($content, 'Name cannot be blank') === false);

            //Load Model Detail Views
            $this->setGetArray(array('id' => $superProductId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('products/default/details');
        }

        public function testSuperUserCreateAction()
        {
            $super                                      = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            Yii::app()->user->userModel                 = $super;
            $this->resetGetArray();

            $currency                                   = new Currency();
            $currency->code                             = 'USD';
            $currency->rateToBase                       = 1;
            $currency->save();

            $currencyRec                                = Currency::getByCode('USD');

            $currencyValue1Array                        = array('currency' => array('id' => $currencyRec->id), 'value' => 500.54);

            ProductTemplateTestHelper::createProductTemplateByName("My Catalog Item 1");
            $productTemplate                            = ProductTemplate::getByName('My Catalog Item 1');
            $product                                    = array();
            $product['name']                            = 'Red Widget';
            $product['quantity']                        = 5;
            $product['priceFrequency']                  = ProductTemplate::PRICE_FREQUENCY_ONE_TIME;
            $product['sellPrice']                       = $currencyValue1Array;

            $product['type']                            = ProductTemplate::TYPE_PRODUCT;
            $product['stage']['value']                  = Product::OPEN_STAGE;
            $product['productTemplate']                 = array('id' => $productTemplate[0]->id);
            $this->setPostArray(array('Product' => $product, 'Product_owner_name' => 'Super User'));
            $redirectUrl                                = $this->runControllerWithRedirectExceptionAndGetUrl('products/default/create');

            $products                                   = Product::getByName('Red Widget');
            $this->assertEquals(1, count($products));
            $this->assertTrue  ($products[0]->id > 0);
            $this->assertEquals(500.54, $products[0]->sellPrice->value);
            $compareRedirectUrl                         = Yii::app()->createUrl('products/default/details', array('id' => $products[0]->id));
            $this->assertEquals($compareRedirectUrl, $redirectUrl);
        }

        public function testSuperUserDeleteAction()
        {
            $super                      = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            Yii::app()->user->userModel = $super;

            $product                    = ProductTestHelper::createProductByNameForOwner("My New Product", $super);

            //Delete a product
            $this->setGetArray(array('id' => $product->id));
            $this->resetPostArray();
            $products       = Product::getAll();
            $this->assertEquals(14, count($products));
            $this->runControllerWithRedirectExceptionAndGetContent('products/default/delete');
            $products       = Product::getAll();
            $this->assertEquals(13, count($products));
            try
            {
                Product::getById($product->id);
                $this->fail();
            }
            catch (NotFoundException $e)
            {
                //success
            }
        }

        /**
         * @deletes selected products.
         */
        public function testMassDeleteActionsForSelectedIds()
        {
            $super                  = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $products               = Product::getAll();
            $this->assertEquals(13, count($products));
            $superProductId        = self::getModelIdByModelNameAndName('Product', 'My Product 1');
            $superProductId2       = self::getModelIdByModelNameAndName('Product', 'My Product 2');
            $superProductId3       = self::getModelIdByModelNameAndName('Product', 'My Product 3');
            $superProductId4       = self::getModelIdByModelNameAndName('Product', 'My Product 4');
            $superProductId5       = self::getModelIdByModelNameAndName('Product', 'My Product 5');
            $superProductId6       = self::getModelIdByModelNameAndName('Product', 'My Product 6');
            $superProductId7       = self::getModelIdByModelNameAndName('Product', 'My Product 7');
            $superProductId8       = self::getModelIdByModelNameAndName('Product', 'My Product 8');
            $superProductId9       = self::getModelIdByModelNameAndName('Product', 'My Product 9');
            $superProductId10      = self::getModelIdByModelNameAndName('Product', 'My Product 10');
            $superProductId11      = self::getModelIdByModelNameAndName('Product', 'My Product 11');
            $superProductId12      = self::getModelIdByModelNameAndName('Product', 'My Product 12');
            //Load Model MassDelete Views.
            //MassDelete view for single selected ids
            $this->setGetArray(array('selectedIds' => '5,6,7,8,9', 'selectAll' => '', ));  // Not Coding Standard
            $this->resetPostArray();
            $content                = $this->runControllerWithNoExceptionsAndGetContent('products/default/massDelete');

            $this->assertFalse(strpos($content, '<strong>5</strong>&#160;Products selected for removal') === false);

             //MassDelete view for all result selected ids
            $this->setGetArray(array('selectAll' => '1'));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('products/default/massDelete');
            $this->assertFalse(strpos($content, '<strong>13</strong>&#160;Products selected for removal') === false);

            //MassDelete for selected Record Count
            $products               = Product::getAll();
            $this->assertEquals(13, count($products));

            //MassDelete for selected ids for paged scenario
            $superProduct1 = Product::getById($superProductId);
            $superProduct2 = Product::getById($superProductId2);
            $superProduct3 = Product::getById($superProductId3);
            $superProduct4 = Product::getById($superProductId4);
            $superProduct5 = Product::getById($superProductId5);
            $superProduct6 = Product::getById($superProductId6);
            $superProduct7 = Product::getById($superProductId7);

            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massDeleteProgressPageSize');
            $this->assertEquals(5, $pageSize);
            //MassDelete for selected ids for page 1
            $this->setGetArray(array(
                'selectedIds'  => $superProductId . ',' . $superProductId2 . ',' .  // Not Coding Standard
                                  $superProductId3 . ',' . $superProductId4 . ',' . // Not Coding Standard
                                  $superProductId5 . ',' . $superProductId6 . ',' . // Not Coding Standard
                                  $superProductId7,
                'selectAll'    => '',
                'massDelete'   => '',
                'Product_page' => 1));
            $this->setPostArray(array('selectedRecordCount' => 7));
            $this->runControllerWithExitExceptionAndGetContent('products/default/massDelete');

            //MassDelete for selected Record Count
            $products = Product::getAll();
            $this->assertEquals(8, count($products));

            //MassDelete for selected ids for page 2
            $this->setGetArray(array(
                'selectedIds'  => $superProductId . ',' . $superProductId2 . ',' .  // Not Coding Standard
                                  $superProductId3 . ',' . $superProductId4 . ',' . // Not Coding Standard
                                  $superProductId5 . ',' . $superProductId6 . ',' . // Not Coding Standard
                                  $superProductId7,
                'selectAll'    => '',
                'massDelete'   => '',
                'Product_page' => 2));
            $this->setPostArray(array('selectedRecordCount' => 7));
            $this->runControllerWithNoExceptionsAndGetContent('products/default/massDeleteProgress');

            //MassDelete for selected Record Count
            $products = Product::getAll();
            $this->assertEquals(6, count($products));
        }

        /**
         *Test Bug with mass delete and multiple pages when using select all
         */
        public function testMassDeletePagesProperlyAndRemovesAllSelected()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //MassDelete for selected Record Count
            $products = Product::getAll();
            $this->assertEquals(6, count($products));

            //save Model MassDelete for entire search result
            $this->setGetArray(array(
                'selectAll' => '1',           // Not Coding Standard
                'Product_page' => 1));
            $this->setPostArray(array('selectedRecordCount' => 7));
            //Run Mass Delete using progress save for page1.
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massDeleteProgressPageSize');
            $this->assertEquals(5, $pageSize);
            $this->runControllerWithExitExceptionAndGetContent('products/default/massDelete');

            //check for previous mass delete progress
            $products = Product::getAll();
            $this->assertEquals(1, count($products));

            $this->setGetArray(array(
                'selectAll' => '1',           // Not Coding Standard
                'Product_page' => 2));
            $this->setPostArray(array('selectedRecordCount' => 7));
            //Run Mass Delete using progress save for page2.
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massDeleteProgressPageSize');
            $this->assertEquals(5, $pageSize);
            $this->runControllerWithNoExceptionsAndGetContent('products/default/massDeleteProgress');

            //calculating products count
            $products = Product::getAll();
            $this->assertEquals(0, count($products));
        }

        public function testCloningWithAnotherProduct()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            Yii::app()->user->userModel = $super;

            $product = ProductTestHelper::createProductByNameForOwner("My Product 1", $super);
            $id = $product->id;
            $this->setGetArray(array('id' => $id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('products/default/copy');
            $this->assertTrue(strpos($content, 'My Product 1') > 0);
            $products = Product::getAll();
            $this->assertEquals(1, count($products));
        }
    }
?>