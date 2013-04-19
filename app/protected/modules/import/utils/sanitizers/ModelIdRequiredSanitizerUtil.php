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
     * Sanitizer for resolving if an attribute is required or not and whether the value is present. Override to
     * handle attributes that are relation models specifically.
     */
    class ModelIdRequiredSanitizerUtil extends RequiredSanitizerUtil
    {
        public static function getLinkedMappingRuleType()
        {
            return 'DefaultModelNameId';
        }

        /**
         * Resolves that the value is not null or the value is null and a valid default value is available for
         * the model id. If not, then an InvalidValueToSanitizeException is thrown.
         * @param string $modelClassName
         * @param string $attributeName
         * @param mixed $value
         * @param array $mappingRuleData
         */
        public static function sanitizeValue($modelClassName, $attributeName, $value, $mappingRuleData)
        {
            assert('is_string($modelClassName)');
            assert('is_string($attributeName)');
            if ($value != null)
            {
                return $value;
            }
            assert('$modelClassName::isRelation($attributeName)');
            $relationModelClassName = $modelClassName::getRelationModelClassName($attributeName);
            assert('$value == null || $value instanceof $relationModelClassName');
            assert('$mappingRuleData["defaultModelId"] == null || is_string($mappingRuleData["defaultModelId"]) ||
                    is_int($mappingRuleData["defaultModelId"])');
            if ($mappingRuleData['defaultModelId'] != null)
            {
                try
                {
                   $relationModel       = $relationModelClassName::getById((int)$mappingRuleData['defaultModelId']);
                }
                catch (NotFoundException $e)
                {
                    throw new InvalidValueToSanitizeException(Zurmo::t('ImportModule', 'The id specified did not match any existing records.'));
                }
                return $relationModel;
            }
            else
            {
                $model = new $modelClassName(false);
                if (!$model->isAttributeRequired($attributeName))
                {
                    return $value;
                }
                throw new InvalidValueToSanitizeException(Zurmo::t('ImportModule', 'This id is required and was not specified.'));
            }
        }
    }
?>