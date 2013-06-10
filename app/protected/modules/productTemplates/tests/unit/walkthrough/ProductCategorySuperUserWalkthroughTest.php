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
     * Product Template Super User Walkthrough.
     * Walkthrough for the super user of all possible actions.
     * Since this is a super user, he should have access to all controller actions
     * without any exceptions being thrown.
     */
    class ProductCategorySuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            ProductCategoryTestHelper::createProductCategoryByName("My Category 1");
            $category1 = ProductCategory::getByName("My Category 1");
            ProductCategoryTestHelper::createProductCategoryByName("My Category 2");
            ProductCategoryTestHelper::createProductCategoryByName("My Category 3", $category1[0]);
            ProductCategoryTestHelper::createProductCategoryByName("My Category 4");
            ProductCategoryTestHelper::createProductCategoryByName("My Category 5");
            ProductCategoryTestHelper::createProductCategoryByName("My Category 6");
            ProductCategoryTestHelper::createProductCategoryByName("My Category 7");
            ProductCategoryTestHelper::createProductCategoryByName("My Category 8");
            ProductCategoryTestHelper::createProductCategoryByName("My Category 9");
            ProductCategoryTestHelper::createProductCategoryByName("My Category 10");
            ProductCategoryTestHelper::createProductCategoryByName("My Category 11");
            ProductCategoryTestHelper::createProductCategoryByName("My Category 12");
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test all default controller actions that do not require any POST/GET variables to be passed.
            //This does not include portlet controller actions.
            $this->runControllerWithNoExceptionsAndGetContent('productTemplates/category');
            $this->runControllerWithNoExceptionsAndGetContent('productTemplates/category/index');
            $this->runControllerWithNoExceptionsAndGetContent('productTemplates/category/create');

            //Default Controller actions requiring some sort of parameter via POST or GET
            //Load Model Edit Views
            $productTemplates    = ProductCategory::getAll();
            $this->assertEquals(12, count($productTemplates));
            $superCategoryId     = self::getModelIdByModelNameAndName('ProductCategory', 'My Category 1');
            $superCategoryId2    = self::getModelIdByModelNameAndName('ProductCategory', 'My Category 2');
            $superCategoryId3    = self::getModelIdByModelNameAndName('ProductCategory', 'My Category 3');
            $superCategoryId4    = self::getModelIdByModelNameAndName('ProductCategory', 'My Category 4');
            $superCategoryId5    = self::getModelIdByModelNameAndName('ProductCategory', 'My Category 5');
            $superCategoryId6    = self::getModelIdByModelNameAndName('ProductCategory', 'My Category 6');
            $superCategoryId7    = self::getModelIdByModelNameAndName('ProductCategory', 'My Category 7');
            $superCategoryId8    = self::getModelIdByModelNameAndName('ProductCategory', 'My Category 8');
            $superCategoryId9    = self::getModelIdByModelNameAndName('ProductCategory', 'My Category 9');
            $superCategoryId10   = self::getModelIdByModelNameAndName('ProductCategory', 'My Category 10');
            $superCategoryId11   = self::getModelIdByModelNameAndName('ProductCategory', 'My Category 11');
            $superCategoryId12   = self::getModelIdByModelNameAndName('ProductCategory', 'My Category 12');
            $this->setGetArray(array('id' => $superCategoryId2));
            $this->runControllerWithNoExceptionsAndGetContent('productTemplates/category/edit');

            $superCategory2      = ProductCategory::getById($superCategoryId2);
            $this->setPostArray(array('ProductCategory' => array('productCategory' => array('id' => $superCategoryId))));
            $this->runControllerWithRedirectExceptionAndGetContent('productTemplates/category/edit');
            $superCategory2      = ProductCategory::getById($superCategoryId2);
            $superCategory       = ProductCategory::getById($superCategoryId);
            $this->assertEquals($superCategoryId, $superCategory2->productCategory->id);
            //Test having a failed validation on the contact during save.
            $this->setGetArray (array('id'              => $superCategoryId2));
            $this->setPostArray(array('ProductCategory' => array('name' => '')));
            $content = $this->runControllerWithNoExceptionsAndGetContent('productTemplates/category/edit');
            $this->assertFalse(strpos($content, 'Name cannot be blank') === false);

            //Load Model Detail Views
            $this->setGetArray(array('id' => $superCategoryId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('productTemplates/category/details');

            //actionModalList
            $this->setGetArray(array(
                'modalTransferInformation' => array('sourceIdFieldId' => 'x', 'sourceNameFieldId' => 'y', 'sourceModelId' => 66, 'modalId' => 10)
            ));
            $this->runControllerWithNoExceptionsAndGetContent('productTemplates/category/modalList');
        }

        public function testSuperUserCreateAction()
        {
            $super                                      = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            Yii::app()->user->userModel                 = $super;
            $this->resetGetArray();
            $superCategoryId                            = self::getModelIdByModelNameAndName('ProductCategory', 'My Category 1');
            $productCategory                            = array();
            $productCategory['name']                    = 'Red Widget';
            $productCategoryParent                      = array('id' => $superCategoryId);

            $productCategory['productCategory']         = $productCategoryParent;
            $this->setPostArray(array('ProductCategory' => $productCategory));
            $redirectUrl                                = $this->runControllerWithRedirectExceptionAndGetUrl('productTemplates/category/create');

            $productCategories                          = ProductCategory::getByName('Red Widget');
            $this->assertEquals(1, count($productCategories));
            $this->assertTrue  ($productCategories[0]->id > 0);
            $this->assertEquals($superCategoryId, $productCategories[0]->productCategory->id);
            $compareRedirectUrl                         = Yii::app()->createUrl('productTemplates/category/details', array('id' => $productCategories[0]->id));
            $this->assertEquals($compareRedirectUrl, $redirectUrl);
        }

        public function testSuperUserDeleteAction()
        {
            $super                      = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            Yii::app()->user->userModel = $super;

            $productCategory            = ProductCategoryTestHelper::createProductCategoryByName("My New Category");

            //Delete a product template
            $this->setGetArray(array('id' => $productCategory->id));
            $this->resetPostArray();
            $productCategories          = ProductCategory::getAll();
            $this->assertEquals(14, count($productCategories));
            $this->runControllerWithRedirectExceptionAndGetContent('productTemplates/category/delete');
            $productCategories          = ProductCategory::getAll();
            $this->assertEquals(13, count($productCategories));
            try
            {
                ProductCategory::getById($productCategory->id);
                $this->fail();
            }
            catch (NotFoundException $e)
            {
                //success
            }
        }
    }
?>