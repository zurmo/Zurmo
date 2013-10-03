<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    abstract class ContactEmailTemplateNamesDropDownElement extends StaticDropDownFormElement
    {
        const DISABLE_DROPDOWN_WHEN_AJAX_IN_PROGRESS    = true;

        const DISABLE_TEXTBOX_WHEN_AJAX_IN_PROGRESS    = true;

        const NOTIFICATION_BAR_ID                      = 'FlashMessageBar';

        abstract protected function getModuleId();

        protected function renderControlNonEditable()
        {
            throw new NotSupportedException();
        }

        protected function renderControlEditable()
        {
            $this->attribute = 'contactEmailTemplateNames';
            $dropDownArray = $this->getDropDownArray();
            $htmlOptions   = $this->getEditableHtmlOptions();
            $name          = $this->getEditableInputName();
            $this->registerScripts();
            return ZurmoHtml::dropDownList($name, null, $dropDownArray, $htmlOptions);
        }

        protected function registerScripts()
        {
            $this->registerUpdateFlashBarScript();
            $this->registerDropDownChangeHandlerScript();
        }

        protected function registerDropDownChangeHandlerScript()
        {
            $dropDownId = $this->getEditableInputId() . '_value';
            $scriptName = $dropDownId . '_changeHandler';
            if (Yii::app()->clientScript->isScriptRegistered($scriptName))
            {
                return;
            }
            else
            {
                // Begin Not Coding Standard
                Yii::app()->clientScript->registerScript($scriptName, '
                        function updateContentElementsWithData(textContentElement, htmlContentElement, subjectElement, data)
                        {
                            if ($(htmlContentElement).css("display") !== "none")
                            {
                                $(htmlContentElement).redactor("toggle");
                            }
                            updateElementWithData(textContentElement, data.textContent);
                            updateElementWithData(subjectElement, data.subject);
                            $(htmlContentElement).redactor("set", data.htmlContent);
                            //$(htmlContentElement).redactor("sync");
                        }

                        function updateElementWithData(element, data)
                        {
                            if ($(element).attr("type") == "text")
                            {
                                $(element).val(data);
                            }
                            else
                            {
                                $(element).html(data);
                            }
                        }

                        function deleteExistingAttachments()
                        {
                            $("table.files tr.template-download td.name span.upload-actions.delete button.icon-delete")
                                .click();
                        }

                        function updateAddFilesWithDataFromAjax(filesIds, notificationBarId)
                        {
                            if (filesIds != "")
                            {
                                var url             = "' . $this->getCloneExitingFilesUrl() . '";
                                var templateId      = "#' . FileUpload::DOWNLOAD_TEMPLATE_ID .'";
                                var targetClass     = ".files";
                                var filesIdsString  = filesIds.join();
                                $.ajax(
                                    {
                                        url:        url,
                                        dataType:   "json",
                                        data:
                                        {
                                            commaSeparatedExistingModelIds: filesIdsString
                                        },
                                        success:    function(data, status, request)
                                                    {
                                                        $(templateId).tmpl(data).appendTo(targetClass);
                                                    },
                                        error:      function(request, status, error)
                                                    {
                                                        var data = {' . // Not Coding Standard
                                                                    '   "message" : "' . Zurmo::t('Core',
                                                                            'There was an error processing your request') .
                                                                    '",
                                                                    "type"    : "error"
                                                                    };
                                                        updateFlashBar(data, notificationBarId);
                                                    },
                                    }
                                );
                            }
                        }

                        $("#' . $dropDownId . '").unbind("change.action").bind("change.action", function(event, ui)
                        {
                            selectedOption          = $(this).find(":selected");
                            selectedOptionValue     = selectedOption.val();
                            if (selectedOptionValue)
                            {
                                var dropDown            = $(this);
                                var notificationBarId   = "' . static::NOTIFICATION_BAR_ID . '";
                                var url                 = "' . $this->getEmailTemplateDetailsUrl() . '";
                                var disableDropDown     = "' . static::DISABLE_DROPDOWN_WHEN_AJAX_IN_PROGRESS . '";
                                var disableTextBox      = "' . static::DISABLE_TEXTBOX_WHEN_AJAX_IN_PROGRESS. '";
                                var textContentId       = "' . $this->getTextContentId() . '";
                                var htmlContentId       = "' . $this->getHtmlContentId() . '";
                                var subjectId           = "' . $this->getSubjectId() . '";
                                var subjectElement      = $("#" + subjectId);
                                var textContentElement  = $("#" + textContentId);
                                var htmlContentElement  = $("#" + htmlContentId);
                                var redActorElement     = $("#" + htmlContentId).parent().find(".redactor_editor");
                                $.ajax(
                                    {
                                        url:        url,
                                        dataType:   "json",
                                        data:
                                        {
                                            id: selectedOptionValue,
                                            renderJson: true,
                                            includeFilesInJson: true
                                        },
                                        beforeSend: function(request, settings)
                                                    {
                                                        $(this).makeLargeLoadingSpinner(true, ".email-template-content");
                                                        if (disableDropDown == true)
                                                        {
                                                            $(dropDown).attr("disabled", "disabled");
                                                        }
                                                        if (disableTextBox == true)
                                                        {
                                                            $(textContentElement).attr("disabled", "disabled");
                                                            $(htmlContentElement).attr("disabled", "disabled");
                                                            $(subjectElement).attr("disabled", "disabled");
                                                            $(redActorElement).hide();
                                                        }
                                                        deleteExistingAttachments();
                                                    },
                                        success:    function(data, status, request)
                                                    {
                                                        $(this).makeLargeLoadingSpinner(false, ".email-template-content");
                                                        $(".email-template-content .big-spinner").remove();
                                                        updateContentElementsWithData(textContentElement,
                                                                                        htmlContentElement,
                                                                                        subjectElement,
                                                                                        data);
                                                        updateAddFilesWithDataFromAjax(data.filesIds, notificationBarId);
                                                    },
                                        error:      function(request, status, error)
                                                    {
                                                        var data = {' . // Not Coding Standard
                                                                    '   "message" : "' . Zurmo::t('Core',
                                                                            'There was an error processing your request') .
                                                                        '",
                                                                        "type"    : "error"
                                                                    };
                                                        updateFlashBar(data, notificationBarId);
                                                    },
                                        complete:   function(request, status)
                                                    {
                                                        $(dropDown).removeAttr("disabled");
                                                        $(dropDown).val("");
                                                        $(textContentElement).removeAttr("disabled");
                                                        $(htmlContentElement).removeAttr("disabled");
                                                        $(subjectElement).removeAttr("disabled");
                                                        $(redActorElement).show();
                                                        event.preventDefault();
                                                        return false;
                                                    }
                                    }
                                );
                            }
                        }
                    );
                ');
                // End Not Coding Standard
            }
        }

        protected function registerUpdateFlashBarScript()
        {
            if (Yii::app()->clientScript->isScriptRegistered('handleUpdateFlashBar'))
            {
                return;
            }
            else
            {
                Yii::app()->clientScript->registerScript('handleUpdateFlashBar', '
                    function updateFlashBar(data, flashBarId)
                    {
                        $("#" + flashBarId).jnotifyAddMessage(
                        {
                            text: data.message,
                            permanent: false,
                            showIcon: true,
                            type: data.type
                        });
                    }
                ');
            }
        }

        protected function renderLabel()
        {
            return null;
        }

        protected function renderError()
        {
            return null;
        }

        protected function getDropDownArray()
        {
            return $this->getAvailableContactEmailTemplateNamesArray();
        }

        protected function getAvailableContactEmailTemplateNamesArray()
        {
            $emailTemplates         = EmailTemplate::getByType(EmailTemplate::TYPE_CONTACT);
            $emailTemplatesArray    = array();
            foreach ($emailTemplates as $emailTemplate)
            {
                $emailTemplatesArray[$emailTemplate->id] = $emailTemplate->name;
            }
            asort($emailTemplatesArray);
            return $emailTemplatesArray;
        }

        protected function getEditableHtmlOptions()
        {
            $moduleName         = $this->getModuleId() . 'sModule';
            $prompt             = array('prompt' => Zurmo::t($moduleName, 'Select a template'));
            $parentHtmlOptions  = parent::getEditableHtmlOptions();
            $htmlOptions        = CMap::mergeArray($parentHtmlOptions, $prompt);
            return $htmlOptions;
        }

        protected function getEmailTemplateDetailsUrl()
        {
            return Yii::app()->createUrl('/emailTemplates/default/details');
        }

        protected function getTextContentId()
        {
            $textContentId = $this->getModuleId();
            $textContentId .= '_';
            $textContentId .= EmailTemplateHtmlAndTextContentElement::TEXT_CONTENT_INPUT_NAME;
            return $textContentId;
        }

        protected function getSubjectId()
        {
            $id = $this->getModuleId();
            $id .= '_subject';
            return $id;
        }

        protected function getHtmlContentId()
        {
            $htmlContentId = $this->getModuleId();
            $htmlContentId .= '_';
            $htmlContentId .= EmailTemplateHtmlAndTextContentElement::HTML_CONTENT_INPUT_NAME;
            return $htmlContentId;
        }

        protected function getCloneExitingFilesUrl()
        {
            return Yii::app()->createUrl('/zurmo/fileModel/cloneExistingFiles');
        }
    }
?>