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

    class ApiRequest extends CHttpRequest
    {
        public static function processRequest()
  {
    // get our verb
    print_r(parse_str(file_get_contents('php://input'), $arguments));
    echo $_SERVER['REQUEST_METHOD'];
    $request_method = strtolower($_SERVER['REQUEST_METHOD']);
    //$return_obj		= new RestRequest();
    // we'll store our data here
    $data			= array();

    switch ($request_method)
    {
      // gets are easy...
      case 'get':
        $data = $_GET;
        break;
      // so are posts
      case 'post':
        $data = $_POST;
        break;
      // here's the tricky bit...
      case 'put':
        // basically, we read a string from PHP's special input location,
        // and then parse it out into an array via parse_str... per the PHP docs:
        // Parses str  as if it were the query string passed via a URL and sets
        // variables in the current scope.
        parse_str(file_get_contents('php://input'), $put_vars);
        $data = $put_vars;
        break;
    }
    print_r($_GET);
    print_r($data);
    exit;
/*
    // store the method
    $return_obj->setMethod($request_method);

    // set the raw data, so we can access it if needed (there may be
    // other pieces to your requests)
    $return_obj->setRequestVars($data);

    if(isset($data['data']))
    {
      // translate the JSON to an Object for use however you want
      $return_obj->setData(json_decode($data['data']));
    }
    return $return_obj;
    */
  }
    }
?>