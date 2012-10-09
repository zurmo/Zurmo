<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
     * details.
     *
     * You should have received a copy of the GNU General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    /**
    * Test Account related API functions.
    */
    class ApiRestAccountTest extends ApiRestTest
    {
        public function testApiServerUrl()
        {
            if (!$this->isApiTestUrlConfigured())
            {
                $this->markTestSkipped(Yii::t('Default', 'API test url is not configured in perInstanceTest.php file.'));
            }
            $this->assertTrue(strlen($this->serverUrl) > 0);
        }

        /**
        * @depends testApiServerUrl
        */
        public function testGetAccount()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $account = AccountTestHelper::createAccountByNameTypeAndIndustryForOwner('First Account', 'Customer', 'Automotive', $super);
            $redBeanModelToApiDataUtil  = new RedBeanModelToApiDataUtil($account);
            $compareData  = $redBeanModelToApiDataUtil->getData();

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/read/' . $account->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($compareData, $response['data']);
        }

        /**
        * @depends testGetAccount
        */
        public function testDeleteAccount()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );
            $accounts = Account::getByName('First Account');
            $this->assertEquals(1, count($accounts));

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/delete/' . $accounts[0]->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/read/' . $accounts[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('The ID specified was invalid.', $response['message']);
        }

        /**
        * @depends testApiServerUrl
        */
        public function testCreateAccount()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            ReadPermissionsOptimizationUtil::rebuild();
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $industryValues = array(
                'Automotive',
                'Adult Entertainment',
                'Financial Services',
                'Mercenaries & Armaments',
            );
            $industryFieldData = CustomFieldData::getByName('Industries');
            $industryFieldData->serializedData = serialize($industryValues);
            $this->assertTrue($industryFieldData->save());

            $typeValues = array(
                'Prospect',
                'Customer',
                'Vendor',
            );
            $typeFieldData = CustomFieldData::getByName('AccountTypes');
            $typeFieldData->serializedData = serialize($typeValues);
            $this->assertTrue($typeFieldData->save());

            $primaryEmail['emailAddress']   = "a@example.com";
            $primaryEmail['optOut']         = 1;

            $secondaryEmail['emailAddress'] = "b@example.com";
            $secondaryEmail['optOut']       = 0;
            $secondaryEmail['isInvalid']    = 1;

            $billingAddress['street1']      = '129 Noodle Boulevard';
            $billingAddress['street2']      = 'Apartment 6000A';
            $billingAddress['city']         = 'Noodleville';
            $billingAddress['postalCode']   = '23453';
            $billingAddress['country']      = 'The Good Old US of A';

            $shippingAddress['street1']     = '25 de Agosto 2543';
            $shippingAddress['street2']     = 'Local 3';
            $shippingAddress['city']        = 'Ciudad de Los Fideos';
            $shippingAddress['postalCode']  = '5123-4';
            $shippingAddress['country']     = 'Latinoland';

            $account = new Account();
            $data['name']                = "My Company";
            $data['officePhone']         = "6438238";
            $data['officeFax']           = "6565465436";
            $data['employees']           = 100;
            $data['website']             = "http://www.google.com";
            $data['annualRevenue']       = "1000000";
            $data['description']         = "Some Description";

            $data['industry']['value']   = $industryValues[2];
            $data['type']['value']       = $typeValues[1];

            $data['primaryEmail']        = $primaryEmail;
            $data['secondaryEmail']      = $secondaryEmail;
            $data['billingAddress']      = $billingAddress;
            $data['shippingAddress']     = $shippingAddress;

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/create/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            $data['owner'] = array(
                'id' => $super->id,
                'username' => 'super'
            );
            $data['createdByUser']    = array(
                'id' => $super->id,
                'username' => 'super'
            );
            $data['modifiedByUser'] = array(
                'id' => $super->id,
                'username' => 'super'
            );
            unset($data['explicitReadWriteModelPermissions']);
            // We need to unset some empty values from response.
            unset($response['data']['createdDateTime']);
            unset($response['data']['modifiedDateTime']);
            unset($response['data']['primaryEmail']['id']);
            unset($response['data']['primaryEmail']['isInvalid']);
            unset($response['data']['secondaryEmail']['id']);
            unset($response['data']['billingAddress']['id']);
            unset($response['data']['billingAddress']['state']);
            unset($response['data']['billingAddress']['longitude']);
            unset($response['data']['billingAddress']['latitude']);
            unset($response['data']['billingAddress']['invalid']);

            unset($response['data']['shippingAddress']['id']);
            unset($response['data']['shippingAddress']['state']);
            unset($response['data']['shippingAddress']['longitude']);
            unset($response['data']['shippingAddress']['latitude']);
            unset($response['data']['shippingAddress']['invalid']);
            unset($response['data']['industry']['id']);
            unset($response['data']['type']['id']);
            unset($response['data']['id']);

            ksort($data);
            ksort($response['data']);
            $this->assertEquals($data, $response['data']);
        }

        /**
        * @depends testCreateAccount
        */
        public function testUpdateAccount()
        {
            RedBeanModel::forgetAll();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $accounts = Account::getByName('My Company');
            $this->assertEquals(1, count($accounts));
            $redBeanModelToApiDataUtil  = new RedBeanModelToApiDataUtil($accounts[0]);
            $compareData  = $redBeanModelToApiDataUtil->getData();

            $data['name']                = "My Company 2";
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/update/' . $compareData['id'], 'PUT', $headers, array('data' => $data));

            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            // We need to unset some empty values from response and dates.
            unset($response['data']['modifiedDateTime']);
            unset($compareData['modifiedDateTime']);
            $compareData['name'] = "My Company 2";
            ksort($compareData);
            ksort($response['data']);
            $this->assertEquals($compareData, $response['data']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/read/' . $compareData['id'], 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            unset($response['data']['modifiedDateTime']);
            ksort($response['data']);
            $this->assertEquals($compareData, $response['data']);
        }

        /**
        * @depends testUpdateAccount
        */
        public function testListAccounts()
        {
            RedBeanModel::forgetAll();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $accounts = Account::getByName('My Company 2');
            $this->assertEquals(1, count($accounts));
            $redBeanModelToApiDataUtil  = new RedBeanModelToApiDataUtil($accounts[0]);
            $compareData  = $redBeanModelToApiDataUtil->getData();

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/list/' , 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['items']));
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals(1, $response['data']['totalCount']);
            $this->assertEquals(array($compareData), $response['data']['items']);
        }

        /**
        * @depends testListAccounts
        */
        public function testUnprivilegedUserViewUpdateDeleteAcounts()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $notAllowedUser = UserTestHelper::createBasicUser('Steven');
            $notAllowedUser->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API);
            $this->assertTrue($notAllowedUser->save());

            // Test with unprivileged user to view, edit and delete account.
            $authenticationData = $this->login('steven', 'steven');
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );
            $everyoneGroup = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $this->assertTrue($everyoneGroup->save());

            $accounts = Account::getByName('My Company 2');
            $this->assertEquals(1, count($accounts));
            $data['name']                = "My Company 3";

            // Check first if user doesn't have rights.
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/read/' . $accounts[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have rights to perform this action.', $response['message']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/update/' . $accounts[0]->id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have rights to perform this action.', $response['message']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/delete/' . $accounts[0]->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have rights to perform this action.', $response['message']);

            //now check if user have rights, but no permissions.
            $notAllowedUser->setRight('AccountsModule', AccountsModule::getAccessRight());
            $notAllowedUser->setRight('AccountsModule', AccountsModule::getCreateRight());
            $notAllowedUser->setRight('AccountsModule', AccountsModule::getDeleteRight());
            $saved = $notAllowedUser->save();
            $this->assertTrue($saved);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/read/' . $accounts[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have permissions for this action.', $response['message']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/update/' . $accounts[0]->id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have permissions for this action.', $response['message']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/delete/' . $accounts[0]->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have permissions for this action.', $response['message']);

            // Test with privileged user
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            unset($data);
            $data['explicitReadWriteModelPermissions'] = array(
                'type' => ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP
            );
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/update/' . $accounts[0]->id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            $authenticationData = $this->login('steven', 'steven');
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/read/' . $accounts[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            unset($data);
            $data['name']                = "My Company 3";
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/update/' . $accounts[0]->id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals('My Company 3', $response['data']['name']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/delete/' . $accounts[0]->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have permissions for this action.', $response['message']);

            // Test with privileged user
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            //Test Delete
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/delete/' . $accounts[0]->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/read/' . $accounts[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
        }

        /**
        * @depends testUnprivilegedUserViewUpdateDeleteAcounts
        */
        public function testSearchAccounts()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );
            AccountTestHelper::createAccountByNameTypeAndIndustryForOwner('First Account', 'Customer', 'Automotive', $super);
            AccountTestHelper::createAccountByNameTypeAndIndustryForOwner('Second Account', 'Customer', 'Automotive', $super);
            AccountTestHelper::createAccountByNameTypeAndIndustryForOwner('Third Account', 'Customer', 'Financial Services', $super);
            AccountTestHelper::createAccountByNameTypeAndIndustryForOwner('Forth Account', 'Vendor', 'Financial Services', $super);
            AccountTestHelper::createAccountByNameTypeAndIndustryForOwner('Fifth Account', 'Vendor', 'Financial Services', $super);

            $searchParams = array(
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 3,
                ),
                'search' => array(
                    'name' => '',
                ),
                'sort' => 'name',
            );
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(3, count($response['data']['items']));
            $this->assertEquals(5, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('Fifth Account', $response['data']['items'][0]['name']);
            $this->assertEquals('First Account', $response['data']['items'][1]['name']);
            $this->assertEquals('Forth Account', $response['data']['items'][2]['name']);

            // Second page
            $searchParams['pagination']['page'] = 2;
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(2, count($response['data']['items']));
            $this->assertEquals(5, $response['data']['totalCount']);
            $this->assertEquals(2, $response['data']['currentPage']);
            $this->assertEquals('Second Account', $response['data']['items'][0]['name']);
            $this->assertEquals('Third Account', $response['data']['items'][1]['name']);

            // Search by name
            $searchParams['pagination']['page'] = 1;
            $searchParams['search']['name'] = 'First Account';
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['items']));
            $this->assertEquals(1, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('First Account', $response['data']['items'][0]['name']);

            // No results
            $searchParams['pagination']['page'] = 1;
            $searchParams['search']['name'] = 'First Account 2';
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(0, $response['data']['totalCount']);
            $this->assertFalse(isset($response['data']['items']));

            // Search by name desc.
            $searchParams = array(
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 3,
                ),
                'search' => array(
                    'name' => '',
                ),
                'sort' => 'name.desc',
            );
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(3, count($response['data']['items']));
            $this->assertEquals(5, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('Third Account', $response['data']['items'][0]['name']);
            $this->assertEquals('Second Account', $response['data']['items'][1]['name']);
            $this->assertEquals('Forth Account', $response['data']['items'][2]['name']);

            // Second page
            $searchParams['pagination']['page'] = 2;
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(2, count($response['data']['items']));
            $this->assertEquals(5, $response['data']['totalCount']);
            $this->assertEquals(2, $response['data']['currentPage']);
            $this->assertEquals('First Account', $response['data']['items'][0]['name']);
            $this->assertEquals('Fifth Account', $response['data']['items'][1]['name']);

            // Search by custom fields, order by name desc
            $searchParams = array(
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 3,
                ),
                'search' => array(
                    'industry' => array( 'value' => 'Financial Services'),
                    'type'     => array( 'value' => 'Vendor'),
                    'owner'   => array( 'id' => $super->id),
                ),
                'sort' => 'name.desc',
            );
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(2, $response['data']['totalCount']);
            $this->assertEquals(2, count($response['data']['items']));
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('Forth Account', $response['data']['items'][0]['name']);
            $this->assertEquals('Fifth Account', $response['data']['items'][1]['name']);
        }

        /**
        * @depends testSearchAccounts
        */
        public function testAdvancedSearchAccounts()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel        = $super;

            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $data = array(
                'dynamicSearch' => array(
                    'dynamicClauses' => array(
                        array(
                            'attributeIndexOrDerivedType' => 'owner',
                            'structurePosition' => 1,
                            'owner' => array(
                                'id' => Yii::app()->user->userModel->id,
                            ),
                        ),
                        array(
                            'attributeIndexOrDerivedType' => 'name',
                            'structurePosition' => 2,
                            'name' => 'Fi',
                        ),
                        array(
                            'attributeIndexOrDerivedType' => 'name',
                            'structurePosition' => 3,
                            'name' => 'Se',
                        ),
                    ),
                    'dynamicStructure' => '1 AND (2 OR 3)',
                ),
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 2,
                ),
                'sort' => 'name.asc',
           );

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/list/filter/', 'POST', $headers, array('data' => $data));

            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(2, count($response['data']['items']));
            $this->assertEquals(3, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('Fifth Account', $response['data']['items'][0]['name']);
            $this->assertEquals('First Account', $response['data']['items'][1]['name']);

            // Get second page
            $data['pagination']['page'] = 2;
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/list/filter/', 'POST', $headers, array('data' => $data));

            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['items']));
            $this->assertEquals(3, $response['data']['totalCount']);
            $this->assertEquals(2, $response['data']['currentPage']);
            $this->assertEquals('Second Account', $response['data']['items'][0]['name']);
        }

        /**
        * @depends testApiServerUrl
        */
        public function testCreateWithRelations()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $account2 = AccountTestHelper::createAccountByNameForOwner('Faber', $super);
            $contact = ContactTestHelper::createContactByNameForOwner('Simon', $super);

            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $data['name'] = 'Zurmo';
            $data['modelRelations'] = array(
                'accounts' => array(
                    array(
                        'action' => 'add',
                        'modelId' => $account2->id,
                        'modelClassName' => 'Account'
                    ),
                ),
                'contacts' => array(
                    array(
                        'action' => 'add',
                        'modelId' => $contact->id,
                        'modelClassName' => 'Contact'
                    ),
                ),
            );
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/create/', 'POST', $headers, array('data' => $data));

            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            RedBeanModel::forgetAll();
            $account = Account::getById($response['data']['id']);
            $this->assertEquals(1, count($account->accounts));
            $this->assertEquals($account2->id, $account->accounts[0]->id);
            $this->assertEquals(1, count($account->contacts));
            $this->assertEquals($contact->id, $account->contacts[0]->id);

            $account2 = Account::getById($account2->id);
            $this->assertEquals($account->id, $account2->account->id);

            $contact = Contact::getById($contact->id);
            $this->assertEquals($account->id, $contact->account->id);
        }

        /**
        * @depends testCreateWithRelations
        */
        public function testUpdateWithRelations()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $account  = AccountTestHelper::createAccountByNameForOwner('Factor X', $super);
            $account1 = AccountTestHelper::createAccountByNameForOwner('Miko', $super);
            $account2 = AccountTestHelper::createAccountByNameForOwner('Troter', $super);
            $contact  = ContactTestHelper::createContactByNameForOwner('Simon', $super);

            $redBeanModelToApiDataUtil  = new RedBeanModelToApiDataUtil($account);
            $compareData  = $redBeanModelToApiDataUtil->getData();
            $account->forget();

            $data['modelRelations'] = array(
                'accounts' => array(
                    array(
                        'action' => 'add',
                        'modelId' => $account1->id,
                        'modelClassName' => 'Account'
                    ),
                    array(
                        'action' => 'add',
                        'modelId' => $account2->id,
                        'modelClassName' => 'Account'
                    ),
                ),
                'contacts' => array(
                    array(
                        'action' => 'add',
                        'modelId' => $contact->id,
                        'modelClassName' => 'Contact'
                    ),
                ),
            );
            $data['name'] = 'Zurmo Inc.';

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/update/' . $compareData['id'], 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            unset($response['data']['modifiedDateTime']);
            unset($compareData['modifiedDateTime']);
            $compareData['name'] = 'Zurmo Inc.';
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($compareData, $response['data']);

            RedBeanModel::forgetAll();
            $account = Account::getById($compareData['id']);
            $this->assertEquals(2, count($account->accounts));
            $this->assertEquals($account1->id, $account->accounts[0]->id);
            $this->assertEquals($account2->id, $account->accounts[1]->id);
            $this->assertEquals(1, count($account->contacts));
            $this->assertEquals($contact->id, $account->contacts[0]->id);

            $account1 = Account::getById($account1->id);
            $this->assertEquals($account->id, $account1->account->id);
            $account2 = Account::getById($account2->id);
            $this->assertEquals($account->id, $account2->account->id);
            $contact = Contact::getById($contact->id);
            $this->assertEquals($account->id, $contact->account->id);

            // Now test remove relations
            $data['modelRelations'] = array(
                'accounts' => array(
                    array(
                        'action' => 'remove',
                        'modelId' => $account1->id,
                        'modelClassName' => 'Account'
                    ),
                    array(
                        'action' => 'remove',
                        'modelId' => $account2->id,
                        'modelClassName' => 'Account'
                    ),
                ),
                'contacts' => array(
                    array(
                        'action' => 'remove',
                        'modelId' => $contact->id,
                        'modelClassName' => 'Contact'
                    ),
                ),
            );

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/update/' . $compareData['id'], 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            RedBeanModel::forgetAll();
            $updatedModel = Account::getById($compareData['id']);
            $this->assertEquals(0, count($updatedModel->accounts));
            $this->assertEquals(0, count($updatedModel->contacts));

            $account1 = Account::getById($account1->id);
            $this->assertLessThanOrEqual(0, $account1->account->id);
            $account2 = Account::getById($account2->id);
            $this->assertLessThanOrEqual(0, $account2->account->id);
            $contact = Contact::getById($contact->id);
            $this->assertLessThanOrEqual(0, $contact->account->id);
        }

        /**
        * @depends testApiServerUrl
        */
        public function testEditAccountWithIncompleteData()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            AccountTestHelper::createAccountByNameTypeAndIndustryForOwner('New Account', 'Customer', 'Automotive', $super);

            // Provide data without required field
            $data['officePhone']         = "6438238";
            $data['officeFax']           = "6565465436";

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/create/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);

            $accounts = Account::getByName('New Account');
            $this->assertEquals(1, count($accounts));
            $id = $accounts[0]->id;
            $data = array();
            $data['name']                = '';
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/update/' . $id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals(1, count($response['errors']));
        }

        /**
        * @depends testApiServerUrl
        */
        public function testEditAccountWIthIncorrectDataType()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            AccountTestHelper::createAccountByNameTypeAndIndustryForOwner('Newest Account', 'Customer', 'Automotive', $super);

            // Provide data with wrong type.
            $data['name']         = "AAA";
            $data['employees']           = "SS";

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/create/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals(1, count($response['errors']));

            $accounts = Account::getByName('Newest Account');
            $this->assertEquals(1, count($accounts));
            $id = $accounts[0]->id;
            $data = array();
            $data['employees']                = 'DD';
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/update/' . $id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals(1, count($response['errors']));
        }

        /**
        * @depends testApiServerUrl
        */
        public function testNotAllowedGuestAction()
        {
            $authenticationData = $this->login('st', 'st');
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/account/api/read/1', 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('Sign in required.', $response['message']);
        }
    }
?>