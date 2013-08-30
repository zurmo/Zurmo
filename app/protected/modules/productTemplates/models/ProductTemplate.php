<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class ProductTemplate extends Item
    {
        const TYPE_PRODUCT      = 1;

        const TYPE_SERVICE      = 2;

        const TYPE_SUBSCRIPTION = 3;

        const STATUS_INACTIVE   = 1;

        const STATUS_ACTIVE     = 2;

        const PRICE_FREQUENCY_ONE_TIME  = 1;

        const PRICE_FREQUENCY_MONTHLY   = 2;

        const PRICE_FREQUENCY_ANNUALLY  = 3;

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
                    return Zurmo::t('ProductTemplatesModule', '(Unnamed)');
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
            return 'ProductTemplatesModule';
        }

        /**
         * @return bool
         */
        public static function canSaveMetadata()
        {
            return true;
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
                    'priceFrequency',
                    'status',
                    'type'
                ),
                'relations' => array(
                    'products'                  => array(RedBeanModel::HAS_MANY, 'Product'),
                    'sellPriceFormula'          => array(RedBeanModel::HAS_ONE,   'SellPriceFormula', RedBeanModel::OWNED),
                    'productCategories'         => array(RedBeanModel::MANY_MANY, 'ProductCategory'),
                    'cost'                      => array(RedBeanModel::HAS_ONE,   'CurrencyValue',    RedBeanModel::OWNED,
                                                RedBeanModel::LINK_TYPE_SPECIFIC, 'cost'),
                    'listPrice'                 => array(RedBeanModel::HAS_ONE,   'CurrencyValue',    RedBeanModel::OWNED,
                                                RedBeanModel::LINK_TYPE_SPECIFIC, 'listPrice'),
                    'sellPrice'                 => array(RedBeanModel::HAS_ONE,   'CurrencyValue',    RedBeanModel::OWNED,
                                                RedBeanModel::LINK_TYPE_SPECIFIC, 'sellPrice'),
                ),
                'rules' => array(
                    array('name',             'required'),
                    array('name',             'type',    'type' => 'string'),
                    array('name',             'length',  'min'  => 3, 'max' => 255),
                    array('description',      'type',    'type' => 'string'),
                    array('status',           'required'),
                    array('type',             'required'),
                    array('status',           'type',    'type' => 'integer'),
                    array('type',             'type',    'type' => 'integer'),
                    array('priceFrequency',   'type',    'type' => 'integer'),
                    array('cost',             'required'),
                    array('listPrice',        'required'),
                    array('sellPrice',        'required'),
                    array('sellPriceFormula', 'required'),
                    array('priceFrequency',   'required'),
                ),
                'elements' => array(
                    'product'             => 'Product',
                    'description'         => 'TextArea',
                    'cost'                => 'CurrencyValue',
                    'listPrice'           => 'CurrencyValue',
                    'sellPrice'           => 'CurrencyValue',
                    'type'                => 'ProductTemplateTypeDropDown',
                    'status'              => 'ProductTemplateStatusDropDown',
                    'priceFrequency'      => 'ProductTemplatePriceFrequencyDropDown',
                    'sellPriceFormula'    => 'SellPriceFormulaInformation'

                ),
                'customFields' => array(
                ),
                'defaultSortAttribute' => 'name',
                'noAudit' => array(
                ),
                'nonConfigurableAttributes' => array('sellPriceFormula', 'priceFrequency', 'type')
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
         * @return string
         */
        public static function getRollUpRulesType()
        {
            return 'ProductTemplate';
        }

        /**
         * @return string
         */
        public static function getGamificationRulesType()
        {
            return 'ProductTemplateGamification';
        }

        /**
         * @return bool
         */
        protected function beforeDelete()
        {
            if ($this->getScenario() != 'autoBuildDatabase')
            {
                parent::beforeDelete();
                if (count($this->products) == 0 )
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return parent::beforeDelete();
            }
        }

        /**
         * @return array
         */
        protected static function translatedAttributeLabels($language)
        {
            $params = LabelUtil::getTranslationParamsForAllModules();
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'productTemplate'       => Zurmo::t('ProductTemplatesModule', 'ProductTemplatesModuleSingularLabel',  $params, null, $language),
                    'products'              => Zurmo::t('ProductTemplatesModule', 'ProductsModulePluralLabel',  $params, null, $language),
                    'sellPriceFormula'      => Zurmo::t('ProductTemplatesModule', 'Sell Price Formula',  array(), null, $language),
                    'productCategories'     => ProductCategory::getModelLabelByTypeAndLanguage('Plural', $language),
                    'cost'                  => Zurmo::t('ProductTemplatesModule', 'Cost',  array(), null, $language),
                    'listPrice'             => Zurmo::t('ProductTemplatesModule', 'List Price',  array(), null, $language),
                    'sellPrice'             => Zurmo::t('ProductTemplatesModule', 'Sell Price',  array(), null, $language),
                    'type'                  => Zurmo::t('ProductTemplatesModule', 'Type',  array(), null, $language),
                    'status'                => Zurmo::t('ProductTemplatesModule', 'Status',  array(), null, $language),
                )
            );
        }

        /**
         * Returns the display name for the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getLabel($language = null)
        {
            return Zurmo::t('ProductTemplatesModule', 'Catalog Item', array(), null, $language);
        }

        /**
         * Returns the display name for plural of the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel($language = null)
        {
            return Zurmo::t('ProductTemplatesModule', 'Catalog Items', array(), null, $language);
        }

        /**
         * Sets the scenario for currencyvalue elements to positiveValue for the validation of the price
         * using the rule in CurrencyValue
         * @return bool
         */
        protected function beforeValidate()
        {
            $this->sellPrice->setScenario('positiveValue');
            $this->cost->setScenario('positiveValue');
            $this->listPrice->setScenario('positiveValue');
            return parent::beforeValidate();
        }
    }
?>