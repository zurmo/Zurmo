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

    class ApiRestUserTest extends ApiRestTest
    {
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

            $titles = array('Mr.', 'Mrs.', 'Ms.', 'Dr.', 'Swami');
            $customFieldData = CustomFieldData::getByName('Titles');
            $customFieldData->serializedData = serialize($titles);
            $this->assertTrue($customFieldData->save());

            $primaryEmail['emailAddress']   = "a@example.com";
            $primaryEmail['optOut']         = 1;

            $primaryAddress['street1']      = '129 Noodle Boulevard';
            $primaryAddress['street2']      = 'Apartment 6000A';
            $primaryAddress['city']         = 'Noodleville';
            $primaryAddress['postalCode']   = '23453';
            $primaryAddress['country']      = 'The Good Old US of A';

            $currencies                 = Currency::getAll();
            $currency    = $currencies[0];
            $this->assertEquals('USD', $currency->code);



            $manager = new User();
            $manager->username     = 'smith45';
            $manager->title->value = 'Mr.';
            $manager->firstName    = 'Super';
            $manager->lastName     = 'User';
            $manager->setPassword('jimmy021');
            $saved = $manager->save();
            $this->assertTrue($saved);

            $data['firstName']           = "Michael";
            $data['lastName']            = "Smith";
            $data['jobTitle']            = "President";
            $data['department']          = "Sales";
            $data['officePhone']         = "653-235-7824";
            $data['mobilePhone']         = "653-235-7821";
            $data['officeFax']           = "653-235-7834";
            $data['username']            = "diggy011";
            $data['password']            = "diggy011";
            $data['language']            = "en";
            $data['timeZone']            = "America/Chicago";

            $data['title']['value']      = $titles[3];
            $data['manager']['id']       = $manager->id;

            $data['primaryEmail']        = $primaryEmail;
            $data['primaryAddress']      = $primaryAddress;

            //$data['currency']       = array(
            //    'id' => $currency->id
            //);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/user', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
/*
            $data['owner'] = array(
                'id' => $super->id,
                'username' => 'super'
            );
            */

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
            unset($response['data']['primaryAddress']['id']);
            unset($response['data']['primaryAddress']['state']);
            unset($response['data']['primaryAddress']['longitude']);
            unset($response['data']['primaryAddress']['latitude']);
            unset($response['data']['title']['id']);
            unset($response['data']['currency']);
            $this->assertEquals(ksort($data), ksort($response['data']));
            $id = $response['data']['id'];
            //Test update
            $data['firstName']                = "John";
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/user/' . $id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/user/' . $id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);

            // We need to unset some empty values from response and dates.
            unset($response['data']['createdDateTime']);
            unset($response['data']['modifiedDateTime']);
            unset($response['data']['primaryEmail']['id'] );
            unset($response['data']['primaryAddress']['id']);
            unset($response['data']['primaryAddress']['state']);
            unset($response['data']['primaryAddress']['longitude']);
            unset($response['data']['primaryAddress']['latitude']);
            unset($response['data']['title']['id']);
            unset($response['data']['currency']);

            $this->assertEquals(ksort($data), ksort($response['data']));

            //Test List
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/user', 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(3, count($response['data']));

            //Test Delete
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/user/' . $id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/user/' . $id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_FAILURE, $response['status']);
        }
    }
?>