<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    class Product extends OwnedSecurableItem
    {
        const OPEN_STAGE    = 'Open';

        /**
         * @param string $name
         * @return string
         */
        public static function getByName($name)
        {
            return self::getByNameOrEquivalent('name', $name);
        }

        /**
         * @return string
         */
        public function __toString()
        {
            try
            {
                if (trim($this->name) == '')
                {
                    return Zurmo::t('ProductsModule', '(Unnamed)');
                }
                return $this->name;
            }
            catch (AccessDeniedSecurityException $e)
            {
                return '';
            }
        }

        /**
         * @return string
         */
        public static function getModuleClassName()
        {
            return 'ProductsModule';
        }

        /**
         * @return bool
         */
        public static function canSaveMetadata()
        {
            return true;
        }

        /**
         * @param string $language
         * @return array
         */
        public static function translatedAttributeLabels($language)
        {
            $params = LabelUtil::getTranslationParamsForAllModules();
            return array_merge(parent::translatedAttributeLabels($language), array(
                'priceFrequency'    => Zurmo::t('ProductsModule', 'Price Frequency', $params, null, $language),
                'account'           => Zurmo::t('AccountsModule', 'AccountsModuleSingularLabel', $params, null, $language),
                'contact'           => Zurmo::t('ContactsModule', 'ContactsModuleSingularLabel', $params, null, $language),
                'opportunity'       => Zurmo::t('OpportunitiesModule', 'OpportunitiesModuleSingularLabel', $params, null, $language),
                'productTemplate'   => Zurmo::t('ProductTemplatesModule', 'Catalog Item', $params, null, $language),
                'productCategories' => Zurmo::t('ProductTemplatesModule', 'Product Categories', array(), null, $language),
                'sellPrice'         => Zurmo::t('ProductTemplatesModule', 'Sell Price', array(), null, $language),
                'stage'             => Zurmo::t('ProductsModule', 'Stage', array(), null, $language)
                ));
        }

        /**
         * @return array
         */
        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'name',
                    'description',
                    'quantity',
                    'priceFrequency', //In template it is priceFrequency which is not working here due to difference in type of item
                    'type'
                ),
                'relations' => array(
                    'account'           => array(RedBeanModel::HAS_ONE, 'Account'),
                    'contact'           => array(RedBeanModel::HAS_ONE, 'Contact'),
                    'opportunity'       => array(RedBeanModel::HAS_ONE, 'Opportunity'),
                    'productTemplate'   => array(RedBeanModel::HAS_ONE, 'ProductTemplate'),
                    'stage'             => array(RedBeanModel::HAS_ONE, 'OwnedCustomField', RedBeanModel::OWNED,
                                                RedBeanModel::LINK_TYPE_SPECIFIC, 'stage'),
                    'productCategories' => array(RedBeanModel::MANY_MANY, 'ProductCategory'),
                    'sellPrice'         => array(RedBeanModel::HAS_ONE,   'CurrencyValue',    RedBeanModel::OWNED,
                                                RedBeanModel::LINK_TYPE_SPECIFIC, 'sellPrice'),
                ),
                'rules' => array(
                    array('name',           'required'),
                    array('name',           'type',    'type' => 'string'),
                    array('name',           'length',  'min'  => 3, 'max' => 64),
                    array('description',    'type',    'type' => 'string'),
                    array('quantity',       'numerical',  'min' => 1),
                    array('quantity',       'type',    'type' => 'integer'),
                    array('stage',          'required'),
                    array('quantity',       'required'),
                    array('type',           'type',    'type' => 'integer'),
                    array('priceFrequency', 'type',    'type' => 'integer'),
                    array('sellPrice',      'required'),
                    array('type',           'required'),
                    array('priceFrequency', 'required'),
                ),
                'elements' => array(
                    'account'         => 'Account',
                    'contact'         => 'Contact',
                    'description'     => 'TextArea',
                    'opportunity'     => 'Opportunity',
                    'priceFrequency'  => 'ProductTemplatePriceFrequencyDropDown',
                    'productTemplate' => 'ProductTemplate',
                    'sellPrice'       => 'CurrencyValue',
                    'type'            => 'ProductTemplateTypeDropDown',
                ),
                'customFields' => array(
                    'stage'    => 'ProductStages',
                ),
                'defaultSortAttribute' => 'name',
                'noAudit' => array(
                ),
                'nonConfigurableAttributes' => array('priceFrequency', 'type', 'productTemplate')
            );
            return $metadata;
        }

        /**
         * @return bool
         */
        public static function isTypeDeletable()
        {
            return true;
        }

        /**
         * @return bool
         */
        public static function hasReadPermissionsOptimization()
        {
            return true;
        }

        /**
         * @return string
         */
        public static function getGamificationRulesType()
        {
            return 'ProductGamification';
        }

        /**
         * Sets the scenario for currencyvalue elements to positiveValue for the validation of the price
         * using the rule in CurrencyValue
         * @return bool
         */
        protected function beforeValidate()
        {
            $this->sellPrice->setScenario('positiveValue');
            return parent::beforeValidate();
        }
    }
?>