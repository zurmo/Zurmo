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

    class ZurmoModule extends SecurableModule
    {
        const ADMINISTRATION_CATEGORY_GENERAL = 1;

        const RIGHT_ACCESS_ADMINISTRATION         = 'Access Administration Tab';
        const RIGHT_BULK_WRITE                    = 'Mass Update';
        const RIGHT_ACCESS_GLOBAL_CONFIGURATION   = 'Access Global Configuration';
        const RIGHT_ACCESS_CURRENCY_CONFIGURATION = 'Access Currency Configuration';

        const AUDIT_EVENT_ITEM_CREATED            = 'Item Created';
        const AUDIT_EVENT_ITEM_MODIFIED           = 'Item Modified';
        const AUDIT_EVENT_ITEM_DELETED            = 'Item Deleted';
        const AUDIT_EVENT_ITEM_VIEWED             = 'Item Viewed';

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
            return array('NamedSecurableItem', 'GlobalMetadata', 'PerUserMetadata', 'Portlet', 'CustomFieldData',
                         'CalculatedDerivedAttributeMetadata', 'DropDownDependencyDerivedAttributeMetadata');
        }

        public static function getDefaultMetadata()
        {
            $metadata = array();
            $metadata['global'] = array(
                'configureMenuItems' => array(
                    array(
                        'category'         => ZurmoModule::ADMINISTRATION_CATEGORY_GENERAL,
                        'titleLabel'       => 'Global Configuration',
                        'descriptionLabel' => 'Manage Global Configuration',
                        'route'            => '/zurmo/default/configurationEdit',
                        'right'            => self::RIGHT_ACCESS_GLOBAL_CONFIGURATION,
                    ),
                    array(
                        'category'         => ZurmoModule::ADMINISTRATION_CATEGORY_GENERAL,
                        'titleLabel'       => 'Currency Configuration',
                        'descriptionLabel' => 'Manage Currency Configuration',
                        'route'            => '/zurmo/currency/configurationList',
                        'right'            => self::RIGHT_ACCESS_CURRENCY_CONFIGURATION,
                    ),
                    array(
                        'category'         => ZurmoModule::ADMINISTRATION_CATEGORY_GENERAL,
                        'titleLabel'       => 'Languages',
                        'descriptionLabel' => 'Manage Active Languages',
                        'route'            => '/zurmo/language/configurationList',
                        'right'            => self::RIGHT_ACCESS_GLOBAL_CONFIGURATION,
                    ),
                ),
                'headerMenuItems' => array(
                    array(
                        'label' => 'Your Profile',
                        'route' => 'users/default/profile',
                    ),
                    array(
                        'label' => 'Admin',
                        'route' => 'configuration',
                        'right' => self::RIGHT_ACCESS_ADMINISTRATION,
                    ),
                    array(
                        'label' => 'About',
                        'route' => 'zurmo/default/about',
                    ),
                    array(
                        'label' => 'Logout',
                        'route' => 'zurmo/default/logout',
                    ),
                ),
                'tabMenuItemsModuleOrdering' => array(
                    'home',
                    'accounts',
                    'leads',
                    'contacts',
                    'opportunities'
                ),
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
                        return Yii::t('Default', $auditEvent->eventName);
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
                        $s             .= ', ' . Yii::t('Default', 'Changed') . ' ';
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
                    $s .= ' ' . Yii::t('Default', 'from') . ' ';
                    $s .= AuditUtil::stringifyValue($attributeModel, $attributeName, $oldValue, $format) . ' ';
                    $s .= Yii::t('Default', 'to') . ' ';
                    $s .= AuditUtil::stringifyValue($attributeModel, $attributeName, $newValue, $format);
                    break;
            }
            return $s;
        }

        public static function getDemoDataMakerClassName()
        {
            return 'ZurmoDemoDataMaker';
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
                            'zurmoToken' => ZurmoModule::getZurmoToken(),
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

        /**
         * Get the global configuration value - Zurmo token which is used to indentify installation.
         * @return string - $zurmoToken.
         */
        public static function getZurmoToken()
        {
            if (null != $zurmoToken = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'zurmoToken'))
            {
                return $zurmoToken;
            }
            else
            {
                $zurmoToken = self::setZurmoToken();
                return $zurmoToken;
            }
        }

        /**
         * Set Zurmo token.
         */
        public static function setZurmoToken($zurmoToken = null)
        {
            if (!isset($zurmoToken) || !is_int($zurmoToken))
            {
                $zurmoToken = mt_rand( 1000000000 , 9999999999 );
            }

            ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'zurmoToken', $zurmoToken);
            return $zurmoToken;
        }
    }
?>
