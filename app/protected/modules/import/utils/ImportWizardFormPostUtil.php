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
                    $postMappingData[$columnName]['mappingRulesData'] = array();
                }
            }
            foreach ($postMappingData as $columnName => $mappingData)
            {
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