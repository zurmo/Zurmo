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

    class ApiRestTestModelItemTest extends ApiRestTest
    {
        public function testApiServerUrl()
        {
            $this->assertTrue(strlen($this->serverUrl) > 0);
        }

        /**
        * @depends testApiServerUrl
        */
        public function testLogin()
        {
            $headers = array(
                'Accept: application/json',
                'ZURMO_AUTH_USERNAME: super',
                'ZURMO_AUTH_PASSWORD: super',
                'ZURMO_API_REQUEST_TYPE: REST',
            );
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/zurmo/api/login/', 'POST', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertTrue(isset($response['data']['sessionId']) && is_string($response['data']['sessionId']));
            $this->assertTrue(isset($response['data']['token']) && is_string($response['data']['token']));
            //ToDo: Check if session exist
        }

        /**
        * @depends testApiServerUrl
        */
        public function testCreate()
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

            $currencies                 = Currency::getAll();
            $currencyValue              = new CurrencyValue();
            $currencyValue->value       = 100;
            $currencyValue->currency    = $currencies[0];
            $this->assertEquals('USD', $currencyValue->currency->code);

            $testItem2 = new ApiTestModelItem2();
            $testItem2->name     = 'John';
            $this->assertTrue($testItem2->save());

            $testItem4 = new ApiTestModelItem4();
            $testItem4->name     = 'John';
            $this->assertTrue($testItem4->save());

            //HAS_MANY and MANY_MANY relationships should be ignored.
            $testItem3_1 = new ApiTestModelItem3();
            $testItem3_1->name     = 'Kevin';
            $this->assertTrue($testItem3_1->save());

            $testItem3_2 = new ApiTestModelItem3();
            $testItem3_2->name     = 'Jim';
            $this->assertTrue($testItem3_2->save());


            $testItem = new ApiTestModelItem();
            $testItem->firstName     = 'Bob5';
            $testItem->lastName      = 'Bob5';
            $testItem->boolean       = true;
            $testItem->date          = '2002-04-03';
            $testItem->dateTime      = '2002-04-03 02:00:43';
            $testItem->float         = 54.22;
            $testItem->integer       = 10;
            $testItem->phone         = '21313213';
            $testItem->string        = 'aString';
            $testItem->textArea      = 'Some Text Area';
            $testItem->url           = 'http://www.asite.com';
            $testItem->owner         = $super;
            $testItem->currencyValue = $currencyValue;
            $testItem->hasOne        = $testItem2;
            $testItem->hasMany->add($testItem3_1);
            $testItem->hasMany->add($testItem3_2);
            $testItem->hasOneAlso    = $testItem4;
            $testItem->save();
            $util  = new RedBeanModelToApiDataUtil($testItem);
            $data  = $util->getData();
            $compareData = $data;

            $testItem->delete();
            $testItem->forget();
            unset($testItem);

            unset($data['createdDateTime']);
            unset($data['modifiedDateTime']);
            unset($data['createdByUser']);
            unset($data['modifiedByUser']);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/testModelItem/api/create/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);

            $id = $response['data']['id'];
            unset($response['data']['createdDateTime']);
            unset($response['data']['modifiedDateTime']);
            unset($response['data']['id']);
            unset($compareData['id']);
            unset($response['data']['currencyValue']['id']);
            unset($compareData['currencyValue']['id']);
            unset($compareData['createdDateTime']);
            unset($compareData['modifiedDateTime']);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            ksort($compareData);
            ksort($response['data']);
            $this->assertEquals($compareData, $response['data']);

        }

        /**
         * @depends testCreate
         */
        public function testGet()
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
            $testModels = ApiTestModelItem::getByName('Bob5');
            $this->assertEquals(1, count($testModels));
            $redBeanModelToApiDataUtil  = new RedBeanModelToApiDataUtil($testModels[0]);
            $compareData  = $redBeanModelToApiDataUtil->getData();

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/testModelItem/api/read/' . $compareData['id'], 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($compareData, $response['data']);
        }

        /**
         * @depends testGet
         */
        public function testUpdate()
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

            $testModels = ApiTestModelItem::getByName('Bob5');
            $this->assertEquals(1, count($testModels));
            $redBeanModelToApiDataUtil  = new RedBeanModelToApiDataUtil($testModels[0]);
            $compareData  = $redBeanModelToApiDataUtil->getData();

            $data = array('firstName' => 'Bob6');
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/testModelItem/api/update/' . $compareData['id'], 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            unset($response['data']['modifiedDateTime']);
            unset($compareData['modifiedDateTime']);
            $compareData['firstName'] = 'Bob6';
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($compareData, $response['data']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/testModelItem/api/read/' . $compareData['id'], 'GET', $headers);
            $response = json_decode($response, true);
            unset($response['data']['modifiedDateTime']);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($compareData, $response['data']);;
        }

        /**
         * @depends testUpdate
         */
        public function testList()
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

            $testModels = ApiTestModelItem::getByName('Bob6');
            $this->assertEquals(1, count($testModels));
            $redBeanModelToApiDataUtil  = new RedBeanModelToApiDataUtil($testModels[0]);
            $compareData  = $redBeanModelToApiDataUtil->getData();

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/testModelItem/api/list/', 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['array']));
            $this->assertEquals(array($compareData), $response['data']['array']);
        }

        /**
         * @depends testList
         */
        public function testDelete()
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

            $testModels = ApiTestModelItem::getByName('Bob6');
            $this->assertEquals(1, count($testModels));

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/testModelItem/api/delete/' . $testModels[0]->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/testModelItem/api/read/' . $testModels[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
        }

        /**
        * @depends testApiServerUrl
        */
        public function testLogout()
        {
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/zurmo/api/logout', 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
        }
    }
?>