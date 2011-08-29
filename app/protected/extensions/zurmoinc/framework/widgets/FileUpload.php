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
     * Utilizes file upload plpugin here: https://github.com/blueimp/jQuery-File-Upload
     */
    class FileUpload extends ZurmoWidget
    {
        public $scriptFile = array('jquery.fileupload.js',
                                   'jquery.fileupload-ui.js', 'jquery.tmpl.min.js', 'jquery.iframe-transport.js');

        public $cssFile    = 'jquery.fileupload-ui.css';

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
         * Javascript string of an action to be performed before the upload of a file begins.
         * @var string
         */
        public $beforeUploadAction;

        /**
         * Javascript string of an action to be performed after a file is deleted.
         * @var string
         */
        public $afterDeleteAction;


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
            Yii::app()->getClientScript()->registerCoreScript('jquery.ui');
            parent::init();
        }

        public function run()
        {
            $id = $this->getId();
            $jsonEncodedExistingFiles = CJSON::encode($this->existingFiles);

            if ($this->allowMultipleUpload)
            {
                $sendAction = null;
                $addLabel   = Yii::t('Default', 'Add Files');
            }
            else
            {
                $sendAction = "\$('#{$this->formName}').find('.files > tbody').children().remove();";
                $addLabel = Yii::t('Default', 'Add File');
            }

            $javaScript = <<<EOD
$(function () {
    'use strict';

    // Initialize the jQuery File Upload widget:
    $('#fileUpload{$id}').fileupload({
        dataType: 'json',
        url: '{$this->uploadUrl}',
        autoUpload: true,
        sequentialUploads: true,
        maxFileSize: {$this->maxSize},
        add: function (e, data) {
            {$this->beforeUploadAction}
            {$sendAction}
            var that = $(this).data('fileupload');
            that._adjustMaxNumberOfFiles(-data.files.length);
            data.isAdjusted = true;
            data.isValidated = that._validate(data.files);
            data.context = that._renderUpload(data.files)
                .appendTo($(this).find('.files')).fadeIn(function () {
                    // Fix for IE7 and lower:
                    $(this).show();
                }).data('data', data);
            if ((that.options.autoUpload || data.autoUpload) &&
                    data.isValidated) {
                data.jqXHR = data.submit();
            }
        }
    });
    // Open download dialogs via iframes,
    // to prevent aborting current uploads:
    $('#fileUpload{$id} .files a:not([target^=_blank])').live('click', function (e) {
        e.preventDefault();
        $('<iframe style="display:none;"></iframe>')
            .prop('src', this.href)
            .appendTo('body');
    });
    $('.fileupload-buttonbar').removeClass('ui-widget-header ui-corner-top');
    $('.fileupload-content').removeClass('ui-widget-content ui-corner-bottom');
    $('#fileUpload{$id}').bind('fileuploaddestroy', function (e, data) {
    $('#ImportWizardForm_rowColumnDelimiter').removeAttr('readonly');
            {$this->afterDeleteAction}
    });
    $('#fileUpload{$id}').bind('fileuploadalways', function (e, data) {
        if (data == undefined || data.result == undefined ||
          ((data.result[0] != undefined && data.result[0].error != undefined) || data.result.error != undefined))
        {
            setTimeout(function () {
               $('#{$this->formName}').find('.files > tbody').children(':last').fadeOut('slow', function() { $(this).remove();});
               {$this->afterDeleteAction}
            }, 1000);
        }
    });
    //load existing files
    var existingFiles = {$jsonEncodedExistingFiles};
    var fu = $('#fileUpload{$id}').data('fileupload');
    fu._adjustMaxNumberOfFiles(-existingFiles.length);
    fu._renderDownload(existingFiles)
        .appendTo($('#fileUpload{$id} .files'))
        .fadeIn(function () {
            // Fix for IE7 and lower:
            $(this).show();
    });
});

EOD;
            Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $id, $javaScript);

            $htmlOptions = array('id' => $this->inputId);
            echo '<div id="fileUpload' . $id . '">'                         . "\n";
            echo '<div class="fileupload-buttonbar">'           	        . "\n";
            echo '<label class="fileinput-button">'                         . "\n";
            echo '<span>' . $addLabel . '</span>'                           . "\n";
            echo CHtml::fileField($this->inputName, null, $htmlOptions);
            echo '</label>' . $this->renderMaxSizeContent($this->maxSize)   . "\n";
            echo '</div><div class="clear"></div>'                          . "\n";
            echo '<div class="fileupload-content">'                         . "\n";
            echo '<table class="files"><tbody><tr><td colspan="6">'         . "\n";
            echo '</td></tr></tbody></table>'                               . "\n";
            echo '</div>'                                                   . "\n";
            echo '</div>'                                                   . "\n";
            echo $this->makeUploadRowScriptContent()                        . "\n";
            echo $this->makeDownloadRowScriptContent()                      . "\n";
        }

        private function makeDownloadRowScriptContent()
        {
            $deleteLabel = Yii::t('Default', 'Delete');
$scriptContent = <<<EOD
<script id="template-download" type="text/x-jquery-tmpl">
    <tr class="template-download{{if error}} ui-state-error{{/if}}">
        {{if error}}
            <td class="error" colspan="4">\${error}</td>
        {{else}}
            <input name="{$this->hiddenInputName}[]" type="hidden" value="\${id}"/>
            <td class="name" colspan="4">\${name}</td>
            <td class="size">\${size}</td>
            <td class="delete">
                <button data-type="DELETE" data-url="{$this->deleteUrl}&id=\${id}">{$deleteLabel}</button>
            </td>
        {{/if}}

    </tr>
</script>

EOD;
            return $scriptContent;
            return $js;
        }

        private function makeUploadRowScriptContent()
        {
            $startLabel  = Yii::t('Default', 'Start');
            $cancelLabel = Yii::t('Default', 'Cancel');
$scriptContent = <<<EOD
<script id="template-upload" type="text/x-jquery-tmpl">
    <tr class="template-upload{{if error}} ui-state-error{{/if}}">
        <td class="preview"></td>
        <td class="name">\${name}</td>
        <td class="size">\${sizef}</td>
        {{if error}}
            <td class="error" colspan="2">\${error}</td>
        {{else}}
            <td class="progress"><div></div></td>
            <td class="start"><button>{$startLabel}</button></td>
        {{/if}}
        <td class="cancel"><button>{$cancelLabel}</button></td>
    </tr>
</script>
EOD;
            return $scriptContent;
        }

        protected static function renderMaxSizeContent($maxSize)
        {
            assert('is_int($maxSize) || $maxSize == null');
            if ($maxSize == null)
            {
                return;
            }
            $content = '&#160;' . Yii::t('Default', 'Max upload size: {maxSize}',
                       array('{maxSize}' => FileModelDisplayUtil::convertSizeToHumanReadableAndGet($maxSize)));
            return $content;
        }
    }
?>