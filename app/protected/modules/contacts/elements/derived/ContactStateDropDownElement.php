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

    class ContactStateDropDownElement extends DropDownElement implements DerivedElementInterface
    {
        /**
         * Override to utilize 'id' as the attribute not 'value'
         */
        protected function renderControlEditable()
        {
            return $this->form->dropDownList(
                $this->model->{$this->attribute},
                'id',
                $this->getDropDownArray(),
                $this->getEditableHtmlOptions()
            );
        }

        /**
         * Renders the noneditable dropdown content.
         * Takes the model attribute value and converts it into the proper display value
         * based on the corresponding dropdown display label.
         * @return A string containing the element's content.
         */
        protected function renderControlNonEditable()
        {
            $label = ContactsUtil::resolveStateLabelByLanguage($this->model->{$this->attribute}, Yii::app()->language);
            return Yii::app()->format->text($label);
        }

        /**
         * Override so we can force attribute to be set at 'state' since this
         * is the correct attributeName for anything using this derived element
         */
        public function __construct($model, $attribute, $form = null, array $params = array())
        {
            assert('$attribute == "null"');
            parent::__construct($model, $attribute, $form, $params);
            $this->attribute = 'state';
        }

        protected function getDropDownArray()
        {
            return ContactsUtil::getContactStateDataFromStartingStateKeyedByIdAndLabelByLanguage(Yii::app()->language);
        }

        public static function getDisplayName()
        {
            return Yii::t('Default', 'Status');
        }

        /**
         * Get the attributeNames of attributes used in
         * the derived element.
         * @return array of model attributeNames used.
         */
        public static function getModelAttributeNames()
        {
            return array(
                'state',
            );
        }

        protected function getIdForSelectInput()
        {
            return $this->getEditableInputId($this->attribute, 'id');
        }

        protected function getNameForSelectInput()
        {
            return $this->getEditableInputName($this->attribute, 'id');
        }
    }
?>