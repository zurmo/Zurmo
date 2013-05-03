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
     * View class for the general data component for the workflow wizard user interface
     */
    class GeneralDataForWorkflowWizardView extends ComponentForWorkflowWizardView
    {
        /**
         * @return string
         */
        public static function getWizardStepTitle()
        {
            return Zurmo::t('WorkflowsModule', 'Save Workflow');
        }

        /**
         * @return string
         */
        public static function getPreviousPageLinkId()
        {
            return 'generalDataPreviousLink';
        }

        /**
         * @return string
         */
        public static function getNextPageLinkId()
        {
            return 'generalDataSaveAndRunLink';
        }

        /**
         * @return string
         */
        protected function renderFormContent()
        {
            $leftSideContent   = null;
            $element           = new TextElement($this->model, 'name', $this->form);
            $leftSideContent   = '<table><colgroup><col class="col-0"><col class="col-1">' . '</colgroup><tr>' . $element->render() . '</tr>';
            $element           = new TextAreaElement($this->model, 'description', $this->form, array('rows' => 5));
            $leftSideContent  .= '<tr>' . $element->render() . '</tr>';
            $element           = new TriggerOnStaticDropDownElement($this->model, 'triggerOn', $this->form);
            $leftSideContent  .= '<tr>' . $element->render() . '</tr>';
            $element           = new CheckBoxElement($this->model, 'isActive', $this->form);
            $leftSideContent  .= '<tr>' . $element->render() . '</tr>';
            $leftSideContent  .= '</table>';
            $leftSideContent   = ZurmoHtml::tag('div', array('class' => 'panel'), $leftSideContent);
            $leftSideContent   = ZurmoHtml::tag('div', array('class' => 'left-column full-width'), $leftSideContent);

            $content = '<div class="attributesContainer">';
            $content .= $leftSideContent;
            $content .= '</div>';
            return $content;
        }

        /**
         * @return string
         */
        protected function renderNextPageLinkContent()
        {
            $params                = array();
            $params['label']       = Zurmo::t('Core', 'Save');
            $params['htmlOptions'] = array('id' => static::getNextPageLinkId(), 'onclick' => 'js:$(this).addClass("attachLoadingTarget");');
            $element               = new SaveButtonActionElement(null, null, null, $params);
            return $element->render();
        }
    }
?>