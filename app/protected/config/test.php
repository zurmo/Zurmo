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
    if (!defined('IS_TEST'))
    {
        define('IS_TEST', true);
    }
    $common_config = CMap::mergeArray(
        require('main.php'),
        array(
            'import' => array(
                'application.extensions.zurmoinc.framework.tests.common.*',
                'application.extensions.zurmoinc.framework.tests.unit.*',
                'application.extensions.zurmoinc.framework.tests.unit.components.*',
                'application.extensions.zurmoinc.framework.tests.unit.forms.*',
                'application.extensions.zurmoinc.framework.tests.unit.models.*',
                'application.extensions.zurmoinc.framework.tests.unit.modules.*',
                'application.extensions.zurmoinc.framework.tests.unit.views.*',
                'application.modules.zurmo.tests.components.*',
                'application.tests.unit.*',
////////////////////////////////////////////////////////////////////////////////
// Temporary - See Readme.txt in the app/protected/tests/unit/notSupposedToBeHere directory.
                'application.tests.unit.notSupposedToBeHere.*',
////////////////////////////////////////////////////////////////////////////////
            ),
            'components' => array(
                'fixture' => array(
                    'class' => 'system.test.CDbFixtureManager',
                ),
            ),
            'components' => array(
                'user' => array(
                    'class' => 'TestWebUser',
                ),
                'urlManager' => array (
                     'rules'=>array(
                        // API REST patterns
    /*
                        array('api/<model>Api/read',   'pattern'=>'api/<model:\w+>/api/<id:\d+>', 'verb'=>'GET'),
                        array('api/<model>Api/list',   'pattern'=>'api/<model:\w+>/api/*',          'verb'=>'GET'),
                        array('api/<model>Api/update', 'pattern'=>'api/<model:\w+>/api/<id:\d+>', 'verb'=>'PUT'),
                        array('api/<model>Api/delete', 'pattern'=>'api/<model:\w+>/api/<id:\d+>', 'verb'=>'DELETE'),
                        array('api/<model>Api/create', 'pattern'=>'api/<model:\w+>/api/',          'verb'=>'POST'),
                        '<module:\w+>/<controller:\w+>/<action:\w+>'=>'<module>/<controller>/<action>',
                        */
                    )
                ),
            ),
        )
    );
    //override and use test specific begin behavior
    $common_config['behaviors']['onBeginRequest'] = array(
        'class' => 'application.tests.BeginRequestTestBehavior'
    );
    //override and use INSTANCE_ROOT for handling paths during testing.
    $common_config['components']['assetManager']['baseUrl'] = INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'assets/';
    $common_config['behaviors']['onEndRequest']['class'] = 'application.tests.EndRequestTestBehavior';
    //breaks WalkthroughDocumentationTests so disabling Csrf and Cookie validation and use different class
    $common_config['components']['clientScript']['class']             = 'application.tests.ClientScriptForTesting';
    $common_config['components']['request']['class']                  = 'application.tests.HttpRequestForTesting';
    $common_config['components']['request']['enableCsrfValidation']   = false; //todo: get this working, since for production this is true.
    $common_config['components']['request']['enableCookieValidation'] = false;
    $common_config['components']['emailHelper']['class']              = 'application.tests.EmailHelperForTesting';
    //Set the GeoCodeApiKey to null which will work for localhost requests. If this is not running on
    //localhost, then modify perInstanceConfig.php with an updated key.
    if(!isset($common_config['params']['testGoogleGeoCodeApiKey']))
    {
        $common_config['params']['testGoogleGeoCodeApiKey'] = null;
    }
    return $common_config;
?>
