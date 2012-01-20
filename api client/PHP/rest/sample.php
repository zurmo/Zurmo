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

    require_once "include/Auth.php";
    require_once "include/ApiTest.php";

    $baseUrl  = "http://zurmo.local";
    $username = "super";
    $password = "super";

    //Log in
    $response = Auth::login($baseUrl, $username, $password);

    if (!isset($response['status']) || $response['status'] != 'SUCCESS')
    {
        echo "Couldn't login";
        exit;
    }
    $sessionId = $response['data']['sessionId'];
    echo "Logged in. <br /><br />";

    //Create new model
    $data = array('name' => 'new name');
    $response = ApiTest::create($baseUrl, $sessionId, $data);
    if (!isset($response['status']) || $response['status'] != 'SUCCESS')
    {
        echo "Couldn't create new model";
        exit;
    }
    $id = $response['data']['id'];
    echo "Model created with id:{$id}. <br /><br />";

    //Update existing model
    $data = array('name' => 'changed name');
    $response = ApiTest::update($baseUrl, $sessionId, $data, $id);
    if (!isset($response['status']) || $response['status'] != 'SUCCESS')
    {
        echo "Couldn't update model";
        exit;
    }
    echo "Model updated. <br /><br />";

    //Get model by id
    $response = ApiTest::view($baseUrl, $sessionId, $id);
    if (!isset($response['status']) || $response['status'] != 'SUCCESS')
    {
        echo "Couldn't update model";
        exit;
    }
    $data = $response['data'];
    echo "Model viewed: <br />";
    print_r($data);
    echo "<br /><br />";

    //Get all models
    $response = ApiTest::listAll($baseUrl, $sessionId);
    if (!isset($response['status']) || $response['status'] != 'SUCCESS')
    {
        echo "Couldn't get all models";
        exit;
    }
    $data = $response['data'];
    echo "Model viewed: <br />";
    print_r($data);
    echo "<br /><br />";

    //Delete model
    $response = ApiTest::delete($baseUrl, $sessionId, $id);
    if (!isset($response['status']) || $response['status'] != 'SUCCESS')
    {
        echo "Couldn't delete model";
        exit;
    }
    echo "Model with id: {$id} deleted. <br /><br />";

    Auth::logout($baseUrl, $sessionId);

?>
