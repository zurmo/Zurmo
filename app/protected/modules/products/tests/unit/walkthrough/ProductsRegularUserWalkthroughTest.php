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
     * Products Module Walkthrough.
     * Walkthrough for a peon user.  The peon user at first will have no granted
     * rights or permissions.  Most attempted actions will result in an ExitException
     * and a access failure view.  After this, we elevate the user with added tab rights
     * so that some of the actions will result in success and no exceptions being thrown.
     * There will still be some actions they cannot get too though because of the lack of
     * elevated permissions.  Then we will elevate permissions to allow the user to access
     * other owner's records.
     */
    class ProductsRegularUserWalkthroughTest extends ZurmoRegularUserWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = Yii::app()->user->userModel;

            //Setup test data owned by the super user.
            ProductTestHelper::createProductByNameForOwner("My Product 1", $super);
            ProductTestHelper::createProductByNameForOwner("My Product 2", $super);
            ProductTestHelper::createProductByNameForOwner("My Product 3", $super);
            ProductTestHelper::createProductByNameForOwner("My Product 4", $super);
            //Setup default dashboard.
            Dashboard::getByLayoutIdAndUser(Dashboard::DEFAULT_USER_LAYOUT_ID, $super);
            ReadPermissionsOptimizationUtil::rebuild();
        }

        public function testRegularUserAllControllerActionsNoElevation()
        {
            //Create product owned by user super.
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $product = ProductTestHelper::createProductByNameForOwner('My Product 5', $super);
            Yii::app()->user->userModel = User::getByUsername('nobody');

            //Now test all portlet controller actions
            $this->runControllerShouldResultInAccessFailureAndGetContent('products/default');
            $this->runControllerShouldResultInAccessFailureAndGetContent('products/default/index');
            $this->runControllerShouldResultInAccessFailureAndGetContent('products/default/list');
            $this->runControllerShouldResultInAccessFailureAndGetContent('products/default/create');
            $this->runControllerShouldResultInAccessFailureAndGetContent('products/default/edit');
            $this->setGetArray(array('id' => $product->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('products/default/details');
            $this->setGetArray(array('selectedIds' => '4,5,6,7,8', 'selectAll' => ''));  // Not Coding Standard
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('products/default/massEdit');
            $this->setGetArray(array('selectAll' => '1', 'Product_page' => 2));
            $this->runControllerShouldResultInAccessFailureAndGetContent('products/default/massEditProgressSave');

            //actionDelete should fail.
            $this->setGetArray(array('id' => $product->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('products/default/delete');
        }

        /**
         * @depends testRegularUserAllControllerActionsNoElevation
         */
        public function testRegularUserControllerActionsWithElevationToAccessAndCreate()
        {
            //Now test peon with elevated rights to tabs /other available rights
            $nobody = $this->logoutCurrentUserLoginNewUserAndGetByUsername('nobody');

            //Now test peon with elevated rights to products
            $nobody->setRight('ProductsModule', ProductsModule::RIGHT_ACCESS_PRODUCTS);
            $nobody->setRight('ProductsModule', ProductsModule::RIGHT_CREATE_PRODUCTS);
            $nobody->setRight('ProductsModule', ProductsModule::RIGHT_DELETE_PRODUCTS);
            $this->assertTrue($nobody->save());

            //Test nobody with elevated rights.
            Yii::app()->user->userModel = $nobody;
            $content = $this->runControllerWithNoExceptionsAndGetContent('products/default/list');

            $this->assertFalse(strpos($content, 'John Kenneth Galbraith') === false);
            $this->runControllerWithNoExceptionsAndGetContent('products/default/create');
            //Test nobody can view an existing product he owns.
            $product = ProductTestHelper::createProductByNameForOwner('productOwnedByNobody', $nobody);

            //At this point the listview for products should show the search/list and not the helper screen.
            $content = $this->runControllerWithNoExceptionsAndGetContent('products/default/list');
            $this->assertTrue(strpos($content, 'John Kenneth Galbraith') === false);

            $this->setGetArray(array('id' => $product->id));
            $this->runControllerWithNoExceptionsAndGetContent('products/default/edit');

            //Test nobody can delete an existing product he owns and it redirects to index.
            $this->setGetArray(array('id' => $product->id));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('products/default/delete',
                                                                   Yii::app()->createUrl('products/default/index'));
        }

        /**
         * @depends testRegularUserControllerActionsWithElevationToAccessAndCreate
         */
        public function testRegularUserControllerActionsWithElevationToModels()
        {
            //Create product owned by user super.
            $super      = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $product    = ProductTestHelper::createProductByNameForOwner('productForElevationToModelTest', $super);

            //Test nobody, access to edit and details should fail.
            $nobody = $this->logoutCurrentUserLoginNewUserAndGetByUsername('nobody');
            $this->setGetArray(array('id' => $product->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('products/default/edit');
            $this->setGetArray(array('id' => $product->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('products/default/details');
            $this->setGetArray(array('id' => $product->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('products/default/delete');

            //give nobody access to read
            Yii::app()->user->userModel = $super;
            $product->addPermissions($nobody, Permission::READ);
            $this->assertTrue($product->save());

            //Now the nobody user can access the details view.
            Yii::app()->user->userModel = $nobody;
            $this->setGetArray(array('id' => $product->id));
            $this->runControllerWithNoExceptionsAndGetContent('products/default/details');

            //Test nobody, access to edit should fail.
            $this->setGetArray(array('id' => $product->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('products/default/edit');
            $this->setGetArray(array('id' => $product->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('products/default/delete');

            $productId  = $product->id;
            $product->forget();
            $product    = Product::getById($productId);
            //give nobody access to read and write
            Yii::app()->user->userModel = $super;
            $product->addPermissions($nobody, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            //TODO :Its wierd that giving opportunity errors
            $this->assertTrue($product->save());

            //Now the nobody user should be able to access the edit view and still the details view.
            Yii::app()->user->userModel = $nobody;
            $this->setGetArray(array('id' => $product->id));
            $this->runControllerWithNoExceptionsAndGetContent('products/default/details');
            $this->setGetArray(array('id' => $product->id));
            $this->runControllerWithNoExceptionsAndGetContent('products/default/edit');

            $productId  = $product->id;
            $product->forget();
            $product    = Product::getById($productId);
            //revoke nobody access to read
            Yii::app()->user->userModel = $super;
            $product->addPermissions($nobody, Permission::READ_WRITE_CHANGE_PERMISSIONS, Permission::DENY);
            $this->assertTrue($product->save());

            //Test nobody, access to detail should fail.
            Yii::app()->user->userModel = $nobody;
            $this->setGetArray(array('id' => $product->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('products/default/details');
            $this->setGetArray(array('id' => $product->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('products/default/edit');

            //create some roles
            Yii::app()->user->userModel = $super;
            $parentRole = new Role();
            $parentRole->name = 'AAA';
            $this->assertTrue($parentRole->save());

            $childRole = new Role();
            $childRole->name = 'BBB';
            $this->assertTrue($childRole->save());

            $userInParentRole = User::getByUsername('confused');
            $userInChildRole = User::getByUsername('nobody');

            $childRole->users->add($userInChildRole);
            $this->assertTrue($childRole->save());
            $parentRole->users->add($userInParentRole);
            $parentRole->roles->add($childRole);
            $this->assertTrue($parentRole->save());

            //create product owned by super

            $product2 = ProductTestHelper::createProductByNameForOwner('testingParentRolePermission', $super);

            //Test userInParentRole, access to details and edit should fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $product2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('products/default/details');
            $this->setGetArray(array('id' => $product2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('products/default/edit');

            //give userInChildRole access to READ
            Yii::app()->user->userModel = $super;
            $product2->addPermissions($userInChildRole, Permission::READ);
            $this->assertTrue($product2->save());

            //Test userInChildRole, access to details should not fail.
            Yii::app()->user->userModel = $userInChildRole;
            $this->setGetArray(array('id' => $product2->id));
            $this->runControllerWithNoExceptionsAndGetContent('products/default/details');

            //Test userInParentRole, access to details should not fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $product2->id));
            $this->runControllerWithNoExceptionsAndGetContent('products/default/details');

            $productId  = $product2->id;
            $product2->forget();
            $product2   = Product::getById($productId);

            //give userInChildRole access to read and write
            Yii::app()->user->userModel = $super;
            $product2->addPermissions($userInChildRole, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($product2->save());

            //Test userInChildRole, access to edit should not fail.
            Yii::app()->user->userModel = $userInChildRole;
            $this->setGetArray(array('id' => $product2->id));
            $this->runControllerWithNoExceptionsAndGetContent('products/default/edit');

            //Test userInParentRole, access to edit should not fail.
            $this->logoutCurrentUserLoginNewUserAndGetByUsername($userInParentRole->username);
            $this->setGetArray(array('id' => $product2->id));
            $this->runControllerWithNoExceptionsAndGetContent('products/default/edit');

            $productId  = $product2->id;
            $product2->forget();
            $product2   = Product::getById($productId);
            //revoke userInChildRole access to read and write
            Yii::app()->user->userModel = $super;
            $product2->addPermissions($userInChildRole, Permission::READ_WRITE_CHANGE_PERMISSIONS, Permission::DENY);
            $this->assertTrue($product2->save());

            //Test userInChildRole, access to detail should fail.
            Yii::app()->user->userModel = $userInChildRole;
            $this->setGetArray(array('id' => $product2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('products/default/details');
            $this->setGetArray(array('id' => $product2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('products/default/edit');

            //Test userInParentRole, access to detail should fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $product2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('products/default/details');
            $this->setGetArray(array('id' => $product2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('products/default/edit');

            //clear up the role relationships between users so not to effect next assertions
            $parentRole->users->remove($userInParentRole);
            $parentRole->roles->remove($childRole);
            $this->assertTrue($parentRole->save());
            $childRole->users->remove($userInChildRole);
            $this->assertTrue($childRole->save());

            //create some groups and assign users to groups
            Yii::app()->user->userModel = $super;
            $parentGroup = new Group();
            $parentGroup->name = 'AAA';
            $this->assertTrue($parentGroup->save());

            $childGroup = new Group();
            $childGroup->name = 'BBB';
            $this->assertTrue($childGroup->save());

            $userInChildGroup = User::getByUsername('confused');
            $userInParentGroup = User::getByUsername('nobody');

            $childGroup->users->add($userInChildGroup);
            $this->assertTrue($childGroup->save());
            $parentGroup->users->add($userInParentGroup);
            $parentGroup->groups->add($childGroup);
            $this->assertTrue($parentGroup->save());
            $parentGroup->forget();
            $childGroup->forget();
            $parentGroup = Group::getByName('AAA');
            $childGroup = Group::getByName('BBB');

            //Add access for the confused user to Products and creation of Products.
            $userInChildGroup->setRight('ProductsModule', ProductsModule::RIGHT_ACCESS_PRODUCTS);
            $userInChildGroup->setRight('ProductsModule', ProductsModule::RIGHT_CREATE_PRODUCTS);
            $this->assertTrue($userInChildGroup->save());

            //create product owned by super
            $product3 = ProductTestHelper::createProductByNameForOwner('testingParentGroupPermission', $super);

            //Test userInParentGroup, access to details and edit should fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $product3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('products/default/details');
            $this->setGetArray(array('id' => $product3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('products/default/edit');

            //Test userInChildGroup, access to details and edit should fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->setGetArray(array('id' => $product3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('products/default/details');
            $this->setGetArray(array('id' => $product3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('products/default/edit');

            //give parentGroup access to READ
            Yii::app()->user->userModel = $super;
            $product3->addPermissions($parentGroup, Permission::READ);
            $this->assertTrue($product3->save());

            //Test userInParentGroup, access to details should not fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $product3->id));
            $this->runControllerWithNoExceptionsAndGetContent('products/default/details');

            //Test userInChildGroup, access to details should not fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->setGetArray(array('id' => $product3->id));
            $this->runControllerWithNoExceptionsAndGetContent('products/default/details');

            $productId  = $product3->id;
            $product3->forget();
            $product3   = Product::getById($productId);
            //give parentGroup access to read and write
            Yii::app()->user->userModel = $super;
            $product3->addPermissions($parentGroup, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($product3->save());

            //Test userInParentGroup, access to edit should not fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $product3->id));
            $this->runControllerWithNoExceptionsAndGetContent('products/default/edit');

            //Test userInChildGroup, access to edit should not fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->logoutCurrentUserLoginNewUserAndGetByUsername($userInChildGroup->username);
            $this->setGetArray(array('id' => $product3->id));
            $this->runControllerWithNoExceptionsAndGetContent('products/default/edit');

            $productId  = $product3->id;
            $product3->forget();
            $product3   = Product::getById($productId);
            //revoke parentGroup access to read and write
            Yii::app()->user->userModel = $super;
            $product3->addPermissions($parentGroup, Permission::READ_WRITE_CHANGE_PERMISSIONS, Permission::DENY);
            $this->assertTrue($product3->save());

            //Test userInChildGroup, access to detail should fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->setGetArray(array('id' => $product3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('products/default/details');
            $this->setGetArray(array('id' => $product3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('products/default/edit');

            //Test userInParentGroup, access to detail should fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $product3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('products/default/details');
            $this->setGetArray(array('id' => $product3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('products/default/edit');

            //clear up the role relationships between users so not to effect next assertions
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $userInParentGroup->forget();
            $userInChildGroup->forget();
            $childGroup->forget();
            $parentGroup->forget();
            $userInParentGroup          = User::getByUsername('nobody');
            $userInChildGroup           = User::getByUsername('confused');
            $childGroup                 = Group::getByName('BBB');
            $parentGroup                = Group::getByName('AAA');

            //clear up the role relationships between users so not to effect next assertions
            $parentGroup->users->remove($userInParentGroup);
            $parentGroup->groups->remove($childGroup);
            $this->assertTrue($parentGroup->save());
            $childGroup->users->remove($userInChildGroup);
            $this->assertTrue($childGroup->save());
        }

        /**
         * @depends testRegularUserControllerActionsWithElevationToModels
         */
        public function testRegularUserViewingProductWithoutAccessToAccount()
        {
            $super       = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $aUser       = UserTestHelper::createBasicUser('aUser');
            $aUser->setRight('ProductsModule', ProductsModule::RIGHT_ACCESS_PRODUCTS);
            $this->assertTrue($aUser->save());
            $aUser       = User::getByUsername('aUser');
            $product     = ProductTestHelper::createProductByNameForOwner('productOwnedByaUser', $aUser);
            $id          = $product->id;
            $product->forget();
            unset($product);
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('aUser');
            $content = $this->runControllerWithNoExceptionsAndGetContent('products/default');
            $this->assertFalse(strpos($content, 'Fatal error: Method Product::__toString() must not throw an exception') > 0);
        }

         /**
         * @deletes selected products.
         */
        public function testRegularMassDeleteActionsForSelectedIds()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $confused = User::getByUsername('confused');
            $nobody = User::getByUsername('nobody');
            $this->assertEquals(Right::DENY, $confused->getEffectiveRight('ZurmoModule', ZurmoModule::RIGHT_BULK_DELETE));
            $confused->setRight('ZurmoModule', ZurmoModule::RIGHT_BULK_DELETE);
            //Load MassDelete view
            $products = Product::getAll();
            $this->assertEquals(9, count($products));
            $product1 = ProductTestHelper::createProductByNameForOwner('productDelete1', $confused);
            $product2 = ProductTestHelper::createProductByNameForOwner('productDelete2', $confused);
            $product3 = ProductTestHelper::createProductByNameForOwner('productDelete3', $nobody);
            $product4 = ProductTestHelper::createProductByNameForOwner('productDelete4', $confused);
            $product5 = ProductTestHelper::createProductByNameForOwner('productDelete5', $confused);
            $product6 = ProductTestHelper::createProductByNameForOwner('productDelete6', $nobody);
            $selectedIds = $product1->id . ',' . $product2->id . ',' . $product3->id ;    // Not Coding Standard
            $this->setGetArray(array('selectedIds' => $selectedIds, 'selectAll' => ''));  // Not Coding Standard
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('products/default/massDelete');
            $this->assertFalse(strpos($content, '<strong>3</strong>&#160;Products selected for removal') === false);
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massDeleteProgressPageSize');
            $this->assertEquals(5, $pageSize);
            //calculating products after adding 6 new records
            $products = Product::getAll();
            $this->assertEquals(15, count($products));
            //Deleting 6 opportunities for pagination scenario
            //Run Mass Delete using progress save for page1
            $selectedIds = $product1->id . ',' . $product2->id . ',' . // Not Coding Standard
                           $product3->id . ',' . $product4->id . ',' . // Not Coding Standard
                           $product5->id . ',' . $product6->id;        // Not Coding Standard
            $this->setGetArray(array(
                'selectedIds' => $selectedIds, // Not Coding Standard
                'selectAll' => '',
                'Product_page' => 1));
            $this->setPostArray(array('selectedRecordCount' => 6));
            $content = $this->runControllerWithExitExceptionAndGetContent('products/default/massDelete');
            $products = Product::getAll();
            $this->assertEquals(10, count($products));

            //Run Mass Delete using progress save for page2
            $selectedIds = $product1->id . ',' . $product2->id . ',' . // Not Coding Standard
                           $product3->id . ',' . $product4->id . ',' . // Not Coding Standard
                           $product5->id . ',' . $product6->id;        // Not Coding Standard
            $this->setGetArray(array(
                'selectedIds' => $selectedIds, // Not Coding Standard
                'selectAll' => '',
                'Product_page' => 2));
            $this->setPostArray(array('selectedRecordCount' => 6));
            $content = $this->runControllerWithNoExceptionsAndGetContent('products/default/massDeleteProgress');
            $products = Product::getAll();
            $this->assertEquals(9, count($products));
        }

         /**
         *Test Bug with mass delete and multiple pages when using select all
         */
        public function testRegularMassDeletePagesProperlyAndRemovesAllSelected()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $confused = User::getByUsername('confused');
            $nobody = User::getByUsername('nobody');

            //Load MassDelete view for the 6 products
            $products = Product::getAll();
            $this->assertEquals(9, count($products));

            //mass Delete pagination scenario
            //Run Mass Delete using progress save for page1
            $this->setGetArray(array(
                'selectAll' => '1',
                'Product_page' => 1));
            $this->setPostArray(array('selectedRecordCount' => 9));
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massDeleteProgressPageSize');
            $this->assertEquals(5, $pageSize);
            $content = $this->runControllerWithExitExceptionAndGetContent('products/default/massDelete');
            $products = Product::getAll();
            $this->assertEquals(4, count($products));

           //Run Mass Delete using progress save for page2
            $this->setGetArray(array(
                'selectAll' => '1',
                'Product_page' => 2));
            $this->setPostArray(array('selectedRecordCount' => 9));
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massDeleteProgressPageSize');
            $this->assertEquals(5, $pageSize);
            $content = $this->runControllerWithNoExceptionsAndGetContent('products/default/massDeleteProgress');

            $products = Product::getAll();
            $this->assertEquals(0, count($products));
        }
    }
?>