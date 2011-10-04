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

    abstract class SecurableModule extends Module
    {
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
    }
?>
