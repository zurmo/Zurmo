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
            return array('NamedSecurableItem', 'GlobalMetadata', 'PerUserMetadata', 'Portlet', 'CustomFieldData');
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
                    if($format == 'short')
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
                    if($format == 'long')
                    {
                        $s             .= strval($auditEvent);
                        $s             .= ", $name";
                        $s             .= ', ' . yii::t('Default', 'Changed') . ' ';
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
                    $s .= ' ' . yii::t('Default', 'from') . ' ';
                    $s .= AuditUtil::stringifyValue($attributeModel, $attributeName, $oldValue, $format) . ' ';
                    $s .= yii::t('Default', 'to') . ' ';
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
    }
?>
