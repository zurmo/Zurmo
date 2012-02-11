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
                'enableCsrfValidation' => false,
                'enableCookieValidation' => false, //keep off until we can fix it on linux/windows servers.
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
            'application.extensions.zurmoinc.framework.adapters.RedBeanModelSelectQueryAdapter',
            'application.extensions.zurmoinc.framework.adapters.RedBeanModelJoinTablesQueryAdapter',
            'application.extensions.zurmoinc.framework.adapters.StateMetadataAdapter',
            'application.extensions.zurmoinc.framework.adapters.YiiToJqueryUIDatePickerLocalization',
            'application.extensions.zurmoinc.framework.adapters.ZurmoExtScriptToMinifyVendorAdapter',
            'application.extensions.zurmoinc.framework.adapters.columns.CheckBoxListViewColumnAdapter',
            'application.extensions.zurmoinc.framework.adapters.columns.CurrencyValueListViewColumnAdapter',
            'application.extensions.zurmoinc.framework.adapters.columns.DateListViewColumnAdapter',
            'application.extensions.zurmoinc.framework.adapters.columns.DateTimeListViewColumnAdapter',
            'application.extensions.zurmoinc.framework.adapters.columns.DecimalListViewColumnAdapter',
            'application.extensions.zurmoinc.framework.adapters.columns.DropDownListViewColumnAdapter',
            'application.extensions.zurmoinc.framework.adapters.columns.EmailAddressInformationListViewColumnAdapter',
            'application.extensions.zurmoinc.framework.adapters.columns.FullNameListViewColumnAdapter',
            'application.extensions.zurmoinc.framework.adapters.columns.IntegerListViewColumnAdapter',
            'application.extensions.zurmoinc.framework.adapters.columns.ListViewColumnAdapter',
            'application.extensions.zurmoinc.framework.adapters.columns.MultiSelectDropDownListViewColumnAdapter',
            'application.extensions.zurmoinc.framework.adapters.columns.PhoneListViewColumnAdapter',
            'application.extensions.zurmoinc.framework.adapters.columns.RadioDropDownListViewColumnAdapter',
            'application.extensions.zurmoinc.framework.adapters.columns.TextAreaListViewColumnAdapter',
            'application.extensions.zurmoinc.framework.adapters.columns.TextListViewColumnAdapter',
            'application.extensions.zurmoinc.framework.adapters.columns.UrlListViewColumnAdapter',
            'application.extensions.zurmoinc.framework.adapters.columns.UserListViewColumnAdapter',
            'application.extensions.zurmoinc.framework.adapters.dataproviders.ChartDataProvider',
            'application.extensions.zurmoinc.framework.adapters.dataproviders.ChartDataProviderFactory',
            'application.extensions.zurmoinc.framework.adapters.dataproviders.DataProviderMetadataAdapter',
            'application.extensions.zurmoinc.framework.adapters.dataproviders.SearchDataProviderMetadataAdapter',
            'application.extensions.zurmoinc.framework.components.Browser',
            'application.extensions.zurmoinc.framework.components.CheckBoxColumn',
            'application.extensions.zurmoinc.framework.components.ClientScript',
            'application.extensions.zurmoinc.framework.components.ConsoleApplication',
            'application.extensions.zurmoinc.framework.components.CustomManagement',
            'application.extensions.zurmoinc.framework.components.Formatter',
            'application.extensions.zurmoinc.framework.components.LinkPager',
            'application.extensions.zurmoinc.framework.components.MappingHelper',
            'application.extensions.zurmoinc.framework.components.PerformanceMeasurement',
            'application.extensions.zurmoinc.framework.components.SequentialProcess',
            'application.extensions.zurmoinc.framework.components.WebApplication',
            'application.extensions.zurmoinc.framework.components.ZurmoAssetManager',
            'application.extensions.zurmoinc.framework.components.ZurmoExtMinScript',
            'application.extensions.zurmoinc.framework.components.ZurmoFileHelper',
            'application.extensions.zurmoinc.framework.controllers.Controller',
            'application.extensions.zurmoinc.framework.controllers.PortletController',
            'application.extensions.zurmoinc.framework.data.DefaultDataMaker',
            'application.extensions.zurmoinc.framework.data.DemoDataHelper',
            'application.extensions.zurmoinc.framework.data.DemoDataMaker',
            'application.extensions.zurmoinc.framework.dataproviders.RedBeanModelDataProvider',
            'application.extensions.zurmoinc.framework.dataproviders.RedBeanModelsDataProvider',
            'application.extensions.zurmoinc.framework.elements.BooleanStaticDropDownElement',
            'application.extensions.zurmoinc.framework.elements.CheckBoxElement',
            'application.extensions.zurmoinc.framework.elements.CollectionElement',
            'application.extensions.zurmoinc.framework.elements.DateDefaultValueStaticDropDownElement',
            'application.extensions.zurmoinc.framework.elements.DateElement',
            'application.extensions.zurmoinc.framework.elements.DateTimeDefaultValueStaticDropDownElement',
            'application.extensions.zurmoinc.framework.elements.DateTimeElement',
            'application.extensions.zurmoinc.framework.elements.DateTimeUserElement',
            'application.extensions.zurmoinc.framework.elements.DecimalElement',
            'application.extensions.zurmoinc.framework.elements.DropDownElement',
            'application.extensions.zurmoinc.framework.elements.EditableDropDownCollectionElement',
            'application.extensions.zurmoinc.framework.elements.Element',
            'application.extensions.zurmoinc.framework.elements.EmailAddressInformationElement',
            'application.extensions.zurmoinc.framework.elements.IntegerElement',
            'application.extensions.zurmoinc.framework.elements.MixedDateTypesForSearchElement',
            'application.extensions.zurmoinc.framework.elements.ModelElement',
            'application.extensions.zurmoinc.framework.elements.ModelsElement',
            'application.extensions.zurmoinc.framework.elements.MultiSelectDropDownElement',
            'application.extensions.zurmoinc.framework.elements.NullElement',
            'application.extensions.zurmoinc.framework.elements.PasswordElement',
            'application.extensions.zurmoinc.framework.elements.PhoneElement',
            'application.extensions.zurmoinc.framework.elements.RadioDropDownElement',
            'application.extensions.zurmoinc.framework.elements.ReadOnlyElement',
            'application.extensions.zurmoinc.framework.elements.ReadOnlyModelElement',
            'application.extensions.zurmoinc.framework.elements.RelatedAttributeArrayDropDownElement',
            'application.extensions.zurmoinc.framework.elements.StaticDropDownElement',
            'application.extensions.zurmoinc.framework.elements.StaticDropDownFormElement',
            'application.extensions.zurmoinc.framework.elements.TextAreaElement',
            'application.extensions.zurmoinc.framework.elements.TextElement',
            'application.extensions.zurmoinc.framework.elements.UrlElement',
            'application.extensions.zurmoinc.framework.elements.actions.ActionElement',
            'application.extensions.zurmoinc.framework.elements.actions.AjaxLinkActionElement',
            'application.extensions.zurmoinc.framework.elements.actions.ButtonActionElement',
            'application.extensions.zurmoinc.framework.elements.actions.CancelLinkActionElement',
            'application.extensions.zurmoinc.framework.elements.actions.CancelToListLinkActionElement',
            'application.extensions.zurmoinc.framework.elements.actions.CreateFromRelatedListLinkActionElement',
            'application.extensions.zurmoinc.framework.elements.actions.DeleteLinkActionElement',
            'application.extensions.zurmoinc.framework.elements.actions.DetailsLinkActionElement',
            'application.extensions.zurmoinc.framework.elements.actions.EditLinkActionElement',
            'application.extensions.zurmoinc.framework.elements.actions.LinkActionElement',
            'application.extensions.zurmoinc.framework.elements.actions.ListLinkActionElement',
            'application.extensions.zurmoinc.framework.elements.actions.RelatedListLinkActionElement',
            'application.extensions.zurmoinc.framework.elements.actions.SaveButtonActionElement',
            'application.extensions.zurmoinc.framework.elements.actions.SelectFromRelatedListAjaxLinkActionElement',
            'application.extensions.zurmoinc.framework.elements.actions.SubmitButtonActionElement',
            'application.extensions.zurmoinc.framework.elements.derived.DateTimeCreatedUserElement',
            'application.extensions.zurmoinc.framework.elements.derived.DateTimeModifiedUserElement',
            'application.extensions.zurmoinc.framework.elements.derived.FullNameElement',
            'application.extensions.zurmoinc.framework.elements.derived.NameIdElement',
            'application.extensions.zurmoinc.framework.elements.derived.TitleFullNameElement',
            'application.extensions.zurmoinc.framework.exceptions.BadPasswordException',
            'application.extensions.zurmoinc.framework.exceptions.BulkInsertFailedException',
            'application.extensions.zurmoinc.framework.exceptions.ExitException',
            'application.extensions.zurmoinc.framework.exceptions.FailedAssertionException',
            'application.extensions.zurmoinc.framework.exceptions.FailedDatabaseSchemaChangeException',
            'application.extensions.zurmoinc.framework.exceptions.FailedFileUploadException',
            'application.extensions.zurmoinc.framework.exceptions.FileNotWriteableException',
            'application.extensions.zurmoinc.framework.exceptions.MissingBeanException',
            'application.extensions.zurmoinc.framework.exceptions.NoRowsInTableException',
            'application.extensions.zurmoinc.framework.exceptions.NotFoundException',
            'application.extensions.zurmoinc.framework.exceptions.NotImplementedException',
            'application.extensions.zurmoinc.framework.exceptions.NotSupportedException',
            'application.extensions.zurmoinc.framework.exceptions.RedirectException',
            'application.extensions.zurmoinc.framework.forms.ConfigurationForm',
            'application.extensions.zurmoinc.framework.forms.ModelForm',
            'application.extensions.zurmoinc.framework.forms.NoRequiredsActiveForm',
            'application.extensions.zurmoinc.framework.forms.SearchForm',
            'application.extensions.zurmoinc.framework.forms.ZurmoActiveForm',
            'application.extensions.zurmoinc.framework.interfaces.CollectionAttributeFormInterface',
            'application.extensions.zurmoinc.framework.interfaces.DerivedElementInterface',
            'application.extensions.zurmoinc.framework.interfaces.ElementActionTypeInterface',
            'application.extensions.zurmoinc.framework.interfaces.PortletViewInterface',
            'application.extensions.zurmoinc.framework.models.BaseCustomField',
            'application.extensions.zurmoinc.framework.models.ConfigurableMetadataModel',
            'application.extensions.zurmoinc.framework.models.CustomField',
            'application.extensions.zurmoinc.framework.models.CustomFieldData',
            'application.extensions.zurmoinc.framework.models.CustomFieldsModel',
            'application.extensions.zurmoinc.framework.models.FileContent',
            'application.extensions.zurmoinc.framework.models.GlobalMetadata',
            'application.extensions.zurmoinc.framework.models.PerUserMetadata',
            'application.extensions.zurmoinc.framework.models.RedBean_Plugin_Optimizer_Blob',
            'application.extensions.zurmoinc.framework.models.RedBean_Plugin_Optimizer_Boolean',
            'application.extensions.zurmoinc.framework.models.RedBean_Plugin_Optimizer_Date',
            'application.extensions.zurmoinc.framework.models.RedBean_Plugin_Optimizer_ExternalSystemId',
            'application.extensions.zurmoinc.framework.models.RedBean_Plugin_Optimizer_Id',
            'application.extensions.zurmoinc.framework.models.RedBeanAfterUpdateHintManager',
            'application.extensions.zurmoinc.framework.models.RedBeanBeforeUpdateHintManager',
            'application.extensions.zurmoinc.framework.models.RedBeanDatabase',
            'application.extensions.zurmoinc.framework.models.RedBeanDbCriteria',
            'application.extensions.zurmoinc.framework.models.RedBeanManyToManyRelatedModels',
            'application.extensions.zurmoinc.framework.models.RedBeanModel',
            'application.extensions.zurmoinc.framework.models.RedBeanModels',
            'application.extensions.zurmoinc.framework.models.RedBeanModelsCache',
            'application.extensions.zurmoinc.framework.models.RedBeanMutableRelatedModels',
            'application.extensions.zurmoinc.framework.models.RedBeanOneToManyRelatedModels',
            'application.extensions.zurmoinc.framework.models.RedBeansCache',
            'application.extensions.zurmoinc.framework.models.RedBeanSort',
            'application.extensions.zurmoinc.framework.models.RedBeanSqlExecuteManager',
            'application.extensions.zurmoinc.framework.models.ZurmoRedBeanPluginQueryLogger',
            'application.extensions.zurmoinc.framework.modules.Module',
            'application.extensions.zurmoinc.framework.portlets.Portlet',
            'application.extensions.zurmoinc.framework.portlets.rulesMyListPortletRules',
            'application.extensions.zurmoinc.framework.portlets.rulesPortletRules',
            'application.extensions.zurmoinc.framework.portlets.rulesPortletRulesFactory',
            'application.extensions.zurmoinc.framework.portlets.rulesRelatedListPortletRules',
            'application.extensions.zurmoinc.framework.rules.EditAndDetailsViewAttributeRules',
            'application.extensions.zurmoinc.framework.rules.MixedDateTimeTypesSearchFormAttributeMappingRules',
            'application.extensions.zurmoinc.framework.rules.MixedDateTypesSearchFormAttributeMappingRules',
            'application.extensions.zurmoinc.framework.rules.OwnedItemsOnlySearchFormAttributeMappingRules',
            'application.extensions.zurmoinc.framework.rules.SearchFormAttributeMappingRules',
            'application.extensions.zurmoinc.framework.utils.ArrayUtil',
            'application.extensions.zurmoinc.framework.utils.AssertUtil',
            'application.extensions.zurmoinc.framework.utils.BooleanUtil',
            'application.extensions.zurmoinc.framework.utils.CurrencyServiceUtil',
            'application.extensions.zurmoinc.framework.utils.CustomFieldDataModelUtil',
            'application.extensions.zurmoinc.framework.utils.CustomFieldDataUtil',
            'application.extensions.zurmoinc.framework.utils.DatabaseCompatibilityUtil',
            'application.extensions.zurmoinc.framework.utils.DataUtil',
            'application.extensions.zurmoinc.framework.utils.DateTimeCalculatorUtil',
            'application.extensions.zurmoinc.framework.utils.DateTimeUtil',
            'application.extensions.zurmoinc.framework.utils.DebugUtil',
            'application.extensions.zurmoinc.framework.utils.DefaultDataUtil',
            'application.extensions.zurmoinc.framework.utils.DemoDataUtil',
            'application.extensions.zurmoinc.framework.utils.FormModelUtil',
            'application.extensions.zurmoinc.framework.utils.GeneralCache',
            'application.extensions.zurmoinc.framework.utils.GetUtil',
            'application.extensions.zurmoinc.framework.utils.GoogleGeoCodeUtil',
            'application.extensions.zurmoinc.framework.utils.GrandTrunkCurrencyServiceUtil',
            'application.extensions.zurmoinc.framework.utils.GroupedAttributeCountUtil',
            'application.extensions.zurmoinc.framework.utils.LabelUtil',
            'application.extensions.zurmoinc.framework.utils.MessageLogger',
            'application.extensions.zurmoinc.framework.utils.MessageStreamer',
            'application.extensions.zurmoinc.framework.utils.MessageUtil',
            'application.extensions.zurmoinc.framework.utils.MetadataUtil',
            'application.extensions.zurmoinc.framework.utils.ModelListLinkProvider',
            'application.extensions.zurmoinc.framework.utils.ModelAttributeToCastTypeUtil',
            'application.extensions.zurmoinc.framework.utils.ModelAttributeToMixedApiTypeUtil',
            'application.extensions.zurmoinc.framework.utils.ModelAttributeToMixedTypeUtil',
            'application.extensions.zurmoinc.framework.utils.ModelAttributeToOperatorTypeUtil',
            'application.extensions.zurmoinc.framework.utils.ModelDataProviderUtil',
            'application.extensions.zurmoinc.framework.utils.PostUtil',
            'application.extensions.zurmoinc.framework.utils.RandomDataUtil',
            'application.extensions.zurmoinc.framework.utils.RedBeanModelDataProviderUtil',
            'application.extensions.zurmoinc.framework.utils.RedBeanModelErrorsToMessagesUtil',
            'application.extensions.zurmoinc.framework.utils.RuntimeUtil',
            'application.extensions.zurmoinc.framework.utils.SearchFormAttributesToSearchDataProviderMetadataUtil',
            'application.extensions.zurmoinc.framework.utils.SearchUtil',
            'application.extensions.zurmoinc.framework.utils.SelectFromRelatedEditModalListLinkProvider',
            'application.extensions.zurmoinc.framework.utils.SelectFromRelatedListModalListLinkProvider',
            'application.extensions.zurmoinc.framework.utils.SQLOperatorUtil',
            'application.extensions.zurmoinc.framework.utils.SQLQueryUtil',
            'application.extensions.zurmoinc.framework.utils.TableUtil',
            'application.extensions.zurmoinc.framework.utils.TextUtil',
            'application.extensions.zurmoinc.framework.utils.UploadedFileUtil',
            'application.extensions.zurmoinc.framework.utils.WebServiceXCurrencyServiceUtil',
            'application.extensions.zurmoinc.framework.utils.ZurmoCurrencyCodes',
            'application.extensions.zurmoinc.framework.utils.ZurmoHtml',
            'application.extensions.zurmoinc.framework.utils.ZurmoMimeTypes',
            'application.extensions.zurmoinc.framework.validators.RedBeanModelCompareDateTimeValidator',
            'application.extensions.zurmoinc.framework.validators.RedBeanModelDateTimeDefaultValueValidator',
            'application.extensions.zurmoinc.framework.validators.RedBeanModelDefaultValueValidator',
            'application.extensions.zurmoinc.framework.validators.RedBeanModelNumberValidator',
            'application.extensions.zurmoinc.framework.validators.RedBeanModelReadOnlyValidator',
            'application.extensions.zurmoinc.framework.validators.RedBeanModelRequiredValidator',
            'application.extensions.zurmoinc.framework.validators.RedBeanModelTypeValidator',
            'application.extensions.zurmoinc.framework.validators.RedBeanModelUniqueValidator',
            'application.extensions.zurmoinc.framework.validators.StringValidatorHelper',
            'application.extensions.zurmoinc.framework.validators.TypeValidator',
            'application.extensions.zurmoinc.framework.views.AjaxPageView',
            'application.extensions.zurmoinc.framework.views.CompareSequentialProcessView',
            'application.extensions.zurmoinc.framework.views.ConfigurableMetadataView',
            'application.extensions.zurmoinc.framework.views.ConfigurationView',
            'application.extensions.zurmoinc.framework.views.ContainedViewCompleteSequentialProcessView',
            'application.extensions.zurmoinc.framework.views.DetailsView',
            'application.extensions.zurmoinc.framework.views.DetailsViewFormLayout',
            'application.extensions.zurmoinc.framework.views.EditAndDetailsView',
            'application.extensions.zurmoinc.framework.views.EditView',
            'application.extensions.zurmoinc.framework.views.FormLayout',
            'application.extensions.zurmoinc.framework.views.GridView',
            'application.extensions.zurmoinc.framework.views.InlineEditView',
            'application.extensions.zurmoinc.framework.views.ListView',
            'application.extensions.zurmoinc.framework.views.MassEditProgressView',
            'application.extensions.zurmoinc.framework.views.MassEditView',
            'application.extensions.zurmoinc.framework.views.MetadataView',
            'application.extensions.zurmoinc.framework.views.ModalConfigEditView',
            'application.extensions.zurmoinc.framework.views.ModalListView',
            'application.extensions.zurmoinc.framework.views.ModalView',
            'application.extensions.zurmoinc.framework.views.ModelView',
            'application.extensions.zurmoinc.framework.views.MyListView',
            'application.extensions.zurmoinc.framework.views.NullView',
            'application.extensions.zurmoinc.framework.views.PageView',
            'application.extensions.zurmoinc.framework.views.PortletFrameView',
            'application.extensions.zurmoinc.framework.views.PortletRefreshView',
            'application.extensions.zurmoinc.framework.views.ProcessView',
            'application.extensions.zurmoinc.framework.views.ProgressView',
            'application.extensions.zurmoinc.framework.views.RelatedListView',
            'application.extensions.zurmoinc.framework.views.SearchView',
            'application.extensions.zurmoinc.framework.views.SequentialProcessContainerView',
            'application.extensions.zurmoinc.framework.views.SequentialProcessView',
            'application.extensions.zurmoinc.framework.views.SequentialProcessViewFactory',
            'application.extensions.zurmoinc.framework.views.TitleBarView',
            'application.extensions.zurmoinc.framework.views.View',
            'application.extensions.zurmoinc.framework.widgets.ClipWidget',
            'application.extensions.zurmoinc.framework.widgets.DesignerLayoutEditor',
            'application.extensions.zurmoinc.framework.widgets.ExtendedGridView',
            'application.extensions.zurmoinc.framework.widgets.FileUpload',
            'application.extensions.zurmoinc.framework.widgets.FusionChart',
            'application.extensions.zurmoinc.framework.widgets.JNotify',
            'application.extensions.zurmoinc.framework.widgets.JuiDatePicker',
            'application.extensions.zurmoinc.framework.widgets.JuiPortlets',
            'application.extensions.zurmoinc.framework.widgets.JuiPortlet',
            'application.extensions.zurmoinc.framework.widgets.JuiSortable',
            'application.extensions.zurmoinc.framework.widgets.MbMenu',
            'application.extensions.zurmoinc.framework.widgets.MultiSelectAutoComplete',
            'application.extensions.zurmoinc.framework.widgets.RssReader',
            'application.extensions.zurmoinc.framework.widgets.SortableCompareLists',
            'application.extensions.zurmoinc.framework.widgets.ZurmoWidget',


        'application.modules.accounts.forms.AccountsSearchForm',
        'application.modules.accounts.models.Account',
        'application.modules.accounts.views.AccountsSearchView',
        'application.modules.accounts.views.AccountsListView',
        'application.modules.accounts.views.AccountsPageView',

        'application.modules.contacts.models.Contact',

        'application.modules.designer.adapters.ModelAttributesAdapter',
        'application.modules.designer.utils.ModelAttributeCollectionUtil',
        'application.modules.designer.utils.ModelAttributeToDesignerTypeUtil',

        'application.modules.notifications.models.Notification',
        'application.modules.opportunities.models.Opportunity',

        'application.modules.users.models.User',
        'application.modules.users.validators.validateTimeZone',
'application.modules.users.validators.UsernameLengthValidator',


        'application.modules.zurmo.components.WebUser',
        'application.modules.zurmo.components.ZurmoBaseController',
        'application.modules.zurmo.components.ZurmoModuleController',
        'application.modules.zurmo.elements.actions.FilteredListEditLinkActionElement',
        'application.modules.zurmo.forms.OwnedSearchForm',
        'application.modules.zurmo.models.Item',
        'application.modules.zurmo.models.Right',
        'application.modules.zurmo.models.Permitable',
        'application.modules.zurmo.models.OwnedCustomField',
        'application.modules.zurmo.models.Currency',
        'application.modules.zurmo.models.OwnedModel',
        'application.modules.zurmo.models.OwnedSecurableItem',
        'application.modules.zurmo.models.SecurableItem',
        'application.modules.zurmo.models.Permission',
        'application.modules.zurmo.models.Person',
        'application.modules.zurmo.models.Email',
        'application.modules.zurmo.models.Address',
        'application.modules.zurmo.models.NamedSecurableItem',
        'application.modules.zurmo.modules.SecurableModule',
        'application.modules.zurmo.modules.GroupsModule',
        'application.modules.zurmo.modules.RolesModule',
        'application.modules.zurmo.ZurmoModule',



        'application.modules.zurmo.views.SearchFilterListView',
        'application.modules.zurmo.views.ZurmoSearchView',
        'application.modules.zurmo.views.FilteredListView',
        'application.modules.zurmo.views.SecuredListView',
        'application.modules.zurmo.views.ZurmoPageView',
        'application.modules.zurmo.views.ZurmoDefaultView',
        'application.modules.zurmo.views.HeaderView',
        'application.modules.zurmo.views.HeaderLinksView',
        'application.modules.zurmo.views.GlobalSearchAndRecentlyViewedView',
        'application.modules.zurmo.views.MenuView',
        'application.modules.zurmo.views.FlashMessageView',
        'application.modules.zurmo.views.ModalContainerView',
        'application.modules.zurmo.views.FooterView',
        'application.modules.zurmo.views.DropDownShortcutsMenuView',

        'application.modules.zurmo.utils.RightsCache',
        'application.modules.zurmo.utils.AuditUtil',
        'application.modules.zurmo.utils.MenuUtil',
        'application.modules.zurmo.utils.security.PermissionsUtil',
        'application.modules.zurmo.utils.security.RightsUtil',
        'application.modules.zurmo.utils.PermissionsCache',




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
            'maps',
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
    $common_config['import'][] = "application.modules.api.adapters.api.*";                          // Not Coding Standard
    $common_config['import'][] = "application.modules.api.tests.unit.forms.*";                      // Not Coding Standard

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
