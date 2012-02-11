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

    class ContactStateAttributeForm extends AttributeForm implements CollectionAttributeFormInterface
    {
        public $contactStatesData;

        public $startingStateOrder;

        public $contactStatesLabels;

        /**
         * Used when changing the value of an existing data item.  Coming in from a post, this array will have the
         * old values that can be used to compare against and update the new values accordingly based on any changes.
         */
        public $contactStatesDataExistingValues;

        public function __construct(Contact $model = null, $attributeName = null)
        {
            assert('$model != null');
            assert('$attributeName != null && is_string($attributeName)');
            parent::__construct($model, $attributeName);
            $this->contactStatesData   = ContactsUtil::getContactStateDataKeyedByOrder();
            $this->contactStatesLabels = ContactsUtil::getContactStateLabelsKeyedByLanguageAndOrder();
            $startingState             = ContactsUtil::getStartingState();
            $this->startingStateOrder  = $startingState->order;
        }

        public function rules()
        {
            return array_merge(parent::rules(), array(
                array('startingStateOrder',   'required'),
                array('contactStatesData',    'safe'),
                array('contactStatesData',    'required', 'message' => 'You must have at least one status.'),
                array('contactStatesData',    'validateContactStatesData'),
                array('contactStatesLabels',  'safe'),
                array('contactStatesDataExistingValues',  'safe'),
            ));
        }

        public function attributeLabels()
        {
            return array_merge(parent::attributeLabels(), array(
                'contactStatesData'      => Yii::t('Default', 'Contact Statuses'),
                'startingStateOrder'     => Yii::t('Default', 'Starting Status'),
                'contactStatesLablsa'    => Yii::t('Default', 'Contact Status Translated Labels'),
            ));
        }

        public static function getAttributeTypeDisplayName()
        {
            return Yii::t('Default', 'Contact Stage');
        }

        public static function getAttributeTypeDisplayDescription()
        {
            return Yii::t('Default', 'The contact stage field');
        }

        public function getAttributeTypeName()
        {
            return 'ContactState';
        }

        /**
         * Override to handle startingStateOrder since the attributePropertyToDesignerFormAdapter does not specifically
         * support this property.
         */
        public function canUpdateAttributeProperty($propertyName)
        {
            if ($propertyName == 'startingStateOrder')
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
            return 'ContactStateModelAttributesAdapter';
        }

        /**
         * Test if there are two picklist values with the same name.  This is not allowed.
         */
        public function validateContactStatesData($attribute, $params)
        {
            $data = $this->$attribute;
            if (array_diff_key( $data , array_unique( $data )) )
            {
                $this->addError('contactStatesData',
                    Yii::t('Default', 'Each ContactsModuleSingularLowerCaseLabel state must be uniquely named',
                                                        LabelUtil::getTranslationParamsForAllModules()));
            }
            foreach ($data as $order => $name)
            {
                $contactState = new ContactState();
                $contactState->name = $name;
                $contactState->order = $order;
                if (!$contactState->validate())
                {
                    foreach ($contactState->getErrors() as $attributeName => $errors)
                    {
                        if ($attributeName == 'name')
                        {
                            foreach ($errors as $error)
                            {
                            $this->addError('contactStatesData', $error);
                            }
                        }
                    }
                }
            }
            //todo: validate against contactState rules as well. like minimum length = 3
        }

        /**
         * Get how many records in the Contact and Lead models have each ContactState selected.
         * During testing, it is possible a contact or lead exists with a contact state id that no longer exists.
         * In that case, it is ignored from the count.
         */
        public function getCollectionCountData()
        {
            $contactStates      = ContactsUtil::getContactStateDataKeyedById();
            $stateNameCountData = array();
            $idCountData        = GroupedAttributeCountUtil::getCountData('Contact', 'state');
            foreach ($idCountData as $id => $count)
            {
                if (isset($contactStates[$id]))
                {
                    $stateNameCountData[$contactStates[$id]] = $count;
                }
            }
            return $stateNameCountData;
        }

        /**
         * Even though contacts and leads use contact state, for now we are treating this only as one model with
         * one attribute using this.
         */
        public function getModelPluralNameAndAttributeLabelsThatUseCollectionData()
        {
            return array();
        }
    }
?>