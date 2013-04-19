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
     * Import rules for any attributes that are a CurrencyValue model.
     */
    class CurrencyValueAttributeImportRules extends NonDerivedAttributeImportRules
    {
        protected static function getAllModelAttributeMappingRuleFormTypesAndElementTypes()
        {
            return array('DefaultValueModelAttribute'       => 'Decimal',
                         'CurrencyIdModelAttribute'         => 'CurrencyDropDownForm',
                         'CurrencyRateToBaseModelAttribute' => 'Decimal');
        }

        public static function getSanitizerUtilTypesInProcessingOrder()
        {
            return array('Number', 'Required');
        }

        /**
         * There is a special way you can import rateToBase and currencyCode for an amount attribute.
         * if the column data is formatted like: $54.67__1.2__USD  then it will split the column and properly
         * handle rate and currency code.  Eventually this will be exposed in the user interface
         *
         * @param mixed $value
         * @param array $columnMappingData
         * @param ImportSanitizeResultsUtil $importSanitizeResultsUtil
         * @return array
         */
        public function resolveValueForImport($value, $columnMappingData, ImportSanitizeResultsUtil $importSanitizeResultsUtil)
        {
            $attributeNames = $this->getRealModelAttributeNames();
            $modelClassName = $this->getModelClassName();
            $parts          = explode(FormModelUtil::DELIMITER, $value);
            if (count($parts) == 3)
            {
                $value          = $parts[0];
                $rateToBase     = $parts[1];
                try
                {
                    $currency   = Currency::getByCode($parts[2]);
                }
                catch (NotFoundException $e)
                {
                    $currency   = null;
                    $importSanitizeResultsUtil->addMessage('Currency Code: ' . $parts[2] . ' is invalid.');
                    $importSanitizeResultsUtil->setModelShouldNotBeSaved();
                }
            }
            else
            {
                $rateToBase = $columnMappingData['mappingRulesData']['CurrencyRateToBaseModelAttributeMappingRuleForm']
                              ['rateToBase'];
                $currency   = Currency::getById((int)$columnMappingData['mappingRulesData']['CurrencyIdModelAttributeMappingRuleForm']['id']);
            }
            $sanitizedValue = ImportSanitizerUtil::
                              sanitizeValueBySanitizerTypes(static::getSanitizerUtilTypesInProcessingOrder(),
                                                            $modelClassName, $this->getModelAttributeName(),
                                                            $value, $columnMappingData, $importSanitizeResultsUtil);
            if ($sanitizedValue == null)
            {
                $sanitizedValue = 0;
            }
            $currencyValue             = new CurrencyValue();
            $currencyValue->setScenario('importModel');
            $currencyValue->value      = $sanitizedValue;
            $currencyValue->rateToBase = $rateToBase;
            $currencyValue->currency   = $currency;
            return array($this->getModelAttributeName() => $currencyValue);
        }
    }
?>