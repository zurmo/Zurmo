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
     * Helper class for adapting one of an action's attributes to a set of appropriate Elements
     */
    class WorkflowActionAttributeToElementAdapter
    {
        /**
         * @var string
         */
        protected $actionType;

        /**
         * @var WorkflowActionAttributeForm
         */
        protected $model;

        /**
         * @var WizardActiveForm
         */
        protected $form;

        /**
         * @var array
         */
        protected $inputPrefixData;

        /**
         * @var bool
         */
        protected $isAttributeRequired;

        /**
         * @param WorkflowActionAttributeForm $model
         * @param WizardActiveForm $form
         * @param string $actionType
         * @param array $inputPrefixData
         * @param boolean $isAttributeRequired
         */
        public function __construct(WorkflowActionAttributeForm $model, WizardActiveForm $form,
                                    $actionType, $inputPrefixData, $isAttributeRequired)
        {
            assert('is_string($actionType)');
            assert('is_array($inputPrefixData)');
            assert('is_bool($isAttributeRequired)');
            $this->model               = $model;
            $this->form                = $form;
            $this->actionType          = $actionType;
            $this->inputPrefixData     = $inputPrefixData;
            $this->isAttributeRequired = $isAttributeRequired;
        }

        /**
         * @return string
         * @throws NotSupportedException if the treeType is invalid or null
         */
        public function getContent()
        {
            $this->form->setInputPrefixData($this->inputPrefixData);
            $content = $this->getAttributeContent();
            $this->form->clearInputPrefixData();
            return $content;
        }

        /**
         * Several attributes have different options available if creating vs. updating an existing model.  User is an
         * example where the options vary based on create vs. update.
         * @return bool
         */
        protected function isCreatingNewModel()
        {
            if ($this->actionType == ActionForWorkflowForm::TYPE_UPDATE_SELF ||
               $this->actionType == ActionForWorkflowForm::TYPE_UPDATE_RELATED)
            {
                return false;
            }
            elseif ($this->actionType == ActionForWorkflowForm::TYPE_CREATE ||
                   $this->actionType == ActionForWorkflowForm::TYPE_CREATE_RELATED)
            {
                return true;
            }
        }

        /**
         * @return string
         * @throws NotSupportedException if the valueElementType is null
         */
        protected function getAttributeContent()
        {
            $shouldSetValueContent                = $this->renderShouldSetValueContent();
            $content                              = null;
            ZurmoHtml::resolveDivWrapperForContent($shouldSetValueContent, $content, 'dynamic-action-attribute-should-set-value');
            ZurmoHtml::resolveDivWrapperForContent($this->model->getDisplayLabel(),  $content, 'dynamic-row-label');
            $content                            .= $this->resolveTypeAndValueContent();
            return $content;
        }

        /**
         * @return string
         */
        protected function resolveTypeAndValueContent()
        {
            $typeContent                         = $this->renderTypeContent();
            $valueContent                        = $this->renderValueContent();
            $typeAndValueContent                 = null;
            ZurmoHtml::resolveDivWrapperForContent($typeContent, $typeAndValueContent, 'dynamic-action-attribute-type');
            $typeAndValueContent                .= $valueContent;
            if ($this->model->shouldSetValue)
            {
                $style = null;
            }
            else
            {
                $style = 'display:none;';
            }
            return ZurmoHtml::tag('div', array('class' => 'dynamic-action-attribute-type-and-value-wrapper',
                                               'style' => $style), $typeAndValueContent);
        }

        /**
         * @return string
         */
        protected function renderShouldSetValueContent()
        {
            $params = array('inputPrefix' => $this->inputPrefixData);
            if ($this->isAttributeRequired)
            {
                $params['disabled'] = true;
            }
            $shouldSetValueElement                    = new ShouldSetValueCheckBoxElement(
                                                        $this->model, 'shouldSetValue', $this->form, $params);
            $shouldSetValueElement->editableTemplate  = '{content}{error}';
            return $shouldSetValueElement->render();
        }

        /**
         * @return A|string
         */
        protected function renderTypeContent()
        {
            $typeValuesAndLabels = $this->model->getTypeValuesAndLabels($this->isCreatingNewModel(), $this->isAttributeRequired);
            if (count($typeValuesAndLabels) > 1)
            {
                $params                         = array('inputPrefix' => $this->inputPrefixData,
                                                        'typeValuesAndLabels' => $typeValuesAndLabels);
                $typeElement                    = new WorkflowActionAttributeTypeStaticDropDownElement(
                                                  $this->model, 'type', $this->form, $params);
                $typeElement->editableTemplate  = '{content}{error}';
                return $typeElement->render();
            }
            else
            {
                $label       = reset($typeValuesAndLabels);
                $name        = Element::resolveInputNamePrefixIntoString($this->inputPrefixData) . '[type]';
                $id          = Element::resolveInputIdPrefixIntoString($this->inputPrefixData) . 'type';
                $htmlOptions = array('id' => $id);
                $content     = ZurmoHtml::tag('span', array(), $label);
                $content    .= ZurmoHtml::hiddenField($name, key($typeValuesAndLabels), $htmlOptions);
                return $content;
            }
        }

        /**
         * @return string
         * @throws NotSupportedException
         */
        protected function renderValueContent()
        {
            $params           = array('inputPrefix' => $this->inputPrefixData);
            $valueElementType = $this->model->getValueElementType();
            if ($valueElementType != null)
            {
                $valueElementClassName = $valueElementType . 'Element';
                $valueElement          = new $valueElementClassName($this->model, 'value', $this->form, $params);
                if ($valueElement instanceof NameIdElement)
                {
                    $valueElement->setIdAttributeId('value');
                    $valueElement->setNameAttributeName('stringifiedModelForValue');
                }
                if ($valueElement instanceof MixedDropDownTypesForWorkflowActionAttributeElement)
                {
                    $valueElement->editableTemplate = '<div class="value-data">{content}{error}</div>';
                }
                elseif ($valueElement instanceof MixedDateTypesForWorkflowActionAttributeElement ||
                       $valueElement instanceof MixedDateTimeTypesForWorkflowActionAttributeElement)
                {
                    $valueElement->editableTemplate = '<div class="value-data has-date-inputs">{content}{error}</div>';
                }
                else
                {
                    $startingDivStyleFirstValue     = null;
                    if ($this->model->type == WorkflowActionAttributeForm::TYPE_STATIC_NULL)
                    {
                        $startingDivStyleFirstValue         = "display:none;";
                        $valueElement->params['disabled']   = 'disabled';
                    }
                    $valueElement->editableTemplate = '<div class="value-data"><div class="first-value-area" style="' .
                        $startingDivStyleFirstValue . '">{content}{error}</div></div>';
                }
                return $valueElement->render();
            }
            else
            {
                throw new NotSupportedException();
            }
        }
    }
?>