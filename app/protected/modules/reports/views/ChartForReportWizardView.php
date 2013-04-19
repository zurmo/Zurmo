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
     * View class for the chart component for the report wizard user interface
     */
    class ChartForReportWizardView extends ComponentForReportWizardView
    {
        /**
         * @return string
         */
        public static function getWizardStepTitle()
        {
            return Zurmo::t('ReportsModule', 'Select a Chart');
        }

        /**
         * @return string
         */
        public static function getPreviousPageLinkId()
        {
            return 'chartPreviousLink';
        }

        /**
         * @return string
         */
        public static function getNextPageLinkId()
        {
            return 'chartNextLink';
        }

        public function registerScripts()
        {
            parent::registerScripts();
            $chartTypesRequiringSecondInputs = ChartRules::getChartTypesRequiringSecondInputs();
            $script = '
                if ($(".chart-selector:checked").val() != "")
                {
                    $("#series-and-range-areas").detach().insertAfter( $(".chart-selector:checked").parent()).removeClass("hidden-element");
                }
                $(".chart-selector").live("change", function()
                    {
                        onChangeChartType(this);
                    }
                );
                function onChangeChartType(changedChartObject)
                {
                    $("#series-and-range-areas").detach().insertAfter( $(changedChartObject).parent()  ).removeClass("hidden-element");
                    arr = ' . CJSON::encode($chartTypesRequiringSecondInputs) . ';
                    if ($(changedChartObject).val() == "")
                    {
                        $("#series-and-range-areas").addClass("hidden-element")
                        $(".first-series-and-range-area").hide();
                        $(".first-series-and-range-area").find("select option:selected").removeAttr("selected");
                        $(".first-series-and-range-area").find("select").prop("disabled", true);
                    }
                    else
                    {
                        $(".first-series-and-range-area").show();
                        $(".first-series-and-range-area").find("select").prop("disabled", false);
                    }
                    if ($.inArray($(changedChartObject).val(), arr) != -1)
                    {
                        $(".second-series-and-range-area").show();
                        $(".second-series-and-range-area").find("select").prop("disabled", false);
                    }
                    else
                    {
                        $(".second-series-and-range-area").hide();
                        $(".second-series-and-range-area").find("select option:selected").removeAttr("selected");
                        $(".second-series-and-range-area").find("select").prop("disabled", true);
                    }
                }
            ';
            Yii::app()->getClientScript()->registerScript('ChartChangingScript', $script);
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
            $inputPrefixData   = array(get_class($this->model), get_class($this->model->chart));
            $this->form->setInputPrefixData($inputPrefixData);
            $params            = array('inputPrefix' => $inputPrefixData);
            $content           = '<div class="attributesContainer">';
            $element           = new ChartTypeRadioStaticDropDownForReportElement($this->model->chart, 'type', $this->form,
                array_merge($params, array('addBlank' => true)));
            $leftSideContent   = $element->render();
            $element           = new MixedChartRangeAndSeriesElement($this->model->chart, null, $this->form, $params);
            $content          .= ZurmoHtml::tag('div', array('class' => 'panel'), $leftSideContent);
            $rightSideContent  = ZurmoHtml::tag('div', array(), $element->render());
            $rightSideContent  = ZurmoHtml::tag('div', array('class' => 'buffer'), $rightSideContent);
            $content          .= ZurmoHtml::tag('div', array('id' => 'series-and-range-areas', 'class' => 'right-side-edit-view-panel hidden-element'), $rightSideContent);
            $content          .= $this->renderChartTipContent();
            $content          .= '</div>';
            $this->form->clearInputPrefixData();
            $this->registerScripts();
            return $content;
        }

        protected function renderChartTipContent()
        {
            $content  = ZurmoHtml::tag('h3', array(), Zurmo::t('Core', 'Quick Tip'));
            $content .= ZurmoHtml::tag('p', array(),
                                       Zurmo::t('WorkflowsModule', 'In order to use a grouping as a series field, ' .
                                                    'the grouping must be added as a display column.'));
            $content  = ZurmoHtml::tag('div', array(), $content);
            $content  = ZurmoHtml::tag('div', array('class' => 'buffer'), $content);
            $content  = ZurmoHtml::tag('div', array('class'    => 'right-side-edit-view-panel'), $content);
            return $content;
        }
    }
?>