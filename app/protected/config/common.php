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
        'defaultController' => 'home/default/welcome',
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
            'apiRequest' => array(
                'class' => 'application.modules.api.components.ApiRequest',
            ),
            'apiHelper' => array(
                'class' => 'application.modules.api.components.ZurmoApiHelper',
            ),
            'assetManager' => array(
                'class' => 'ZurmoAssetManager',
                'basePath' => INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'assets/',
            ),
            'browser' => array(
                'class' => 'application.core.components.Browser',
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
                'class' => 'application.core.components.CustomManagement',
            ),
            'db' => array(
                'emulatePrepare' => true,
                'charset'        => 'utf8',
            ),
            'emailHelper' => array(
                'class'       => 'application.modules.emailMessages.components.EmailHelper',
            ),
            'authenticationHelper' => array(
                'class'       => 'application.modules.zurmo.components.ZurmoAuthenticationHelper',
            ),
            'errorHandler' => array(
                'errorAction' => 'zurmo/default/error',
            ),
            'format' => array(
                'class' => 'application.core.components.Formatter',
            ),
            'imap' => array(
                'class'       => 'application.modules.emailMessages.components.ZurmoImap',
            ),
            'gameHelper' => array(
                'class' => 'application.modules.gamification.components.GameHelper',
            ),
            'gamificationObserver' => array(
                'class' => 'application.modules.gamification.observers.GamificationObserver',
            ),
            /* Will be enabled later
            'messages' => array(
                'class' => 'application.core.components.ZurmoMessageSource',
            ),
            */
            'minScript' => array(
                'class' => 'application.core.components.ZurmoExtMinScript',
                'groupMap' => array(
                    'css' => array(
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'themes/THEME_NAME/css/newui.css',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/timepicker/assets/jquery-ui-timepicker-addon.css'
                    ),

                    'js' => array(
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . '../yii/framework/web/js/source/jquery.min.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . '../yii/framework/web/js/source/jquery.yii.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . '../yii/framework/web/js/source/jquery.ba-bbq.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . '../yii/framework/web/js/source/jui/js/jquery-ui.min.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . '../yii/framework/web/js/source/jquery.yiiactiveform.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/qtip/assets/jquery.qtip-2.min.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/core/widgets/assets/extendedGridView/jquery.yiigridview.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/core/elements/assets/Modal.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/core/views/assets/dynamicSearchViewUtils.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/core/views/assets/FormUtils.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/core/views/assets/ListViewUtils.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/core/views/assets/interactions.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/core/views/assets/dropDownInteractions.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/core/views/assets/jquery.truncateText.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/core/widgets/assets/rssReader/jquery.zrssfeed.min.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/core/widgets/assets/juiportlets/JuiPortlets.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/core/widgets/assets/jnotify/jquery.jnotify.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/core/widgets/assets/juiMultiSelect/jquery.multiselect.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/core/widgets/assets/fileUpload/jquery.fileupload.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/core/widgets/assets/fileUpload/jquery.fileupload-ui.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/core/widgets/assets/fileUpload/jquery.tmpl.min.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/core/widgets/assets/fileUpload/jquery.iframe-transport.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/timepicker/assets/jquery-ui-timepicker-addon.min.js',
                        INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/core/widgets/assets/calendar/Calendar.js'
                    )
                ),
                //Add scripts here that do not need to load when using an ajax request such as a modal search box.  The scripts
                //are already loaded in the minified script that loads on every page.
                'usingAjaxShouldNotIncludeJsPathAliasesAndFileNames' => array(
                    array('system.web.js.source',                                       '/jquery.min.js'),
                    array('system.web.js.source',                                       '/jquery.yii.js'),
                    array('system.web.js.source',                                       '/jquery.ba-bbq.js'),
                    array('system.web.js.source',                                       '/jui/js/jquery-ui.min.js'),
                    array('system.web.js.source',                                       '/jquery.yiiactiveform.js'),
                    array('application.extensions.qtip.assets',                         '/jquery.qtip-2.min.js'),
                    array('application.core.widgets.assets',   '/extendedGridView/jquery.yiigridview.js'),
                    array('application.core.elements.assets',  '/Modal.js'),
                    array('application.core.views.assets',     '/FormUtils.js'),
                    array('application.core.views.assets',     '/dynamicSearchViewUtils.js'),
                    array('application.core.views.assets',     '/ListViewUtils.js'),
                    array('application.core.views.assets',     '/interactions.js'),
                    array('application.core.views.assets',     '/jquery.truncateText.js'),
                    array('application.core.widgets.assets',   '/rssReader/jquery.zrssfeed.min.js'),
                    array('application.core.widgets.assets',   '/juiportlets/JuiPortlets.js'),
                    array('application.core.widgets.assets',   '/jnotify/jquery.jnotify.js'),
                    array('application.core.widgets.assets',   '/juiMultiSelect/jquery.multiselect.js'),
                    array('application.core.widgets.assets',   '/fileUpload/jquery.fileupload.js'),
                    array('application.core.widgets.assets',   '/fileUpload/jquery.fileupload-ui.js'),
                    array('application.core.widgets.assets',   '/fileUpload/jquery.tmpl.min.js'),
                    array('application.core.widgets.assets',   '/fileUpload/jquery.iframe-transport.js'),
                    array('application.extensions.timepicker.assets',                   '/jquery-ui-timepicker-addon.min.js'),
                    array('application.core.widgets.assets',   '/calendar/Calendar.js')
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
                'listPageSize'               => 10,
                'subListPageSize'            => 5,
                'modalListPageSize'          => 5,
                'massEditProgressPageSize'   => 5,
                'autoCompleteListPageSize'   => 5,
                'importPageSize'             => 50,
                'dashboardListPageSize'      => 5,
                'apiListPageSize'            => 10,
                'massDeleteProgressPageSize' => 5
            ),
            'performance' => array(
                'class'          => 'application.core.components.PerformanceMeasurement',
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
                'basePath'  => INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'themes',
                'class'     => 'application.core.components.ThemeManager',
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
                    array('zurmo/api/logout',                   'pattern' => 'zurmo/api/logout',                              'verb' => 'GET'),    // Not Coding Standard
                    array('<module>/<model>Api/read',           'pattern' => '<module:\w+>/<model:\w+>/api/read/<id:\d+>',    'verb' => 'GET'),    // Not Coding Standard
                    array('<module>/<model>Api/read',           'pattern' => '<module:\w+>/<model:\w+>/api/read/<id:\w+>',    'verb' => 'GET'),    // Not Coding Standard
                    array('<module>/<model>Api/list',           'pattern' => '<module:\w+>/<model:\w+>/api/list/*',           'verb' => 'GET'),    // Not Coding Standard
                    array('<module>/<model>Api/list',           'pattern' => '<module:\w+>/<model:\w+>/api/list/',            'verb' => 'POST'),    // Not Coding Standard
                    array('<module>/<model>Api/update',         'pattern' => '<module:\w+>/<model:\w+>/api/update/<id:\d+>',  'verb' => 'PUT'),    // Not Coding Standard
                    array('<module>/<model>Api/delete',         'pattern' => '<module:\w+>/<model:\w+>/api/delete/<id:\d+>',  'verb' => 'DELETE'), // Not Coding Standard
                    array('<module>/<model>Api/create',         'pattern' => '<module:\w+>/<model:\w+>/api/create/',          'verb' => 'POST'),   // Not Coding Standard
                    array('<module>/<model>Api/<action>',       'pattern' => '<module:\w+>/<model:\w+>/api/<action>/*'),                           // Not Coding Standard

                    '<module:\w+>/<controller:\w+>/<action:\w+>' => '<module>/<controller>/<action>',                       // Not Coding Standard
                )
            ),
            'user' => array(
                'allowAutoLogin' => true,
                'class'          => 'WebUser',
                'loginUrl'       => array('zurmo/default/login'),
                'loginRequiredAjaxResponse' => 'sessionTimeout',
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
            'application.core.utils.ArrayUtil',
            'application.core.utils.FileUtil',
            'application.core.utils.ZurmoCache',
            'application.core.utils.GeneralCache',
            'application.core.exceptions.NotFoundException',
            'application.core.components.ZurmoLocale',
            'application.core.utils.Zurmo',
            'application.modules.api.tests.unit.models.*',
            'application.modules.api.tests.unit.forms.*',
            'application.modules.install.serviceHelpers.MemcacheServiceHelper',
            'application.modules.install.serviceHelpers.ServiceHelper',
            'application.modules.install.serviceHelpers.SetIncludePathServiceHelper',
            'application.modules.install.utils.InstallUtil',
        ),

        'modules' => array(
            'accounts',
            'activities',
            'api',
            'comments',
            'configuration',
            'contacts',
            'conversations',
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
            'missions',
            'notes',
            'notifications',
            'opportunities',
            'rssReader',
            'socialItems',
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
            'redBeanVersion'    => '3.2',
            'yiiVersion'        => '1.1.13',
            'memcacheServers'   => $memcacheServers,
            'supportedLanguages' => array(
                'en' => 'English',
                'es' => 'Spanish',
                'it' => 'Italian',
                'fr' => 'French',
                'de' => 'German',
            ),
            'sentryDsn'    => 'http://5232100222bc4404b368026413df2d9a:47f7a2f1542348d68bea7b00f2261ede@sentry.zurmo.com/2',
        ),
        'preload' => array(
            'browser',
            'sanitizer',
            'log'
        ),
    );
    return $common_config;
?>
