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
     * View class for the  email messages component for the workflow wizard user interface
     */
    class EmailMessagesForWorkflowWizardView extends ComponentForWorkflowWizardView
    {
        const ADD_EMAIL_MESSAGE_LINK_ID   ='AddEmailMessageLink';

        /**
         * @return string
         */
        public static function getWizardStepTitle()
        {
            return Zurmo::t('WorkflowsModule', 'Select Messages');
        }

        /**
         * @return string
         */
        public static function getPreviousPageLinkId()
        {
            return 'emailMessagesPreviousLink';
        }

        /**
         * @return string
         */
        public static function getNextPageLinkId()
        {
            return 'emailMessagesNextLink';
        }

        /**
         * @return string
         */
        public static function getZeroComponentsClassName()
        {
            return 'ZeroEmailMessages';
        }

        public function registerScripts()
        {
            parent::registerScripts();
            $this->registerRemoveEmailMessageScript();
            $this->registerRemoveEmailMessageRecipientScript();
        }

        /**
         * @return bool
         */
        protected function isListContentSortable()
        {
            return true;
        }

        /**
         * @return string
         */
        protected function renderFormContent()
        {
            $content  = '<div>';
            $content .= $this->renderAddEmailMessageLinkContentAndWrapper();
            $content .= $this->renderZeroComponentsContentAndWrapper();
            $content .= $this->renderEmailMessagesContentAndWrapper();
            $content .= '</div>';
            $this->registerScripts();
            return $content;
        }

        /**
         * @return string
         */
        protected function getZeroComponentsMessageContent()
        {
            return '<div class="large-icon"></div><h2>' . Zurmo::t('WorkflowsModule', 'Send a message') . '</h2>';
        }

        /**
         * @return string
         */
        protected function renderZeroComponentsContentAndWrapper()
        {
            return ZurmoHtml::tag('div', array('class' => 'zero-components-view ' .
                   ComponentForWorkflowForm::TYPE_EMAIL_MESSAGES), $this->getZeroComponentsContent());
        }

        /**
         * @return string
         */
        protected function renderAddEmailMessageLinkContentAndWrapper()
        {
            $content  = $this->renderAddEmailMessageLink(Zurmo::t('WorkflowsModule', 'Add Email Message'));
            return ZurmoHtml::tag('div', array('class' => 'add-email-message-button-container'), $content);
        }

        /**
         * @return string
         */
        protected function renderEmailMessagesContentAndWrapper()
        {
            $rowCount                    = 0;
            $items                       = $this->getItemsContent($rowCount);
            $itemsContent                = $this->getNonSortableListContent($items);
            $idInputHtmlOptions          = array('id' => static::resolveRowCounterInputId(ComponentForWorkflowForm::TYPE_EMAIL_MESSAGES));
            $hiddenInputName             = ComponentForWorkflowForm::TYPE_EMAIL_MESSAGES . 'RowCounter';
            $droppableAttributesContent  = ZurmoHtml::tag('div', array('class' => 'dynamic-rows'), $itemsContent);
            $content                     = ZurmoHtml::hiddenField($hiddenInputName, $rowCount, $idInputHtmlOptions);
            $content                    .= ZurmoHtml::tag('div', array('class' => 'droppable-dynamic-rows-container ' .
                                           ComponentForWorkflowForm::TYPE_EMAIL_MESSAGES), $droppableAttributesContent);
            return $content;
        }

        /**
         * @param string $label
         * @return string
         */
        protected function renderAddEmailMessageLink($label)
        {
            assert('is_string($label)');
            $rowCounterInputId = static::resolveRowCounterInputId(ComponentForWorkflowForm::TYPE_EMAIL_MESSAGES);
            $moduleClassNameId = get_class($this->model) . '[moduleClassName]';
            $url               = Yii::app()->createUrl('workflows/default/addEmailMessage',
                                 array_merge($_GET, array('type' => $this->model->type)));
            $aContent          = ZurmoHtml::wrapLink($label);
            return  ZurmoHtml::ajaxLink($aContent, $url,
                    array(
                        'type'    => 'GET',
                        'data'    => 'js:\'moduleClassName=\' + $("input:radio[name=\"' .
                            $moduleClassNameId . '\"]:checked").val() + ' .
                            '\'&rowNumber=\' + $(\'#' . $rowCounterInputId . '\').val()',
                        'url'     =>  $url,
                        'beforeSend' => 'js:function(){ makeOrRemoveLoadingSpinner(true, "#" + $(this).attr("id")); }',
                        'success' => 'js:function(data)
                        {
                            $(\'#' . $rowCounterInputId . '\').val(parseInt($(\'#' . $rowCounterInputId . '\').val()) + 1);
                            $(".droppable-dynamic-rows-container.' . ComponentForWorkflowForm::TYPE_EMAIL_MESSAGES
                                . '").find(".dynamic-rows").find("ul:first").first().append(data);
                            rebuildWorkflowEmailMessageRowNumbers("' . get_class($this) . '");
                            $(".' . static::getZeroComponentsClassName() . '").hide();
                        }',
                    ),
                    array('id' => self::ADD_EMAIL_MESSAGE_LINK_ID,
                          'class'      => 'attachLoading z-button ')
            );
        }

        /**
         * @return int
         */
        protected function getItemsCount()
        {
            return count($this->model->emailMessages);
        }

        /**
         * @param int $rowCount
         * @return array|string
         */
        protected function getItemsContent(& $rowCount)
        {
            return $this->renderEmailMessages($rowCount, $this->model->emailMessages);
        }

        /**
         * @param integer $rowCount
         * @param Array $emailMessages
         * @return array
         */
        protected function renderEmailMessages(& $rowCount, $emailMessages)
        {
            assert('is_int($rowCount)');
            assert('is_array($emailMessages)');
            $items                      = array();
            foreach ($emailMessages as $emailMessage)
            {
                $inputPrefixData   = array(get_class($this->model), ComponentForWorkflowForm::TYPE_EMAIL_MESSAGES, (int)$rowCount);
                $rowCounterInputId = ComponentForWorkflowWizardView::
                                     resolveRowCounterInputId(ComponentForWorkflowForm::TYPE_EMAIL_MESSAGES);
                $view              = new EmailMessageRowForWorkflowComponentView($emailMessage, $rowCount, $inputPrefixData,
                                        $this->form, get_class($this->model), $rowCounterInputId);
                $view->addWrapper  = false;
                $items[]           = array('content' => $view->render());
                $rowCount++;
            }
            return $items;
        }

        protected function registerRemoveEmailMessageScript()
        {
            $script = '
                $(".remove-dynamic-row-link").live("click", function()
                {
                    size = $(this).parent().parent().parent().find("li").size();
                    $(this).parent().parent().remove(); //removes the <li>
                    if (size < 2)
                    {
                        $(".' . static::getZeroComponentsClassName() . '").show();
                    }
                    rebuildWorkflowEmailMessageRowNumbers("' . get_class($this) . '");
                    return false;
                });
            ';
            // End Not Coding Standard
            Yii::app()->clientScript->registerScript('removeEmailMessageScript', $script);
        }

        protected function registerRemoveEmailMessageRecipientScript()
        {
            $script = '
                $(".' . EmailMessageRecipientRowForWorkflowComponentView::REMOVE_LINK_CLASS_NAME . '").live("click", function()
                {
                    div = $(this).parentsUntil(".' .
                            EmailMessageRowForWorkflowComponentView::RECIPIENTS_CONTAINER_CLASS_NAME . '").parent()
                            .find(".' . EmailMessageRowForWorkflowComponentView::EMAIL_MESSAGE_RECIPIENTS_ROW_CLASS_NAME .
                            '");
                    $(this).parent().parent().remove(); //removes the <li>
                    rebuildWorkflowEmailMessageRecipientRowNumbers(div);
                    return false;
                });
            ';
            // End Not Coding Standard
            Yii::app()->clientScript->registerScript('removeEmailMessageRecipientScript', $script);
        }
    }
?>