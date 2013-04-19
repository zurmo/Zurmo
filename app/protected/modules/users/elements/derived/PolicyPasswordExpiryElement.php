<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
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
                'name'  => $this->getEditableInputName($this->getExpiresAttributeName()),
                'id'    => $this->getEditableInputId  ($this->getExpiresAttributeName(), 'value'),
                'onchange' => 'enableDisablePolicyTextField($(this).val(), \''. $inputId . '\', \''. $compareValue . '\');',
            );
            $content  = '<div class="hasHalfs"><div class="threeFifths">';
            $content .= $this->getInheritedContent();
            $content .= $this->form->dropDownList($this->model, $this->getExpiresAttributeName(), $dropDownArray, $htmlOptions);
            $content .= '</div>';
            $content .= '<span class="mad-lib twoFifths">' . Zurmo::t('UsersModule', 'every');
            $htmlOptions = array(
                'id'       => $inputId,
                'name'     => $this->getEditableInputName($this->getExpiryAttributeName()),
                'class'    => $this->resolveInputClassDisabled()
            );
            $content .= $this->form->textField($this->model, $this->getExpiryAttributeName(), $htmlOptions);
            $content .= Zurmo::t('UsersModule', 'days') . '</span>';
            $content .= '</div>';
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
            return Yii::app()->format->text(Zurmo::t('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRES));
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
                ''          => Zurmo::t('UsersModule', 'Not Set'),
                Policy::YES => Zurmo::t('UsersModule', 'Yes'),
            );
        }

        protected function resolveInputClassDisabled()
        {
            $expires = $this->getExpiryAttributeName();
            $expiry = $this->getExpiresAttributeName();
            if ($this->model->{$expiry} == null && $expires != Policy::YES)
            {
                return 'disabled';
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
                return Zurmo::t('UsersModule', 'Inherited Value:') . '&#160;' . $this->model->{$inheritedAttributeName} . '<br/>';
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