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
     * View class for the actions component for the workflow wizard user interface
     */
    class ActionsForWorkflowWizardView extends ComponentForWorkflowWizardView
    {
        const ACTION_TYPE_NAME                          = 'actionType';
        const ACTION_TYPE_RELATION_NAME                 = 'actionTypeRelatedModel';
        const ACTION_TYPE_RELATED_MODEL_RELATION_NAME   = 'actionTypeRelatedRelatedModel';
        const ACTION_TYPE_RELATION_DIV_ID               = 'action-type-related-model-selector';
        const ACTION_TYPE_RELATED_MODEL_RELATION_DIV_ID = 'action-type-related-related-model-selector';

        /**
         * @return string
         */
        public static function getWizardStepTitle()
        {
            return Zurmo::t('WorkflowsModule', 'Select Actions');
        }

        /**
         * @return string
         */
        public static function getPreviousPageLinkId()
        {
            return 'actionsPreviousLink';
        }

        /**
         * @return string
         */
        public static function getNextPageLinkId()
        {
            return 'actionsNextLink';
        }

        /**
         * @param string $moduleClassName
         * @param string $modelClassName
         * @param string $workflowType
         * @return array
         */
        public static function resolveTypeRelationDataAndLabels($moduleClassName, $modelClassName, $workflowType)
        {
            assert('is_string($moduleClassName)');
            assert('is_string($modelClassName)');
            assert('is_string($workflowType)');
            $data = array('' => Zurmo::t('WorkflowsModule', 'Select Module'));
            return array_merge($data, ActionForWorkflowForm::
                                      getTypeRelationDataAndLabels($moduleClassName, $modelClassName, $workflowType));
        }

        /**
         * @param string $moduleClassName
         * @param string $modelClassName
         * @param string $workflowType
         * @param string $relation
         * @return array
         */
        public static function resolveTypeRelatedModelRelationDataAndLabels($moduleClassName, $modelClassName, $workflowType, $relation)
        {
            assert('is_string($moduleClassName)');
            assert('is_string($modelClassName)');
            assert('is_string($workflowType)');
            assert('is_string($relation)');
            $data = array('' => Zurmo::t('WorkflowsModule', 'Select Module'));
            return array_merge($data, ActionForWorkflowForm::getTypeRelatedModelRelationDataAndLabels($moduleClassName,
                                      $modelClassName, $workflowType, $relation));
        }

        /**
         * @return string
         */
        public static function getZeroComponentsClassName()
        {
            return 'ZeroActions';
        }

        /**
         * @return array
         */
        protected static function resolveTypeDataAndLabels()
        {
            $data = array();
            return array_merge($data, ActionForWorkflowForm::getTypeDataAndLabels());
        }

        /**
         * Register scripts needed for this view
         */
        public function registerScripts()
        {
            parent::registerScripts();
            $this->registerActionTypeDropDownOnChangeScript();
            $this->registerActionTypeRelationDropDownOnChangeScript();
            $this->registerActionTypeRelatedModelRelationDropDownOnChangeScript();
            $this->registerAddActionScript();
            $this->registerRemoveActionScript();
            $this->registerTypeChangeScript();
            $this->registerRowEditScript();
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
            $content .= $this->renderAttributeSelectorContentAndWrapper();
            $content .= $this->renderZeroComponentsContentAndWrapper();
            $content .= $this->renderActionsContentAndWrapper();
            $content .= '</div>';
            $this->registerScripts();
            return $content;
        }

        /**
         * @return string
         */
        protected function getZeroComponentsMessageContent()
        {
            return '<div class="large-icon"></div><h2>' . Zurmo::t('WorkflowsModule', 'Select an action') . '</h2>';
        }

        /**
         * @return string
         */
        protected function renderZeroComponentsContentAndWrapper()
        {
            return ZurmoHtml::tag('div', array('class' => 'zero-components-view ' .
                ComponentForWorkflowForm::TYPE_ACTIONS), $this->getZeroComponentsContent());
        }

        /**
         * @return string
         */
        protected function renderAttributeSelectorContentAndWrapper()
        {
            $htmlOptions                   = array();
            $htmlOptions['empty']          = Zurmo::t('WorkflowsModule', 'Select Action');
            $actionTypeContent             = ZurmoHtml::dropDownList(self::ACTION_TYPE_NAME, null,
                                             static::resolveTypeDataAndLabels(), $htmlOptions);
            $content  = '';
            $content .= $actionTypeContent;
            $content .= ZurmoHtml::tag('div', array('id'    => self::ACTION_TYPE_RELATION_DIV_ID,
                                                    'class' => 'related-model-selector',
                                                    'style' => "display:none;"), null);
            $content .= ZurmoHtml::tag('div', array('id'    => self::ACTION_TYPE_RELATED_MODEL_RELATION_DIV_ID,
                                                    'class' => 'related-model-selector',
                                                    'style' => "display:none;"), null);
            return      ZurmoHtml::tag('div', array('class' => 'action-type-selector-container'), $content);
        }

        /**
         * @return string
         */
        protected function renderActionsContentAndWrapper()
        {
            $rowCount                    = 0;
            $items                       = $this->getItemsContent($rowCount);
            $itemsContent                = $this->getSortableListContent($items, ComponentForWorkflowForm::TYPE_ACTIONS);
            $idInputHtmlOptions          = array('id' => static::resolveRowCounterInputId(ComponentForWorkflowForm::TYPE_ACTIONS));
            $hiddenInputName             = ComponentForWorkflowForm::TYPE_ACTIONS . 'RowCounter';
            $droppableAttributesContent  = ZurmoHtml::tag('div', array('class' => 'dynamic-rows'), $itemsContent);
            $content                     = ZurmoHtml::hiddenField($hiddenInputName, $rowCount, $idInputHtmlOptions);
            $content                    .= ZurmoHtml::tag('div', array('class' => 'droppable-dynamic-rows-container ' .
                                           ComponentForWorkflowForm::TYPE_ACTIONS), $droppableAttributesContent);
            return $content;
        }

        /**
         * @return int
         */
        protected function getItemsCount()
        {
            return count($this->model->actions);
        }

        /**
         * @param int $rowCount
         * @return array|string
         */
        protected function getItemsContent(& $rowCount)
        {
            return $this->renderActions($rowCount, $this->model->actions);
        }

        /**
         * @param integer $rowCount
         * @param array $actions
         * @return array
         */
        protected function renderActions(& $rowCount, Array $actions)
        {
            assert('is_int($rowCount)');
            assert('is_array($actions)');
            $items                      = array();
            foreach ($actions as $action)
            {
                $inputPrefixData  = array(get_class($this->model), ComponentForWorkflowForm::TYPE_ACTIONS, (int)$rowCount);
                $view             = new ActionRowForWorkflowComponentView($action, $rowCount, $inputPrefixData, $this->form);
                $view->addWrapper = false;
                $items[]          = array('content' => $view->render());
                $rowCount++;
            }
            return $items;
        }

        protected function registerActionTypeDropDownOnChangeScript()
        {
            $id                = self::ACTION_TYPE_NAME;
            $inputDivId        = self::ACTION_TYPE_RELATION_DIV_ID;
            $relatedInputDivId = self::ACTION_TYPE_RELATED_MODEL_RELATION_DIV_ID;
            $moduleClassNameId = get_class($this->model) . '[moduleClassName]';
            $url               = Yii::app()->createUrl('workflows/default/changeActionType',
                                 array_merge($_GET, array('type' => $this->model->type)));
            // Begin Not Coding Standard
            $ajaxSubmitScript  = ZurmoHtml::ajax(array(
                'type'    => 'GET',
                'data'    => 'js:\'moduleClassName=\' + $("input:radio[name=\"' . $moduleClassNameId . '\"]:checked").val()',
                'url'     =>  $url,
                'beforeSend' => 'js:function(){
                        $("#' . $inputDivId . '").html("<span class=\"loading z-spinner\"></span>");
                        //attachLoadingSpinner("' . $inputDivId . '", true, "dark");
                        $("#' . $inputDivId . '").show();
                        }',
                'success' => 'js:function(data){ $("#' . $inputDivId . '").html(data);}',
            ));
            $script = "$('#" . $id . "').live('change', function()
            {
                $('#" . $inputDivId . "').html('');
                $('#" . $inputDivId . "').hide();
                $('#" . $relatedInputDivId . "').html('');
                $('#" . $relatedInputDivId . "').hide();
                $('.action-type-selector-container').find('#" . $inputDivId . "').html('');
                $('.action-type-selector-container').find('#" . $relatedInputDivId . "').html('');
                if ($('#" . $id . "').val() == '')
                {
                    //do nothing
                }
                else if ($('#" . $id . "').val() == '" . ActionForWorkflowForm::TYPE_UPDATE_SELF . "')
                {
                    loadWorkflowAction();
                }
                else
                {
                    $ajaxSubmitScript
                }
            }
            );";
            // End Not Coding Standard
            Yii::app()->clientScript->registerScript('actionTypeDropDownOnChangeScript', $script);
        }

        protected function registerActionTypeRelationDropDownOnChangeScript()
        {
            $id                = self::ACTION_TYPE_RELATION_NAME;
            $inputDivId        = self::ACTION_TYPE_RELATED_MODEL_RELATION_DIV_ID;
            $moduleClassNameId = get_class($this->model) . '[moduleClassName]';
            $url               = Yii::app()->createUrl('workflows/default/changeActionTypeRelatedModel',
                array_merge($_GET, array('type' => $this->model->type)));
            // Begin Not Coding Standard
            $ajaxSubmitScript  = ZurmoHtml::ajax(array(
                'type'    => 'GET',
                'data'    => 'js:\'relation=\' + $(this).val() + \'&moduleClassName=\' + $("input:radio[name=\"' .
                              $moduleClassNameId . '\"]:checked").val()',
                'url'     =>  $url,
                'beforeSend' => 'js:function(){
                        $("#' . $inputDivId . '").html("<span class=\"loading z-spinner\"></span>");
                        //attachLoadingSpinner("' . $inputDivId . '", true, "dark");
                        $("#' . $inputDivId . '").show();
                        }',
                'success' => 'js:function(data){$("#' . $inputDivId . '").html(data);}',
            ));
            $script = "$('#" . $id . "').live('change', function()
            {
                $('.action-type-selector-container').find('#" . $inputDivId . "').html('');
                if ($('#" . $id . "').val() == '')
                {
                    $('#" . $inputDivId . "').html('');
                    $('#" . $inputDivId . "').hide();
                }
                else if ($('#" . self::ACTION_TYPE_NAME . "').val() == '" . ActionForWorkflowForm::TYPE_CREATE_RELATED . "')
                {
                    $ajaxSubmitScript
                }
                else
                {
                    loadWorkflowAction();
                }
            }
            );";
            // End Not Coding Standard
            Yii::app()->clientScript->registerScript('actionTypeRelationDropDownOnChangeScript', $script);
        }

        protected function registerActionTypeRelatedModelRelationDropDownOnChangeScript()
        {
            $id     = self::ACTION_TYPE_RELATED_MODEL_RELATION_NAME;
            $script = "$('#" . $id . "').live('change', function()
            {
                if ($('#" . $id . "').val() != '')
                {
                    loadWorkflowAction();
                }
            }
            );";
            // End Not Coding Standard
            Yii::app()->clientScript->registerScript('actionTypeRelatedModelRelationDropDownOnChangeScript', $script);
        }

        protected function registerAddActionScript()
        {
            $rowCounterInputId = static::resolveRowCounterInputId(ComponentForWorkflowForm::TYPE_ACTIONS);
            $moduleClassNameId = get_class($this->model) . '[moduleClassName]';
            $url               = Yii::app()->createUrl('workflows/default/addAction',
                array_merge($_GET, array('type' => $this->model->type)));
            // Begin Not Coding Standard
            $ajaxSubmitScript  = ZurmoHtml::ajax(array(
                'type'    => 'GET',
                'data'    => 'js:\'actionType=\' + $(".action-type-selector-container").find("#' .
                                 self::ACTION_TYPE_NAME . '").val()
                                 + \'&relation=\' + ($(".action-type-selector-container").find("#' .
                                 self::ACTION_TYPE_RELATION_NAME . '").val() || "")
                                 + \'&relatedModelRelation=\' + ($(".action-type-selector-container").find("#' .
                                 self::ACTION_TYPE_RELATED_MODEL_RELATION_NAME . '").val() || "")
                                 + \'&moduleClassName=\' + $("input:radio[name=\"' .
                                 $moduleClassNameId . '\"]:checked").val() + ' .
                                 '\'&rowNumber=\' + $(\'#' . $rowCounterInputId . '\').val()',
                'url'     =>  $url,
                'beforeSend' => 'js:function(xhr, options){
                    //attachLoadingSpinner("' . $this->form->getId() . '", true, "dark"); - add spinner to block anything else

                    //check if any li is open and if yes validate the form again
                    var actionsList = $(".droppable-dynamic-rows-container.' . ComponentForWorkflowForm::TYPE_ACTIONS . '").find(".dynamic-rows").find("ul:first").children();
                    $.each(actionsList, function(){
                        if ( $(this).hasClass("expanded-row") ){
                            /*alert("please save and validate the open action panel");
                            try
                            {
                                xhr.abort();
                            }
                            catch(error)
                            {
                                console.log(error);
                            }
                            $("#' . self::ACTION_TYPE_NAME . '").val("");
                            $("#' . self::ACTION_TYPE_RELATION_DIV_ID . '").html("");
                            $("#' . self::ACTION_TYPE_RELATION_DIV_ID . '").hide();
                            $("#' . self::ACTION_TYPE_RELATED_MODEL_RELATION_DIV_ID . '").html("");
                            $("#' . self::ACTION_TYPE_RELATED_MODEL_RELATION_DIV_ID . '").hide();
                            return false;*/
                        }
                    });
                }',
                'success' => 'js:function(data){
                    //when ajax comes back after choosing something in thedropdown
                     $("#actionsNextLink").parent().parent().hide();
                    $(".droppable-dynamic-rows-container.' . ComponentForWorkflowForm::TYPE_ACTIONS .
                        '").find(".dynamic-rows").find("ul:first").children().hide();
                    $(\'#' . $rowCounterInputId . '\').val(parseInt($(\'#' . $rowCounterInputId . '\').val()) + 1);
                    $(".droppable-dynamic-rows-container.' . ComponentForWorkflowForm::TYPE_ACTIONS .
                        '").find(".dynamic-rows").find("ul:first").append(data);
                    rebuildWorkflowActionRowNumbers("' . get_class($this) . '");
                    $(".' . static::getZeroComponentsClassName() . '").hide();
                    $("#' . self::ACTION_TYPE_NAME . '").val("");
                    $("#' . self::ACTION_TYPE_RELATION_DIV_ID . '").html("");
                    $("#' . self::ACTION_TYPE_RELATION_DIV_ID . '").hide();
                    $("#' . self::ACTION_TYPE_RELATED_MODEL_RELATION_DIV_ID . '").html("");
                    $("#' . self::ACTION_TYPE_RELATED_MODEL_RELATION_DIV_ID . '").hide();
                }',
            ));
            $script = "function loadWorkflowAction()
                {
                    var getDropdownAjaxCall = $ajaxSubmitScript
                }
            ";
            // End Not Coding Standard
            Yii::app()->clientScript->registerScript('workflowAddActionScript', $script);
        }

        protected function registerRemoveActionScript()
        {
            $script = '
                $(".remove-dynamic-row-link").live("click", function()
                {
                    size = $(this).parent().parent().parent().find("li").size();
                    $(this).parentsUntil("ul").siblings().show();
                    $(this).parent().parent().remove(); //removes the <li>
                    if (size < 2)
                    {
                        $(".' . static::getZeroComponentsClassName() . '").show();
                    }
                    rebuildWorkflowActionRowNumbers("' . get_class($this) . '");
                    $("#actionsNextLink").parent().parent().show();
                    return false;
                });
            ';
            // End Not Coding Standard
            Yii::app()->clientScript->registerScript('removeActionScript', $script);
        }

        protected function registerTypeChangeScript()
        {
            Yii::app()->clientScript->registerScript('actionAttributeTypeChangeRules', "
                $('.actionAttributeType').live('change', function()
                    {
                        arr  = " . CJSON::encode(WorkflowActionAttributeTypeStaticDropDownElement::getValueTypesRequiringFirstInput()) . ";
                        arr2 = " . CJSON::encode(WorkflowActionAttributeTypeStaticDropDownElement::getValueTypesRequiringSecondInput()) . ";
                        var firstValueArea  = $(this).parent().parent().parent().find('.value-data').find('.first-value-area');
                        var secondValueArea = $(this).parent().parent().parent().find('.value-data').find('.second-value-area');
                        if ($.inArray($(this).val(), arr) != -1)
                        {
                            firstValueArea.show();
                            firstValueArea.find(':input, select').prop('disabled', false);
                        }
                        else
                        {
                            firstValueArea.hide();
                            firstValueArea.find(':input, select').prop('disabled', true);
                        }
                        if ($.inArray($(this).val(), arr2) != -1)
                        {
                            secondValueArea.show();
                            secondValueArea.find(':input, select').prop('disabled', false);
                        }
                        else
                        {
                            secondValueArea.hide();
                            secondValueArea.find(':input, select').prop('disabled', true);
                        }
                    }
                );
            ");
        }

        protected function registerRowEditScript()
        {
            //when clicking the EDIT button on each row
            $script = "$('.edit-dynamic-row-link').live('click', function()
            {
                $('#' + $(this).data().row.toString()).toggleClass('expanded-row');
                $('#' + $(this).data().row.toString() + ' .toggle-me').toggle();
                $('#' + $(this).data().row.toString() + ' .edit-dynamic-row-link').toggle();
                if ($('#' + $(this).data().row.toString()).hasClass('expanded-row'))
                {
                    $('#' + $(this).data().row.toString()).siblings().hide();
                }
                $('#actionsNextLink').parent().parent().hide();
            });";
            Yii::app()->clientScript->registerScript('registerRowEditScript', $script);
        }
    }
?>