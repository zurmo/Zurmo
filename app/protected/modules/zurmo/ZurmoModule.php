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

    class ZurmoModule extends SecurableModule
    {
        const ADMINISTRATION_CATEGORY_GENERAL = 1;

        const RIGHT_ACCESS_ADMINISTRATION         = 'Access Administration Tab';
        const RIGHT_BULK_WRITE                    = 'Mass Update';
        const RIGHT_ACCESS_GLOBAL_CONFIGURATION   = 'Access Global Configuration';
        const RIGHT_ACCESS_CURRENCY_CONFIGURATION = 'Access Currency Configuration';
        const RIGHT_BULK_DELETE                   = 'Mass Delete';

        const AUDIT_EVENT_ITEM_CREATED            = 'Item Created';
        const AUDIT_EVENT_ITEM_MODIFIED           = 'Item Modified';
        const AUDIT_EVENT_ITEM_DELETED            = 'Item Deleted';
        const AUDIT_EVENT_ITEM_VIEWED             = 'Item Viewed';

        public static function getTranslatedRightsLabels()
        {
            $labels                                             = array();
            $labels[self::RIGHT_ACCESS_ADMINISTRATION]          = Zurmo::t('ZurmoModule', 'Access Administration Tab');
            $labels[self::RIGHT_BULK_WRITE]                     = Zurmo::t('ZurmoModule', 'Mass Update');
            $labels[self::RIGHT_ACCESS_GLOBAL_CONFIGURATION]    = Zurmo::t('ZurmoModule', 'Access Global Configuration');
            $labels[self::RIGHT_ACCESS_CURRENCY_CONFIGURATION]  = Zurmo::t('ZurmoModule', 'Access Currency Configuration');
            $labels[self::RIGHT_BULK_DELETE]                    = Zurmo::t('ZurmoModule', 'Mass Delete');
            return $labels;
        }

        public function canDisable()
        {
            return false;
        }

        public function getDependencies()
        {
            return array();
        }

        public function getRootModelNames()
        {
            // ZurmoModule is a special case in that most of its models
            // are non-root models of things that root models in the other
            // modules, and because ZurmoModule is the root of the module
            // dependence hierarchy it needed concern itself, other than
            // with the models that are specific to itself.
            return array('ActiveLanguage', 'AuditEvent', 'NamedSecurableItem', 'GlobalMetadata', 'PerUserMetadata', 'Portlet',
                         'CustomFieldData', 'CalculatedDerivedAttributeMetadata', 'DropDownDependencyDerivedAttributeMetadata',
                         'SavedSearch', 'MessageSource', 'MessageTranslation');
        }

        public static function getDefaultMetadata()
        {
            $metadata = array();
            $metadata['global'] = array(
                'configureMenuItems' => array(
                    array(
                        'category'         => ZurmoModule::ADMINISTRATION_CATEGORY_GENERAL,
                        'titleLabel'       => "eval:Zurmo::t('ZurmoModule', 'Global Configuration')",
                        'descriptionLabel' => "eval:Zurmo::t('ZurmoModule', 'Manage Global Configuration')",
                        'route'            => '/zurmo/default/configurationEdit',
                        'right'            => self::RIGHT_ACCESS_GLOBAL_CONFIGURATION,
                    ),
                    array(
                        'category'         => ZurmoModule::ADMINISTRATION_CATEGORY_GENERAL,
                        'titleLabel'       => "eval:Zurmo::t('ZurmoModule', 'Currency Configuration')",
                        'descriptionLabel' => "eval:Zurmo::t('ZurmoModule', 'Manage Currency Configuration')",
                        'route'            => '/zurmo/currency/configurationList',
                        'right'            => self::RIGHT_ACCESS_CURRENCY_CONFIGURATION,
                    ),
                    array(
                        'category'         => ZurmoModule::ADMINISTRATION_CATEGORY_GENERAL,
                        'titleLabel'       => "eval:Zurmo::t('ZurmoModule', 'Languages')",
                        'descriptionLabel' => "eval:Zurmo::t('ZurmoModule', 'Manage Active Languages')",
                        'route'            => '/zurmo/language/configurationList',
                        'right'            => self::RIGHT_ACCESS_GLOBAL_CONFIGURATION,
                    ),
                    array(
                        'category'         => ZurmoModule::ADMINISTRATION_CATEGORY_GENERAL,
                        'titleLabel'       => "eval:Zurmo::t('ZurmoModule', 'Developer Tools')",
                        'descriptionLabel' => "eval:Zurmo::t('ZurmoModule', 'Access Developer Tools')",
                        'route'            => '/zurmo/development/',
                        'right'            => self::RIGHT_ACCESS_GLOBAL_CONFIGURATION,
                    ),
                    array(
                        'category'         => ZurmoModule::ADMINISTRATION_CATEGORY_GENERAL,
                        'titleLabel'       => "eval:Zurmo::t('ZurmoModule', 'Authentication Configuration')",
                        'descriptionLabel' => "eval:Zurmo::t('ZurmoModule', 'Manage Authentication Configuration')",
                        'route'            => '/zurmo/authentication/configurationEdit',
                        'right'            => self::RIGHT_ACCESS_GLOBAL_CONFIGURATION,
                    ),
                ),
                'headerMenuItems' => array(
                    array(
                        'label'  => "eval:Zurmo::t('ZurmoModule', 'Settings')",
                        'url'    => array('/configuration'),
                        'right'  => self::RIGHT_ACCESS_ADMINISTRATION,
                        'order'  => 6,
                        'mobile' => false,
                    ),
                    array(
                        'label'  => "eval:Zurmo::t('ZurmoModule', 'Need Support?')",
                        'url'    => 'http://www.zurmo.com/needSupport.php',
                        'order'  => 9,
                        'mobile' => true,
                    ),
                    array(
                        'label'  => "eval:Zurmo::t('ZurmoModule', 'About Zurmo')",
                        'url'    => array('/zurmo/default/about'),
                        'order'  => 10,
                        'mobile' => true,
                    ),
                ),
                'configureSubMenuItems' => array(
                    array(
                        'category'         => self::ADMINISTRATION_CATEGORY_GENERAL,
                        'titleLabel'       => "eval:Zurmo::t('ZurmoModule', 'LDAP Configuration')",
                        'descriptionLabel' => "eval:Zurmo::t('ZurmoModule', 'Manage LDAP Authentication')",
                        'route'            => '/zurmo/ldap/configurationEditLdap',
                        'right'            => self::RIGHT_ACCESS_GLOBAL_CONFIGURATION,
                    ),
                ),
                'adminTabMenuItemsModuleOrdering' => array(
                    'home',
                    'configuration',
                    'designer',
                    'import',
                    'groups',
                    'users',
                    'roles',
                    'workflows',
                ),
                'tabMenuItemsModuleOrdering' => array(
                    'home',
                    'mashableInbox',
                    'accounts',
                    'leads',
                    'contacts',
                    'opportunities',
                    'marketing',
                    'reports',
                )
            );
            return $metadata;
        }

        public static function stringifyAuditEvent(AuditEvent $auditEvent, $format = 'long')
        {
            assert('$format == "long" || $format == "short"');
            $s = null;
            switch ($auditEvent->eventName)
            {
                case self::AUDIT_EVENT_ITEM_CREATED:
                case self::AUDIT_EVENT_ITEM_DELETED:
                    if ($format == 'short')
                    {
                        return Zurmo::t('ZurmoModule', $auditEvent->eventName);
                    }
                    $s   .= strval($auditEvent);
                    $name = unserialize($auditEvent->serializedData);
                    $s   .= ", $name";
                    break;

                case self::AUDIT_EVENT_ITEM_MODIFIED:
                    list($name, $attributeNames, $oldValue, $newValue) = unserialize($auditEvent->serializedData);
                    $modelClassName = $auditEvent->modelClassName;
                    $model          = new $modelClassName();
                    if ($format == 'long')
                    {
                        $s             .= strval($auditEvent);
                        $s             .= ", $name";
                        $s             .= ', ' . Zurmo::t('ZurmoModule', 'Changed') . ' ';
                    }
                    $attributeModel = $model;
                    $attributeLabels = array();
                    for ($i = 0; $i < count($attributeNames); $i++)
                    {
                        $attributeName = $attributeNames[$i];
                        if (!$attributeModel instanceof RedBeanModels)
                        {
                            $attributeLabels[] = $attributeModel->getAttributeLabel($attributeName);
                        }
                        else
                        {
                            // TODO - auditing of related collections
                            $attributeLabels[] = 'Collection';
                            break;
                        }
                        if ($i < count($attributeNames) - 1)
                        {
                            $attributeModel = $attributeModel->$attributeName;
                        }
                    }
                    $s .= join(' ', $attributeLabels);
                    $s .= ' ' . Zurmo::t('ZurmoModule', 'from') . ' ';
                    $s .= AuditUtil::stringifyValue($attributeModel, $attributeName, $oldValue, $format) . ' ';
                    $s .= Zurmo::t('ZurmoModule', 'to') . ' ';
                    $s .= AuditUtil::stringifyValue($attributeModel, $attributeName, $newValue, $format);
                    break;
            }
            return $s;
        }

        public static function getDemoDataMakerClassNames()
        {
            return array('ZurmoDemoDataMaker');
        }

        public static function getDefaultDataMakerClassName()
        {
            return 'ZurmoDefaultDataMaker';
        }

        /**
        * When updates info are pulled from zurmo home.
        * @return $lastAttemptedInfoUpdateTimeStamp
        */
        public static function getLastAttemptedInfoUpdateTimeStamp()
        {
            $lastAttemptedInfoUpdateTimeStamp = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'lastAttemptedInfoUpdateTimeStamp');
            return $lastAttemptedInfoUpdateTimeStamp;
        }

        /**
         * Set $lastAttemptedInfoUpdateTimeStamp global configuration.
         * This function is called during execution of ZurmoModule::checkAndUpdateZurmoInfo()
         */
        public static function setLastAttemptedInfoUpdateTimeStamp()
        {
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'lastAttemptedInfoUpdateTimeStamp', time());
        }

        /**
         * Get last Zurmo Stable version from global configuration property.
         */
        public static function getLastZurmoStableVersion()
        {
            $lastZurmoStableVersion = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'lastZurmoStableVersion');
            return $lastZurmoStableVersion;
        }

        /**
         * Set lastZurmoStableVersion global pconfiguration property.
         * @param string $zurmoVersion
         */
        public static function setLastZurmoStableVersion($zurmoVersion)
        {
            assert('isset($zurmoVersion)');
            assert('$zurmoVersion != ""');
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'lastZurmoStableVersion', $zurmoVersion);
        }

        /**
         * Check if available zurmo updates has been checked within the last 7 days. If not, then perform
         * update and update the lastAttemptedInfoUpdateTimeStamp and lastZurmoStableVersion global configuration properties.
         * @param boolean $forceCheck - If true, it will ignore the last time the check was made
         */
        public static function checkAndUpdateZurmoInfo($forceCheck = false)
        {
            $lastAttemptedInfoUpdateTimeStamp = self::getLastAttemptedInfoUpdateTimeStamp();
            if ( $forceCheck || $lastAttemptedInfoUpdateTimeStamp == null ||
            (time() - $lastAttemptedInfoUpdateTimeStamp) > (7 * 24 * 60 * 60))
            {
                $headers = array(
                            'Accept: application/json',
                            'ZURMO_API_REQUEST_TYPE: REST',
                );
                $data = array(
                            'zurmoToken' => ZURMO_TOKEN,
                            'zurmoVersion' => VERSION,
                            'serializedData' => ''
                );

                if (isset($_SERVER['SERVER_ADDR']))
                {
                    $data['serverIpAddress'] = $_SERVER['SERVER_ADDR'];
                }

                if (isset($_SERVER['SERVER_NAME']))
                {
                    $data['serverName'] = $_SERVER['SERVER_NAME'];
                }

                if (isset($_SERVER['SERVER_SOFTWARE']))
                {
                    $data['serverSoftware'] = $_SERVER['SERVER_SOFTWARE'];
                }

                $response = ApiRestHelper::createApiCall('http://updates.zurmo.com/app/index.php/updatesManager/api/create', 'POST', $headers, array('data' => $data));
                $response = json_decode($response, true);
                if (ApiResponse::STATUS_SUCCESS == $response['status'])
                {
                    if (isset($response['data']['latestStableZurmoVersion']) && $response['data']['latestStableZurmoVersion'] != '')
                    {
                        self::setLastZurmoStableVersion($response['data']['latestStableZurmoVersion']);
                    }

                    $zurmoServiceHelper = new ZurmoServiceHelper();
                    if (!$zurmoServiceHelper->runCheckAndGetIfSuccessful())
                    {
                        $message                    = new NotificationMessage();
                        $message->textContent       = $zurmoServiceHelper->getMessage();
                        $rules = new NewZurmoVersionAvailableNotificationRules();
                        NotificationsUtil::submit($message, $rules);
                    }
                }
                self::setLastAttemptedInfoUpdateTimeStamp();
            }
        }
    }
?>
