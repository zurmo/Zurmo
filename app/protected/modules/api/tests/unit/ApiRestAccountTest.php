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

    class ApiRestAccountTest extends BaseTest
    {
        public $serverUrl = '';
        public $freeze = false;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = SecurityTestHelper::createSuperAdmin();
        }

        public function setUp(){
            parent::setUp();
            if (strlen(Yii::app()->params['testApiUrl']) > 0)
            {
                $this->serverUrl = Yii::app()->params['testApiUrl'];
            }
            $freeze = false;
            if (RedBeanDatabase::isFrozen())
            {
                RedBeanDatabase::unfreeze();
                $freeze = true;
            }
            $this->freeze = $freeze;
        }

        public function teardown()
        {
            if ($this->freeze)
            {
                RedBeanDatabase::freeze();
            }
            parent::teardown();
        }

        public function testApiServerUrl()
        {
            $this->assertTrue(strlen($this->serverUrl) > 0);
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

            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

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

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/account', 'POST', $headers, array('data' => $data));
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
            unset($response['data']['primaryEmail']['id'] );
            unset($response['data']['secondaryEmail']['id']);
            unset($response['data']['billingAddress']['id']);
            unset($response['data']['billingAddress']['state']);
            unset($response['data']['billingAddress']['longitude']);
            unset($response['data']['billingAddress']['latitude']);

            unset($response['data']['shippingAddress']['id']);
            unset($response['data']['shippingAddress']['state']);
            unset($response['data']['shippingAddress']['longitude']);
            unset($response['data']['shippingAddress']['latitude']);
            unset($response['data']['industry']['id']);
            unset($response['data']['type']['id']);

            $this->assertEquals(ksort($data), ksort($response['data']));
            $id = $response['data']['id'];
            //Test update
            $data['name']                = "My Company 2";
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/account/' . $id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/account/' . $id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);

            // We need to unset some empty values from response and dates.
            unset($response['data']['createdDateTime']);
            unset($response['data']['modifiedDateTime']);
            unset($response['data']['primaryEmail']['id'] );
            unset($response['data']['secondaryEmail']['id']);
            unset($response['data']['billingAddress']['id']);
            unset($response['data']['billingAddress']['state']);
            unset($response['data']['billingAddress']['longitude']);
            unset($response['data']['billingAddress']['latitude']);

            unset($response['data']['shippingAddress']['id']);
            unset($response['data']['shippingAddress']['state']);
            unset($response['data']['shippingAddress']['longitude']);
            unset($response['data']['shippingAddress']['latitude']);
            unset($response['data']['industry']['id']);
            unset($response['data']['type']['id']);

            $this->assertEquals(ksort($data), ksort($response['data']));

            //Test List
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/account', 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']));
            foreach ($response['data'] as $key => $value)
            {
                unset($response['data'][$key]['createdDateTime']);
                unset($response['data'][$key]['modifiedDateTime']);
                unset($response['data'][$key]['primaryEmail']['id'] );
                unset($response['data'][$key]['primaryEmail']['isInvalid'] );
                unset($response['data'][$key]['secondaryEmail']['id']);
                unset($response['data'][$key]['billingAddress']['id']);
                unset($response['data'][$key]['billingAddress']['state']);
                unset($response['data'][$key]['billingAddress']['longitude']);
                unset($response['data'][$key]['billingAddress']['latitude']);

                unset($response['data'][$key]['shippingAddress']['id']);
                unset($response['data'][$key]['shippingAddress']['state']);
                unset($response['data'][$key]['shippingAddress']['longitude']);
                unset($response['data'][$key]['shippingAddress']['latitude']);
                unset($response['data'][$key]['industry']['id']);
                unset($response['data'][$key]['type']['id']);
                unset($response['data'][$key]['id']);
                ksort($response['data'][$key]);
            }
            $this->assertEquals(array($data), $response['data']);

            //Test Delete
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/account/' . $id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/account/' . $id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_FAILURE, $response['status']);
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