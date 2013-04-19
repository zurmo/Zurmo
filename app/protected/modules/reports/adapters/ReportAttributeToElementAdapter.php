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
     * Helper class for adapting an attribute to an Element
     */
    class ReportAttributeToElementAdapter extends WizardModelAttributeToElementAdapter
    {
        /**
         * @var bool
         */
        protected $showAvailableRuntimeFilter = true;

        /**
         * @return string
         * @throws NotSupportedException if the treeType is invalid or null
         */
        public function getContent()
        {
            $this->form->setInputPrefixData($this->inputPrefixData);
            if ($this->treeType == ComponentForReportForm::TYPE_FILTERS)
            {
                $content = $this->getContentForFilter();
            }
            elseif ($this->treeType == ComponentForReportForm::TYPE_DISPLAY_ATTRIBUTES)
            {
                $content = $this->getContentForDisplayAttribute();
            }
            elseif ($this->treeType == ComponentForReportForm::TYPE_ORDER_BYS)
            {
                $content = $this->getContentForOrderBy();
            }
            elseif ($this->treeType == ComponentForReportForm::TYPE_GROUP_BYS)
            {
                $content = $this->getContentForGroupBy();
            }
            elseif ($this->treeType == ComponentForReportForm::TYPE_DRILL_DOWN_DISPLAY_ATTRIBUTES)
            {
                $content = $this->getContentForDrillDownDisplayAttribute();
            }
            else
            {
                throw new NotSupportedException();
            }
            $this->form->clearInputPrefixData();
            return $content;
        }

        /**
         * @return string
         * @throws NotSupportedException if the valueElementType is null
         */
        protected function getContentForFilter()
        {
            $params                                 = array('inputPrefix' => $this->inputPrefixData);
            if ($this->model->hasAvailableOperatorsType() && count($this->model->getOperatorValuesAndLabels()) > 1)
            {
                $operatorElement                    = new OperatorStaticDropDownElement($this->model, 'operator', $this->form, $params);
                $operatorElement->editableTemplate  = '{content}{error}';
                $operatorContent                    = $operatorElement->render();
            }
            else
            {
                $operatorContent                    = null;
            }
            $valueElementType                       = $this->model->getValueElementType();
            if ($valueElementType != null)
            {
                $valueElementClassName              = $valueElementType . 'Element';
                $valueElement                       = new $valueElementClassName($this->model, 'value', $this->form, $params);
                if ($valueElement instanceof NameIdElement)
                {
                    $valueElement->setIdAttributeId('value');
                    $valueElement->setNameAttributeName('stringifiedModelForValue');
                }
                if ($valueElement instanceof MixedNumberTypesElement)
                {
                    $valueElement->editableTemplate = '<div class="value-data">{content}{error}</div>';
                }
                elseif ($valueElement instanceof MixedDateTypesElement)
                {
                    $valueElement->editableTemplate = '<div class="dynamic-row-operator">{valueType}</div>' .
                                                      '<div class="value-data has-date-inputs">' .
                                                      '<div class="first-value-area">{content}{error}</div></div>';
                }
                else
                {
                    $startingDivStyleFirstValue     = null;
                    if (in_array($this->model->getOperator(), array(OperatorRules::TYPE_IS_NULL, OperatorRules::TYPE_IS_NOT_NULL)))
                    {
                        $startingDivStyleFirstValue         = "display:none;";
                        $valueElement->params['disabled']   = 'disabled';
                    }
                    $valueElement->editableTemplate = '<div class="value-data"><div class="first-value-area" style="' .
                                                      $startingDivStyleFirstValue . '">{content}{error}</div></div>';
                }
                $valueContent                   = $valueElement->render();
            }
            else
            {
                throw new NotSupportedException();
            }
            $content                                = $this->renderAttributeIndexOrDerivedType();
            $content                               .= $this->renderHiddenOperator();
            self::resolveDivWrapperForContent($this->model->getDisplayLabel(), $content, 'dynamic-row-label');
            self::resolveDivWrapperForContent($operatorContent,                $content, 'dynamic-row-operator');
            $content                               .= $valueContent;
            if ($this->showAvailableRuntimeFilter)
            {
                $runTimeElement                         = new CheckBoxElement($this->model, 'availableAtRunTime',
                                                                    $this->form, $params);
                $runTimeElement->editableTemplate       = '{label}{content}{error}';
                $runTimeContent                         = $runTimeElement->render();
                self::resolveDivWrapperForContent($runTimeContent, $content, 'report-runtime-availability');
            }
            return $content;
        }

        /**
         * Builds hidden operator input. Used in the event there is only one operator available. No reason to show
         * that in the user interface
         * @return string
         */
        protected function renderHiddenOperator()
        {
            if ($this->model->hasAvailableOperatorsType() && count($this->model->getOperatorValuesAndLabels()) == 1)
            {
                $hiddenInputName     = Element::resolveInputNamePrefixIntoString(
                    array_merge($this->inputPrefixData, array('operator')));
                $hiddenInputId       = Element::resolveInputIdPrefixIntoString(
                    array_merge($this->inputPrefixData, array('operator')));
                $idInputHtmlOptions  = array('id' => $hiddenInputId);
                $valuesAndLabels     = $this->model->getOperatorValuesAndLabels();
                return ZurmoHtml::hiddenField($hiddenInputName, key($valuesAndLabels), $idInputHtmlOptions);
            }
        }

        /**
         * @return string
         * @throws NotSupportedException if the reportType is rows and columns since that report type does not have
         * group bys
         */
        protected function getContentForGroupBy()
        {
            if ($this->model->getReportType() == Report::TYPE_ROWS_AND_COLUMNS)
            {
                throw new NotSupportedException();
            }
            elseif ($this->model->getReportType() == Report::TYPE_MATRIX)
            {
                $params                               = array('inputPrefix' => $this->inputPrefixData);
                $groupByAxisElement                   = new GroupByAxisStaticDropDownElement($this->model, 'axis',
                                                                                             $this->form, $params);
                $groupByAxisElement->editableTemplate = '{content}{error}';
                $groupByAxisElement                   = $groupByAxisElement->render();
            }
            else
            {
                $groupByAxisElement                   = null;
            }
            $content                                  = $this->renderAttributeIndexOrDerivedType();
            self::resolveDivWrapperForContent($this->model->getDisplayLabel(), $content, 'dynamic-row-label');
            self::resolveDivWrapperForContent($groupByAxisElement,             $content, 'dynamic-row-field');
            return $content;
        }

        /**
         * @return string
         * @throws NotSupportedException if the reportType is rows and columns since that report type does not have
         * order bys
         */
        protected function getContentForOrderBy()
        {
            if ($this->model->getReportType() == Report::TYPE_MATRIX)
            {
                throw new NotSupportedException();
            }
            $params                             = array('inputPrefix' => $this->inputPrefixData);
            $directionElement                   = new OrderByStaticDropDownElement($this->model, 'order', $this->form,
                                                                                   $params);
            $directionElement->editableTemplate = '{content}{error}';
            $directionElement                   = $directionElement->render();
            $content                            = $this->renderAttributeIndexOrDerivedType();
            self::resolveDivWrapperForContent($this->model->getDisplayLabel(), $content, 'dynamic-row-label');
            self::resolveDivWrapperForContent($directionElement,               $content, 'dynamic-row-field');
            return $content;
        }

        /**
         * @return string
         */
        protected function getContentForDisplayAttribute()
        {
            $params                                = array('inputPrefix' => $this->inputPrefixData);
            $displayLabelElement                   = new TextElement($this->model, 'label', $this->form, $params);
            $displayLabelElement->editableTemplate = '{content}{error}';
            $displayLabelElement                   = $displayLabelElement->render();
            $content                               = $this->renderAttributeIndexOrDerivedType();
            self::resolveDivWrapperForContent($this->model->getDisplayLabel(), $content, 'dynamic-row-label');
            self::resolveDivWrapperForContent($displayLabelElement,            $content, 'dynamic-row-field');
            return $content;
        }

        /**
         * @return string
         * @throws NotSupportedException if the reportType is not summation, since only summation has drill down.
         */
        protected function getContentForDrillDownDisplayAttribute()
        {
            if ($this->model->getReportType() == Report::TYPE_ROWS_AND_COLUMNS ||
               $this->model->getReportType() == Report::TYPE_MATRIX)
            {
                throw new NotSupportedException();
            }
            return $this->getContentForDisplayAttribute();
        }
    }
?>