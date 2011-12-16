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

    class ApiRestMeetingTest extends ApiRestTest
    {
        public function testApiServerUrl()
        {
            $this->assertTrue(strlen($this->serverUrl) > 0);
        }

        /**
        * @depends testApiServerUrl
        */
        public function testListViewCreateUpdateDelete()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $notAllowedUser = UserTestHelper::createBasicUser('Steven');
            $notAllowedUser->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API);
            $saved = $notAllowedUser->save();

            $sessionId = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $sessionId
            );

            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

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

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/meeting', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);

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
            $data['latestDateTime'] = $startStamp;
            $this->assertEquals(ksort($data), ksort($response['data']));
            $id = $response['data']['id'];

            //Test update
            $data['description']    = "Some new description";
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/meeting/' . $id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/meeting/' . $id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);

            // We need to unset some empty values from response and dates.
            unset($response['data']['createdDateTime']);
            unset($response['data']['modifiedDateTime']);
            unset($response['data']['category']['id']);
            $this->assertEquals(ksort($data), ksort($response['data']));

            //Test List
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/meeting', 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['array']));
            foreach ($response['data']['array'] as $key => $value)
            {
                unset($response['data']['array'][$key]['createdDateTime']);
                unset($response['data']['array'][$key]['modifiedDateTime']);
                unset($response['data']['array'][$key]['category']['id']);
                unset($response['data']['array'][$key]['id']);
                ksort($response['data']['array'][$key]);
            }
            $this->assertEquals(array($data), $response['data']['array']);

            // Test with unprivileged user to view, edit and delete account.
            $sessionId = $this->login('steven', 'steven');
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $sessionId
            );
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/meeting/' . $id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_FAILURE, $response['status']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/meeting/' . $id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_FAILURE, $response['status']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/meeting/' . $id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_FAILURE, $response['status']);

            // Test with privileged user
            $sessionId = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $sessionId
            );
            //Test Delete
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/meeting/' . $id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/meeting/' . $id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_FAILURE, $response['status']);
        }

        /**
        * @depends testListViewCreateUpdateDelete
        */
        public function testSearch()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $anotherUser = User::getByUsername('steven');

            $super = User::getByUsername('super');
            $sessionId = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $sessionId
            );
            MeetingTestHelper::createMeetingByNameForOwner('First Meeting', $super);
            MeetingTestHelper::createMeetingByNameForOwner('Second Meeting', $super);
            MeetingTestHelper::createMeetingByNameForOwner('Third Meeting', $super);
            MeetingTestHelper::createMeetingByNameForOwner('Forth Meeting', $anotherUser);
            MeetingTestHelper::createMeetingByNameForOwner('Fifth Meeting', $super);

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
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/meeting/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(3, count($response['data']['array']));
            $this->assertEquals(5, $response['data']['total']);
            $this->assertEquals('Fifth Meeting', $response['data']['array'][0]['name']);
            $this->assertEquals('First Meeting', $response['data']['array'][1]['name']);
            $this->assertEquals('Forth Meeting', $response['data']['array'][2]['name']);

            // Second page
            $searchParams['pagination']['page'] = 2;
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/meeting/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(2, count($response['data']['array']));
            $this->assertEquals(5, $response['data']['total']);
            $this->assertEquals('Second Meeting', $response['data']['array'][0]['name']);
            $this->assertEquals('Third Meeting', $response['data']['array'][1]['name']);

            // Search by name
            $searchParams['pagination']['page'] = 1;
            $searchParams['search']['name'] = 'First Meeting';
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/meeting/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['array']));
            $this->assertEquals(1, $response['data']['total']);
            $this->assertEquals('First Meeting', $response['data']['array'][0]['name']);

            // No results
            $searchParams['pagination']['page'] = 1;
            $searchParams['search']['name'] = 'First Meeting 2';
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/meeting/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(0, $response['data']['total']);
            $this->assertFalse(isset($response['data']['array']));

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
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/meeting/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(3, count($response['data']['array']));
            $this->assertEquals(5, $response['data']['total']);
            $this->assertEquals('Third Meeting', $response['data']['array'][0]['name']);
            $this->assertEquals('Second Meeting', $response['data']['array'][1]['name']);
            $this->assertEquals('Forth Meeting', $response['data']['array'][2]['name']);

            // Second page
            $searchParams['pagination']['page'] = 2;
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/meeting/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(2, count($response['data']['array']));
            $this->assertEquals(5, $response['data']['total']);
            $this->assertEquals('First Meeting', $response['data']['array'][0]['name']);
            $this->assertEquals('Fifth Meeting', $response['data']['array'][1]['name']);

            // Search by custom fields, order by name desc
            $searchParams = array(
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 3,
                ),
                'search' => array(
                    'owner'   => array( 'id' => 1),
                ),
                'sort' => 'name.desc',
            );

            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/meeting/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(3, count($response['data']['array']));
            $this->assertEquals(4, $response['data']['total']);
            $this->assertEquals('Third Meeting', $response['data']['array'][0]['name']);
            $this->assertEquals('Second Meeting', $response['data']['array'][1]['name']);
            $this->assertEquals('First Meeting', $response['data']['array'][2]['name']);
        }
    }
?>