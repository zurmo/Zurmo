<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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
                'application.extensions.zurmoinc.framework.adapters.*',
                'application.extensions.zurmoinc.framework.adapters.columns.*',
                'application.extensions.zurmoinc.framework.adapters.dataproviders.*',
                'application.extensions.zurmoinc.framework.configuration.*',
                'application.extensions.zurmoinc.framework.components.*',
                'application.extensions.zurmoinc.framework.controllers.*',
                'application.extensions.zurmoinc.framework.dataproviders.*',
                'application.extensions.zurmoinc.framework.elements.*',
                'application.extensions.zurmoinc.framework.elements.actions.*',
                'application.extensions.zurmoinc.framework.elements.derived.*',
                'application.extensions.zurmoinc.framework.exceptions.*',
                'application.extensions.zurmoinc.framework.forms.*',
                'application.extensions.zurmoinc.framework.interfaces.*',
                'application.extensions.zurmoinc.framework.models.*',
                'application.extensions.zurmoinc.framework.models.validators.*',
                'application.extensions.zurmoinc.framework.modules.*',
                'application.extensions.zurmoinc.framework.portlets.*',
                'application.extensions.zurmoinc.framework.portlets.rules.*',
                'application.extensions.zurmoinc.framework.rules.*',
                'application.extensions.zurmoinc.framework.utils.*',
                'application.extensions.zurmoinc.framework.validators.*',
                'application.extensions.zurmoinc.framework.views.*',
                'application.extensions.zurmoinc.framework.widgets.*',
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
            ),
        )
    );

    foreach ($common_config['modules'] as $index => $moduleName)
    {
        //This is to handle nested modules in the config above.
        if (is_array($moduleName))
        {
            $moduleName = $index;
        }
        $common_config['import'][] = "application.modules.$moduleName.*";                           // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.adapters.*";                  // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.adapters.columns.*";          // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.dataproviders.*";             // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.elements.*";                  // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.elements.actions.*";          // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.elements.actions.security.*"; // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.elements.derived.*";          // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.components.*";                // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.controllers.*";               // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.controllers.filters.*";       // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.exceptions.*";                // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.forms.*";                     // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.forms.attributes.*";          // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.interfaces.*";                // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.jobs.*";                      // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.models.*";                    // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.modules.*";                   // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.rules.*";                     // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.rules.attributes.*";          // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.rules.policies.*";            // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.tests.unit.*";                // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.tests.unit.files.*";          // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.tests.unit.models.*";         // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.tests.unit.walkthrough.*";    // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.utils.*";                     // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.utils.charts.*";              // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.utils.sanitizers.*";          // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.utils.security.*";            // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.utils.analyzers.*";           // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.validators.*";                // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.views.*";                     // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.views.attributetypes.*";      // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.views.charts.*";              // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.views.related.*";             // Not Coding Standard
        $common_config['import'][] = "application.modules.$moduleName.widgets.*";                   // Not Coding Standard
    }

    // Add aliases here that are likely to only be specific to a particular module.

    $common_config['import'][] = "application.modules.designer.rules.*";                            // Not Coding Standard
    $common_config['import'][] = "application.modules.designer.rules.elements.*";                   // Not Coding Standard
    $common_config['import'][] = "application.modules.designer.elements.layoutsettings.*";          // Not Coding Standard
    $common_config['import'][] = "application.modules.designer.forms.attributes.*";                 // Not Coding Standard
    $common_config['import'][] = "application.modules.install.serviceHelpers.*";                    // Not Coding Standard
    $common_config['import'][] = "application.modules.zurmo.elements.security.*";                   // Not Coding Standard
    $common_config['import'][] = "application.modules.zurmo.utils.security.*";                      // Not Coding Standard
    $common_config['import'][] = "application.modules.zurmo.views.currency.*";                      // Not Coding Standard
    $common_config['import'][] = "application.modules.zurmo.views.language.*";                      // Not Coding Standard
    $common_config['import'][] = "application.modules.zurmo.views.security.*";                      // Not Coding Standard

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
    if (!isset($common_config['params']['testGoogleGeoCodeApiKey']))
    {
        $common_config['params']['testGoogleGeoCodeApiKey'] = null;
    }
    return $common_config;
?>
