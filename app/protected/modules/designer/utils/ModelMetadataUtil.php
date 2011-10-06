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
     * Helper functionality for use in manipulating model metadata.
     */
    class ModelMetadataUtil
    {
        public static function addOrUpdateMember($modelClassName,
                                                 $memberName,
                                                 $attributeLabels,
                                                 $defaultValue,
                                                 $maxLength,
                                                 $minValue,
                                                 $maxValue,
                                                 $precision,
                                                 $isRequired,
                                                 $isAudited,
                                                 $elementType,
                                           array $partialTypeRule,
                                           array $mixedRule = null)
        {
            assert('is_string($modelClassName) && $modelClassName != ""');
            assert('is_string($memberName)     && $memberName != ""');
            assert('is_array($attributeLabels)');
            assert('$maxLength === null || is_int($maxLength)');
            assert('$precision === null || is_int($precision)');
            assert('is_bool($isRequired)');
            assert('is_bool($isAudited)');
            assert('$mixedRule === null || is_array($mixedRule)');
            $metadata = $modelClassName::getMetadata();
            assert('isset($metadata[$modelClassName])');
            if (!isset   (             $metadata[$modelClassName]['members']) ||
                !in_array($memberName, $metadata[$modelClassName]['members']))
            {
                $metadata[$modelClassName]['members'][] = $memberName;
            }
            static::resolveAddOrRemoveNoAuditInformation($isAudited, $metadata[$modelClassName], $memberName);
            $metadata[$modelClassName]['elements'][$memberName] = $elementType;
            self::resolveAttributeLabelsMetadata($attributeLabels, $metadata, $modelClassName, $memberName);
            self::addOrUpdateRules($modelClassName, $memberName, $defaultValue, $maxLength,
                                   $minValue, $maxValue, $precision, $isRequired, $partialTypeRule,
                                   $metadata, $mixedRule);
            $modelClassName::setMetadata($metadata);
        }

        /**
         * Updating existing relation attributes and add new has_one relations that are owned only.
         * Currently does not support setting the default value.
         */
        public static function addOrUpdateRelation($modelClassName,
                                              $relationName,
                                              $attributeLabels,
                                              $elementType,
                                              $isRequired,
                                              $isAudited,
                                              $relationModelClassName)
        {
            assert('is_string($modelClassName)      && $modelClassName != ""');
            assert('is_string($relationName)        && $relationName != ""');
            assert('is_array($attributeLabels)');
            assert('is_string($elementType)     && $elementType != ""');
            assert('is_bool($isRequired)');
            assert('is_bool($isAudited)');
            assert('is_string($relationModelClassName)     && $relationModelClassName != ""');
            $metadata = $modelClassName::getMetadata();
            assert('isset($metadata[$modelClassName])');
            if (!isset           (               $metadata[$modelClassName]['relations']) ||
                 !array_key_exists($relationName, $metadata[$modelClassName]['relations']))
            {
                //assumes HAS_ONE for now and RedBeanModel::OWNED.
                $metadata[$modelClassName]['relations'][$relationName] = array(
                                                                            RedBeanModel::HAS_ONE,
                                                                            $relationModelClassName,
                                                                            RedBeanModel::OWNED);
            }
            static::resolveAddOrRemoveNoAuditInformation($isAudited, $metadata[$modelClassName], $relationName);
            $metadata[$modelClassName]['elements'][$relationName] = $elementType;
            self::resolveAttributeLabelsMetadata($attributeLabels, $metadata, $modelClassName, $relationName);
            self::addOrUpdateRules($modelClassName, $relationName, null, null, null,
                                   null, null, $isRequired, array(), $metadata);

            $modelClassName::setMetadata($metadata);
        }

        public static function addOrUpdateCustomFieldRelation($modelClassName,
                                                              $relationName,
                                                              $attributeLabels,
                                                              $defaultValue,
                                                              $isRequired,
                                                              $isAudited,
                                                              $elementType,
                                                              $customFieldDataName,
                                                              $customFieldDataData = null)
        {
            assert('is_string($modelClassName)      && $modelClassName != ""');
            assert('is_string($relationName)        && $relationName != ""');
            assert('is_array($attributeLabels)');
            assert('is_bool($isRequired)');
            assert('is_bool($isAudited)');
            assert('is_string($customFieldDataName) && $customFieldDataName != ""');
            $metadata = $modelClassName::getMetadata();
            assert('isset($metadata[$modelClassName])');
            if (!isset           (               $metadata[$modelClassName]['relations']) ||
                 !array_key_exists($relationName, $metadata[$modelClassName]['relations']))
            {
                $metadata[$modelClassName]['relations'][$relationName] = array(RedBeanModel::HAS_ONE,  'CustomField');
            }
            $metadata[$modelClassName]['elements'][$relationName] = $elementType;
            self::resolveAttributeLabelsMetadata($attributeLabels, $metadata, $modelClassName, $relationName);
            if (!isset           (               $metadata[$modelClassName]['customFields']) ||
                 !array_key_exists($relationName, $metadata[$modelClassName]['customFields']))
            {
                $metadata[$modelClassName]['customFields'][$relationName] = $customFieldDataName;
            }
            static::resolveAddOrRemoveNoAuditInformation($isAudited, $metadata[$modelClassName], $relationName);
            if ($customFieldDataData !== null)
            {
                $customFieldData = CustomFieldData::getByName($customFieldDataName);
                $customFieldData->serializedData = serialize($customFieldDataData);
                $saved = $customFieldData->save();
                assert('$saved');
            }
            self::addOrUpdateRules($modelClassName, $relationName, $defaultValue, null, null,
                                   null, null, $isRequired, array(), $metadata);
            $modelClassName::setMetadata($metadata);
        }

        private static function addOrUpdateRules($modelClassName,
                                                 $attributeName,
                                                 $defaultValue,
                                                 $maxLength,
                                                 $minValue,
                                                 $maxValue,
                                                 $precision,
                                                 $isRequired,
                                           array $partialTypeRule,
                                                 &$metadata,
                                                 array $mixedRule = null)
        {
            assert('is_string($modelClassName) && $modelClassName != ""');
            assert('is_string($attributeName)  && $attributeName != ""');
            assert('$maxLength === null || is_int($maxLength)');
            assert('is_bool($isRequired)');
            assert('isset($metadata[$modelClassName])');
            assert('$mixedRule === null || is_array($mixedRule)');
            $defaultFound    = false;
            $lengthFound     = false;
            $numericalFound  = false;
            $requiredFound   = false;
            $typeRuleFound   = false;
            $mixedRuleFound  = false;
            if (isset($metadata[$modelClassName]['rules']))
            {
                $i = 0;
                while ($i < count($metadata[$modelClassName]['rules']))
                {
                    $rule = $metadata[$modelClassName]['rules'][$i];
                    if ($rule[0] == $attributeName)
                    {
                        switch ($rule[1])
                        {
                            case 'default':
                                if ($defaultValue !== null)
                                {
                                    $metadata[$modelClassName]['rules'][$i]['value'] = $defaultValue;
                                }
                                $defaultFound = true;
                                break;

                            case 'length':
                                if ($maxLength !== null)
                                {
                                    $metadata[$modelClassName]['rules'][$i]['max'] = $maxLength;
                                }
                                $lengthFound = true;
                                break;

                            case 'numerical':
                                if ($minValue !== null)
                                {
                                    $metadata[$modelClassName]['rules'][$i]['min'] = $minValue;
                                }
                                if ($maxValue !== null)
                                {
                                    $metadata[$modelClassName]['rules'][$i]['max'] = $maxValue;
                                }
                                if ($precision !== null)
                                {
                                    $metadata[$modelClassName]['rules'][$i]['precision'] = $precision;
                                }
                                $numericalFound = true;
                                break;

                            case 'required':
                                if (!$isRequired)
                                {
                                    unset($metadata[$modelClassName]['rules'][$i]);
                                }
                                $requiredFound = true;
                                continue;
                        }

                        if (count($partialTypeRule) > 0 && $rule[1] == $partialTypeRule[0])
                        {
                            if (count($partialTypeRule) > 1)
                            {
                                $typeRule = $partialTypeRule;
                                array_unshift($typeRule, $attributeName);
                                $metadata[$modelClassName]['rules'][$i] = $typeRule;
                            }
                            $typeRuleFound = true;
                        }
                        if (count($mixedRule) > 0 && $rule[1] == $mixedRule[0])
                        {
                            $mixedRuleToUpdate = $mixedRule;
                            array_unshift($mixedRuleToUpdate, $attributeName);
                            $metadata[$modelClassName]['rules'][$i] = $mixedRuleToUpdate;
                            $mixedRuleFound = true;
                        }
                    }
                    $i++;
                }
            }
            if (!$defaultFound && $defaultValue !== null)
            {
                $metadata[$modelClassName]['rules'][] = array($attributeName, 'default', 'value' => $defaultValue);
            }
            if (!$lengthFound && $maxLength !== null)
            {
                $metadata[$modelClassName]['rules'][] = array($attributeName, 'length', 'max' => $maxLength);
            }
            if (!$numericalFound && ($minValue !== null || $maxValue !== null || $precision !== null))
            {
                $rule = array($attributeName, 'numerical');
                if ($minValue !== null)
                {
                    $rule['min'] = $minValue;
                }
                if ($maxValue !== null)
                {
                    $rule['max'] = $maxValue;
                }
                if ($precision !== null)
                {
                    $rule['precision'] = $precision;
                }
                $metadata[$modelClassName]['rules'][] = $rule;
            }
            if (!$requiredFound && $isRequired)
            {
                $metadata[$modelClassName]['rules'][] = array($attributeName, 'required');
            }
            if (!$typeRuleFound && count($partialTypeRule) > 0)
            {
                $typeRule = $partialTypeRule;
                array_unshift($typeRule, $attributeName);
                $metadata[$modelClassName]['rules'][] = $typeRule;
            }
            if (!$mixedRuleFound && count($mixedRule) > 0)
            {
                $mixedRuleToAdd = $mixedRule;
                array_unshift($mixedRuleToAdd, $attributeName);
                $metadata[$modelClassName]['rules'][] = $mixedRuleToAdd;
            }
            // Fix indexes.
            $metadata[$modelClassName]['rules'] = array_values($metadata[$modelClassName]['rules']);
        }

        public static function removeAttribute($modelClassName,
                                               $attributeName)
        {
            $metadata = $modelClassName::getMetadata();
            if (isset($metadata[$modelClassName]['members']))
            {
                $i = array_search($attributeName, $metadata[$modelClassName]['members']);
                if ($i !== false)
                {
                    unset($metadata[$modelClassName]['members'][$i]);
                    // Fix indexes.
                    $metadata[$modelClassName]['members'] =
                        array_values($metadata[$modelClassName]['members']);
                }
            }
            if (isset($metadata[$modelClassName]['relations']))
            {
                unset($metadata[$modelClassName]['relations'][$attributeName]);
            }
            if (isset($metadata[$modelClassName]['noAudit']))
            {
                unset($metadata[$modelClassName]['noAudit'][$attributeName]);
            }
            $i = 0;
            while ($i < count($metadata[$modelClassName]['rules']))
            {
                $rule = $metadata[$modelClassName]['rules'][$i];
                if ($rule[0] == $attributeName)
                {
                    unset($metadata[$modelClassName]['rules'][$i]);
                    // Fix indexes.
                    $metadata[$modelClassName]['rules'] = array_values($metadata[$modelClassName]['rules']);
                    continue;
                }
                $i++;
            }
            $modelClassName::setMetadata($metadata);
        }

        protected static function resolveAddOrRemoveNoAuditInformation($isAudited, & $modelMetadata, $attributeName)
        {
            assert('is_bool($isAudited)');
            assert('is_array($modelMetadata)');
            assert('is_string($attributeName)');
            if (!$isAudited)
            {
                if (!isset($modelMetadata['noAudit']) || !in_array($attributeName, $modelMetadata['noAudit']))
                {
                    $modelMetadata['noAudit'][] = $attributeName;
                }
            }
            else
            {
                if (isset($modelMetadata['noAudit']) && in_array($attributeName, $modelMetadata['noAudit']))
                {
                    $key = array_search($attributeName, $modelMetadata['noAudit']);
                    unset($modelMetadata['noAudit'][$key]);
                    $modelMetadata['noAudit'] = array_values($modelMetadata['noAudit']);
                }
            }
        }

        /**
         * Given an array of attributeLabels, resolve that array into any existing attributeLabels in the metadata.
         * This is needed in case a language has been inactivated for example, we do not want to lose the translation.
         * @param array $attributeLabels
         * @param array $metadata
         * @param string $modelClassName
         * @param string $labelsAttributeName
         */
        protected static function resolveAttributeLabelsMetadata($attributeLabels, & $metadata,
                                                                 $modelClassName, $labelsAttributeName)
        {
            assert('is_array($attributeLabels)');
            assert('is_array($metadata)');
            assert('is_string($modelClassName)');
            assert('is_string($labelsAttributeName)');
            if(!isset($metadata[$modelClassName]['labels'][$labelsAttributeName]))
            {
                $metadata[$modelClassName]['labels'][$labelsAttributeName] = array();
            }
            $metadata[$modelClassName]['labels'][$labelsAttributeName] =
            array_merge($metadata[$modelClassName]['labels'][$labelsAttributeName], $attributeLabels);
        }
    }
?>
