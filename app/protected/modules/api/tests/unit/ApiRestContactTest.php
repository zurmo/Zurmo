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
    * Test Contact related API functions.
    */
    class ApiRestContactTest extends ApiRestTest
    {
        public function testApiServerUrl()
        {
            if (!$this->isApiTestUrlConfigured())
            {
                $this->markTestSkipped(Zurmo::t('ApiModule', 'API test url is not configured in perInstanceTest.php file.'));
            }
            $this->assertTrue(strlen($this->serverUrl) > 0);
        }

        /**
        * @depends testApiServerUrl
        */
        public function testGetContact()
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
            $this->assertTrue(ContactsModule::loadStartingData());
            $contact = ContactTestHelper::createContactByNameForOwner('First', $super);

            $redBeanModelToApiDataUtil  = new RedBeanModelToApiDataUtil($contact);
            $compareData  = $redBeanModelToApiDataUtil->getData();

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/read/' . $contact->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($compareData, $response['data']);
        }

        /**
         * @depends testGetContact
         */
        public function testDeleteContact()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $contacts = Contact::getByName('First Firstson');
            $this->assertEquals(1, count($contacts));

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/delete/' . $contacts[0]->id, 'DELETE', $headers);

            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/read/' . $contacts[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('The ID specified was invalid.', $response['message']);
        }

        /**
        * @depends testApiServerUrl
        */
        public function testCreateContact()
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

            $industryValues = array(
                'Automotive',
                'Adult Entertainment',
                'Financial Services',
                'Mercenaries & Armaments',
            );
            $industryFieldData = CustomFieldData::getByName('Industries');
            $industryFieldData->serializedData = serialize($industryValues);
            $this->assertTrue($industryFieldData->save());

            $sourceValues = array(
                'Word of Mouth',
                'Outbound',
                'Trade Show',
            );
            $sourceFieldData = CustomFieldData::getByName('LeadSources');
            $sourceFieldData->serializedData = serialize($sourceValues);
            $this->assertTrue($sourceFieldData->save());

            $titles = array('Mr.', 'Mrs.', 'Ms.', 'Dr.', 'Swami');
            $customFieldData = CustomFieldData::getByName('Titles');
            $customFieldData->serializedData = serialize($titles);
            $this->assertTrue($customFieldData->save());

            $this->assertEquals(6, count(ContactState::GetAll()));
            $contactStates = ContactState::GetAll();
            $primaryEmail['emailAddress']   = "a@example.com";
            $primaryEmail['optOut']         = 1;

            $secondaryEmail['emailAddress'] = "b@example.com";
            $secondaryEmail['optOut']       = 0;
            $secondaryEmail['isInvalid']    = 1;

            $primaryAddress['street1']      = '129 Noodle Boulevard';
            $primaryAddress['street2']      = 'Apartment 6000A';
            $primaryAddress['city']         = 'Noodleville';
            $primaryAddress['postalCode']   = '23453';
            $primaryAddress['country']      = 'The Good Old US of A';

            $secondaryAddress['street1']    = '25 de Agosto 2543';
            $secondaryAddress['street2']    = 'Local 3';
            $secondaryAddress['city']       = 'Ciudad de Los Fideos';
            $secondaryAddress['postalCode'] = '5123-4';
            $secondaryAddress['country']    = 'Latinoland';

            $account        = new Account();
            $account->name  = 'Some Account';
            $account->owner = $super;
            $this->assertTrue($account->save());

            $data['firstName']           = "Michael";
            $data['lastName']            = "Smith";
            $data['jobTitle']            = "President";
            $data['department']          = "Sales";
            $data['officePhone']         = "653-235-7824";
            $data['mobilePhone']         = "653-235-7821";
            $data['officeFax']           = "653-235-7834";
            $data['description']         = "Some desc.";
            $data['companyName']         = "Michael Co";
            $data['website']             = "http://sample.com";

            $data['industry']['value']   = $industryValues[2];
            $data['source']['value']     = $sourceValues[1];
            $data['title']['value']      = $titles[3];
            $data['state']['id']         = ContactsUtil::getStartingState()->id;
            $data['account']['id']       = $account->id;

            $data['primaryEmail']        = $primaryEmail;
            $data['secondaryEmail']      = $secondaryEmail;
            $data['primaryAddress']      = $primaryAddress;
            $data['secondaryAddress']    = $secondaryAddress;

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/create/', 'POST', $headers, array('data' => $data));
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

            // We need to unset some empty values from response.
            unset($response['data']['createdDateTime']);
            unset($response['data']['modifiedDateTime']);
            unset($response['data']['primaryEmail']['id'] );
            unset($response['data']['primaryEmail']['isInvalid']);
            unset($response['data']['secondaryEmail']['id']);
            unset($response['data']['primaryAddress']['id']);
            unset($response['data']['primaryAddress']['state']);
            unset($response['data']['primaryAddress']['longitude']);
            unset($response['data']['primaryAddress']['latitude']);
            unset($response['data']['primaryAddress']['invalid']);

            unset($response['data']['secondaryAddress']['id']);
            unset($response['data']['secondaryAddress']['state']);
            unset($response['data']['secondaryAddress']['longitude']);
            unset($response['data']['secondaryAddress']['latitude']);
            unset($response['data']['secondaryAddress']['invalid']);
            unset($response['data']['industry']['id']);
            unset($response['data']['source']['id']);
            unset($response['data']['title']['id']);
            unset($response['data']['id']);

            ksort($data);
            ksort($response['data']);
            $this->assertEquals($data, $response['data']);
        }

        /**
         * @depends testCreateContact
         */
        public function testUpdateContact()
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

            $contacts = Contact::getByName('Michael Smith');
            $this->assertEquals(1, count($contacts));
            $redBeanModelToApiDataUtil  = new RedBeanModelToApiDataUtil($contacts[0]);
            $compareData  = $redBeanModelToApiDataUtil->getData();

            $data['department']                = "Support";
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/update/' . $compareData['id'], 'PUT', $headers, array('data' => $data));

            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            // We need to unset some empty values from response and dates.
            unset($response['data']['modifiedDateTime']);
            unset($compareData['modifiedDateTime']);
            $compareData['department'] = "Support";
            ksort($compareData);
            ksort($response['data']);
            $this->assertEquals($compareData, $response['data']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/read/' . $compareData['id'], 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            unset($response['data']['modifiedDateTime']);
            ksort($response['data']);
            $this->assertEquals($compareData, $response['data']);
        }

        /**
         * @depends testUpdateContact
         */
        public function testListContacts()
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

            $contacts = Contact::getByName('Michael Smith');
            $this->assertEquals(1, count($contacts));
            $redBeanModelToApiDataUtil  = new RedBeanModelToApiDataUtil($contacts[0]);
            $compareData  = $redBeanModelToApiDataUtil->getData();

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/list/' , 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['items']));
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals(1, $response['data']['totalCount']);
            $this->assertEquals(array($compareData), $response['data']['items']);
        }

        /**
        * @depends testListContacts
        */
        public function testUnprivilegedUserViewUpdateDeleteContacts()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $notAllowedUser = UserTestHelper::createBasicUser('Steven');
            $notAllowedUser->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API);
            $saved = $notAllowedUser->save();

            $authenticationData = $this->login('steven', 'steven');
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $everyoneGroup = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $this->assertTrue($everyoneGroup->save());

            $contacts = Contact::getByName('Michael Smith');
            $this->assertEquals(1, count($contacts));
            $data['department']                = "Support";
            // Test with unprivileged user to view, edit and delete account.
            $authenticationData = $this->login('steven', 'steven');
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/read/' . $contacts[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have rights to perform this action.', $response['message']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/update/' . $contacts[0]->id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have rights to perform this action.', $response['message']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/delete/' . $contacts[0]->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have rights to perform this action.', $response['message']);

            //now check if user have rights, but no permissions.
            $notAllowedUser->setRight('ContactsModule', ContactsModule::getAccessRight());
            $notAllowedUser->setRight('ContactsModule', ContactsModule::getCreateRight());
            $notAllowedUser->setRight('ContactsModule', ContactsModule::getDeleteRight());
            $saved = $notAllowedUser->save();
            $this->assertTrue($saved);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/read/' . $contacts[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have permissions for this action.', $response['message']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/update/' . $contacts[0]->id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have permissions for this action.', $response['message']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/delete/' . $contacts[0]->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have permissions for this action.', $response['message']);

            // Allow everyone group to read/write contact
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
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/update/' . $contacts[0]->id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            $authenticationData = $this->login('steven', 'steven');
            $headers = array(
                            'Accept: application/json',
                            'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                            'ZURMO_TOKEN: ' . $authenticationData['token'],
                            'ZURMO_API_REQUEST_TYPE: REST',
            );
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/read/' . $contacts[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            unset($data);
            $data['department']                = "Support";
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/update/' . $contacts[0]->id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals('Support', $response['data']['department']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/delete/' . $contacts[0]->id, 'DELETE', $headers);
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
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/delete/' . $contacts[0]->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/read/' . $contacts[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
        }

        /**
        * @depends testUnprivilegedUserViewUpdateDeleteContacts
        */
        public function testSearchContacts()
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
            //Setup test data owned by the super user.
            $account  = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
            $account2 = AccountTestHelper::createAccountByNameForOwner('superAccount2', $super);

            ContactTestHelper::createContactWithAccountByNameForOwner('First Contact', $super, $account);
            ContactTestHelper::createContactWithAccountByNameForOwner('Second Contact', $super, $account);
            ContactTestHelper::createContactWithAccountByNameForOwner('Third Contact', $super, $account);
            ContactTestHelper::createContactWithAccountByNameForOwner('Forth Contact', $super, $account2);
            ContactTestHelper::createContactWithAccountByNameForOwner('Fifth Contact', $super, $account2);

            $searchParams = array(
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 3,
                ),
                'search' => array(
                    'firstName' => '',
                ),
                'sort' => 'firstName',
            );
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(3, count($response['data']['items']));
            $this->assertEquals(5, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('Fifth Contact', $response['data']['items'][0]['firstName']);
            $this->assertEquals('First Contact', $response['data']['items'][1]['firstName']);
            $this->assertEquals('Forth Contact', $response['data']['items'][2]['firstName']);

            // Second page
            $searchParams['pagination']['page'] = 2;
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(2, count($response['data']['items']));
            $this->assertEquals(5, $response['data']['totalCount']);
            $this->assertEquals(2, $response['data']['currentPage']);
            $this->assertEquals('Second Contact', $response['data']['items'][0]['firstName']);
            $this->assertEquals('Third Contact', $response['data']['items'][1]['firstName']);

            // Search by name
            $searchParams['pagination']['page'] = 1;
            $searchParams['search']['firstName'] = 'First Contact';
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['items']));
            $this->assertEquals(1, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('First Contact', $response['data']['items'][0]['firstName']);

            // No results
            $searchParams['pagination']['page'] = 1;
            $searchParams['search']['firstName'] = 'First Contact 2';
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/list/filter/' . $searchParamsQuery, 'GET', $headers);
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
                    'firstName' => '',
                ),
                'sort' => 'firstName.desc',
            );
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(3, count($response['data']['items']));
            $this->assertEquals(5, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('Third Contact', $response['data']['items'][0]['firstName']);
            $this->assertEquals('Second Contact', $response['data']['items'][1]['firstName']);
            $this->assertEquals('Forth Contact', $response['data']['items'][2]['firstName']);

            // Second page
            $searchParams['pagination']['page'] = 2;
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(2, count($response['data']['items']));
            $this->assertEquals(5, $response['data']['totalCount']);
            $this->assertEquals(2, $response['data']['currentPage']);
            $this->assertEquals('First Contact', $response['data']['items'][0]['firstName']);
            $this->assertEquals('Fifth Contact', $response['data']['items'][1]['firstName']);

            // Search by custom fields, order by name desc
            $searchParams = array(
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 3,
                ),
                'search' => array(
                    'account' => array( 'id' => $account2->id),
                    'owner'   => array( 'id' => $super->id),
                ),
                'sort' => 'firstName.desc',
            );
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(2, $response['data']['totalCount']);
            $this->assertEquals(2, count($response['data']['items']));
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('Forth Contact', $response['data']['items'][0]['firstName']);
            $this->assertEquals('Fifth Contact', $response['data']['items'][1]['firstName']);
        }

        /**
        * @depends testSearchContacts
        */
        public function testAdvancedSearchContacts()
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
                            'firstName' => 'Fi',
                        ),
                        array(
                            'attributeIndexOrDerivedType' => 'name',
                            'structurePosition' => 3,
                            'firstName' => 'Se',
                        ),
                    ),
                    'dynamicStructure' => '1 AND (2 OR 3)',
                ),
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 2,
                ),
                'sort' => 'firstName.asc',
           );

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/list/filter/', 'POST', $headers, array('data' => $data));

            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(2, count($response['data']['items']));
            $this->assertEquals(3, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('Fifth Contact', $response['data']['items'][0]['firstName']);
            $this->assertEquals('First Contact', $response['data']['items'][1]['firstName']);

            // Get second page
            $data['pagination']['page'] = 2;
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/list/filter/', 'POST', $headers, array('data' => $data));

            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['items']));
            $this->assertEquals(3, $response['data']['totalCount']);
            $this->assertEquals(2, $response['data']['currentPage']);
            $this->assertEquals('Second Contact', $response['data']['items'][0]['firstName']);
        }

        /**
        * Test get contacts that are releted with particular opportunity(MANY_MANY relationship)
        * @depends testAdvancedSearchContacts
        */
        public function testGetContactsThatAreRelatedWithOpportunity()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $firstOpportunity = OpportunityTestHelper::createOpportunityByNameForOwner('First Opportunity', $super);
            $secondOpportunity = OpportunityTestHelper::createOpportunityByNameForOwner('Second Opportunity', $super);

            $contacts = Contact::getByName('First Contact First Contactson');
            $firstContact = $contacts[0];

            $contacts = Contact::getByName('Second Contact Second Contactson');
            $secondContact = $contacts[0];

            $contacts = Contact::getByName('Third Contact Third Contactson');
            $thirdContact = $contacts[0];

            $contacts = Contact::getByName('Forth Contact Forth Contactson');
            $forthContact = $contacts[0];

            $firstOpportunity->contacts->add($firstContact);
            $firstOpportunity->contacts->add($secondContact);
            $firstOpportunity->contacts->add($thirdContact);
            $firstOpportunity->save();

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
                            'attributeIndexOrDerivedType' => 'opportunities'. DynamicSearchUtil::RELATION_DELIMITER .'id',
                            'structurePosition' => 1,
                            'opportunities' => array(
                                'id' => $firstOpportunity->id
                            )
                        ),
                    ),
                    'dynamicStructure' => '1',
                ),
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 2,
                ),
                'sort' => 'firstName.desc',
           );

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/list/filter/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);

            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(2, count($response['data']['items']));
            $this->assertEquals(3, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('Third Contact', $response['data']['items'][0]['firstName']);
            $this->assertEquals('Second Contact', $response['data']['items'][1]['firstName']);

            // Get second page
            $data['pagination']['page'] = 2;
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/list/filter/', 'POST', $headers, array('data' => $data));

            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['items']));
            $this->assertEquals(3, $response['data']['totalCount']);
            $this->assertEquals(2, $response['data']['currentPage']);
            $this->assertEquals('First Contact', $response['data']['items'][0]['firstName']);
        }

        /**
        * @depends testApiServerUrl
        */
        public function testCreateWithRelations()
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

            $opportunity = OpportunityTestHelper::createOpportunityByNameForOwner('My Opportunity', $super);

            $data['firstName']           = "Freddy";
            $data['lastName']            = "Smith";
            $data['state']['id']         = ContactsUtil::getStartingState()->id;

            $data['modelRelations'] = array(
                'opportunities' => array(
                    array(
                        'action' => 'add',
                        'modelId' => $opportunity->id,
                        'modelClassName' => 'Opportunity'
                    ),
                ),
            );
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/create/', 'POST', $headers, array('data' => $data));

            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($data['firstName'], $response['data']['firstName']);
            $this->assertEquals($data['lastName'], $response['data']['lastName']);

            RedBeanModel::forgetAll();
            $contact = Contact::getById($response['data']['id']);
            $this->assertEquals(1, count($contact->opportunities));
            $this->assertEquals($opportunity->id, $contact->opportunities[0]->id);

            $opportunity = Opportunity::getById($opportunity->id);
            $this->assertEquals(1, count($opportunity->contacts));
            $this->assertEquals($contact->id, $opportunity->contacts[0]->id);
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

            $contact  = ContactTestHelper::createContactByNameForOwner('Simon', $super);
            $opportunity = OpportunityTestHelper::createOpportunityByNameForOwner('My Opportunity', $super);

            $redBeanModelToApiDataUtil  = new RedBeanModelToApiDataUtil($contact);
            $compareData  = $redBeanModelToApiDataUtil->getData();
            $contact->forget();

            $data['modelRelations'] = array(
                'opportunities' => array(
                    array(
                        'action' => 'add',
                        'modelId' => $opportunity->id,
                        'modelClassName' => 'Opportunity'
                    ),
                ),
            );
            $data['firstName'] = 'Fred';

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/update/' . $compareData['id'], 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            unset($response['data']['modifiedDateTime']);
            unset($compareData['modifiedDateTime']);
            $compareData['firstName'] = 'Fred';
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($compareData, $response['data']);

            RedBeanModel::forgetAll();
            $contact = Contact::getById($compareData['id']);
            $this->assertEquals(1, count($contact->opportunities));
            $this->assertEquals($opportunity->id, $contact->opportunities[0]->id);

            $opportunity = Opportunity::getById($opportunity->id);
            $this->assertEquals(1, count($opportunity->contacts));
            $this->assertEquals($contact->id, $opportunity->contacts[0]->id);

            // Now test remove relations
            $data['modelRelations'] = array(
                'opportunities' => array(
                    array(
                        'action' => 'remove',
                        'modelId' => $opportunity->id,
                        'modelClassName' => 'Opportunity'
                    ),
                ),
            );

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/update/' . $compareData['id'], 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            RedBeanModel::forgetAll();
            $contact = Contact::getById($compareData['id']);
            $this->assertEquals(0, count($contact->opportunities));
            $opportunity = Opportunity::getById($opportunity->id);
            $this->assertEquals(0, count($opportunity->contacts));
        }

        /**
        * @depends testApiServerUrl
        */
        public function testEditContactWithIncompleteData()
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

            $contact = ContactTestHelper::createContactByNameForOwner('New Contact', $super);

            // Provide data without required fields.
            $data['companyName']         = "Test 123";

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/create/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals(2, count($response['errors']));

            $id = $contact->id;
            $data = array();
            $data['lastName']                = '';
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/update/' . $id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals(1, count($response['errors']));
        }

        /**
        * @depends testApiServerUrl
        */
        public function testEditContactWIthIncorrectDataType()
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

            $contact = ContactTestHelper::createContactByNameForOwner('Newest Contact', $super);

            // Provide data with wrong type.
            $data['companyName']         = "A";

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/create/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals(3, count($response['errors']));

            $id = $contact->id;
            $data = array();
            $data['companyName']         = "A";
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/contacts/contact/api/update/' . $id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals(1, count($response['errors']));
        }
    }
?>