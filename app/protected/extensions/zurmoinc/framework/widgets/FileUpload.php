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
     * Render a file upload element that can allow for multiple file uploads and calls ajax to upload the files to
     * the server as you add them.
     */
    class FileUpload extends ZurmoWidget
    {
        public $scriptFile = array('jquery.fileupload.js', 'jquery.fileupload-ui.js');

        public $assetFolderName = 'fileUpload';

        /**
         * Url used when uploading a file.
         * @var string
         */
        public $uploadUrl;

        /**
         * Url used when deleting a file.
         * @var string
         */
        public $deleteUrl;

        /**
         * Allow multiple file upload.
         * @var boolean
         */
        public $allowMultipleUpload = false;

        /**
         * Data to pass to the file upload script.
         * @see https://github.com/blueimp/jQuery-File-Upload/wiki/Options
         * @var array
         */
        public $options;

        /**
         * Name of form to attach actions to.
         * @var string
         */
        public $formName;

        /**
         * Name of the file input field.
         * @var string
         */
        public $inputName;

        /**
         * Id of the file input field.
         * @var string
         */
        public $inputId;

        /**
         * Used on the hidden input for each of the associated files. Stores the fileModel id.
         * @var string
         */
        public $hiddenInputName;

        /**
         * If existing files exist, this array should be populated with name, size, and id for each existing file.
         * @var array
         */
        public $existingFiles;

        /**
         * The maximum size allowed for file uploads.
         * @var integer
         */
        public $maxSize;

        /**
         * Initializes the widget.
         * This method will publish JUI assets if necessary.
         * It will also register jquery and JUI JavaScript files and the theme CSS file.
         * If you override this method, make sure you call the parent implementation first.
         */
        public function init()
        {
            assert('is_string($this->uploadUrl) && $this->uploadUrl != ""');
            assert('is_string($this->deleteUrl) && $this->deleteUrl != ""');
            assert('is_string($this->formName)  && $this->formName  != ""');
            assert('is_string($this->inputId)   && $this->inputId   != ""');
            assert('is_string($this->inputName) && $this->inputName != ""');
            assert('is_string($this->hiddenInputName) && $this->hiddenInputName != ""');
            assert('is_array($this->existingFiles)');
            parent::init();
        }

        public function run()
        {
            $id = $this->getId();
            $options                     = $this->options;
            $options['url']              = $this->uploadUrl;
            $options['uploadTable']      = "#files{$id}";
            $options['downloadTable']    = "#files{$id}";
            $options['buildUploadRow']   = $this->makeUploadRowScriptContent();
            $options['buildDownloadRow'] = $this->makeDownloadRowScriptContent();

            $encodedOptions  = CJavaScript::encode($options);
            $javaScript = <<<EOD
jQuery('#{$this->formName}').fileUploadUI({$encodedOptions});
$('#{$this->formName}').find('.delete-file-link').live('click', function () {
    $.ajax({
      url: "{$this->deleteUrl}&id=" + $(this).prev().val(),
    });
    $(this).parent().parent().remove();
});
EOD;
            Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $id, $javaScript);

            $htmlOptions = array('id' => $this->inputId);
            echo '<div class="multiple-file-upload">' . "\n";
            echo CHtml::fileField($this->inputName, null, $htmlOptions) . $this->renderMaxSizeContent($this->maxSize);
            echo '<table id="files' . $id . '">' . "\n";
            echo '<colgroup><col/><col/><col/></colgroup>' . "\n";
            echo '<tbody>'  . "\n";
            echo '<tr><td></td><td></td></tr>'. "\n";
            foreach ($this->existingFiles as $existingFile)
            {
                echo '<tr><td>' . Yii::app()->format->text($existingFile['name']) . '</td>' . "\n";
                echo '<td>' . Yii::app()->format->text($existingFile['size']) . '</td><td>';
                //Keep thie hidden input right before the delete link. This will ensure the delete link works properly.
                echo '<input name="' . $this->hiddenInputName . '[]" type="hidden" value="' . $existingFile['id'] . '"/>';
                echo '<span class="ui-icon ui-icon-trash delete-file-link">' . Yii::t('Default', 'Delete');
                echo '</span></td>' . "\n";
                echo '</tr>' . "\n";
            }
            echo '</tbody>' . "\n";
            echo '</table>' . "\n";
            echo '</div>'   . "\n";
        }

        private function makeDownloadRowScriptContent()
        {
            $deleteLabel = Yii::t('Default', 'Delete');
            $js = <<<EOD
js:function (file, index) {
    $('#{$this->formName}').find('.file-upload-error-row').remove();
    if (file.error != null)
    {
        return $('<tr class="file-upload-error-row"><td colspan="3"><span class="error">' + file.error + '</span></td><tr>');
    }
    else
    {
        return $('<tr><td>' + file.name + '<\/td>' +
            '<td>' + file.humanReadableSize + '</td>' +
            '<td><input name="{$this->hiddenInputName}[]" type="hidden" value="' + file.id + '"/>' +
            '<span class="ui-icon ui-icon-trash delete-file-link">$deleteLabel<\/span><\/td>' +
            '<\/tr>');
    }
}
EOD;
            return $js;
        }

        private function makeUploadRowScriptContent()
        {
            if ($this->allowMultipleUpload)
            {
                $params      = "file, index";
                $file        = "file[index].name";
                $extraAction = null;
            }
            else
            {
                $params      = "file, index";
                $file        = "file[0].name";
                $extraAction = "$('#{$this->formName}').find('.delete-file-link').parent().parent().remove()";
            }
            $cancelLabel = Yii::t('Default', 'Cancel');
            $js = <<<EOD
js:function ($params) {
    $extraAction
    return $('<tr>'+
        '<td class="filename">'+$file+'</td>'+
        '<td class="file_upload_progress"><div></div></td>'+
        '<td class="file_upload_cancel">'+
            '<button class="ui-state-default ui-corner-all">'+
                '<span class="ui-icon ui-icon-cancel">$cancelLabel</span>'+
            '</button>'+
        '</td>'+
    '</tr>');
}
EOD;
        return $js;
        }

        protected static function renderMaxSizeContent($maxSize)
        {
            assert('is_int($maxSize) || $maxSize == null');
            if($maxSize == null)
            {
                return;
            }
            $content = '&#160;' . Yii::t('Default', 'Max upload size: {maxSize}',
                       array('{maxSize}' => FileModelDisplayUtil::convertSizeToHumanReadableAndGet($maxSize)));
            return $content;
        }
    }
?>