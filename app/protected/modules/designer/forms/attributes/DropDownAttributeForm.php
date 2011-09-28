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

    class DropDownAttributeForm extends AttributeForm implements CollectionAttributeFormInterface
    {
        public $customFieldDataName;

        public $customFieldDataData;

        /**
         * Used when changing the value of an existing data item.  Coming in from a post, this array will have the
         * old values that can be used to compare against and update the new values accordingly based on any changes.
         */
        public $customFieldDataDataExistingValues;

        /**
         * CustomFieldData model id.
         */
        protected $customFieldDataId;

        public $defaultValueOrder;

        /**
         * Override needed to translate defaultValue to the order.  Order corresponds to the keyed index of the
         * customFieldDataData array.  This is needed for the form to operate correctly in the user interface.
         * Otherwise if you select a default as a new pick list item, the user interface has no way of posting
         * the correct Id for the defaultValue since the new pick list item has not been created yet.
         * Also need override to properly adapt pick list items.
         */
        public function __construct(RedBeanModel $model = null, $attributeName = null)
        {
            parent::__construct($model, $attributeName);
            if ($model !== null)
            {
                $this->customFieldDataName = $model->$attributeName->data->name;
                $this->customFieldDataId   = $model->$attributeName->data->id;
                $this->customFieldDataData = unserialize($model->$attributeName->data->serializedData);
                $this->defaultValueOrder   = DropDownDefaultValueOrderUtil::getDefaultValueOrderFromDefaultValue(
                                                $this->defaultValue, $this->customFieldDataData);
            }
        }

        /**
         * Get how many records in a model have each possible customFieldData value selected.
         * If the customFieldData doesn't exist yet, then return 0.
         */
        public function getCollectionCountData()
        {
            if ($this->customFieldDataId > 0)
            {
            return GroupedAttributeCountUtil::getCountData('CustomField', 'value', 'data',
                                                $this->customFieldDataId);
            }
            return 0;
        }

        public function getCustomFieldDataId()
        {
            return $this->customFieldDataId;
        }

        public function rules()
        {
            return array_merge(parent::rules(), array(
                array('customFieldDataData',                'safe'),
                array('customFieldDataDataExistingValues',  'safe'),
                array('customFieldDataData',                'required',
                                                            'message' => 'You must have at least one pick list item.'),
                array('customFieldDataData',                'validateCustomFieldDataData'),
                array('defaultValueOrder',                  'safe'),
                array('attributeName',                      'length', 'min'  => 3, 'max' => 64),
                ));
        }

        public function attributeLabels()
        {
            return array_merge(parent::attributeLabels(), array(
                'customFieldDataData' => Yii::t('Default', 'Pick List Values'),
                'defaultValueOrder'   => Yii::t('Default', 'Default Value'),
            ));
        }

        public static function getAttributeTypeDisplayName()
        {
            return yii::t('Default', 'Pick List');
        }

        public static function getAttributeTypeDisplayDescription()
        {
            return yii::t('Default', 'A pick list with specific values to select from');
        }

        public function getAttributeTypeName()
        {
            return 'DropDown';
        }

        /**
         * @see AttributeForm::getModelAttributeAdapterNameForSavingAttributeFormData()
         */
        public static function getModelAttributeAdapterNameForSavingAttributeFormData()
        {
            return 'DropDownModelAttributesAdapter';
        }

        /**
         * Test if there are two picklist values with the same name.  This is not allowed.
         */
        public function validateCustomFieldDataData($attribute, $params)
        {
            $data = $this->$attribute;
            if (!empty($data) && $nonUniqueData = array_diff_key( $data , ArrayUtil::array_iunique( $data )) )
            {
                $nonUniqueValuesString = null;
                foreach($nonUniqueData as $nonUniqueValue)
                {
                    if ($nonUniqueValuesString != null)
                    {
                       $nonUniqueValuesString .= ', ';
                    }
                    $nonUniqueValuesString .= $nonUniqueValue;
                }
                $this->addError('customFieldDataData',
                Yii::t('Default',
                'Each item must be uniquely named and the following are not: {values}',
                array('{values}' => $nonUniqueValuesString)));
            }
        }

        /**
         * Override to handle defaultValueOrder since the attributePropertyToDesignerFormAdapter does not specifically
         * support this property.
         */
        public function canUpdateAttributeProperty($propertyName)
        {
            if ($propertyName == 'defaultValueOrder')
            {
                return true;
            }
            return $this->attributePropertyToDesignerFormAdapter->canUpdateProperty($propertyName);
        }

        /**
         * How many attributes that are drop downs across the different models are using this customFieldData.
         */
        public function getModelPluralNameAndAttributeLabelsThatUseCollectionData()
        {
            return CustomFieldDataModelUtil::getModelPluralNameAndAttributeLabelsByName($this->customFieldDataName);
        }
    }
?>
