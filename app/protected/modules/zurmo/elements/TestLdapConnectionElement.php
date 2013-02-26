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
     * Utilize this element to display a  button that can be used to send a test Ldap connection while setting
     * up the Ldap server configuration.
     */
    class TestLdapConnectionElement extends Element
    {
        /**
         * Renders a button.
         * @return A string containing the element's content.
         */
        protected function renderControlEditable()
        {
            $htmlOptions             = array();
            $htmlOptions['id']       = $this->getEditableInputId();
            $htmlOptions['name']     = $this->getEditableInputName();
            $htmlOptions['disabled'] = $this->getDisabledValue();
            $htmlOptions             = array_merge($this->getHtmlOptions(), $htmlOptions);
            $content                 = $this->renderTestButton();
            return $content;
        }

        protected function renderControlNonEditable()
        {
            throw new NotImplementedException();
        }

        protected function renderError()
        {
            return null;
        }

       /**
         * Render a test button. This link calls a modal
         * popup.
         * @return The element's content as a string.
         */
        protected function renderTestButton()
        {
            $content  = '<span>';
            $content .= ZurmoHtml::ajaxLink(
                ZurmoHtml::tag('span', array('class' => 'z-label'), Zurmo::t('Default', 'Test Connection')),
                Yii::app()->createUrl('zurmo/ldap/testConnection/', array()),
                static::resolveAjaxOptionsForTestLdapConnection($this->form->getId()),
                array('id' => 'TestLdapConnectionButton', 'class' => 'LdapTestingButton z-button')
            );
            $content .= '</span>';
            return $content;
        }

        protected static function resolveAjaxOptionsForTestLdapConnection($formId)
        {
            assert('is_string($formId)');
            $title               = Zurmo::t('Default', 'Test Connection Results');
            $ajaxOptions         = ModalView::getAjaxOptionsForModalLink($title);
            $ajaxOptions['type'] = 'POST';
            $ajaxOptions['data'] = 'js:$("#' . $formId . '").serialize()';
            return $ajaxOptions;
        }
    }
?>