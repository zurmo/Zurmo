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

    class ApiRestCustomFieldTest extends ApiRestTest
    {
        public function testApiServerUrl()
        {
            $this->assertTrue(strlen($this->serverUrl) > 0);
        }

        /**
        * @depends testApiServerUrl
        */
        public function testListView()
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

            $industryValues = array(
                            'Automotive',
                            'Adult Entertainment',
                            'Financial Services',
                            'Mercenaries & Armaments',
            );
            $industryFieldData = CustomFieldData::getByName('Industries');
            $industryFieldData->serializedData = serialize($industryValues);
            $this->assertTrue($industryFieldData->save());

            //Test List
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/customData', 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);

            foreach ($response['data'] as $key => $value)
            {
                ksort($response['data'][$key]);
            }
            foreach ($industryValues as $key => $value)
            {
                ksort($industryValues);
            }
            $this->assertEquals($industryValues, $response['data']['Industries']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/customData/Industries', 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);

            ksort($response['data']);
            $this->assertEquals($industryValues, $response['data']);

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
    }
?>