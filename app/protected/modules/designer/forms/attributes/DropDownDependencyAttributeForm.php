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

    /**
     * Form for managing the dependent drop down derived attributes that can be created in the designer tool.
     * An example is if you have 3 dropdowns that need to be connected together so some values in the second dropdown
     * only show based on the value of the first dropdown.
     */
    class DropDownDependencyAttributeForm extends AttributeForm
    {
        public $id;

        /**
         * Array of mapping data.  Below is an example:
         * @code
            <?php
                $mappingData = array(
                    array('attributeName' => 'topLevelAttributeName'),
                    array('attributeName' => 'secondLevelAttributeName',
                          'valuesToParentValues' => array('secondLevelValueB' => 'topLevelValueA')),
                    array('attributeName' => 'thirdLevelAttributeName',
                          'valuesToParentValues' => array()),
                    array('attributeName' => 'fourthLevelAttributeName',
                          'valuesToParentValues' => array()),
                );
            ?>
         * @endcode
         *
         * @var array
         */
        public $mappingData = array();

        /**
         * The model class name that this drop down dependency is related to.
         * @param string $modelClassName
         */
        public $modelClassName;

        public function __construct(RedBeanModel $model = null, $attributeName = null)
        {
            assert('$attributeName === null || is_string($attributeName)');
            assert('$model === null || !$model->isAttribute($attributeName)');
            if ($model !== null)
            {
                if($attributeName != null)
                {
                    $metadata              = DropDownDependencyDerivedAttributeMetadata::
                                             getByNameAndModelClassName($attributeName, get_class($model));
                    $unserializedMetadata  = unserialize($metadata->serializedMetadata);
                    $this->id              = $metadata->id;
                    $this->attributeName   = $metadata->name;
                    $this->attributeLabels = $unserializedMetadata['attributeLabels'];
                    $this->mappingData     = $unserializedMetadata['mappingData'];
                    $this->modelClassName  = get_class($model);
                }
            }
        }

        public function rules()
        {
            return array_merge(parent::rules(), array(
                array('mappingData', 'safe'),
                array('mappingData', 'validateMappingData'),
            ));
        }

        public function attributeLabels()
        {
            return array_merge(parent::attributeLabels(), array(
                'mappingData'   => Yii::t('Default', 'Dependency Mapping'),
            ));
        }
        public static function getAttributeTypeDisplayName()
        {
            return Yii::t('Default', 'Dependent Pick Lists');
        }

        public static function getAttributeTypeDisplayDescription()
        {
            return Yii::t('Default', 'A set of dependent pick lists');
        }

        public function getAttributeTypeName()
        {
            return 'DropDownDependency';
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
                $models = DropDownDependencyDerivedAttributeMetadata::
                          getByNameAndModelClassName($this->attributeName, $this->modelClassName);
                $this->addError('attributeName', Yii::t('Default', 'A field with this name is already used.'));
            }
            catch(NotFoundException $e)
            {
            }
        }

        /**
         * Make sure the mappings are formed correctly.
         */
        public function validateMappingData($attribute, $params)
        {
            assert('$this->modelClassName != null');
            assert('$attribute == "mappingData"');
            $mappingData            = $this->$attribute;
            $selectedAttributeNames = 0;
            foreach($mappingData as $data)
            {
                if(isset($data['attributeName']) && $data['attributeName'] != null)
                {
                    $selectedAttributeNames ++;
                }
            }
            if(count($mappingData) < 2  || $selectedAttributeNames < 2)
            {
                $this->addError('mappingData',  Yii::t('Default', 'You must select at least 2 pick-lists.'));
            }
            if(count($mappingData) > 4 || $selectedAttributeNames > 4)
            {
                $this->addError('mappingData',  Yii::t('Default', 'You can only have at most 4 pick-lists selected.'));
            }
            foreach($mappingData as $position => $attributeNameAndData)
            {
                assert('isset($attributeNameAndData["attributeName"])');
                if($position > 0 && $attributeNameAndData['attributeName'] != null)
                {
                    if(!isset($attributeNameAndData['valuesToParentValues']) ||
                       self::getValuesToParentValuesMappedCount($attributeNameAndData['valuesToParentValues']) == 0)
                    {
                        $this->addError('mappingData', Yii::t('Default', 'At least one pick-list value must be mapped for each used level.'));
                    }
                    else
                    {
                        $customFieldData        = CustomFieldDataModelUtil::
                                                  getDataByModelClassNameAndAttributeName($this->modelClassName,
                                                                                          $attributeNameAndData['attributeName']);
                        $dataValues             = unserialize($customFieldData->serializedData);
                        $parentPosition         = $position - 1;
                        $parentAttributeName    = $mappingData[$parentPosition]['attributeName'];
                        $parentCustomFieldData  = CustomFieldDataModelUtil::
                                                  getDataByModelClassNameAndAttributeName($this->modelClassName,
                                                                                          $parentAttributeName);
                        $parentDataValues       = unserialize($parentCustomFieldData->serializedData);

                        foreach($attributeNameAndData['valuesToParentValues'] as $customFieldDataValue => $parentCustomFieldDataValue)
                        {
                            if($parentCustomFieldDataValue != null &&
                               !in_array($parentCustomFieldDataValue, $parentDataValues))
                            {
                                $this->addError('mappingData',
                                                Yii::t('Default',
                                                'Each pick-list value must map correctly to a parent pick-list value. ' .
                                                'This value does map correctly: {value} - {parentValue}',
                                                array('{value}'       => $customFieldDataValue,
                                                      '{parentValue}' => $parentCustomFieldDataValue)));
                            }
                        }
                    }
                }
            }
        }


        /**
         * @see AttributeForm::getModelAttributeAdapterNameForSavingAttributeFormData()
         */
        public static function getModelAttributeAdapterNameForSavingAttributeFormData()
        {
            return 'DropDownDependencyModelDerivedAttributesAdapter';
        }

        public function sanitizeFromPostAndSetAttributes($values)
        {
            assert('is_array($values)');
            if(isset($values['mappingData']))
            {
                foreach($values['mappingData'] as $position => $data)
                {
                    if($data['attributeName'] == null && isset($data['valuesToParentValues']))
                    {
                        unset($values['mappingData'][$position]['valuesToParentValues']);
                    }
                }
            }
            parent::sanitizeFromPostAndSetAttributes($values);
        }

        public function getValuesToParentValuesMappedCount($valuesToParentValues)
        {
            assert('is_array($valuesToParentValues)');
            $count = 0;
            foreach($valuesToParentValues as $value => $parentValue)
            {
                if($parentValue != null)
                {
                    $count ++;
                }
            }
            return $count;
        }

        public function canUpdateAttributeProperty($propertyName)
        {
            if($propertyName == 'attributeName' && $this->id != null)
            {
                return false;
            }
            return true;
        }
    }
?>
