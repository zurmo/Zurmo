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

    abstract class AttributeForm extends ConfigurableMetadataModel
    {
        public $attributeName;
        public $attributeLabels;
        public $defaultValue = null;
        public $isAudited    = false;
        public $isRequired   = false;
        protected $attributePropertyToDesignerFormAdapter;
        protected $modelClassName;

        public function __construct(RedBeanModel $model = null, $attributeName = null)
        {
            assert('$attributeName === null || is_string($attributeName)');
            assert('$model === null || $model->isAttribute($attributeName)');
            if ($model !== null)
            {
                $this->attributeName     = $attributeName;
                $this->attributeLabels   = $model->getAttributeLabelsForAllSupportedLanguagesByAttributeName(
                                                    $attributeName);
                //should be $model->getAttributeLabelsForAllSupportedLanguagesByAttributeName($attributeName);
                $this->attributePropertyToDesignerFormAdapter = new AttributePropertyToDesignerFormAdapter();
                $validators = $model->getValidators($attributeName);
                foreach ($validators as $validator)
                {
                    if ($validator instanceof CDefaultValueValidator)
                    {
                        $this->defaultValue = $validator->value;
                    }
                    elseif ($validator instanceof CRequiredValidator)
                    {
                        $this->isRequired = true;
                        $modelAttributesAdapter = new ModelAttributesAdapter($model);
                        if ($modelAttributesAdapter->isStandardAttribute($attributeName))
                        {
                            $this->attributePropertyToDesignerFormAdapter->setUpdateRequiredFieldStatus(false);
                        }
                    }
                }
                if ($model instanceof Item && $attributeName != null)
                {
                    $this->isAudited = $model->isAttributeAudited($attributeName);
                }
            }
        }

        public function __toString()
        {
            $attributeLabel = ModelFormAttributeLabelsUtil::getTranslatedAttributeLabelByLabels($this->attributeLabels);
            if ($attributeLabel == null)
            {
                return yii::t('Default', '(Unnamed)');
            }
            return $attributeLabel;
        }

        public function rules()
        {
            return array(
                array('attributeName', 'required'),
                array('attributeName', 'match', 'pattern' => '/^[A-Za-z0-9_]+$/', // Not Coding Standard
                                                'message' =>  yii::t('Default', 'Name must not contain spaces or special characters'),
                ),
                array('attributeName', 'match', 'pattern' => '/^[a-z]/', // Not Coding Standard
                                                'message' =>  yii::t('Default', 'First character must be a lower case letter'),
                ),
                array('attributeLabels',   'validateAttributeLabels'),
                array('defaultValue',  'safe'),
                array('isAudited',     'boolean'),
                array('isRequired',    'boolean'),
                array('attributeName',
                    'validateAttributeNameDoesNotExists',
                    'skipOnError' => true,
                    'on'   => 'createAttribute',
                ),
            );
        }

        public function attributeLabels()
        {
            return array(
                'attributeName'   => Yii::t('Default', 'Field Name'),
                'attributeLabels' => Yii::t('Default', 'Display Name'),
                'defaultValue'    => Yii::t('Default', 'Default Value'),
                'isAudited'       => Yii::t('Default', 'Track Audit Log'),
                'isRequired'      => Yii::t('Default', 'Required Field'),
            );
        }

        public function canUpdateAttributeProperty($propertyName)
        {
            return $this->attributePropertyToDesignerFormAdapter->canUpdateProperty($propertyName);
        }

        public static function getAttributeTypeDisplayName()
        {
            return null;
        }

        public static function getAttributeTypeDisplayDescription()
        {
            return null;
        }

        abstract public function getAttributeTypeName();

        /**
         * Returns a partial rule for the attribute being edited by this form.
         * For a rule array('attributeName', 'type', 'type' => 'thetypename');
         * the method must return array('type', 'type' => 'thetypename') appropriate
         * to its data. For a rule array('attributeName', 'boolean');
         * the method must return array('boolean'), for example for a checkbox.
         * If the method is not overridden or returns an empty array no rule
         * will be be added to model metadata in regards to the type of the
         * attribute.
         */
        public function getModelAttributePartialRule()
        {
            return array();
        }

        /**
         * Validates that attribute name does not already exist
         * on model.  Ignores check if this is an existing attribute
         * being modified since you can't modify the attribute name except
         * during attribute creation.
         */
        public function validateAttributeNameDoesNotExists()
        {
            assert('$this->modelClassName != null');
            $modelClassName = $this->modelClassName;
            $model = new $modelClassName();
            if ($model->isAttribute($this->attributeName))
            {
                $this->addError('attributeName', Yii::t('Default', 'A field with this name is already used.'));
            }
        }

        public function validateAttributeLabels($attribute, $params)
        {
            $data = $this->$attribute;
            foreach (Yii::app()->languageHelper->getActiveLanguagesData() as $language => $name)
            {
                if ( empty($data[$language]))
                {
                    $this->addError($attribute . '[' . $language . ']', Yii::t('Default', 'Label must not be empty.'));
                }
            }
        }

        public function setModelClassName($modelClassName)
        {
            $this->modelClassName = $modelClassName;
        }

        /**
         * Override if you need to specify a different ModelAttributeAdapterType when saving attributeForm data.
         * This is needed because you can have different logic for calling setting attribute metadata from a form.
         * This allows you to specify an override adapter class.
         * @return string
         */
        public static function getModelAttributeAdapterNameForSavingAttributeFormData()
        {
            return 'ModelAttributesAdapter';
        }
    }
?>
