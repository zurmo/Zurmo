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
                'class' => 'ZurmoAssetManager',
                'basePath' => INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'assets/',
            ),
            'apiRequest' => array(
                'class' => 'application.modules.api.components.ApiRequest',
            ),
            'apiHelper' => array(
                'class' => 'application.modules.api.components.ZurmoApiHelper',
            ),
            'browser' => array(
                'class' => 'application.extensions.zurmoinc.framework.components.Browser',
            ),
            'clientScript' => array(
                'class' => 'ClientScript',
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
                'class'       => 'application.modules.emailMessages.components.EmailHelper',
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
            'gameHelper' => array(
                'class' => 'application.modules.gamification.components.GameHelper',
            ),
            'gamificationObserver' => array(
                'class' => 'application.modules.gamification.observers.GamificationObserver',
            ),
            'minScript' => array(
                'class' => 'application.extensions.zurmoinc.framework.components.ZurmoExtMinScript',
                'groupMap' => array(
                    'css' => array(
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'themes/THEME_NAME/css/theme.css',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'themes/THEME_NAME/css/cgrid-view.css',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'themes/THEME_NAME/css/designer.css',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'themes/THEME_NAME/css/form.css',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'themes/THEME_NAME/css/jquery-ui.css',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'themes/THEME_NAME/css/main.css',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'themes/THEME_NAME/css/mbmenu.css',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'themes/THEME_NAME/css/widget-juiportlets.css',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'themes/THEME_NAME/css/newui.css',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'themes/THEME_NAME/css/jquery-multiselect.css',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/zurmoinc/framework/widgets/assets/fileUpload/css/jquery.fileupload-ui.css',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/timepicker/assets/jquery-ui-timepicker-addon.css'
                    ),

                    'js' => array(
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . '/../yii/framework/web/js/source/jquery.min.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . '/../yii/framework/web/js/source/jquery.yii.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . '/../yii/framework/web/js/source/jquery.ba-bbq.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . '/../yii/framework/web/js/source/jui/js/jquery-ui.min.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/qtip/assets/jquery.qtip-1.0.0-rc3.min.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/zurmoinc/framework/widgets/assets/extendedGridView/jquery.yiigridview.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/zurmoinc/framework/widgets/assets/fusionChart/jquery.fusioncharts.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/zurmoinc/framework/elements/assets/Modal.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/zurmoinc/framework/views/assets/FormUtils.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/zurmoinc/framework/views/assets/ListViewUtils.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/zurmoinc/framework/views/assets/interactions.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/zurmoinc/framework/views/assets/dropDownInteractions.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/zurmoinc/framework/views/assets/jquery.dropkick-1.0.0.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/zurmoinc/framework/widgets/assets/rssReader/jquery.zrssfeed.min.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/zurmoinc/framework/widgets/assets/juiportlets/JuiPortlets.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/zurmoinc/framework/widgets/assets/jnotify/jquery.jnotify.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/zurmoinc/framework/widgets/assets/juiMultiSelect/jquery.multiselect.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/zurmoinc/framework/widgets/assets/fileUpload/jquery.fileupload.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/zurmoinc/framework/widgets/assets/fileUpload/jquery.fileupload-ui.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/zurmoinc/framework/widgets/assets/fileUpload/jquery.tmpl.min.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/zurmoinc/framework/widgets/assets/fileUpload/jquery.iframe-transport.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/timepicker/assets/jquery-ui-timepicker-addon.min.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/zurmoinc/framework/widgets/assets/calendar/Calendar.js'
                    )
                ),
                //Add scripts here that do not need to load when using an ajax request such as a modal search box.  The scripts
                //are already loaded in the minified script that loads on every page.
                'usingAjaxShouldNotIncludeJsPathAliasesAndFileNames' => array(
                    array('system.web.js.source',                                       '/jquery.min.js'),
                    array('system.web.js.source',                                       '/jquery.yii.js'),
                    array('system.web.js.source',                                       '/jquery.ba-bbq.js'),
                    array('system.web.js.source',                                       '/jui/js/jquery-ui.min.js'),
                    array('application.extensions.qtip.assets',                         '/jquery.qtip-1.0.0-rc3.min.js'),
                    array('application.extensions.zurmoinc.framework.widgets.assets',   '/extendedGridView/jquery.yiigridview.js'),
                    array('application.extensions.zurmoinc.framework.widgets.assets',   '/fusionChart/jquery.fusioncharts.js'),
                    array('application.extensions.zurmoinc.framework.elements.assets',  '/Modal.js'),
                    array('application.extensions.zurmoinc.framework.views.assets',     '/FormUtils.js'),
                    array('application.extensions.zurmoinc.framework.views.assets',     '/ListViewUtils.js'),
                    array('application.extensions.zurmoinc.framework.views.assets',     '/interactions.js'),
                    array('application.extensions.zurmoinc.framework.widgets.assets',   '/rssReader/jquery.zrssfeed.min.js'),
                    array('application.extensions.zurmoinc.framework.widgets.assets',   '/juiportlets/JuiPortlets.js'),
                    array('application.extensions.zurmoinc.framework.widgets.assets',   '/jnotify/jquery.jnotify.js'),
                    array('application.extensions.zurmoinc.framework.widgets.assets',   '/juiMultiSelect/jquery.multiselect.js'),
                    array('application.extensions.zurmoinc.framework.widgets.assets',   '/fileUpload/jquery.fileupload.js'),
                    array('application.extensions.zurmoinc.framework.widgets.assets',   '/fileUpload/jquery.fileupload-ui.js'),
                    array('application.extensions.zurmoinc.framework.widgets.assets',   '/fileUpload/jquery.tmpl.min.js'),
                    array('application.extensions.zurmoinc.framework.widgets.assets',   '/fileUpload/jquery.iframe-transport.js'),
                    array('application.extensions.timepicker.assets',                   '/jquery-ui-timepicker-addon.min.js'),
                    array('application.extensions.zurmoinc.framework.widgets.assets',   '/calendar/Calendar.js')
                ),
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
            'mappingHelper' => array(
                'class' => 'application.modules.maps.components.ZurmoMappingHelper',
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
            'session' => array(
                'class'     => 'application.modules.zurmo.components.ZurmoSession',
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
                'enableCsrfValidation' => true,
                'enableCookieValidation' => false, //keep off until we can fix it on linux/windows servers.
            ),
            'statePersister' => array(
                'class'     => 'application.modules.zurmo.components.ZurmoDbStatePersister',
            ),
            'urlManager' => array (
                'urlFormat' => 'path',
                'caseSensitive' => true,
                'showScriptName' => true,
                'rules' => array(
                    // API REST patterns
                    array('zurmo/api/logout',      'pattern' => 'zurmo/api/logout',                    'verb' => 'GET'),    // Not Coding Standard
                    array('<module>/api/read',     'pattern' => '<module:\w+>/api/read/<id:\d+>',      'verb' => 'GET'),    // Not Coding Standard
                    array('<module>/api/list',     'pattern' => '<module:\w+>/api/list/*',             'verb' => 'GET'),    // Not Coding Standard
                    array('<module>/api/update',   'pattern' => '<module:\w+>/api/update/<id:\d+>',    'verb' => 'PUT'),    // Not Coding Standard
                    array('<module>/api/delete',   'pattern' => '<module:\w+>/api/delete/<id:\d+>',    'verb' => 'DELETE'), // Not Coding Standard
                    array('<module>/api/create',   'pattern' => '<module:\w+>/api/create/',            'verb' => 'POST'),   // Not Coding Standard

                    array('zurmo/<model>Api/read', 'pattern' => 'zurmo/<model:\w+>/api/read/<id:\d+>', 'verb' => 'GET'),    // Not Coding Standard
                    array('zurmo/<model>Api/read', 'pattern' => 'zurmo/<model:\w+>/api/read/<id:\w+>', 'verb' => 'GET'),    // Not Coding Standard
                    array('zurmo/<model>Api/list', 'pattern' => 'zurmo/<model:\w+>/api/list/*',        'verb' => 'GET'),    // Not Coding Standard
                    '<module:\w+>/<controller:\w+>/<action:\w+>' => '<module>/<controller>/<action>',                       // Not Coding Standard
                )
            ),
            'user' => array(
                'allowAutoLogin' => true,
                'class'          => 'WebUser',
                'loginUrl'       => array('zurmo/default/login'),
        'behaviors' => array(
            'onAfterLogin' => array(
                'class' => 'application.modules.gamification.behaviors.WebUserAfterLoginGamificationBehavior'
            ),
        ),
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
            'application.modules.zurmo.components.BeginRequestBehavior',
            'application.extensions.zurmoinc.framework.utils.ArrayUtil',
            'application.extensions.zurmoinc.framework.utils.FileUtil',
            'application.extensions.zurmoinc.framework.utils.GeneralCache',
            'application.extensions.zurmoinc.framework.exceptions.NotFoundException',
            'application.modules.api.tests.unit.models.*',
            'application.modules.api.tests.unit.forms.*',
            'application.modules.install.serviceHelpers.MemcacheServiceHelper',
            'application.modules.install.serviceHelpers.ServiceHelper',
            'application.modules.install.utils.InstallUtil',
        ),

        'modules' => array(
            'accounts',
            'activities',
            'api',
            'configuration',
            'contacts',
            'designer',
            'emailMessages',
            'export',
            'gamification',
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
            'maps',
        ),

        'params' => array(
            'redBeanVersion'    => '1.3',
            'yiiVersion'        => '1.1.10',
            'memcacheServers'   => $memcacheServers,
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

    // Routes for api test
    $testApiConfig['components']['urlManager']['rules'] = array(
        array('api/<model>Api/read',     'pattern' => 'api/<model:\w+>/api/read/<id:\d+>',   'verb' => 'GET'),    // Not Coding Standard
        array('api/<model>Api/list',     'pattern' => 'api/<model:\w+>/api/list/*',          'verb' => 'GET'),    // Not Coding Standard
        array('api/<model>Api/update',   'pattern' => 'api/<model:\w+>/api/update/<id:\d+>', 'verb' => 'PUT'),    // Not Coding Standard
        array('api/<model>Api/delete',   'pattern' => 'api/<model:\w+>/api/delete/<id:\d+>', 'verb' => 'DELETE'), // Not Coding Standard
        array('api/<model>Api/create',   'pattern' => 'api/<model:\w+>/api/create/',         'verb' => 'POST'),   // Not Coding Standard
        array('api/<model>Api/<action>', 'pattern' => 'api/<model:\w+>/api/<action>/*'),                          // Not Coding Standard
    );

    $common_config = CMap::mergeArray($testApiConfig, $common_config);
    return $common_config;
?>
