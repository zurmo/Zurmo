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
            $title       = Yii::t('Default', 'If unchecked, will use system SMTP settings.');
            $content     = '<span id="custom-outbound-settings-tooltip" class="tooltip"  title="' . $title . '">?</span>';
            $qtip = new ZurmoTip(array('options' => array('position' => array('my' => 'bottom right', 'at' => 'top left'))));
            $qtip->addQTip("#custom-outbound-settings-tooltip");
            return $content;
        }

        protected function renderLabel()
        {
            $label = Yii::t('Default', 'Custom Outbound Email Settings');
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