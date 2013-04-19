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

    class OutboundSettingsCheckBoxElement extends CheckBoxElement
    {
        protected function renderControlEditable()
        {
            $attribute     = $this->attribute;
            $isHidden      = !$this->model->$attribute;
            if ($isHidden)
            {
                $style = 'display: none;';
            }
            else
            {
                $style = null;
            }
            $checkBox      = parent::renderControlEditable();
            $sendTestEmail = new SendATestEmailToElement($this->model, 'aTestToAddress', $this->form);
            $sendTestEmail->editableTemplate = '{label}{content}{error}';
            $content       = ZurmoHtml::tag('div', array('class' => 'beforeToolTip'), $checkBox);
            $settings      = $this->renderEditableTextField($this->model, $this->form, 'outboundHost');
            $settings     .= $this->renderEditableTextField($this->model, $this->form, 'outboundPort');
            $settings     .= $this->renderEditableTextField($this->model, $this->form, 'outboundUsername');
            $settings     .= $this->renderEditableTextField($this->model, $this->form, 'outboundPassword', true);
            $settings     .= $this->renderEditableTextField($this->model, $this->form, 'outboundSecurity');
            $settings     .= $sendTestEmail->renderEditable();
            $content      .= ZurmoHtml::tag('div', array('class' => 'outbound-settings', 'style' => $style),
                                         $settings);
            $this->renderScripts();
            return $content;
        }

        public function renderEditableTextField($model, $form, $attribute, $isPassword = false)
        {
            $id          = $this->getEditableInputId($attribute);
            $htmlOptions = array(
                'name'  => $this->getEditableInputName($attribute),
                'id'    => $id,
            );
            $label       = $form->labelEx  ($model, $attribute, array('for'   => $id));
            if (!$isPassword)
            {
                $textField = $form->textField($model, $attribute, $htmlOptions);
            }
            else
            {
                $textField = $form->passwordField($model, $attribute, $htmlOptions);
            }
            $error       = $form->error    ($model, $attribute);
            return '<div>' . $label . $textField . $error . '</div>';
        }

        protected static function renderToolTipContent()
        {
            $title       = Zurmo::t('UsersModule', 'If unchecked, will use system SMTP settings.');
            $content     = '<span id="custom-outbound-settings-tooltip" class="tooltip"  title="' . $title . '">?</span>';
            $qtip = new ZurmoTip(array('options' => array('position' => array('my' => 'bottom right', 'at' => 'top left'))));
            $qtip->addQTip("#custom-outbound-settings-tooltip");
            return $content;
        }

        protected function renderLabel()
        {
            $label = Zurmo::t('UsersModule', 'Custom Outbound Email Settings');
            if ($this->form === null)
            {
                return $this->getFormattedAttributeLabel();
            }
            $content  = ZurmoHtml::label($label, $this->getEditableInputId());
            $content .= self::renderToolTipContent();
            return $content;
        }

        protected function renderScripts()
        {
            $checkBoxId = $this->getEditableInputId();
            // Begin Not Coding Standard
            Yii::app()->clientScript->registerScript('userEmailConfigurationOutbound', "
                    $('#{$checkBoxId}').change(function(){
                        $('.outbound-settings').toggle();
                    });
                ");
            // End Not Coding Standard
        }
    }
?>