<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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
     * Helper class for SearchWithDataProvider type test cases.
     * Includes a variety of helper functions to assist creating seed data.
     */
    class ModulesSearchWithDataProviderTestHelper
    {
        /**
         * @return array of values.
         */
        public static function createCustomFieldData($name)
        {
            $customFieldData = CustomFieldData::getByName($name);
            assert('count(unserialize($customFieldData->serializedData)) == 0');
            $values = array(
                $name . '1',
                $name . '2',
                $name . '3',
                $name . '4',
                $name . '5',
            );
            $customFieldData->defaultValue = null;
            $customFieldData->serializedData = serialize($values);
            $saved = $customFieldData->save();
            assert('$saved');
            return $values;
        }

        public static function createCustomAttributesForModel(RedBeanModel $model)
        {
            self::createCheckBoxAttribute       ($model, 'checkBox');
            self::createCurrencyValueAttribute  ($model, 'currency');
            self::createDateAttribute           ($model, 'date');
            self::createDateTimeAttribute       ($model, 'dateTime');
            self::createDecimalAttribute        ($model, 'decimal');
            self::createDropDownAttribute       ($model, 'dropDown');
            self::createIntegerAttribute        ($model, 'integer');
            //self::createMultiSelectDropDownAttribute($model, 'multiSelect');
            self::createPhoneAttribute          ($model, 'phone');
            self::createRadioDropDownAttribute  ($model, 'radio');
            self::createTextAttribute           ($model, 'text');
            self::createTextAreaAttribute       ($model, 'textArea');
            self::createUrlAttribute            ($model, 'url');
        }

        /**
         * Create sets of accounts mixed and matched by various attributes values.
         */
        public static function createAccounts()
        {
            //tbd - what we are going to do here.
            /*
            $user1 = User::getByUsername('user1');
            $user2 = User::getByUsername('user2');
            $user3 = User::getByUsername('user3');
            $user4 = User::getByUsername('user4');
            $user5 = User::getByUsername('user5');

            //create accounts with different things?

            AccountTestHelper::createAccountByNameForOwner('ABC', $user1);
            AccountTestHelper::createAccountByNameForOwner('DEF', $user1);
            */
        }

        public static function createCheckBoxAttribute(RedBeanModel $model, $name)
        {
            $attributeForm = new CheckBoxAttributeForm();
            $attributeForm->attributeName    = $name;
            $attributeForm->attributeLabels  = self::generateAtrributeLabelsByName($name);
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName($model);
            $adapter->setAttributeMetadataFromForm($attributeForm);
        }

        public static function createCurrencyValueAttribute(RedBeanModel $model, $name)
        {
            $attributeForm = new CurrencyValueAttributeForm();
            $attributeForm->attributeName    = $name;
            $attributeForm->attributeLabels  = self::generateAtrributeLabelsByName($name);
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName($model);
            $adapter->setAttributeMetadataFromForm($attributeForm);
        }

        public static function createDateAttribute(RedBeanModel $model, $name)
        {
            $attributeForm = new DateAttributeForm();
            $attributeForm->attributeName    = $name;
            $attributeForm->attributeLabels  = self::generateAtrributeLabelsByName($name);
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName($model);
            $adapter->setAttributeMetadataFromForm($attributeForm);
        }

        public static function createDateTimeAttribute(RedBeanModel $model, $name)
        {
            $attributeForm = new DateTimeAttributeForm();
            $attributeForm->attributeName    = $name;
            $attributeForm->attributeLabels  = self::generateAtrributeLabelsByName($name);
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName($model);
            $adapter->setAttributeMetadataFromForm($attributeForm);
        }

        public static function createDecimalAttribute(RedBeanModel $model, $name)
        {
            $attributeForm = new DecimalAttributeForm();
            $attributeForm->attributeName    = $name;
            $attributeForm->attributeLabels  = self::generateAtrributeLabelsByName($name);
            $attributeForm->maxLength        = 6;
            $attributeForm->precisionLength  = 2;
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName($model);
            $adapter->setAttributeMetadataFromForm($attributeForm);
        }

        public static function createDropDownAttribute(RedBeanModel $model, $name)
        {
            $attributeForm = new DropDownAttributeForm();
            $attributeForm->attributeName       = $name;
            $attributeForm->attributeLabels     = self::generateAtrributeLabelsByName($name);
            $attributeForm->customFieldDataData = self::createCustomFieldData($name . 'List');
            $attributeForm->customFieldDataName = $name . 'List';
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName($model);
            $adapter->setAttributeMetadataFromForm($attributeForm);
        }

        public static function createIntegerAttribute(RedBeanModel $model, $name)
        {
            $attributeForm = new IntegerAttributeForm();
            $attributeForm->attributeName    = $name;
            $attributeForm->attributeLabels  = self::generateAtrributeLabelsByName($name);
            $attributeForm->maxLength        = 11;
            $attributeForm->minValue         = -500000;
            $attributeForm->maxValue          = 500000;
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName($model);
            $adapter->setAttributeMetadataFromForm($attributeForm);
        }

        public static function createMultiSelectDropDownAttribute(RedBeanModel $model, $name)
        {
            //todo: once multiSelect is completed.
        }

        public static function createPhoneAttribute(RedBeanModel $model, $name)
        {
            $attributeForm = new PhoneAttributeForm();
            $attributeForm->attributeName    = $name;
            $attributeForm->attributeLabels  = self::generateAtrributeLabelsByName($name);
            $attributeForm->maxLength        = 20;
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName($model);
            $adapter->setAttributeMetadataFromForm($attributeForm);
        }

        public static function createRadioDropDownAttribute(RedBeanModel $model, $name)
        {
            $attributeForm = new RadioDropDownAttributeForm();
            $attributeForm->attributeName       = $name;
            $attributeForm->attributeLabels     = self::generateAtrributeLabelsByName($name);
            $attributeForm->customFieldDataData = self::createCustomFieldData($name . 'List');
            $attributeForm->customFieldDataName = $name . 'List';
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName($model);
            $adapter->setAttributeMetadataFromForm($attributeForm);
        }

        public static function createTextAttribute(RedBeanModel $model, $name)
        {
            $attributeForm = new TextAttributeForm();
            $attributeForm->attributeName    = $name;
            $attributeForm->attributeLabels  = self::generateAtrributeLabelsByName($name);
            $attributeForm->maxLength        = 50;
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName($model);
            $adapter->setAttributeMetadataFromForm($attributeForm);
        }

        public static function createTextAreaAttribute(RedBeanModel $model, $name)
        {
            $attributeForm = new TextAreaAttributeForm();
            $attributeForm->attributeName    = $name;
            $attributeForm->attributeLabels  = self::generateAtrributeLabelsByName($name);
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName($model);
            $adapter->setAttributeMetadataFromForm($attributeForm);
        }

        public static function createUrlAttribute(RedBeanModel $model, $name)
        {
            $attributeForm = new UrlAttributeForm();
            $attributeForm->attributeName    = $name;
            $attributeForm->attributeLabels  = self::generateAtrributeLabelsByName($name);
            $attributeForm->maxLength        = 50;
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName($model);
            $adapter->setAttributeMetadataFromForm($attributeForm);
        }

        protected static function generateAtrributeLabelsByName($name)
        {
            return array(
                'de' => $name. ' de',
                'en' => $name. ' en',
                'es' => $name. ' es',
                'fr' => $name. ' fr',
                'it' => $name. ' it',
            );
        }
    }
?>