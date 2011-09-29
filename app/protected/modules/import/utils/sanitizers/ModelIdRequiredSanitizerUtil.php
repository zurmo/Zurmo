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
            $model = new $modelClassName(false);
            assert('$model->isRelation($attributeName)');
            $relationModelClassName = $model->getRelationModelClassName($attributeName);
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
                    throw new InvalidValueToSanitizeException(Yii::t('Default', 'The id specified did not match any existing records.'));
                }
                return $relationModel;
            }
            else
            {
                if (!$model->isAttributeRequired($attributeName))
                {
                    return $value;
                }
                throw new InvalidValueToSanitizeException(Yii::t('Default', 'This id is required and was not specified.'));
            }
        }
    }
?>