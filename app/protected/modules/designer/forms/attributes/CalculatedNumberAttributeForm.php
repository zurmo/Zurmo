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
     * Form for working with calculated number derived attributes.
     */
    class CalculatedNumberAttributeForm extends AttributeForm
    {
        public $id;

        public $formula;

        protected $modelClassName;

        public function __construct(RedBeanModel $model = null, $attributeName = null)
        {
            assert('$attributeName === null || is_string($attributeName)');
            assert('$model === null || !$model->isAttribute($attributeName)');
            if ($model !== null)
            {
                if ($attributeName != null)
                {
                    $metadata              = CalculatedDerivedAttributeMetadata::
                                             getByNameAndModelClassName($attributeName, get_class($model));
                    $unserializedMetadata  = unserialize($metadata->serializedMetadata);
                    $this->id              = $metadata->id;
                    $this->attributeName   = $metadata->name;
                    $this->attributeLabels = $unserializedMetadata['attributeLabels'];
                    $this->formula         = $unserializedMetadata['formula'];
                }
                else
                {
                    $unserializedMetadata = array();
                }
                $this->modelClassName = get_class($model);
            }
        }

        public function rules()
        {
            return array_merge(parent::rules(), array(
                array('formula',        'required'),
                array('formula',        'validateFormula'),
            ));
        }

        public function attributeLabels()
        {
            return array_merge(parent::attributeLabels(), array(
                'formula' => Yii::t('Default', 'Formula'),
            ));
        }

        public static function getAttributeTypeDisplayName()
        {
            return Yii::t('Default', 'Calculated Number');
        }

        public static function getAttributeTypeDisplayDescription()
        {
            return Yii::t('Default', 'A calculated number based on other field values');
        }

        public function getAttributeTypeName()
        {
            return 'CalculatedNumber';
        }

        public function validateFormula($attribute, $params)
        {
            assert('$attribute == "formula"');
            assert('$this->modelClassName != null');
            $modelClassName = $this->modelClassName;
            $model          = new $modelClassName(false);
            $adapter        = new ModelNumberOrCurrencyAttributesAdapter($model);
            if (!CalculatedNumberUtil::isFormulaValid($this->{$attribute}, $adapter))
            {
                $this->addError('formula', Yii::t('Default', 'The formula is invalid.'));
            }
        }

        /**
         * (non-PHPdoc)
         * @see AttributeForm::validateAttributeNameDoesNotExists()
         */
        public function validateAttributeNameDoesNotExists()
        {
            assert('$this->modelClassName != null');
            try
            {
                $models = CalculatedDerivedAttributeMetadata::
                          getByNameAndModelClassName($this->attributeName, $this->modelClassName);
                if (count($models) > 0)
                {
                    $this->addError('attributeName', Yii::t('Default', 'A field with this name is already used.'));
                }
            }
            catch (NotFoundException $e)
            {
            }
        }

        /**
         * @see AttributeForm::getModelAttributeAdapterNameForSavingAttributeFormData()
         */
        public static function getModelAttributeAdapterNameForSavingAttributeFormData()
        {
            return 'CalculatedNumberModelDerivedAttributesAdapter';
        }

        public function canUpdateAttributeProperty($propertyName)
        {
            if ($propertyName == 'attributeName' && $this->id != null)
            {
                return false;
            }
            return true;
        }
    }
?>
