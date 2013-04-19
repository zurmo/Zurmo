<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/
    if (!defined('IS_TEST'))
    {
        define('IS_TEST', true);
    }
    $common_config = CMap::mergeArray(
        require('main.php'),
        array(
            'import' => array(
                'application.tests.unit.*',
                'ext.userinterface.UserInterface',
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
    $common_config['components']['clientScript']['class']              = 'application.tests.ClientScriptForTesting';
    $common_config['components']['request']['class']                   = 'application.tests.HttpRequestForTesting';
    $common_config['components']['userInterface']['class']             = 'application.tests.UserInterfaceForTesting';
    $common_config['components']['request']['enableCsrfValidation']    = false; //todo: get this working, since for production this is true.
    $common_config['components']['request']['enableCookieValidation']  = false;
    $common_config['components']['emailHelper']['class']               = 'application.tests.EmailHelperForTesting';
    $common_config['components']['languageHelper']['class']               = 'application.tests.ZurmoLanguageHelperForTesting';
    $common_config['components']['timeZoneHelper']['timeZone']         = 'UTC';
    unset($common_config['components']['apiRequest']);
    unset($common_config['components']['apiHelper']);
    //Set the GeoCodeApiKey to null which will work for localhost requests. If this is not running on
    //localhost, then modify perInstanceConfig.php with an updated key.
    if (!isset($common_config['params']['testGoogleGeoCodeApiKey']))
    {
        $common_config['params']['testGoogleGeoCodeApiKey'] = null;
    }

    if (isset($emailTestAccounts) && !empty($emailTestAccounts))
    {
        $common_config['params']['emailTestAccounts'] = $emailTestAccounts;
    }

    if (isset($authenticationTestSettings) && !empty($authenticationTestSettings))
    {
        $common_config['params']['authenticationTestSettings'] = $authenticationTestSettings;
    }

    if (isset($testApiUrl))
    {
        $common_config['params']['testApiUrl'] = $testApiUrl;
    }
    if (isset($testGoogleGeoCodeApiKey))
    {
        $common_config['params']['testGoogleGeoCodeApiKey'] = $testGoogleGeoCodeApiKey;
    }
    return $common_config;
?>
