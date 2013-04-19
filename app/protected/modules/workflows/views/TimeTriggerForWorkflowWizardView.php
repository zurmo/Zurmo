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
     * View class for the time trigger component for the workflow wizard user interface
     */
    class TimeTriggerForWorkflowWizardView extends ComponentForWorkflowWizardView
    {
        /**
         * @return string
         */
        public static function getWizardStepTitle()
        {
            return Zurmo::t('WorkflowsModule', 'Select Time Trigger');
        }

        /**
         * @return string
         */
        public static function getPreviousPageLinkId()
        {
            return 'timeTriggerPreviousLink';
        }

        /**
         * @return string
         */
        public static function getNextPageLinkId()
        {
            return 'timeTriggerNextLink';
        }

        protected function registerScripts()
        {
            parent::registerScripts();
            $script = '
                $(".remove-dynamic-row-link.' . TimeTriggerForWorkflowForm::getType() . '").live("click", function()
                {
                    $(this).parent().remove();
                    $("#ByTimeWorkflowWizardForm_timeTriggerAttribute").val("");
                    $(".NoTimeTrigger").show();
                    $("#time-trigger-container").hide();
                    return false;
                });
            ';
            Yii::app()->getClientScript()->registerScript('TimeTriggerForWorkflowComponentScript', $script);
        }

        /**
         * @return bool
         */
        protected function isListContentSortable()
        {
            return true;
        }

        /**
         * @return int
         */
        protected function getItemsCount()
        {
            return count($this->model->timeTrigger);
        }

        /**
         * @return string
         */
        protected function renderFormContent()
        {
            $content  = '<div>';
            $content .= $this->renderAttributeSelectorContentAndWrapper();
            $content .= $this->renderZeroComponentsContentAndWrapper();
            $content .= $this->renderTimeTriggerContentAndWrapper();
            $content .= '</div>';
            $this->registerScripts();
            return $content;
        }

        /**
         * @return string
         */
        public static function getZeroComponentsClassName()
        {
            return 'NoTimeTrigger';
        }

        /**
         * @return string
         */
        protected function getZeroComponentsMessageContent()
        {
            return '<div class="large-icon"></div><h2>' . Zurmo::t('WorkflowsModule', 'Select a time trigger') . '</h2>';
        }

        /**
         * @return string
         */
        protected function renderZeroComponentsContentAndWrapper()
        {
            return ZurmoHtml::tag('div', array('class' => 'zero-components-view ' .
                   ComponentForWorkflowForm::TYPE_TIME_TRIGGER), $this->getZeroComponentsContent());
        }

        /**
         * @return string
         */
        protected function renderAttributeSelectorContentAndWrapper()
        {
            $element                    = new TimeTriggerAttributeStaticDropDownElement($this->model,
                                          'timeTriggerAttribute', $this->form, array('addBlank' => true));
            $element->editableTemplate  = '{content}{error}';
            $attributeSelectorContent   = $element->render();
            return ZurmoHtml::tag('div', array('class' => 'time-trigger-attribute-selector-container'),
                                         $attributeSelectorContent);
        }

        /**
         * @return string
         */
        protected function renderTimeTriggerContentAndWrapper()
        {
            if ($this->model->timeTriggerAttribute != null)
            {
                $componentType       = TimeTriggerForWorkflowForm::getType();
                $inputPrefixData     = array(get_class($this->model), $componentType);
                $adapter             = new WorkflowAttributeToElementAdapter($inputPrefixData,
                                       $this->model->timeTrigger, $this->form, $componentType);
                $view                = new AttributeRowForWorkflowComponentView($adapter,
                                       1, $inputPrefixData, $this->model->timeTriggerAttribute,
                                       false, true, $componentType);
                $timeTriggerContent  = ZurmoHtml::tag('div', array('class' => 'dynamic-rows'),
                                       ZurmoHtml::tag('ul', array(), $view->render()));
                $htmlOptions         = array('id' => 'time-trigger-container');
            }
            else
            {
                $timeTriggerContent = ZurmoHtml::tag('div', array('class' => 'dynamic-rows'), ZurmoHtml::tag('ul', array(), ''));
                $htmlOptions         = array('id' => 'time-trigger-container', 'style' => 'display:none');
            }
            return ZurmoHtml::tag('div', $htmlOptions, $timeTriggerContent);
        }
    }
?>