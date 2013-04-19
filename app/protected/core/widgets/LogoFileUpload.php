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
     * Class to extend File Upload Widget
     * Displays thumbnail with uploaded file
     */
    class LogoFileUpload extends FileUpload
    {
        public function run()
        {
            $id = $this->getId();
            $jsonEncodedExistingFiles = CJSON::encode($this->existingFiles);

            if ($this->allowMultipleUpload)
            {
                $sendAction = null;
                $addLabel   = ZurmoHtml::tag('strong', array('class' => 'add-label'), Zurmo::t('Core', 'Add Files'));
            }
            else
            {
                $sendAction = "\$('#{$this->formName}').find('.files > tbody').children().remove();";
                $addLabel   = ZurmoHtml::tag('strong', array('class' => 'add-label'), Zurmo::t('Core', 'Add File'));
            }
            $this->registerScriptForLogoFileElement($id, $sendAction, $jsonEncodedExistingFiles);

            $htmlOptions = array('id' => $this->inputId);
            $html  = '<div id="fileUpload' . $id . '">';
            $html .= '<div class="fileupload-buttonbar clearfix">';
            $html .= '<div class="addfileinput-button"><span>Y</span>' . $addLabel;
            $html .= ZurmoHtml::fileField($this->inputName, null, $htmlOptions);
            $html .= '</div>' . self::renderMaxSizeContent($this->maxSize, $this->showMaxSize);
            $html .= '</div><div class="fileupload-content"><table class="files"><tbody></tbody></table></div></div>';
            $html .= $this->makeUploadRowScriptContent();
            $html .= $this->makeDownloadRowScriptContent();
            echo $html;
        }

        protected function registerScriptForLogoFileElement($id, $sendAction, $jsonEncodedExistingFiles)
        {
            // Begin Not Coding Standard
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
                    makeOrRemoveTogglableSpinner(true, '#'+'fileUpload{$id}');
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
            // End Not Coding Standard
            Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $id, $javaScript);
        }

        protected function makeDownloadRowScriptContent()
        {
            $deleteLabel   = 'Delete';
            $removeLabel   = Zurmo::t('Core', 'Remove');
            $scriptContent = <<<EOD
<script id="template-download" type="text/x-jquery-tmpl">
    <tr class="template-download uploaded-logo-template{{if error}} ui-state-error{{/if}}">
        {{if error}}
            <td class="error" colspan="4">\${error}</td>
        {{else}}
            <td class="name">
                <span class="uploaded-logo"><img src="\${thumbnail_url}"/></span>
                \${name} <span class="file-size">(\${sizef})</span>
                <span class="upload-actions delete">
                    <button class="icon-delete" title="{$removeLabel}" data-url="{$this->deleteUrl}"><span><!--{$deleteLabel}--><span></button>
                </span>
                <input name="{$this->hiddenInputName}[]" type="hidden" value="\${id}"/>
            </td>
        {{/if}}
    </tr>
</script>
EOD;
            return $scriptContent;
        }
    }
?>