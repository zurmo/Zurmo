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

    $common_config = array(
        'basePath'          => COMMON_ROOT . DIRECTORY_SEPARATOR . 'protected',
        'name'              => 'ZurmoCRM',
        'defaultController' => 'home/default',
        'sourceLanguage'    => 'en',

        'behaviors' => array(
            'onBeginRequest' => array(
                'class' => 'application.modules.zurmo.components.BeginRequestBehavior'
            ),
            'onEndRequest' => array(
                'class' => 'application.modules.zurmo.components.EndRequestBehavior'
            )
        ),

        'components' => array(
            'assetManager' => array(
                'basePath' => INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'assets/',
            ),
            'apiRequest' => array(
                'class' => 'application.modules.api.components.ApiRequest',
            ),
            'apiHelper' => array(
                'class' => 'application.modules.zurmo.components.ZurmoApiHelper',
            ),
            'browser' => array(
                'class'          => 'application.extensions.zurmoinc.framework.components.Browser',
            ),
            'clientScript' => array(
                'class' => 'ClientScript',
            ),
            'cache' => array(
                'class' => 'CMemCache',
                'servers' => $memcacheServers,
            ),
            'currencyHelper' => array(
                'class' => 'application.modules.zurmo.components.ZurmoCurrencyHelper',
                'baseCode' => 'USD',
                'serviceType' => 'GrandTrunk',
            ),
            'custom' => array(
                'class' => 'application.extensions.zurmoinc.framework.components.CustomManagement',
            ),
            'db' => array(
                'emulatePrepare' => true,
                'charset'        => 'utf8',
            ),
            'emailHelper' => array(
                'class'       => 'application.modules.zurmo.components.ZurmoEmailHelper',
            ),
            'errorHandler' => array(
                'errorAction' => 'zurmo/default/error',
            ),
            'format' => array(
                'class' => 'application.extensions.zurmoinc.framework.components.Formatter',
            ),
            'fusioncharts' => array(
                'class' => 'application.extensions.fusioncharts.fusionCharts',
            ),
            'minScript' => array(
                'class' => 'application.extensions.zurmoinc.framework.components.ZurmoExtMinScript',
                'groupMap' => array(
                    'css' => array(
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'themes/THEME_NAME/css/screen.css',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'themes/THEME_NAME/css/theme.css',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'themes/THEME_NAME/css/cgrid-view.css',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'themes/THEME_NAME/css/designer.css',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'themes/THEME_NAME/css/form.css',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'themes/THEME_NAME/css/jquery-ui.css',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'themes/THEME_NAME/css/main.css',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'themes/THEME_NAME/css/mbmenu.css',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'themes/THEME_NAME/css/widget-juiportlets.css',
                    ),

                    'js' => array(
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . '/../yii/framework/web/js/source/jquery.min.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . '/../yii/framework/web/js/source/jquery.yii.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . '/../yii/framework/web/js/source/jquery.ba-bbq.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . '/../yii/framework/web/js/source/jui/js/jquery-ui.min.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/qtip/assets/jquery.qtip-1.0.0-rc3.min.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/zurmoinc/framework/widgets/assets/fusionChart/jquery.fusioncharts.js',

                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/zurmoinc/framework/elements/assets/Modal.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/zurmoinc/framework/views/assets/FormUtils.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/zurmoinc/framework/views/assets/ListViewUtils.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/zurmoinc/framework/widgets/assets/rssReader/jquery.zrssfeed.min.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/zurmoinc/framework/widgets/assets/juiportlets/JuiPortlets.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/zurmoinc/framework/widgets/assets/jnotify/jquery.jnotify.js',


                    )
                )
            ),
            'languageHelper' => array(
                'class'          => 'application.modules.zurmo.components.ZurmoLanguageHelper',
            ),
            'log' => array(
                'class' => 'CLogRouter',
                'routes' => array(
                    array(
                        'class'  => 'CFileLogRoute',
                        'levels' => 'error, warning',
                    ),
                ),
            ),
            'pagination' => array(
                'class' => 'application.modules.zurmo.components.ZurmoPaginationHelper',
                'listPageSize'             => 10,
                'subListPageSize'          => 5,
                'modalListPageSize'        => 5,
                'massEditProgressPageSize' => 5,
                'autoCompleteListPageSize' => 5,
                'importPageSize'           => 50,
                'dashboardListPageSize'    => 5,
                'apiListPageSize'          => 10,
            ),
            'performance' => array(
                'class'          => 'application.extensions.zurmoinc.framework.components.PerformanceMeasurement',
            ),
            'sanitizer' => array(
                'class'          => 'application.extensions.esanitizer.ESanitizer',
                'sanitizeGet'    => false, //off for now
                'sanitizePost'   => false, //off for now
                'sanitizeCookie' => false, //off for now
            ),
            'session'=>array(
                'class'=>'application.modules.zurmo.components.ZurmoSession',
                'autoStart' => false,
            ),
            'themeManager' => array(
                'basePath' => INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'themes',
            ),
            'timeZoneHelper' => array(
                'class' => 'application.modules.zurmo.components.ZurmoTimeZoneHelper',
                'timeZone'             => 'America/Chicago',
            ),
            'request' => array(
                'enableCsrfValidation' => false,
                'enableCookieValidation' => false, //keep off until we can fix it on linux/windows servers.
            ),
            'urlManager' => array (
                'urlFormat' => 'path',
                'caseSensitive' => true,
                'showScriptName' => true,
                'rules'=>array(
                    // REST patterns
                    //array('api/rest/login',  'pattern'=>'api/rest/login',                'verb'=>'POST'),
                    //array('api/rest/logout', 'pattern'=>'api/rest/logout',               'verb'=>'GET'),
                    //array('api/rest/listCustomData', 'pattern'=>'api/rest/customData',       'verb'=>'GET'),
                    //array('api/rest/customData', 'pattern'=>'api/rest/customData/<model:\w+>', 'verb'=>'GET'),
                    //array('api/rest/list',   'pattern'=>'api/rest/<model:\w+>',          'verb'=>'GET'),
                    //array('api/rest/view',   'pattern'=>'api/rest/<model:\w+>/<id:\d+>', 'verb'=>'GET'),
                    //array('api/rest/list',   'pattern'=>'api/rest/<model:\w+>/*',          'verb'=>'GET'),
                    //array('api/rest/update', 'pattern'=>'api/rest/<model:\w+>/<id:\d+>', 'verb'=>'PUT'),
                    //array('api/rest/delete', 'pattern'=>'api/rest/<model:\w+>/<id:\d+>', 'verb'=>'DELETE'),
                    //array('api/rest/create', 'pattern'=>'api/rest/<model:\w+>',          'verb'=>'POST'),
                    '<module:\w+>/<controller:\w+>/<action:\w+>'=>'<module>/<controller>/<action>',
                )
            ),
            'user' => array(
                'allowAutoLogin' => true,
                'class'          => 'WebUser',
                'loginUrl'       => array('zurmo/default/login'),
            ),
            'widgetFactory' => array(
                'widgets' => array(
                    'EJuiDateTimePicker' => array(
                        'cssFile' => false,
                    ),
                    'JuiDatePicker' => array(
                        'cssFile' => false,
                    ),
                    'CJuiDialog' => array(
                        'cssFile' => false,
                    ),
                    'CJuiProgressBar' => array(
                        'cssFile' => false,
                    ),
                    'CJuiAutoComplete' => array(
                        'cssFile' => false,
                    ),
                    'JuiSortable' => array(
                        'cssFile' => false,
                    ),
                ),
            ),
        ),
        'controllerMap' => array(
            'min' => 'application.extensions.minscript.controllers.ExtMinScriptController',
        ),
        'import' => array(
            'application.extensions.zurmoinc.framework.adapters.*',
            'application.extensions.zurmoinc.framework.adapters.api.*',
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
        ),

        'modules' => array(
            'accounts',
            'activities',
            'api',
            'configuration',
            'contacts',
            'designer',
            'home',
            'import',
            'install',
            'jobsManager',
            'leads',
            'meetings',
            'notes',
            'notifications',
            'opportunities',
            'rssReader',
            'tasks',
            'zurmo' => array(
                'modules' => array(
                    'groups' => array('class' => 'zurmo.modules.GroupsModule'),
                    'roles'  => array('class' => 'zurmo.modules.RolesModule'),
                ),
            ),
            'users',
        ),

        'params' => array(
            'redBeanVersion'    => '1.3',
            'yiiVersion'        => '1.1.8',
            'supportedLanguages' => array(
                'en' => 'English',
                'es' => 'Spanish',
                'it' => 'Italian',
                'fr' => 'French',
                'de' => 'German',
            ),
        ),
        'preload' => array(
            'browser',
            'sanitizer'
        ),
    );

    // THIS IS LIKELY TO BE A PERFORMANCE ISSUE, SEARCHING SO MANY DIRECTORIES. TO BE INVESTIGATED.
    // Add aliases here that are likely to be useful in any module.
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
        $common_config['import'][] = "application.modules.$moduleName.tests.unit.controllers.*";    // Not Coding Standard
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
    return $common_config;
?>
