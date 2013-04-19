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

    /**
     * Sanitizer that is for attributes that are user models.
     */
    class UserValueTypeSanitizerUtil extends ExternalSystemIdSuppportedSanitizerUtil
    {
        public static function getBatchAttributeValueDataAnalyzerType()
        {
            return 'UserValueType';
        }

        public static function getSqlAttributeValueDataAnalyzerType()
        {
            return 'UserValueType';
        }

        public static function getLinkedMappingRuleType()
        {
            return 'UserValueTypeModelAttribute';
        }

        public static function getUsernames()
        {
            $sql = 'select username from ' . User::getTableName('User');
            return R::getCol($sql);
        }

        public static function getUserIds()
        {
            $sql = 'select id from ' . User::getTableName('User');
            return R::getCol($sql);
        }

        public static function getUserExternalSystemIds()
        {
            $columnName = ExternalSystemIdUtil::EXTERNAL_SYSTEM_ID_COLUMN_NAME;
            RedBeanColumnTypeOptimizer::
            externalIdColumn(User::getTableName('User'), $columnName);
            $sql = 'select ' . $columnName . ' from ' . User::getTableName('User');
            return R::getCol($sql);
        }

        /**
         * Given a value that is either a zurmo user id, a username, or an external system user id, resolve that the
         * value is valid.  If the value is not valid then an InvalidValueToSanitizeException is thrown.
         * @param string $modelClassName
         * @param string $attributeName
         * @param mixed $value
         * @param array $mappingRuleData
         */
        public static function sanitizeValue($modelClassName, $attributeName, $value, $mappingRuleData)
        {
            assert('is_string($modelClassName)');
            assert('is_string($attributeName)');
            assert('$mappingRuleData["type"] == UserValueTypeModelAttributeMappingRuleForm::ZURMO_USER_ID ||
                    $mappingRuleData["type"] == UserValueTypeModelAttributeMappingRuleForm::EXTERNAL_SYSTEM_USER_ID ||
                    $mappingRuleData["type"] == UserValueTypeModelAttributeMappingRuleForm::ZURMO_USERNAME');
            if ($value == null)
            {
                return $value;
            }
            if ($mappingRuleData["type"] == UserValueTypeModelAttributeMappingRuleForm::ZURMO_USER_ID)
            {
                try
                {
                    if ((int)$value <= 0)
                    {
                        throw new InvalidValueToSanitizeException(Zurmo::t('ImportModule', 'The user id specified did not match any existing records.'));
                    }
                    return User::getById((int)$value);
                }
                catch (NotFoundException $e)
                {
                    throw new InvalidValueToSanitizeException(Zurmo::t('ImportModule', 'The user id specified did not match any existing records.'));
                }
            }
            elseif ($mappingRuleData["type"] == UserValueTypeModelAttributeMappingRuleForm::EXTERNAL_SYSTEM_USER_ID)
            {
                try
                {
                    return static::getModelByExternalSystemIdAndModelClassName($value, 'User');
                }
                catch (NotFoundException $e)
                {
                    throw new InvalidValueToSanitizeException(Zurmo::t('ImportModule', 'The other user id specified did not match any existing records.'));
                }
            }
            else
            {
                try
                {
                    return User::getByUsername(strtolower($value));
                }
                catch (NotFoundException $e)
                {
                    throw new InvalidValueToSanitizeException(Zurmo::t('ImportModule', 'The username specified did not match any existing records.'));
                }
            }
        }
    }
?>