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
     * Element for displaying integer type policies in the
     * administrative interface for policies on permitables
     * Element consists of a text field and a helper dropdown
     * that is used to disable or enable the text field.  The helper
     * dropdown will populate to YES if there is an explicit value
     * for the text field.
     *
     * If there is just an inherited value, then a read-only text will
     * appear above the helper dropdown with the inherited value information
     */
    class PolicyIntegerAndStaticDropDownElement extends Element
    {
        const HELPER_DROPDOWN_VALUE_YES = 1;

        /**
         * Renders the editable dropdown content.
         * @return A string containing the element's content.
         */
        protected function renderControlEditable()
        {
            $this->registerScripts();
            $dropDownArray = $this->getHelperDropDownArray();
            $inputId       = $this->getIdForInput();
            $compareValue  = PolicyIntegerAndStaticDropDownElement::HELPER_DROPDOWN_VALUE_YES;
            $htmlOptions   = array(
                'id'       => $this->getIdForHelperInput(),
                'onchange' => 'enableDisablePolicyTextField($(this).val(), \''. $inputId . '\', \''. $compareValue . '\');',
            );
            $content       = $this->getInheritedContent();
            $content      .= CHtml::dropDownList(
                                $this->getNameForHelperInput(),
                                $this->getHelperValue(),
                                $dropDownArray,
                                $htmlOptions);
            $content      .= '&#160;';
            $htmlOptions   = array(
                                'id'       => $inputId,
                                'readonly' => $this->getReadOnlyValue());
            $content      .= $this->form->textField($this->model, $this->attribute, $htmlOptions);
            return $content;
        }

        /**
         * Renders a message.
         * @return The element's content.
         */
        protected function renderControlNonEditable()
        {
            throw new NotImplementedException();
        }

        /**
         * Generate the element label content. Override
         * to always for non-editable label
         * @return A string containing the element's label
         */
        protected function renderLabel()
        {
            return $this->resolveNonActiveFormFormattedLabel($this->getFormattedAttributeLabel());
        }

        protected function getNameForHelperInput()
        {
            return $this->getEditableInputName($this->attribute . FormModelUtil::DELIMITER . 'helper');
        }

        protected function getIdForHelperInput()
        {
            return $this->getEditableInputId($this->attribute . FormModelUtil::DELIMITER . 'helper');
        }

        protected function getIdForInput()
        {
            return $this->getEditableInputId();
        }

        protected function getHelperDropDownArray()
        {
            return array(
                ''          => Yii::t('Default', 'Not Set'),
                PolicyIntegerAndStaticDropDownElement::HELPER_DROPDOWN_VALUE_YES => Yii::t('Default', 'Yes'),
            );
        }

        protected function getHelperValue()
        {
            $helperValue = $this->model->{$this->attribute . FormModelUtil::DELIMITER . 'helper'};
            if ($helperValue == null)
            {
                if ($this->model->{$this->attribute} != null)
                {
                    return PolicyIntegerAndStaticDropDownElement::HELPER_DROPDOWN_VALUE_YES;
                }
            }
            return $helperValue;
        }

        protected function getReadOnlyValue()
        {
            if ($this->model->{$this->attribute} == null &&
            $this->getHelperValue() != PolicyIntegerAndStaticDropDownElement::HELPER_DROPDOWN_VALUE_YES)
            {
                return 'readonly';
            }
            return null;
        }

        protected function registerScripts()
        {
            Yii::app()->clientScript->registerScript(
                'EnableDisablePolicyTextField',
                PoliciesElementUtil::getEnableDisablePolicyTextFieldScript(),
                CClientScript::POS_END
            );
        }

        protected function getInheritedContent()
        {
            $inheritedAttributeName = $this->attribute . '__inherited';
            if ($this->model->{$inheritedAttributeName} != null)
            {
                return Yii::t('Default', 'Inherited Value:') . '&#160;' . $this->model->{$inheritedAttributeName} . '<br/>';
            }
            return null;
        }
    }
?>