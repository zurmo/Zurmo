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

    class DateTimeAttributeForm extends AttributeForm
    {
        public $defaultValueCalculationType;

        /**
         * Override needed to translate defaultValueCalculationType  rule to the defaultValueCalculationType.
         */
        public function __construct(RedBeanModel $model = null, $attributeName = null)
        {
            parent::__construct($model, $attributeName);
            if ($model !== null)
            {
                $validators = $model->getValidators($attributeName);
                foreach ($validators as $validator)
                {
                    if ($validator instanceof RedBeanModelDateTimeDefaultValueValidator)
                    {
                        $this->defaultValueCalculationType = $validator->value;
                    }
                }
            }
        }

        public function rules()
        {
            return array_merge(parent::rules(), array(
                array('defaultValueCalculationType',      'safe'),
            ));
        }

        public function attributeLabels()
        {
            return array_merge(parent::attributeLabels(), array(
                'defaultValueCalculationType' => Yii::t('Default', 'Default Value'),
            ));
        }

        public static function getAttributeTypeDisplayName()
        {
            return yii::t('Default', 'Date Time');
        }

        public function getAttributeTypeName()
        {
            return 'DateTime';
        }

        public static function getAttributeTypeDisplayDescription()
        {
            return yii::t('Default', 'A date/time field');
        }

        public function getModelAttributePartialRule()
        {
            return array('type', 'type' => 'datetime');
        }

        /**
         * Override to handle defaultValueCalculationType since the attributePropertyToDesignerFormAdapter
         * does not specifically support this property.
         */
        public function canUpdateAttributeProperty($propertyName)
        {
            if ($propertyName == 'defaultValueCalculationType')
            {
                return true;
            }
            return $this->attributePropertyToDesignerFormAdapter->canUpdateProperty($propertyName);
        }

        /**
         * @see AttributeForm::getModelAttributeAdapterNameForSavingAttributeFormData()
         */
        public static function getModelAttributeAdapterNameForSavingAttributeFormData()
        {
            return 'DateTimeModelAttributesAdapter';
        }
    }
?>
