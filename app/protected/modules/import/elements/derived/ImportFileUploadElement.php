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
     * Element for allowing a user to upload a file for an import.
     */
    class ImportFileUploadElement extends Element
    {
        protected function renderControlNonEditable()
        {
            throw new NotSupportedException();
        }

        protected function renderControlEditable()
        {
            assert('$this->model instanceof ImportWizardForm');
            assert('$this->attribute == null');
            $existingFilesInformation = array();
            if (!empty($this->model->fileUploadData))
            {
                $existingFilesInformation[]        = $this->model->fileUploadData;
                $existingFilesInformation[0]['id'] = $this->model->id;
            }
            $content = $this->renderDelimiterAndEnclosureContent($existingFilesInformation);

            $inputNameAndId = $this->getEditableInputId('file');

            $beforeUploadAction  = "$('#{$this->getEditableInputId('rowColumnDelimiter')}').attr('readonly', true);";
            $beforeUploadAction .= "$('#{$this->getEditableInputId('rowColumnDelimiter')}').addClass('readonly-field');";
            $beforeUploadAction .= "$('#{$this->getEditableInputId('rowColumnEnclosure')}').attr('readonly', true);";
            $beforeUploadAction .= "$('#{$this->getEditableInputId('rowColumnEnclosure')}').addClass('readonly-field');";

            $afterDeleteAction   = "$('#{$this->getEditableInputId('rowColumnDelimiter')}').removeAttr('readonly');";
            $afterDeleteAction  .= "$('#{$this->getEditableInputId('rowColumnDelimiter')}').removeClass('readonly-field');";
            $afterDeleteAction  .= "$('#{$this->getEditableInputId('rowColumnEnclosure')}').removeAttr('readonly');";
            $afterDeleteAction  .= "$('#{$this->getEditableInputId('rowColumnEnclosure')}').removeClass('readonly-field');";

            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("filesElement");
            $cClipWidget->widget('ext.zurmoinc.framework.widgets.FileUpload', array(
                'uploadUrl'            => Yii::app()->createUrl("import/default/uploadFile",
                                                        array('filesVariableName' => $inputNameAndId,
                                                              'id' => $this->model->id)),
                'deleteUrl'            => Yii::app()->createUrl("import/default/deleteFile"),
                'inputName'            => $inputNameAndId,
                'inputId'              => $inputNameAndId,
                'hiddenInputName'      => 'fileId',
                'formName'             => $this->form->id,
                'existingFiles'        => $existingFilesInformation,
                'maxSize'              => (int)InstallUtil::getMaxAllowedFileSize(),
                'beforeUploadAction'   => $beforeUploadAction,
                'afterDeleteAction'    => $afterDeleteAction,
            ));
            $cClipWidget->endClip();
            $content .= $cClipWidget->getController()->clips['filesElement'];
            return $content;
        }

        protected function renderDelimiterAndEnclosureContent($existingFilesInformation)
        {
            assert('is_array($existingFilesInformation)');
            $params = array('htmlOptions' => array('size' => 5));
            if (count($existingFilesInformation) == 1)
            {
                $params['htmlOptions']['readonly']  = 'readonly';
                $params['htmlOptions']['class']     = 'readonly-field';
            }

            $delimiterElement                          = new TextElement($this->model, 'rowColumnDelimiter',
                                                         $this->form, $params);
            $delimiterElement->editableTemplate        = '<tr><td>{label}</td><td colspan="3">{content}</td></tr>';
            $enclosureElement                          = new TextElement($this->model, 'rowColumnEnclosure',
                                                         $this->form, $params);
            $enclosureElement->editableTemplate        = '<tr><td>{label}</td><td colspan="3">{content}</td></tr>';
            $content  = $delimiterElement->render();
            //$content .= $enclosureElement->render();
            return $content;
        }

        protected function renderError()
        {
        }

        protected function renderLabel()
        {
            return Yii::t('Default', 'Please select the CSV to upload');
        }
    }
?>