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

    class ProductCategory extends Item
    {
        const EVERYONE_CATEGORY_NAME            = 'Everyone';

        const ERROR_EXIST_TEMPLATE              = 1;

        const ERROR_EXIST_CHILD_CATEGORIES      = 2;

        /**
         * @param string $name
         * @return string
         */
        public static function getByName($name)
        {
            return self::getByNameOrEquivalent('name', $name);
        }

        /**
         * @return array
         */
        protected function untranslatedAttributeLabels()
        {
            return array_merge(parent::untranslatedAttributeLabels(),
                array(
                )
            );
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
         * Returns the display name for the model class.
         * @return dynamic label name based on module.
         */
        protected static function getLabel($language = null)
        {
            return 'Product Category';
        }

        /**
         * Returns the display name for plural of the model class.
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel($language = null)
        {
            return 'Product Categories';
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
                    'name'
                ),
                'relations' => array(
                    'productTemplates'  => array(RedBeanModel::MANY_MANY, 'ProductTemplate'),
                    'products'          => array(RedBeanModel::MANY_MANY, 'Product'),
                    'productCatalogs'   => array(RedBeanModel::MANY_MANY, 'ProductCatalog'),
                    'productCategory'   => array(RedBeanModel::HAS_MANY_BELONGS_TO, 'ProductCategory'),
                    'productCategories' => array(RedBeanModel::HAS_MANY, 'ProductCategory'),
                ),
                'rules' => array(
                    array('name',  'required'),
                    array('name',  'type',    'type' => 'string'),
                    array('name',  'length',  'min'  => 3,  'max' => 64),
                ),
                'elements' => array(
                ),
                'customFields' => array(
                ),
                'defaultSortAttribute' => 'name',
                'noAudit' => array(
                ),
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
            return 'ProductCategory';
        }

        public static function getGamificationRulesType()
        {
            //return 'ProductCategoryGamification';
        }

        /**
         * @return string
         */
        protected function beforeDelete()
        {
            if ($this->getScenario() != 'autoBuildDatabase')
            {
                parent::beforeDelete();

                if (count($this->productTemplates) > 0 || count($this->productCategories) > 0 )
                {
                    return false;
                }
                else
                {
                    return true;
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
            return array_merge(parent::translatedAttributeLabels($language), array(
                'productCategory'   => Zurmo::t('ProductTemplatesModule', 'Parent ' . self::getModelLabelByTypeAndLanguage('Singular', $language), array(), null, $language),
                'productCategories' => self::getModelLabelByTypeAndLanguage('Plural', $language),
                'productCatalogs'   => ProductCatalog::getModelLabelByTypeAndLanguage('Plural', $language),
                'products'          => Zurmo::t('ProductTemplatesModule', 'ProductsModulePluralLabel', array(), null, $language),
                'productTemplates'  => Zurmo::t('ProductTemplatesModule', 'ProductTemplatesModulePluralLabel', array(), null, $language)
            ));
        }
    }
?>