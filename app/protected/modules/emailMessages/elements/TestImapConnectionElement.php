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
     * Utilize this element to display a text input and button that can be used to send a test email while setting
     * up the outbound email configuration.
     */
    class TestImapConnectionElement extends Element
    {
        /**
         * Renders the text input and button.
         * @return A string containing the element's content.
         */
        protected function renderControlEditable()
        {
            assert('empty($this->model->{$this->attribute})');
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
            $id       = 'testImapConnection';
            $content  = '<span>';
            $content .= ZurmoHtml::ajaxLink(
                ZurmoHtml::wrapLabel(Zurmo::t('ZurmoModule', 'Test Connection')),
                Yii::app()->createUrl('emailMessages/default/testImapConnection/', array()),
                static::resolveAjaxOptionsForTestEmailSettings($this->form->getId()),
                array('id' => $id, 'class' => 'EmailTestingButton z-button')
            );
            $content .= '</span>';
            return $content;
        }

        protected static function resolveAjaxOptionsForTestEmailSettings($formId)
        {
            assert('is_string($formId)');
            $title               = Zurmo::t('EmailMessagesModule', 'Test Message Results');
            $ajaxOptions         = ModalView::getAjaxOptionsForModalLink($title);
            $ajaxOptions['type'] = 'POST';
            $ajaxOptions['data'] = 'js:$("#' . $formId . '").serialize()';
            return $ajaxOptions;
        }
    }
?>