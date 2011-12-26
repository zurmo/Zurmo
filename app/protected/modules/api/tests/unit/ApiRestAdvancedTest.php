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

    class ApiRestAdvancedTest extends ApiRestTest
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
                'ZURMO_AUTH_PASSWORD: super'
            );
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/login', 'POST', $headers);
            $response = json_decode($response, true);

            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
            $this->assertTrue(isset($response['data']['sessionId']) && is_string($response['data']['sessionId']));
            //ToDo: Check if session exist
            //$this->sessionId = $response['data']['sessionId'];
        }


        /**
        * @depends testApiServerUrl
        */
        public function testListViewCreateUpdateDeleteWithRelatedModels()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
            );

            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

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
            $testItem->firstName     = 'Bob3';
            $testItem->lastName      = 'Bob3';
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
            $createStamp             = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $this->assertTrue($testItem->save());
            $id = $testItem->id;
            $testItem->forget();
            unset($testItem);

            $testItem    = ApiTestModelItem::getById($id);
            $adapter     = new RedBeanModelToApiDataUtil($testItem);
            $data        = $adapter->getData();

            $compareData = array(
                'id'                => $id,
                'firstName'         => 'Bob3',
                'lastName'          => 'Bob3',
                'boolean'           => 1,
                'date'              => '2002-04-03',
                'dateTime'          => '2002-04-03 02:00:43',
                'float'             => 54.22,
                'integer'           => 10,
                'phone'             => '21313213',
                'string'            => 'aString',
                'textArea'          => 'Some Text Area',
                'url'               => 'http://www.asite.com',
                'currencyValue'     => array(
                    'id'         => $currencyValue->id,
                    'value'      => 100,
                    'rateToBase' => 1,
                    'currency'   => array(
                        'id'     => $currencies[0]->id,
                    ),
                ),
                'dropDown'          => null,
                'radioDropDown'     => null,
                'hasOne'            => array('id' => $testItem2->id),
                'hasOneAlso'        => array('id' => $testItem4->id),
                'primaryEmail'      => null,
                'primaryAddress'    => null,
                'secondaryEmail'    => null,
                'owner' => array(
                    'id' => $super->id,
                    'username' => 'super'
                ),
                'createdByUser'    => array(
                    'id' => $super->id,
                    'username' => 'super'
                ),
                'modifiedByUser' => array(
                    'id' => $super->id,
                    'username' => 'super'
                )
            );
            unset($data['createdDateTime']);
            unset($data['modifiedDateTime']);
            $this->assertEquals($compareData, $data);

            //Test View
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/apiTestModelItem/' . $id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
            unset($response['data']['createdDateTime']);
            unset($response['data']['modifiedDateTime']);
            $this->assertEquals($compareData, $response['data']);

            //Test List
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/apiTestModelItem', 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['array']));
            foreach ($response['data']['array'] as $key => $value)
            {
                unset($response['data']['array'][$key]['createdDateTime']);
                unset($response['data']['array'][$key]['modifiedDateTime']);
            }
            $this->assertEquals(array($compareData), $response['data']['array']);

            //Test Update
            $compareData['lastName'] = 'Bob4';
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/apiTestModelItem/' . $id, 'PUT', $headers, array('data' => $compareData));
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/apiTestModelItem/' . $id, 'GET', $headers);
            $response = json_decode($response, true);
            //Don't compare dates.
            unset($response['data']['createdDateTime']);
            unset($response['data']['modifiedDateTime']);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($compareData, $response['data']);

            //Test Delete
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/apiTestModelItem/' . $id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/apiTestModelItem/' . $id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_FAILURE, $response['status']);

            //Test Create
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

            $testItem->delete();
            $testItem->forget();
            unset($testItem);


            unset($data['createdDateTime']);
            unset($data['modifiedDateTime']);
            unset($data['createdByUser']);
            unset($data['modifiedByUser']);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/apiTestModelItem', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);

            $compareData = array(
                'id'                => $response['data']['id'],
                'firstName'         => 'Bob5',
                'lastName'          => 'Bob5',
                'boolean'           => 1,
                'date'              => '2002-04-03',
                'dateTime'          => '2002-04-03 02:00:43',
                'float'             => 54.22,
                'integer'           => 10,
                'phone'             => '21313213',
                'string'            => 'aString',
                'textArea'          => 'Some Text Area',
                'url'               => 'http://www.asite.com',
                'currencyValue'     => array(
                    'id'         => $currencyValue->id,
                    'value'      => 100,
                    'rateToBase' => 1,
                    'currency'   => array(
                        'id'     => $currencies[0]->id,
                    ),
                ),
                'dropDown'          => null,
                'radioDropDown'     => null,
                'hasOne'            => array('id' => $testItem2->id),
                'hasOneAlso'        => array('id' => $testItem4->id),
                'primaryEmail'      => null,
                'primaryAddress'    => null,
                'secondaryEmail'    => null,
                'owner' => array(
                    'id' => $super->id,
                    'username' => 'super'
                ),
                'createdByUser'    => array(
                    'id' => $super->id,
                    'username' => 'super'
                ),
                'modifiedByUser' => array(
                    'id' => $super->id,
                    'username' => 'super'
                )
            );
            unset($response['data']['createdDateTime']);
            unset($response['data']['modifiedDateTime']);
            unset($response['data']['currencyValue']['id']);
            unset($compareData['currencyValue']['id']);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($compareData, $response['data']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/apiTestModelItem/' . $response['data']['id'], 'GET', $headers);
            $response = json_decode($response, true);
            unset($response['data']['createdDateTime']);
            unset($response['data']['modifiedDateTime']);
            unset($response['data']['currencyValue']['id']);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($compareData, $response['data']);
        }

        /**
        * @depends testApiServerUrl
        */
        public function testGetCustomFieldData()
        {
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
            );

            //Fill some data
            $values = array(
                'Automotive',
                'Adult Entertainment',
                'Financial Services',
                'Mercenaries & Armaments',
            );
            $industryFieldData = CustomFieldData::getByName('Industries');
            $industryFieldData->serializedData = serialize($values);
            $this->assertTrue($industryFieldData->save());

            $values = array(
                'Prospect',
                'Customer',
                'Vendor',
            );
            $typeFieldData = CustomFieldData::getByName('AccountTypes');
            $typeFieldData->serializedData = serialize($values);
            $this->assertTrue($typeFieldData->save());

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/customData', 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
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
            );
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/logout', 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
        }
    }
?>