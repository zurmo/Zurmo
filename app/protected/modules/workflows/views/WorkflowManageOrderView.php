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
     * View for selecting which module to manage workflow sequences for
     */
    class WorkflowManageOrderView extends MetadataView
    {
        /**
         * @return string
         */
        public static function getFormId()
        {
            return 'edit-form';
        }

        /**
         * @return string
         */
        protected function renderContent()
        {
            $content  = $this->renderForm();
            $this->renderLoadModuleOrderScriptContent();
            return $content;
        }

        /**
         * @return string
         */
        protected function renderForm()
        {
            $content  = '<div class="wrapper">';
            $content .= ZurmoHtml::tag('h1', array(), $this->renderTitleContent() . 'Workflow Order');
            $content .= '<div class="wide form">';
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget('ZurmoActiveForm',
                                                                        array(
                                                                            'id' => static::getFormId(),
                                                                            'action' => $this->getFormActionUrl(),
                                                                        ));
            $content .= $formStart;
            $content .= $this->renderNoModuleSelectedContentAndWrapper();
            $content .= $this->renderNoWorkflowsToOrderContentAndWrapper();
            $content .= $this->renderModuleSelectorContentAndWrapper($form);
            $content .= $this->renderWorkflowOrderContentAndWrapper();
            $formEnd  = $clipWidget->renderEndWidget();
            $content .= $formEnd;
            $content .= '</div></div>';
            return $content;
        }

        /**
         * @return string
         */
        protected function getNoModuleSelectedContent()
        {
            return '<div class="large-icon"></div><h2>' . Zurmo::t('WorkflowsModule', 'Select a module to order workflow rules') . '</h2>';
        }

        /**
         * @return string
         */
        protected function renderNoModuleSelectedContentAndWrapper()
        {
            return ZurmoHtml::tag('div', array('class' => 'select-module-view zero-components-view WorkflowRulesOrder'), $this->getNoModuleSelectedContent());
        }

        /**
         * @return string
         */
        protected function getNoWorkflowsToOrderContent()
        {
            return '<div class="large-icon"></div><p>' . Zurmo::t('WorkflowsModule', 'This module does not have any workflows to order') . '</p>';
        }

        /**
         * @return string
         */
        protected function renderNoWorkflowsToOrderContentAndWrapper()
        {
            return ZurmoHtml::tag('div', array('class' => 'no-workflows-to-order-view', 'style' => "display:none;"),
                                  $this->getNoWorkflowsToOrderContent());
        }

        /**
         * @param $form
         * @return string
         */
        protected function renderModuleSelectorContentAndWrapper($form)
        {
            $element                    = new ModuleForWorkflowStaticDropDownElement(new SavedWorkflow(),
                                          'moduleClassName', $form, array('addBlank' => true));
            $element->editableTemplate  = '{content}{error}';
            return ZurmoHtml::tag('div', array('class' => 'workflow-order-module-selector-container'), $element->render());
        }

        /**
         * @return string
         */
        protected function renderWorkflowOrderContentAndWrapper()
        {
            $content  =  ZurmoHtml::tag('div', array('id' => 'workflow-order-container'),
                            ZurmoHtml::tag('div', array('class' => 'dynamic-rows'), ''));
            $content .=  '<div class="float-bar" style="display:none"><div class="view-toolbar-container clearfix dock disable-float-bar"><div class="form-toolbar">'
                         . $this->renderSaveLinkContent() . '</div></div></div>';
            return $content;
        }

        /**
         * @return string
         */
        protected function renderSaveLinkContent()
        {
            $aContent                = ZurmoHtml::wrapLink(Zurmo::t('Core', 'Save'));
            return       ZurmoHtml::ajaxLink($aContent, $this->getFormActionUrl(),
                array(  'type'       => 'POST',
                        'dataType'   => 'json',
                        'data'       => 'js:$("#' . static::getFormId() . '").serialize()',
                        'complete'   => 'js:function(){detachLoadingOnSubmit("' . static::getFormId() . '");}',
                        'success'    => 'function(data)
                                        {
                                            $("#FlashMessageBar").jnotifyAddMessage(
                                            {
                                                text: data.message, permanent: false, showIcon: true, type: data.type
                                            });
                                        }',
                ),
                array('id'       => 'save-order',
                      'class'    => 'attachLoading z-button',
                      'onclick'    => 'js:$(this).addClass("loading").addClass("loading-ajax-submit");
                                                        makeOrRemoveLoadingSpinner(true, "#" + $(this).attr("id"));'));
        }

        /**
         * @return mixed
         */
        protected function getFormActionUrl()
        {
            return Yii::app()->createUrl('workflows/default/saveOrder');
        }

        /**
         * @return array
         */
        protected function getClientOptions()
        {
            return array(
                'validateOnSubmit'  => true,
                'validateOnChange'  => false,
                'beforeValidate'    => 'js:beforeValidateAction',
                'afterValidate'     => 'js:afterValidateAjaxAction',
                'afterValidateAjax' => $this->renderConfigSaveAjax(static::getFormId()),
            );
        }

        protected function renderLoadModuleOrderScriptContent()
        {
            $id         = 'SavedWorkflow_moduleClassName_value';
            $inputDivId = 'dynamic-rows';
            $url        =  Yii::app()->createUrl('workflows/default/loadOrderByModule');
            // Begin Not Coding Standard
            $ajaxSubmitScript  = ZurmoHtml::ajax(array(
                'type'     => 'GET',
                'dataType' => 'json',
                'data'     => 'js:\'moduleClassName=\' + $(this).val()',
                'url'      =>  $url,
                'success'  => 'js:function(data){
                                if (data.dataToOrder == "true")
                                {
                                    $(".no-workflows-to-order-view").hide();
                                    $(".select-module-view").hide();
                                    $(".float-bar").show();
                                    $(".' . $inputDivId . '").html(data.content);
                                }
                                else
                                {
                                    $(".select-module-view").hide();
                                    $(".float-bar").hide();
                                    $(".' . $inputDivId . '").html("");
                                    $(".no-workflows-to-order-view").show();
                                }}',
            ));
            $script = "$('#" . $id . "').unbind('change'); $('#" . $id . "').bind('change', function()
            {

                if ($('#" . $id . "').val() == '')
                {
                    $('.no-workflows-to-order-view').hide();
                    $('.select-module-view').show();
                    $('.float-bar').hide();
                    $('." . $inputDivId . "').html('');
                }
                else
                {
                    $ajaxSubmitScript
                }
            });";
            // End Not Coding Standard
            Yii::app()->clientScript->registerScript('timeTriggerAttributeDropDownOnChangeScript', $script);
        }
    }
?>