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
     * Displays first series/range inputs and second series/range inputs. Utilized in conjunction with selecting a
     * chart for reporting. @see ChartTypeRadioStaticDropDownForReportElement
     */
    class MixedChartRangeAndSeriesElement extends Element
    {
        public $editableTemplate = '<td colspan="{colspan}">{content}{error}</td>';

        /**
         * @return The element's content as a string.
         */
        protected function renderControlEditable()
        {
            $startingDivStyleFirstValue         = null;
            $startingDivStyleSecondValue        = null;
            if ($this->model->type == null)
            {
                $startingDivStyleFirstValue = "display:none;";
            }

            if (!in_array($this->model->type, array(ChartRules::TYPE_STACKED_BAR_3D, ChartRules::TYPE_STACKED_COLUMN_3D)))
            {
                $startingDivStyleSecondValue = "display:none;";
            }
            $content  = ZurmoHtml::tag('div', array('class' => 'first-series-and-range-area',
                                                    'style' => $startingDivStyleFirstValue),
                                       ZurmoHtml::tag('div', array(), $this->renderEditableFirstSeriesContent()) .
                                       ZurmoHtml::tag('div', array(), $this->renderEditableFirstRangeContent()));
            $content .= ZurmoHtml::tag('div', array('class' => 'second-series-and-range-area',
                                                    'style' => $startingDivStyleSecondValue),
                                       ZurmoHtml::tag('div', array(), $this->renderEditableSecondSeriesContent()) .
                                       ZurmoHtml::tag('div', array(), $this->renderEditableSecondRangeContent()));
            return $content;
        }

        protected function renderEditableFirstSeriesContent()
        {
            $htmlOptions = array(
                'empty'  => Zurmo::t('Core', '(None)'),
                'id'     => $this->getFirstSeriesEditableInputId(),
            );
            $label       = $this->form->labelEx($this->model, 'firstSeries',
                                                array('for' => $this->getFirstSeriesEditableInputId()));
            $content     = ZurmoHtml::dropDownList($this->getFirstSeriesEditableInputName(),
                                                   $this->model->firstSeries,
                                                   $this->model->getAvailableFirstSeriesDataAndLabels(),
                                                   $htmlOptions
                                                   );
            $error      = $this->form->error($this->model, 'firstSeries',
                            array('inputID' => $this->getFirstSeriesEditableInputId()));
            return $label . $content . $error;
        }

        protected function renderEditableFirstRangeContent()
        {
           $htmlOptions = array(
                'empty' => Zurmo::t('Core', '(None)'),
                'id'    => $this->getFirstRangeEditableInputId(),
           );
           $label        = $this->form->labelEx($this->model, 'firstRange',
                                                array('for' => $this->getFirstRangeEditableInputId()));
           $content      = ZurmoHtml::dropDownList($this->getFirstRangeEditableInputName(),
                                                   $this->model->firstRange,
                                                   $this->model->getAvailableFirstRangeDataAndLabels(),
                                                   $htmlOptions
                                                   );
            $error       = $this->form->error($this->model, 'firstRange',
                           array('inputID' => $this->getFirstRangeEditableInputId()));
            return $label . $content . $error;
        }

        protected function renderEditableSecondSeriesContent()
        {
           $htmlOptions = array(
                'empty' => Zurmo::t('Core', '(None)'),
                'id'    => $this->getSecondSeriesEditableInputId(),
           );
           $label        = $this->form->labelEx($this->model, 'secondSeries',
                                                array('for' => $this->getSecondSeriesEditableInputId()));
           $content      = ZurmoHtml::dropDownList($this->getSecondSeriesEditableInputName(),
                                                   $this->model->secondSeries,
                                                   $this->model->getAvailableSecondSeriesDataAndLabels(),
                                                   $htmlOptions
                                                   );
            $error       = $this->form->error($this->model, 'secondSeries',
                           array('inputID' => $this->getSecondSeriesEditableInputId()));
            return $label . $content . $error;
        }

        protected function renderEditableSecondRangeContent()
        {
           $htmlOptions = array(
                'empty' => Zurmo::t('Core', '(None)'),
                'id'    => $this->getSecondRangeEditableInputId(),
           );
           $label        = $this->form->labelEx($this->model, 'secondRange',
                                                array('for' => $this->getSecondRangeEditableInputId()));
           $content      = ZurmoHtml::dropDownList($this->getSecondRangeEditableInputName(),
                                                   $this->model->secondRange,
                                                   $this->model->getAvailableSecondRangeDataAndLabels(),
                                                   $htmlOptions
                                                   );
            $error       = $this->form->error($this->model, 'secondRange',
                           array('inputID' => $this->getSecondRangeEditableInputId()));
            return $label . $content . $error;
        }

        /**
         * Renders the attribute from the model.
         * @return The element's content.
         */
        protected function renderControlNonEditable()
        {
            throw new NotSupportedException();
        }

        /**
         * Render during the Editable render
         * (non-PHPdoc)
         * @see Element::renderError()
         */
        protected function renderError()
        {
        }

        protected function getFirstSeriesEditableInputId()
        {
            return $this->getEditableInputId('firstSeries');
        }

        protected function getFirstRangeEditableInputId()
        {
            return $this->getEditableInputId('firstRange');
        }

        protected function getSecondSeriesEditableInputId()
        {
            return $this->getEditableInputId('secondSeries');
        }

        protected function getSecondRangeEditableInputId()
        {
            return $this->getEditableInputId('secondRange');
        }

        protected function getFirstSeriesEditableInputName()
        {
            return $this->getEditableInputName('firstSeries');
        }

        protected function getFirstRangeEditableInputName()
        {
            return $this->getEditableInputName('firstRange');
        }

        protected function getSecondSeriesEditableInputName()
        {
            return $this->getEditableInputName('secondSeries');
        }

        protected function getSecondRangeEditableInputName()
        {
            return $this->getEditableInputName('secondRange');
        }
    }
?>