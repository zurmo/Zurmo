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
     * Sanitizer for handling contact state. These are states that are the starting state or after.  Manages
     * if the state is required and the value is present.
     */
    class ContactStateRequiredSanitizerUtil extends RequiredSanitizerUtil
    {
        public static function getLinkedMappingRuleType()
        {
            return 'DefaultContactStateId';
        }

        /**
         * Contact state is required.  If the value provided is null then the sanitizer will attempt use a default
         * value if provided.  If this is missing then a InvalidValueToSanitizeException will be thrown.
         * @param string $modelClassName
         * @param string $attributeName
         * @param mixed $value
         * @param array $mappingRuleData
         */
        public static function sanitizeValue($modelClassName, $attributeName, $value, $mappingRuleData)
        {
            assert('is_string($modelClassName)');
            assert('$attributeName == null');
            assert('is_string($value) || $value == null || $value instanceof ContactState');
            $model                  = new $modelClassName(false);
            assert('$mappingRuleData["defaultStateId"] == null || is_string($mappingRuleData["defaultStateId"]) ||
                    is_int($mappingRuleData["defaultStateId"])');
            if ($value == null)
            {
                if ($mappingRuleData['defaultStateId'] != null)
                {
                    try
                    {
                       $state       = ContactState::getById((int)$mappingRuleData['defaultStateId']);
                    }
                    catch (NotFoundException $e)
                    {
                        throw new InvalidValueToSanitizeException(
                        Zurmo::t('ContactsModule', 'The default status specified does not exist.'));
                    }
                    return $state;
                }
                else
                {
                    throw new InvalidValueToSanitizeException(
                    Zurmo::t('ContactsModule', 'The status is required.  Neither a value nor a default was specified.'));
                }
            }
            return $value;
        }
    }
?>