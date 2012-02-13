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
     * Derived Element
     * Element for displaying the following policies together
     * in one user interface element.
     * UsersModule::POLICY_PASSWORD_EXPIRES
     * UsersModule::POLICY_PASSWORD_EXPIRY_DAYS
     *
     * POLICY_PASSWORD_EXPIRY_DAYS can only be set if
     * POLICY_PASSWORD_EXPIRES is set to Yes.
     */
    class PolicyPasswordExpiryElement extends Element implements DerivedElementInterface
    {
        /**
         * Renders the editable dropdown content.
         * @return A string containing the element's content.
         */
        protected function renderControlEditable()
        {
            assert('$this->attribute == "null"');
            $this->registerScripts();
            $dropDownArray = $this->getExpiresDropDownArray();
            $inputId = $this->getEditableInputId  ($this->getExpiresAttributeName());
            $compareValue = Policy::YES;
            $htmlOptions = array(
                'name' => $this->getEditableInputName($this->getExpiresAttributeName()),
                'id'   => $this->getEditableInputId  ($this->getExpiresAttributeName(), 'value'),
                'onchange' => 'enableDisablePolicyTextField($(this).val(), \''. $inputId . '\', \''. $compareValue . '\');',
            );
            $content  = $this->getInheritedContent();
            $content .= $this->form->dropDownList($this->model, $this->getExpiresAttributeName(), $dropDownArray, $htmlOptions);
            $content .= '&#160;' . Yii::t('Default', 'every') . '&#160;';
            $htmlOptions = array(
                'id'       => $inputId,
                'name'     => $this->getEditableInputName($this->getExpiryAttributeName()),
                'readonly' => $this->getReadOnlyValue(),
            );
            $content .= $this->form->textField($this->model, $this->getExpiryAttributeName(), $htmlOptions);
            $content .= '&#160;' . Yii::t('Default', 'days');
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

        protected function getFormattedAttributeLabel()
        {
            return Yii::app()->format->text(Yii::t('Default', UsersModule::POLICY_PASSWORD_EXPIRES));
        }

        /**
         * Override because derived attribute requires a different
         * attributeName to be used by the form->error method. This is because
         * the standard $this->attribute is 'null' for derived attributes.
         * @return error content
         */
        protected function renderError()
        {
            return $this->form->error($this->model, $this->getExpiryAttributeName());
        }

        protected static function getExpiresAttributeName()
        {
            return FormModelUtil::getDerivedAttributeNameFromTwoStrings('UsersModule', 'POLICY_PASSWORD_EXPIRES');
        }

        protected static function getExpiryAttributeName()
        {
            return FormModelUtil::getDerivedAttributeNameFromTwoStrings('UsersModule', 'POLICY_PASSWORD_EXPIRY_DAYS');
        }

        protected function getExpiresDropDownArray()
        {
            return array(
                ''          => Yii::t('Default', 'Not Set'),
                Policy::YES => Yii::t('Default', 'Yes'),
            );
        }

        protected function getReadOnlyValue()
        {
            $expires = $this->getExpiryAttributeName();
            $expiry = $this->getExpiresAttributeName();
            if ($this->model->{$expiry} == null &&
            $expires != Policy::YES)
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
            $inheritedAttributeName = $this->getExpiryAttributeName() . '__inherited';
            if ($this->model->{$inheritedAttributeName} != null)
            {
                return Yii::t('Default', 'Inherited Value:') . '&#160;' . $this->model->{$inheritedAttributeName} . '<br/>';
            }
            return null;
        }

        /**
         * Get the attributeNames of attributes used in
         * the derived element.
         * @return array of model attributeNames used.
         */
        public static function getModelAttributeNames()
        {
            return array(
                self::getExpiresAttributeName(),
                self::getExpiryAttributeName()
            );
        }
    }
?>