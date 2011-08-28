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
     * Provides similar functionality as the PostUtil except is designed specifically for handling posted
     * data from an ImportWizardForm.
     * @see PostUtil
     */
    class ImportWizardFormPostUtil
    {
        /**
         * Sanitize post data, specifically handling any date and date time conversions from local format to the
         * database format.
         * @param string $importRulesType
         * @param array $postMappingData
         */
        public static function sanitizePostByTypeForSavingMappingData($importRulesType, $postMappingData)
        {
            assert('is_string($importRulesType)');
            assert('is_array($postMappingData)');
            foreach ($postMappingData as $columnName => $mappingData)
            {
                if (!isset($mappingData['mappingRulesData']))
                {
                    continue;
                }
                foreach ($mappingData['mappingRulesData'] as $mappingRuleFormClassName => $mappingRuleFormData)
                {
                    $model = MappingRuleFormAndElementTypeUtil::
                             makeForm($importRulesType, $mappingData['attributeIndexOrDerivedType'],
                             $mappingRuleFormClassName);
                    foreach ($mappingRuleFormData as $attributeName => $value)
                    {
                        if ($value !== null)
                        {
                            if (!is_array($value))
                            {
                                if ($model->isAttribute($attributeName) && $model->isAttributeSafe($attributeName))
                                {
                                    $type = ModelAttributeToMixedTypeUtil::
                                            getTypeByModelUsingValidator($model, $model::getAttributeName());
                                    if ($type == 'Date')
                                    {
                                        $postMappingData[$columnName]
                                                        ['mappingRulesData']
                                                        [$mappingRuleFormClassName]
                                                        [$attributeName] = DateTimeUtil::resolveValueForDateDBFormatted($value);
                                    }
                                    if ($type == 'DateTime' && !empty($value))
                                    {
                                        $postMappingData[$columnName]
                                                        ['mappingRulesData']
                                                        [$mappingRuleFormClassName]
                                                        [$attributeName] =
                                                        DateTimeUtil::convertDateTimeLocaleFormattedDisplayToDbFormattedDateTimeWithSecondsAsZero($value);
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return $postMappingData;
        }
    }
?>