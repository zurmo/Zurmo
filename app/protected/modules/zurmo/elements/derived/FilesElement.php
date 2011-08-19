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
     * User interface element for managing file attachments against a given model.
     *
     */
    class FilesElement extends ModelsElement implements DerivedElementInterface, ElementActionTypeInterface
    {
        protected function renderControlNonEditable()
        {
            assert('$this->model instanceof Item');
            $content = null;
            foreach ($this->model->files as $fileModel)
            {
                if ($content != null)
                {
                    $content .= "<br/>";
                }
                $content .= FileModelDisplayUtil::renderDownloadLinkContentByRelationModelAndFileModel($this->model,
                                                                                                       $fileModel);
                $content .= ' ' . FileModelDisplayUtil::convertSizeToHumanReadableAndGet((int)$fileModel->size);
            }
            return $content;
        }

        protected function renderControlEditable()
        {
            assert('$this->model instanceof Item');
            $existingFilesInformation = array();
            foreach ($this->model->files as $existingFile)
            {
                $existingFilesInformation[] = array('name' => $existingFile->name,
                                                    'size' => FileModelDisplayUtil::convertSizeToHumanReadableAndGet(
                                                                                    (int)$existingFile->size),
                                                    'id'   => $existingFile->id);
            }
            $inputNameAndId = get_class($this->model) . '_files';
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("filesElement");
            $cClipWidget->widget('ext.zurmoinc.framework.widgets.FileUpload', array(
                'uploadUrl'            => Yii::app()->createUrl("zurmo/fileModel/upload",
                                                        array('filesVariableName' => $inputNameAndId)),
                'deleteUrl'            => Yii::app()->createUrl("zurmo/fileModel/delete"),
                'inputName'            => $inputNameAndId,
                'inputId'              => $inputNameAndId,
                'hiddenInputName'      => 'filesIds',
                'formName'             => $this->form->id,
                'allowMultipleUpload'  => true,
                'existingFiles'        => $existingFilesInformation
            ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['filesElement'];
        }

        protected function renderError()
        {
        }

        protected function renderLabel()
        {
            return Yii::t('Default', 'Attachments');
        }

        public static function getDisplayName()
        {
            return Yii::t('Default', 'Attachments');
        }

        /**
         * Get the attributeNames of attributes used in
         * the derived element. For this element, there are no attributes from the model.
         * @return array - empty
         */
        public static function getModelAttributeNames()
        {
            return array();
        }

            /**
         * Gets the action type for the related model's action
         * that is called by the select button or the autocomplete
         * feature in the Editable render.
         */
        public static function getEditableActionType()
        {
            return null;
        }

        public static function getNonEditableActionType()
        {
            return null;
        }
    }
?>