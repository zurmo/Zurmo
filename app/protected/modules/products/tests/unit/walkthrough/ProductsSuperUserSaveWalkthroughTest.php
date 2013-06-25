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

    class ProductsSuperUserSaveWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Setup test data owned by the super user.
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
            $contact = ContactTestHelper::createContactWithAccountByNameForOwner('superContact', $super, $account);
            ProductTestHelper::createProductByNameForOwner("My Product 1", $super);
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Default Controller actions requiring some sort of parameter via POST or GET
            //Load Model Edit Views
            $contacts           = Contact::getByName('superContact superContactson');
            $products           = Product::getAll();
            $this->assertEquals(1, count($contacts));
            $this->assertEquals(1, count($products));
            $superProductId     = self::getModelIdByModelNameAndName('Product', 'My Product 1');
            $this->setGetArray(array('id' => $superProductId));
            $this->runControllerWithNoExceptionsAndGetContent('products/default/edit');
            //Save product.
            $superProduct       = Product::getById($superProductId);
            $this->setPostArray(
                                array(
                                        'Product' => array(
                                                            'contact' => array('id' => $contacts[0]->id),
                                                            'opportunity' => array('id' => ''),
                                                            'owner' => array('id' => $super->id),
                                                            'explicitReadWriteModelPermissions' => array(
                                                                    'type' => ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP,
                                                                    'nonEveryoneGroup' => ''
                                                                )
                                                          )
                                      )
                                );

            //Test having a failed validation on the product during save.
            $this->setGetArray (array('id'      => $superProductId));
            $content = $this->runControllerWithRedirectExceptionAndGetContent('products/default/edit');
        }
    }
?>