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

    class ApiRestTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            R::exec('drop table if exists apimodeltestitem');
        }

        public static function tearDownAfterClass()
        {
            R::exec('drop table if exists apimodeltestitem');
            parent::tearDownAfterClass();
        }

        public function testLogin()
        {
            $headers = array(
                'Accept: application/json',
                'ZURMO_AUTH_USERNAME: super',
                'ZURMO_AUTH_PASSWORD: super'
            );
            $response = $this->createApiCall('http://zurmo.local/api/rest/login', 'POST', $headers);
            $response = json_decode($response, true);

            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
            $this->assertTrue(isset($response['data']['sessionId']) && is_string($response['data']['sessionId']));
            //ToDo: Check if session exist
            //$this->sessionId = $response['data']['sessionId'];
        }

        public function testListViewCreateUpdateDelete()
        {
            $sessionId = $this->login();
            $headers = array(
                                        'Accept: application/json',
                                        'ZURMO_SESSION_ID: ' . $sessionId
            );
            //Test Create
            $data = array('name' => 'new name');
            $response = $this->createApiCall('http://zurmo.local/api/rest/apiTest', 'POST', $headers, $data);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
            $this->assertTrue(is_int($response['data']['id']));
            $this->assertGreaterThan(0, $response['data']['id']);
            $id = $response['data']['id'];

            $response = $this->createApiCall('http://zurmo.local/api/rest/apiTest/' . $id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals('new name', $response['data']['name']);

            //Test Update
            $data = array('name' => 'new name 2');
            $response = $this->createApiCall('http://zurmo.local/api/rest/apiTest/' . $id, 'PUT', $headers, $data);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);

            $response = $this->createApiCall('http://zurmo.local/api/rest/apiTest/' . $id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals('new name 2', $response['data']['name']);

            //Test Delete
            $response = $this->createApiCall('http://zurmo.local/api/rest/apiTest/' . $id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);

            $response = $this->createApiCall('http://zurmo.local/api/rest/apiTest/' . $id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
            //ToDo:Test that it doesn't exist

            //Test List
            $response = $this->createApiCall('http://zurmo.local/api/rest/apiTest', 'GET', $headers);
            $response = json_decode($response, true);

            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(3, count($response['data']));
        }

        public function testLogout()
        {
            $sessionId = $this->login();
            $headers = array(
                            'Accept: application/json',
                            'ZURMO_SESSION_ID: ' . $sessionId
            );
            $response = $this->createApiCall('http://zurmo.local/api/rest/logout', 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
        }

        protected function login()
        {
            $headers = array(
                            'Accept: application/json',
                            'ZURMO_AUTH_USERNAME: super',
                            'ZURMO_AUTH_PASSWORD: super'
            );
            $response = $this->createApiCall('http://zurmo.local/api/rest/login', 'POST', $headers);
            $response = json_decode($response, true);
            return $response['data']['sessionId'];
        }

        protected function createApiCall($url, $method, $headers, $data = array())
        {
            if ($method == 'PUT')
            {
                $headers[] = 'X-HTTP-Method-Override: PUT';
            }

            $handle = curl_init();
            curl_setopt($handle, CURLOPT_URL, $url);
            curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);

            switch($method)
            {

                case 'GET':
                    break;

                case 'POST':
                    curl_setopt($handle, CURLOPT_POST, true);
                    curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
                    break;

                case 'PUT':
                    curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'PUT');
                    curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($data));
                    break;

                case 'DELETE':
                    curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'DELETE');
                    break;
            }
            $response = curl_exec($handle);
            //$info = curl_getinfo($handle);
            //print_r($info);
            return $response;
        }
    }
?>