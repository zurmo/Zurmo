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

    class ApiRestNoteTest extends ApiRestTest
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

            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
            );

            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $occurredOnStamp          = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $data['description']    = "Note description";
            $data['occurredOnDateTime'] = $occurredOnStamp;

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/note', 'POST', $headers, array('data' => $data));
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
            $data['latestDateTime'] = $occurredOnStamp;
            $this->assertEquals(ksort($data), ksort($response['data']));
            $id = $response['data']['id'];

            //Test update
            $updateData['description']    = "Updated note description";
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/note/' . $id, 'PUT', $headers, array('data' => $updateData));
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/note/' . $id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);

            // We need to unset some empty values from response and dates.
            unset($response['data']['createdDateTime']);
            unset($response['data']['modifiedDateTime']);
            $data['description'] = $updateData['description'];
            $this->assertEquals(ksort($data), ksort($response['data']));

            //Test List
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/note', 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['array']));
            foreach ($response['data']['array'] as $key => $value)
            {
                unset($response['data']['array'][$key]['createdDateTime']);
                unset($response['data']['array'][$key]['modifiedDateTime']);
                unset($response['data']['array'][$key]['id']);
                ksort($response['data']['array'][$key]);
            }
            $this->assertEquals(array($data), $response['data']['array']);

            // Test with unprivileged user to view, edit and delete account.
            $authenticationData = $this->login('steven', 'steven');
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
            );
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/note/' . $id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_FAILURE, $response['status']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/note/' . $id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_FAILURE, $response['status']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/note/' . $id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_FAILURE, $response['status']);

            // Test with privileged user
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
            );

            //Test Delete
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/note/' . $id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/note/' . $id, 'GET', $headers);
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
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
            );
            $firstAccount = AccountTestHelper::createAccountByNameTypeAndIndustryForOwner('First Account', 'Customer', 'Automotive', $super);
            $secondAccount = AccountTestHelper::createAccountByNameTypeAndIndustryForOwner('Second Account', 'Customer', 'Automotive', $super);

            NoteTestHelper::createNoteWithOwnerAndRelatedAccount('First Note', $super, $firstAccount);
            NoteTestHelper::createNoteWithOwnerAndRelatedAccount('Second Note', $super, $firstAccount);
            NoteTestHelper::createNoteWithOwnerAndRelatedAccount('Third Note', $super, $secondAccount);
            NoteTestHelper::createNoteWithOwnerAndRelatedAccount('Forth Note', $anotherUser, $secondAccount);
            NoteTestHelper::createNoteWithOwnerAndRelatedAccount('Fifth Note', $super, $firstAccount);

            $searchParams = array(
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 3,
                ),
                'search' => array(
                    'description' => '',
                ),
                'sort' => 'description',
            );
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/note/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(3, count($response['data']['array']));
            $this->assertEquals(5, $response['data']['total']);
            $this->assertEquals('Fifth Note', $response['data']['array'][0]['description']);
            $this->assertEquals('First Note', $response['data']['array'][1]['description']);
            $this->assertEquals('Forth Note', $response['data']['array'][2]['description']);

            // Second page
            $searchParams['pagination']['page'] = 2;
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/note/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(2, count($response['data']['array']));
            $this->assertEquals(5, $response['data']['total']);
            $this->assertEquals('Second Note', $response['data']['array'][0]['description']);
            $this->assertEquals('Third Note', $response['data']['array'][1]['description']);

            // Search by name
            $searchParams['pagination']['page'] = 1;
            $searchParams['search']['description'] = 'First Note';
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/note/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['array']));
            $this->assertEquals(1, $response['data']['total']);
            $this->assertEquals('First Note', $response['data']['array'][0]['description']);

            // No results
            $searchParams['pagination']['page'] = 1;
            $searchParams['search']['description'] = 'First Note 2';
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/note/filter/' . $searchParamsQuery, 'GET', $headers);
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
                    'description' => '',
                ),
                'sort' => 'description.desc',
            );
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/note/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(3, count($response['data']['array']));
            $this->assertEquals(5, $response['data']['total']);
            $this->assertEquals('Third Note', $response['data']['array'][0]['description']);
            $this->assertEquals('Second Note', $response['data']['array'][1]['description']);
            $this->assertEquals('Forth Note', $response['data']['array'][2]['description']);

            // Second page
            $searchParams['pagination']['page'] = 2;
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/note/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(2, count($response['data']['array']));
            $this->assertEquals(5, $response['data']['total']);
            $this->assertEquals('First Note', $response['data']['array'][0]['description']);
            $this->assertEquals('Fifth Note', $response['data']['array'][1]['description']);

            // Search by owner, order by name desc
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
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/note/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(3, count($response['data']['array']));
            $this->assertEquals(4, $response['data']['total']);
            $this->assertEquals('Third Note', $response['data']['array'][0]['description']);
            $this->assertEquals('Second Note', $response['data']['array'][1]['description']);
            $this->assertEquals('First Note', $response['data']['array'][2]['description']);

            // Second page
            $searchParams['pagination']['page'] = 2;
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/note/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['array']));
            $this->assertEquals(4, $response['data']['total']);
            $this->assertEquals('Fifth Note', $response['data']['array'][0]['description']);

            // Search by account, order by name desc
            $searchParams = array(
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 3,
                ),
                'search' => array(
                    'activityItems'   => array( 'id' => $firstAccount->id),// TO DO: search by activity item
                ),
                'sort' => 'name.desc',
            );

            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/note/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(3, count($response['data']['array']));
            $this->assertEquals(3, $response['data']['total']);
            $this->assertEquals('Second Note', $response['data']['array'][0]['description']);
            $this->assertEquals('First Note', $response['data']['array'][1]['description']);
            $this->assertEquals('Fifth Note', $response['data']['array'][2]['description']);
        }

        public function testEditNoteWithIncompleteData()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $super = User::getByUsername('super');
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
            );

            $note = NoteTestHelper::createNoteByNameForOwner('New Note', $super);

            // Provide data without required fields.
            $data['description']         = "";

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/note', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals(1, count($response['errors']));

            $id = $note->id;
            $data = array();
            $data['description']                = '';
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/note/' . $id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals(1, count($response['errors']));
        }

        public function testEditNoteWIthIncorrectDataType()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $super = User::getByUsername('super');
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
            );

            $note = NoteTestHelper::createNoteByNameForOwner('Newest Note', $super);

            // Provide data with wrong type.
            $data['occurredOnDateTime']         = "A";

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/note', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals(2, count($response['errors']));

            $id = $note->id;
            $data = array();
            $data['occurredOnDateTime']         = "A";
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/note/' . $id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals(1, count($response['errors']));
        }
    }
?>