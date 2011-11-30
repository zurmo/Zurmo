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

    class ApiRestAdvancedTest extends BaseTest
    {
        public $serverUrl = '';

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = SecurityTestHelper::createSuperAdmin();
            $jim = UserTestHelper::createBasicUser('jim');
            $values = array(
                'Test1',
                'Test2',
                'Test3',
                'Sample',
                'Demo',
            );
            $customFieldData = CustomFieldData::getByName('ApiTestDropDown');
            $customFieldData->serializedData = serialize($values);
            $saved = $customFieldData->save();
            assert($saved);    // Not Coding Standard

            //Ensure the external system id column is present.
            $columnName = ExternalSystemIdUtil::EXTERNAL_SYSTEM_ID_COLUMN_NAME;
            RedBean_Plugin_Optimizer_ExternalSystemId::
            ensureColumnIsVarchar(User::getTableName('User'), $columnName);
            $userTableName = User::getTableName('User');
            R::exec("update " . $userTableName . " set $columnName = 'A' where id = {$super->id}");
            R::exec("update " . $userTableName . " set $columnName = 'B' where id = {$jim->id}");

            RedBean_Plugin_Optimizer_ExternalSystemId::
                ensureColumnIsVarchar(ApiModelTestItem::getTableName('ApiModelTestItem'),   $columnName);
            RedBean_Plugin_Optimizer_ExternalSystemId::
                ensureColumnIsVarchar(ApiModelTestItem2::getTableName('ApiModelTestItem2'), $columnName);
            RedBean_Plugin_Optimizer_ExternalSystemId::
                ensureColumnIsVarchar(ApiModelTestItem3::getTableName('ApiModelTestItem3'), $columnName);
            RedBean_Plugin_Optimizer_ExternalSystemId::
                ensureColumnIsVarchar(ApiModelTestItem4::getTableName('ApiModelTestItem4'), $columnName);
        }

        public static function tearDownAfterClass()
        {
            //parent::tearDownAfterClass();
        }

        public function setUp(){
            parent::setUp();
            if (strlen(Yii::app()->params['testApiUrl']) > 0)
            {
                $this->serverUrl = Yii::app()->params['testApiUrl'];
            }
        }

        /**
        * This test was needed because of the wierd type casting issues with 0 and 1 and '1' and '0' as keys in an array.
        * '0' and '1' turn into integers which they shouldn't and this messes up the oneOf sql query builder. Additionally
        * on some versions of MySQL, 0,1 in a NOT IN, will evaluate true to 'abc' which it shouldn't.  As a result
        * the 0/1 boolean values have been removed from the BooleanSanitizerUtil::getAcceptableValues().
        */
        public function testBooleanAcceptableValuesMappingAndSqlOneOfString()
        {
            $string = SQLOperatorUtil::
                resolveOperatorAndValueForOneOf('oneOf', BooleanSanitizerUtil::getAcceptableValues());
            $compareString = "IN(lower('false'),lower('true'),lower('y'),lower('n'),lower('yes'),lower('no'),lower('0'),lower('1'),lower(''))"; // Not Coding Standard
            $this->assertEquals($compareString, $string);
        }

        /**
        * @depends testBooleanAcceptableValuesMappingAndSqlOneOfString
        */
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
            $sessionId = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $sessionId
            );

            $externalSystemIdColumnName = ExternalSystemIdUtil::EXTERNAL_SYSTEM_ID_COLUMN_NAME;
            //Add test ApiModelTestItem models for use in this test.
            $apiModelTestItemModel1 = ApiTestHelper::createApiModelTestItem('aaa', 'aba');
            $apiModelTestItemModel2 = ApiTestHelper::createApiModelTestItem('ddw', 'daf');
            //Update model2 to have an externalSystemId.
            R::exec("update " . ApiModelTestItem::getTableName('ApiModelTestItem')
            . " set $externalSystemIdColumnName = 'B' where id = {$apiModelTestItemModel2->id}");

            //Add test ApiModelTestItem2 models for use in this test.
            $apiModelTestItem2Model1 = ApiTestHelper::createApiModelTestItem2('aaa');
            $apiModelTestItem2Model2 = ApiTestHelper::createApiModelTestItem2('bbb');
            $apiModelTestItem2Model3 = ApiTestHelper::createApiModelTestItem2('ccc');
            //Update model2 to have an externalSystemId.
            R::exec("update " . ApiModelTestItem2::getTableName('ApiModelTestItem2')
            . " set $externalSystemIdColumnName = 'B' where id = {$apiModelTestItem2Model2->id}");

            //Add test ApiModelTestItem3 models for use in this test.
            $apiModelTestItem3Model1 = ApiTestHelper::createApiModelTestItem3('aaa');
            $apiModelTestItem3Model2 = ApiTestHelper::createApiModelTestItem3('dd');
            //Update model2 to have an externalSystemId.
            R::exec("update " . ApiModelTestItem3::getTableName('ApiModelTestItem3')
            . " set $externalSystemIdColumnName = 'K' where id = {$apiModelTestItem3Model2->id}");

            //Add test ApiModelTestItem4 models for use in this test.
            $apiModelTestItem4Model1 = ApiTestHelper::createApiModelTestItem4('aaa');
            $apiModelTestItem4Model2 = ApiTestHelper::createApiModelTestItem4('dd');
            //Update model2 to have an externalSystemId.
            R::exec("update " . ApiModelTestItem3::getTableName('ApiModelTestItem4')
            . " set $externalSystemIdColumnName = 'J' where id = {$apiModelTestItem4Model2->id}");

            //Test related models
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/apiTestModelItem/' . $apiModelTestItemModel1->id, 'GET', $headers);
            $response = json_decode($response, true);
            //$myVar = print_r($response, true);
            //$fp = fopen('data.txt', 'w');
            //fwrite($fp, $myVar);
            //fclose($fp);
            //echo $response['data']['string'];
            //            exit;
        }

        /**
        * @depends testApiServerUrl
        */
        public function testLogout()
        {
            $sessionId = $this->login();
            $headers = array(
                            'Accept: application/json',
                            'ZURMO_SESSION_ID: ' . $sessionId
            );
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/logout', 'GET', $headers);
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
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/login', 'POST', $headers);
            $response = json_decode($response, true);
            return $response['data']['sessionId'];
        }

    }
?>