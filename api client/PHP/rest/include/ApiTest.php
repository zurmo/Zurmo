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
    require_once "Request.php";
    class ApiTest extends Request
    {
        /**
         * List all api test models
         * @param string $baseUrl
         * @param string $sessionId
         * @return array $response
         */
        public static function listAll($baseUrl, $sessionId)
        {
            $headers = self::createAuthenticatedHeaders($sessionId);
            $response = self::createApiCall($baseUrl . '/index.php/api/rest/ApiTestModelItem2', 'GET', $headers);
            $response = json_decode($response, true);
            return $response;
        }

        /**
        * View api test models
        * @param string $baseUrl
        * @param string $sessionId
        * @param int $id
        * @return array $response
        */
        public static function view($baseUrl, $sessionId, $id)
        {
            $headers = self::createAuthenticatedHeaders($sessionId);
            $response = self::createApiCall($baseUrl . '/index.php/api/rest/ApiTestModelItem2/' . $id, 'GET', $headers);
            $response = json_decode($response, true);
            return $response;
        }

        /**
        * List all api test models
        * @param string $baseUrl
        * @param string $sessionId
        * @param array $data
        * @return array $response
        */
        public static function create($baseUrl, $sessionId, $data)
        {
            $headers = self::createAuthenticatedHeaders($sessionId);
            $response = self::createApiCall($baseUrl . '/index.php/api/rest/ApiTestModelItem2', 'POST', $headers, $data);
            $response = json_decode($response, true);
            return $response;
        }

        /**
        * List all api test models
        * @param string $baseUrl
        * @param string $sessionId
        * @param array $data
        * @param int $id
        * @return array $response
        */
        public static function update($baseUrl, $sessionId, $data, $id)
        {
            $headers = self::createAuthenticatedHeaders($sessionId);
            $data = array('name' => 'new name 2');
            $response = self::createApiCall($baseUrl . '/index.php/api/rest/ApiTestModelItem2/' . $id, 'PUT', $headers, $data);
            $response = json_decode($response, true);
            return $response;
        }

        /**
        * List all api test models
        * @param string $baseUrl
        * @param string $sessionId
        * @param int $id
        * @return array $response
        */
        public static function delete($baseUrl, $sessionId, $id)
        {
            $headers = self::createAuthenticatedHeaders($sessionId);
            $response = self::createApiCall($baseUrl . '/index.php/api/rest/ApiTestModelItem2/' . $id, 'DELETE', $headers);
            $response = json_decode($response, true);
            return $response;
        }
    }
?>
