<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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
    * Test Meeting related API functions.
    */
    class ApiRestAAMeetingTest extends ApiRestTest
    {
        public function testApiServerUrl()
        {
            $this->assertTrue(strlen($this->serverUrl) > 0);
        }

        /**
        * @depends testApiServerUrl
        */
        public function testGetMeeting()
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
            $meeting = MeetingTestHelper::createMeetingByNameForOwner('First Meeting', $super);

            $redBeanModelToApiDataUtil  = new RedBeanModelToApiDataUtil($meeting);
            $compareData  = $redBeanModelToApiDataUtil->getData();

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/meetings/api/read/' . $meeting->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($compareData, $response['data']);
        }

        /**
         * @depends testGetMeeting
         */
        public function testDeleteMeeting()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $meetings = Meeting::getByName('First Meeting');
            $this->assertEquals(1, count($meetings));

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/meetings/api/delete/' . $meetings[0]->id, 'DELETE', $headers);

            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/meetings/api/read/' . $meetings[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('The ID specified was invalid.', $response['message']);
        }

        public function testCreateMeeting()
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

            $categories = array(
                'Meeting',
                'Call',
            );
            $categoryFieldData = CustomFieldData::getByName('MeetingCategories');
            $categoryFieldData->serializedData = serialize($categories);
            $this->assertTrue($categoryFieldData->save());

            $startStamp             = DateTimeUtil::convertTimestampToDbFormatDateTime(time()  + 10000);
            $endStamp               = DateTimeUtil::convertTimestampToDbFormatDateTime(time() + 11000);

            $data['name']           = "Michael Meeting";
            $data['startDateTime']  = $startStamp;
            $data['endDateTime']    = $endStamp;
            $data['location']       = "Office";
            $data['description']    = "Description";

            $data['category']['value'] = $categories[1];

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/meetings/api/create/', 'POST', $headers, array('data' => $data));
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
            unset($response['data']['category']['id']);
            unset($response['data']['id']);
            $data['latestDateTime'] = $startStamp;

            ksort($data);
            ksort($response['data']);
            $this->assertEquals($data, $response['data']);
        }

        /**
         * @depends testCreateMeeting
         */
        public function testUpdateMeeting()
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

            $meetings = Meeting::getByName('Michael Meeting');
            $this->assertEquals(1, count($meetings));
            $redBeanModelToApiDataUtil  = new RedBeanModelToApiDataUtil($meetings[0]);
            $compareData  = $redBeanModelToApiDataUtil->getData();
            $meetings[0]->forget();

            $data['description']    = "Some new description";
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/meetings/api/update/' . $compareData['id'], 'PUT', $headers, array('data' => $data));

            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            // We need to unset some empty values from response and dates.
            unset($response['data']['modifiedDateTime']);
            unset($compareData['modifiedDateTime']);
            $compareData['description'] = "Some new description";
            ksort($compareData);
            ksort($response['data']);
            $this->assertEquals($compareData, $response['data']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/meetings/api/read/' . $meetings[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            unset($response['data']['modifiedDateTime']);
            ksort($response['data']);
            $this->assertEquals($compareData, $response['data']);
        }

        /**
         * @depends testUpdateMeeting
         */
        public function testListMeetings()
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

            $meetings = Meeting::getByName('Michael Meeting');
            $this->assertEquals(1, count($meetings));
            $redBeanModelToApiDataUtil  = new RedBeanModelToApiDataUtil($meetings[0]);
            $compareData  = $redBeanModelToApiDataUtil->getData();

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/meetings/api/list/' , 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['items']));
            $this->assertEquals(1, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals(array($compareData), $response['data']['items']);
        }

        /**
         * @depends testListMeetings
         */
        public function testUnprivilegedUserViewUpdateDeleteMeetings()
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

            $meetings = Meeting::getByName('Michael Meeting');
            $this->assertEquals(1, count($meetings));
            $data['description']    = "Some new description 2";

            // Test with unprivileged user to view, edit and delete account.
            $authenticationData = $this->login('steven', 'steven');
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/meetings/api/read/' . $meetings[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have rights to perform this action.', $response['message']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/meetings/api/update/' . $meetings[0]->id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have rights to perform this action.', $response['message']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/meetings/api/delete/' . $meetings[0]->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have rights to perform this action.', $response['message']);

            //now check if user have rights, but no permissions.
            $notAllowedUser->setRight('MeetingsModule', MeetingsModule::getAccessRight());
            $notAllowedUser->setRight('MeetingsModule', MeetingsModule::getCreateRight());
            $notAllowedUser->setRight('MeetingsModule', MeetingsModule::getDeleteRight());
            $saved = $notAllowedUser->save();
            $this->assertTrue($saved);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/meetings/api/read/' . $meetings[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have permissions for this action.', $response['message']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/meetings/api/update/' . $meetings[0]->id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have permissions for this action.', $response['message']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/meetings/api/delete/' . $meetings[0]->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have permissions for this action.', $response['message']);

            // Allow everyone group to read/write meeting
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
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/meetings/api/update/' . $meetings[0]->id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            $authenticationData = $this->login('steven', 'steven');
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/meetings/api/read/' . $meetings[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            unset($data);
            $data['description']    = "Some new description 3";
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/meetings/api/update/' . $meetings[0]->id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals("Some new description 3", $response['data']['description']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/meetings/api/delete/' . $meetings[0]->id, 'DELETE', $headers);
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
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/meetings/api/delete/' . $meetings[0]->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/meetings/api/read/' . $meetings[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
        }

        /**
        * @depends testUnprivilegedUserViewUpdateDeleteMeetings
        */
        public function testSearchMeetings()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $anotherUser = User::getByUsername('steven');

            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );
            $firstAccount = AccountTestHelper::createAccountByNameTypeAndIndustryForOwner('First Account', 'Customer', 'Automotive', $super);
            $secondAccount = AccountTestHelper::createAccountByNameTypeAndIndustryForOwner('Second Account', 'Customer', 'Automotive', $super);

            MeetingTestHelper::createMeetingWithOwnerAndRelatedAccount('First Meeting', $super, $firstAccount);
            MeetingTestHelper::createMeetingWithOwnerAndRelatedAccount('Second Meeting', $super, $firstAccount);
            MeetingTestHelper::createMeetingWithOwnerAndRelatedAccount('Third Meeting', $super, $secondAccount);
            MeetingTestHelper::createMeetingWithOwnerAndRelatedAccount('Forth Meeting', $anotherUser, $secondAccount);
            MeetingTestHelper::createMeetingWithOwnerAndRelatedAccount('Fifth Meeting', $super, $firstAccount);

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
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/meetings/api/list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(3, count($response['data']['items']));
            $this->assertEquals(5, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('Fifth Meeting', $response['data']['items'][0]['name']);
            $this->assertEquals('First Meeting', $response['data']['items'][1]['name']);
            $this->assertEquals('Forth Meeting', $response['data']['items'][2]['name']);

            // Second page
            $searchParams['pagination']['page'] = 2;
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/meetings/api/list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(2, count($response['data']['items']));
            $this->assertEquals(5, $response['data']['totalCount']);
            $this->assertEquals(2, $response['data']['currentPage']);
            $this->assertEquals('Second Meeting', $response['data']['items'][0]['name']);
            $this->assertEquals('Third Meeting', $response['data']['items'][1]['name']);

            // Search by name
            $searchParams['pagination']['page'] = 1;
            $searchParams['search']['name'] = 'First Meeting';
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/meetings/api/list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['items']));
            $this->assertEquals(1, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('First Meeting', $response['data']['items'][0]['name']);

            // No results
            $searchParams['pagination']['page'] = 1;
            $searchParams['search']['name'] = 'First Meeting 2';
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/meetings/api/list/filter/' . $searchParamsQuery, 'GET', $headers);
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
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/meetings/api/list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(3, count($response['data']['items']));
            $this->assertEquals(5, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('Third Meeting', $response['data']['items'][0]['name']);
            $this->assertEquals('Second Meeting', $response['data']['items'][1]['name']);
            $this->assertEquals('Forth Meeting', $response['data']['items'][2]['name']);

            // Second page
            $searchParams['pagination']['page'] = 2;
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/meetings/api/list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(2, count($response['data']['items']));
            $this->assertEquals(5, $response['data']['totalCount']);
            $this->assertEquals(2, $response['data']['currentPage']);
            $this->assertEquals('First Meeting', $response['data']['items'][0]['name']);
            $this->assertEquals('Fifth Meeting', $response['data']['items'][1]['name']);

            // Search by custom fields, order by name desc
            $searchParams = array(
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 3,
                ),
                'search' => array(
                    'owner'   => array( 'id' => $super->id),
                ),
                'sort' => 'name.desc',
            );

            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/meetings/api/list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(4, $response['data']['totalCount']);
            $this->assertEquals(3, count($response['data']['items']));
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('Third Meeting', $response['data']['items'][0]['name']);
            $this->assertEquals('Second Meeting', $response['data']['items'][1]['name']);
            $this->assertEquals('First Meeting', $response['data']['items'][2]['name']);

            // Search by account, order by name desc
            $searchParams = array(
                            'pagination' => array(
                                'page'     => 1,
                                'pageSize' => 3,
            ),
                            'search' => array(
                                'activityItems'   => array('id' => $firstAccount->getClassId('Item')),
            ),
                            'sort' => 'name.desc',
            );

            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/meetings/api/list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);

            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(3, $response['data']['totalCount']);
            $this->assertEquals(3, count($response['data']['items']));
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('Second Meeting', $response['data']['items'][0]['name']);
            $this->assertEquals('First Meeting', $response['data']['items'][1]['name']);
            $this->assertEquals('Fifth Meeting', $response['data']['items'][2]['name']);
        }

        public function testEditMeetingWithIncompleteData()
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

            $meeting = MeetingTestHelper::createMeetingByNameForOwner('New Meeting', $super);

            // Provide data without required fields.
            $data['location']         = "Test 123";

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/meetings/api/create/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals(2, count($response['errors']));

            $id = $meeting->id;
            $data = array();
            $data['name']                = '';
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/meetings/api/update/' . $id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals(1, count($response['errors']));
        }

        public function testEditMeetingWIthIncorrectDataType()
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

            $meeting = MeetingTestHelper::createMeetingByNameForOwner('Newest Meeting', $super);

            // Provide data with wrong type.
            $data['startDateTime']         = "A";

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/meetings/api/create/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals(2, count($response['errors']));

            $id = $meeting->id;
            $data = array();
            $data['startDateTime']         = "A";
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/meetings/api/update/' . $id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals(1, count($response['errors']));
        }
    }
?>