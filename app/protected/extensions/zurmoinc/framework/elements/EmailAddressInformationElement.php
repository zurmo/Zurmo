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
     * Display the email address collection
     * which includes an email address, opt out boolean
     * and invalid boolean.
     */
    class EmailAddressInformationElement extends Element
    {
        /**
         * Renders the editable email address content.
         * Takes the model attribute value and converts it into
         * at most 3 items. Email Address display, Opt Out checkbox,
         * and Invalid Email checkbox.
         * @return A string containing the element's content
         */
        protected function renderControlEditable()
        {
            assert('$this->model->{$this->attribute} instanceof Email');
            $addressModel = $this->model->{$this->attribute};
            $content = null;
            $content .= $this->renderEditableEmailAddressTextField    ($addressModel, $this->form, $this->attribute, 'emailAddress') . "<br/>\n";
            $content .= $this->renderEditableEmailAddressCheckBoxField($addressModel, $this->form, $this->attribute, 'optOut') . "<br/>\n";
            $content .= $this->renderEditableEmailAddressCheckBoxField($addressModel, $this->form, $this->attribute, 'isInvalid') . "<br/>\n";
            return $content;
        }

        protected function renderEditableEmailAddressTextField($model, $form, $inputNameIdPrefix, $attribute)
        {
            $htmlOptions = array(
                'name' => $this->getEditableInputName($inputNameIdPrefix, $attribute),
                'id'   => $this->getEditableInputId($inputNameIdPrefix, $attribute),
            );
            $textField = $form->textField($model, $attribute, $htmlOptions);
            $error     = $form->error    ($model, $attribute);
            return $textField . $error;
        }

        protected function renderEditableEmailAddressCheckBoxField($model, $form, $inputNameIdPrefix, $attribute)
        {
            $id = $this->getEditableInputId($inputNameIdPrefix, $attribute);
            $htmlOptions = array(
                'name' => $this->getEditableInputName($inputNameIdPrefix, $attribute),
                'id'   => $id,
            );
            $label         = $form->labelEx ($model, $attribute, array('for'   => $id));
            $checkBoxField = $form->checkBox($model, $attribute, $htmlOptions);
            $error         = $form->error   ($model, $attribute);
            return $checkBoxField . $label . $error;
        }

        /**
         * Renders the noneditable email address content.
         * Takes the model attribute value and converts it into
         * at most 3 items. Email Address display, Opt Out checkbox,
         * and Invalid Email checkbox.
         * @return A string containing the element's content.
         */
        protected function renderControlNonEditable()
        {
            $addressModel    = $this->model->{$this->attribute};
            $emailAddress    = $addressModel->emailAddress;
            $optOut    = $addressModel->optOut;
            $isInvalid    = $addressModel->isInvalid;
            $content = null;
            if (!empty($emailAddress))
            {
                $content  .= Yii::app()->format->email($emailAddress);
                if ($optOut || $isInvalid)
                {
                    $content  .= '&#160;&#40;';
                }
                if ($optOut)
                {
                    $content  .= Yii::t('Default', 'Opted Out');
                }
                if ($isInvalid)
                {
                    if ($optOut)
                    {
                        $content  .= ',&#160;';
                    }
                    $content  .= Yii::t('Default', 'Invalid');
                }
                if ($optOut || $isInvalid)
                {
                    $content  .= '&#41;';
                }
            }
            return $content;
        }

        protected function renderError()
        {
        }

        protected function renderLabel()
        {
            if ($this->form === null)
            {
                return $this->getFormattedAttributeLabel();
            }
            $id = $this->getEditableInputId($this->attribute, 'emailAddress');
            return $this->form->labelEx($this->model, $this->attribute, array('for' => $id));
        }
    }
?>
