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
     * View for displaying a row of action information for a component
     */
    class ActionRowForWorkflowComponentView extends View
    {
        const REQUIRED_ATTRIBUTES_INDEX     = 'Required';

        const NON_REQUIRED_ATTRIBUTES_INDEX = 'NonRequired';

        /**
         * @var ActionForWorkflowForm
         */
        protected $model;

        /**
         * @var int
         */
        protected $rowNumber;

        /**
         * @var array
         */
        protected $inputPrefixData;

        /**
         * @var WizardActiveForm
         */
        protected $form;

        public static function getFormId()
        {
            return WizardView::getFormId();
        }

        /**
         * @param ActionForWorkflowForm $model
         * @param integer $rowNumber
         * @param array $inputPrefixData
         * @param WizardActiveForm $form
         */
        public function __construct(ActionForWorkflowForm $model, $rowNumber, $inputPrefixData, WizardActiveForm $form)
        {
            assert('is_int($rowNumber)');
            assert('is_array($inputPrefixData)');
            $this->model            = $model;
            $this->rowNumber        = $rowNumber;
            $this->inputPrefixData  = $inputPrefixData;
            $this->form             = $form;
        }

        /**
         * @return string
         */
        public function render()
        {
            return $this->renderContent();
        }

        /**
         * @return string
         */
        protected function renderContent()
        {
            $rowId = Element::resolveInputIdPrefixIntoString($this->inputPrefixData);
            $content  = '<div class="row-utils">';
            $content .= $this->renderActionRowNumberLabel();
            $toggleLink = ZurmoHtml::tag('a', array('data-row' => $rowId,
                          'class' => 'edit-dynamic-row-link simple-link toggle-row'), 'Edit');

            $content .= ZurmoHtml::tag('div', array('class' => 'dynamic-row-label'),
                        $this->model->getDisplayLabel() . '&nbsp;&nbsp;' . $toggleLink);
            $content .= $this->renderTypeHiddenInputContent();
            $content .= $this->renderRelationHiddenInputContent();
            $content .= $this->renderRelatedModelRelationHiddenInputContent();
            $content .= '</div>';
            $content .= ZurmoHtml::link('—', '#', array('class' => 'remove-dynamic-row-link'));
            $content .= '<div class="toggle-me">';
            $content .= $this->renderAttributesRowsContent($this->makeAttributeRows());
            $content .= $this->renderSaveActionElementsContent($rowId);
            $content .= '</div>';
            $content  =  ZurmoHtml::tag('div', array('class' => 'dynamic-row'), $content);
            return ZurmoHtml::tag('li', array('id' => $rowId, 'class' => 'expanded-row'), $content);
        }

        /**
         * @return string
         */
        protected function renderActionRowNumberLabel()
        {
            return ZurmoHtml::tag('span', array('class' => 'dynamic-row-number-label'),
                ($this->rowNumber + 1) . '.');
        }

        /**
         * @return string
         */
        protected function renderTypeHiddenInputContent()
        {
            $hiddenInputName     = Element::resolveInputNamePrefixIntoString(
                                   array_merge($this->inputPrefixData, array('type')));
            $hiddenInputId       = Element::resolveInputIdPrefixIntoString(
                                   array_merge($this->inputPrefixData, array('type')));
            $idInputHtmlOptions  = array('id' => $hiddenInputId);
            return ZurmoHtml::hiddenField($hiddenInputName, $this->model->type, $idInputHtmlOptions);
        }

        /**
         * @return string
         */
        protected function renderRelationHiddenInputContent()
        {
            $hiddenInputName     = Element::resolveInputNamePrefixIntoString(
                array_merge($this->inputPrefixData, array('relation')));
            $hiddenInputId       = Element::resolveInputIdPrefixIntoString(
                array_merge($this->inputPrefixData, array('relation')));
            $idInputHtmlOptions  = array('id' => $hiddenInputId);
            return ZurmoHtml::hiddenField($hiddenInputName, $this->model->relation, $idInputHtmlOptions);
        }

        /**
         * @return string
         */
        protected function renderRelatedModelRelationHiddenInputContent()
        {
            $hiddenInputName     = Element::resolveInputNamePrefixIntoString(
                array_merge($this->inputPrefixData, array('relatedModelRelation')));
            $hiddenInputId       = Element::resolveInputIdPrefixIntoString(
                array_merge($this->inputPrefixData, array('relatedModelRelation')));
            $idInputHtmlOptions  = array('id' => $hiddenInputId);
            return ZurmoHtml::hiddenField($hiddenInputName, $this->model->relatedModelRelation, $idInputHtmlOptions);
        }

        /**
         * @return array
         */
        protected function makeAttributeRows()
        {
            $inputPrefixData   = $this->inputPrefixData;
            $inputPrefixData[] = ActionForWorkflowForm::ACTION_ATTRIBUTES;
            if ($this->model->isTypeAnUpdateVariant())
            {
                return $this->resolveAttributeRowsForUpdateTypes($inputPrefixData);
            }
            else
            {
                return $this->resolveAttributeRowsForCreateTypes($inputPrefixData);
            }
        }

        /**
         * @param array $inputPrefixData
         * @return array
         */
        protected function resolveAttributeRowsForUpdateTypes(Array $inputPrefixData)
        {
            assert('is_array($inputPrefixData)');
            $attributeRows     = array(self::REQUIRED_ATTRIBUTES_INDEX     => array(),
                                       self::NON_REQUIRED_ATTRIBUTES_INDEX => array());
            foreach ($this->model->resolveAllActionAttributeFormsAndLabelsAndSort() as $attribute => $actionAttributeForm)
            {
                $elementAdapter  = new WorkflowActionAttributeToElementAdapter($actionAttributeForm, $this->form,
                                   $this->model->type, array_merge($inputPrefixData, array($attribute)), false);
                $attributeRows[self::NON_REQUIRED_ATTRIBUTES_INDEX][] = $elementAdapter->getContent();
            }
            return $attributeRows;
        }

        /**
         * @param Array $inputPrefixData
         * @return array
         */
        protected function resolveAttributeRowsForCreateTypes(Array $inputPrefixData)
        {
            assert('is_array($inputPrefixData)');
            $attributeRows     = array(self::REQUIRED_ATTRIBUTES_INDEX     => array(),
                                       self::NON_REQUIRED_ATTRIBUTES_INDEX => array());
            foreach ($this->model->resolveAllRequiredActionAttributeFormsAndLabelsAndSort() as $attribute => $actionAttributeForm)
            {
                $elementAdapter  = new WorkflowActionAttributeToElementAdapter($actionAttributeForm, $this->form,
                    $this->model->type, array_merge($inputPrefixData, array($attribute)), true);
                $attributeRows[self::REQUIRED_ATTRIBUTES_INDEX][] = $elementAdapter->getContent();
            }
            foreach ($this->model->resolveAllNonRequiredActionAttributeFormsAndLabelsAndSort() as $attribute => $actionAttributeForm)
            {
                $elementAdapter  = new WorkflowActionAttributeToElementAdapter($actionAttributeForm, $this->form,
                    $this->model->type, array_merge($inputPrefixData, array($attribute)), false);
                $attributeRows[self::NON_REQUIRED_ATTRIBUTES_INDEX][] = $elementAdapter->getContent();
            }
            return $attributeRows;
        }

        /**
         * @param $attributeRows
         * @return null|string
         */
        protected function renderAttributesRowsContent(Array $attributeRows)
        {
            assert('is_array($attributeRows)');
            $content = null;
            if (count($attributeRows[self::REQUIRED_ATTRIBUTES_INDEX]) > 0)
            {
                $content .= ZurmoHtml::tag('h3', array(), Zurmo::t('WorkflowsModule', 'Required Fields'));
            }
            foreach ($attributeRows[self::REQUIRED_ATTRIBUTES_INDEX] as $attributeContent)
            {
                $content .= ZurmoHtml::tag('div', array('class' => 'dynamic-sub-row'), $attributeContent);
            }
            if (count($attributeRows[self::REQUIRED_ATTRIBUTES_INDEX]) > 0 &&
               count($attributeRows[self::NON_REQUIRED_ATTRIBUTES_INDEX]) > 0)
            {
                $content .= ZurmoHtml::tag('h3', array(), Zurmo::t('WorkflowsModule', 'Other Fields'));
            }
            foreach ($attributeRows[self::NON_REQUIRED_ATTRIBUTES_INDEX] as $attributeContent)
            {
                $content .= ZurmoHtml::tag('div', array('class' => 'dynamic-sub-row'), $attributeContent);
            }
            return $content;
        }

        /**
         * @param string $rowId
         * @return string
         */
        protected function renderSaveActionElementsContent($rowId)
        {
            assert('is_string($rowId)');
            $params                = array();
            $params['label']       = Zurmo::t('Core', 'Save');
            $params['htmlOptions'] = array('id' => 'saveAction' . $this->rowNumber,
                                     'data-purpose' => 'validate-action',
                                     'data-row' => $rowId,
                                     'onclick' => 'js:$(this).addClass("attachLoadingTarget");');
            $element               = new SaveButtonActionElement(null, null, null, $params);
            return $element->render();
        }
    }
?>