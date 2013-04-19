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

    abstract class SecurableModule extends Module
    {
        /**
         * Returns an array of names of rights applicable to the module.
         */
        public static function getRightsNames()
        {
            $rightNames = array();
            $reflectionClass = new ReflectionClass(get_called_class());
            foreach ($reflectionClass->getConstants() as $key => $value)
            {
                if (self::filterRight($key))
                {
                    $rightNames[] = $value;
                }
            }
            return $rightNames;
        }

        public static function getTranslatedRightsLabels()
        {
            return array();
        }

        /**
         * Returns an array of names of policies applicable to the module.
         */
        public static function getPolicyNames()
        {
            $policyNames = array();
            $reflectionClass = new ReflectionClass(get_called_class());
            foreach ($reflectionClass->getConstants() as $key => $value)
            {
                if (self::filterPolicy($key))
                {
                    $policyNames[] = $value;
                }
            }
            return $policyNames;
        }

        public static function getTranslatedPolicyLabels()
        {
            return array();
        }

        public static function getAuditEventNames()
        {
            $auditEventNames = array();
            $reflectionClass = new ReflectionClass(get_called_class());
            foreach ($reflectionClass->getConstants() as $key => $value)
            {
                if (self::filterAuditEvent($key))
                {
                    $auditEventNames[] = $value;
                }
            }
            return $auditEventNames;
        }

        public static function stringifyAuditEvent(AuditEvent $auditEvent, $format = 'long')
        {
            assert('$format == "long" || $format == "short"');
            return strval($auditEvent);
        }

        private static function filterRight($s)
        {
            return substr($s, 0, 6) == 'RIGHT_';
        }

        private static function filterPolicy($s)
        {
            return substr($s, 0, 7) == 'POLICY_';
        }

        private static function filterAuditEvent($s)
        {
            return substr($s, 0, 6) == 'AUDIT_EVENT_';
        }

        /**
         * @return array of Policy / PolicyRulesType pairings
         */
        public static function getPolicyRulesTypes()
        {
            return array();
        }

        public static function getPolicyDefault($policyName)
        {
            assert('is_string($policyName) && $policyName != ""');
            $className = get_called_class();
            $policyDefaults = $className::$policyDefaults;
            if (array_key_exists($policyName, $policyDefaults))
            {
                return $policyDefaults[$policyName];
            }
            return null;
        }

        public static function isPolicyYesNo($value)
        {
            return in_array($value, array(Policy::NO, Policy::YES));
        }

        /**
         * Override if the module has a right that determines
         * if a user can access the module tab/subtabs in the
         * user interface.
         * @return null or access right
         */
        public static function getAccessRight()
        {
            return null;
        }

        /**
         * Override if the module has a right that determines
         * if a user can create the models in this modules
         * @return null or access right
         */
        public static function getCreateRight()
        {
            return null;
        }

        /**
         * Override if the module has a right that determines
         * if a user can delete the models in this modules
         * @return null or access right
         */
        public static function getDeleteRight()
        {
            return null;
        }

        public static function getSecurableModuleDisplayName()
        {
            return static::getModuleLabelByTypeAndLanguage('Plural');
        }

        /**
         * Override and set to true if the module has a model that utilizes permissions.
         */
        public static function hasPermissions()
        {
            return false;
        }
    }
?>
