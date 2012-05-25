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
     * Utilize this element to display a text input and button that can be used to send a test email while setting
     * up the outbound email configuration.
     */
    class SendATestEmailToElement extends Element
    {
        /**
         * Renders the text input and button.
         * @return A string containing the element's content.
         */
        protected function renderControlEditable()
        {
            assert('empty($this->model->{$this->attribute})');
            $htmlOptions             = array();
            $htmlOptions['id']       = $this->getEditableInputId();
            $htmlOptions['name']     = $this->getEditableInputName();
            $htmlOptions['disabled'] = $this->getDisabledValue();
            $htmlOptions             = array_merge($this->getHtmlOptions(), $htmlOptions);
            $content                 = '<div id="send-test-email-field"><div>';
            $content                .= $this->form->textField($this->model, $this->attribute, $htmlOptions);
            $content                .= '</div>';
            $content                .= '</div>';
            $content                .= $this->renderTestButton();
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
            $id       = 'SendATestEmailToButton';
            $content  = '<span>';
            $content .= CHtml::ajaxButton(Yii::t('Default', 'Send Test Email'),
                Yii::app()->createUrl('emailMessages/default/sendTestMessage/', array()),
                    array(
                        'type' => 'POST',
                        'data' => 'js:$("#' . $this->form->getId() . '").serialize()',
                        'beforeSend' => 'js:function(){$(\'#' . $id . '\').parent().addClass(\'modal-model-select-link\');}',
                        'complete'   => 'js:function(){$(\'#' . $id . '\').parent().removeClass(\'modal-model-select-link\');}',
                        'onclick' => '$("#modalContainer").dialog("open"); return false;',
                        'update'  => '#modalContainer',
                    ),
                    array('id' => $id)
            );
            $content .= '</span>';
            return $content;
        }
    }
?>