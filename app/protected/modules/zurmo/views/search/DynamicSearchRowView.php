<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    /**
     * Class for rendering an extra row when 'Add Field' is clicked in the advanced search.
     */
    class DynamicSearchRowView extends View
    {
        protected $searchableAttributeIndicesAndDerivedTypes;

        protected $rowNumber;

        protected $suffix;

        protected $formModelClassName;

        protected $ajaxOnChangeUrl;

        protected $selectedAttribute;

        protected $inputContent;

        public function __construct($searchableAttributeIndicesAndDerivedTypes, $rowNumber,
                                    $suffix, $formModelClassName, $ajaxOnChangeUrl, $selectedAttribute = null, $inputContent = null)
        {
            assert('is_array($searchableAttributeIndicesAndDerivedTypes)');
            assert('is_int($rowNumber)');
            assert('is_string($suffix) || $suffix == null');
            assert('is_string($formModelClassName)');
            assert('is_string($ajaxOnChangeUrl)');
            assert('is_string($selectedAttribute) || $selectedAttribute == null');
            assert('is_string($inputContent) || $inputContent == null');
            $this->searchableAttributeIndicesAndDerivedTypes    = $searchableAttributeIndicesAndDerivedTypes;
            $this->rowNumber                                    = $rowNumber;
            $this->suffix                                       = $suffix;
            $this->formModelClassName                           = $formModelClassName;
            $this->ajaxOnChangeUrl                              = $ajaxOnChangeUrl;
            $this->selectedAttribute                            = $selectedAttribute;
            $this->inputContent                                 = $inputContent;
        }

        public function render()
        {
            return $this->renderContent();
        }

        protected function renderContent()
        {
            $this->renderScripts();
            $hiddenInputName     = $this->formModelClassName . '[' . DynamicSearchForm::DYNAMIC_NAME . '][' . $this->rowNumber . '][structurePosition]';
            $hiddenInputId       = $this->formModelClassName . '_' . DynamicSearchForm::DYNAMIC_NAME . '_' . $this->rowNumber . '_structurePosition';
            $idInputHtmlOptions  = array('id' => $hiddenInputId, 'class' => 'structure-position');

            $content  = '<div>';
            $content .= ZurmoHtml::wrapLabel(($this->rowNumber + 1) . '.', 'dynamic-search-row-number-label');
            $content .= $this->renderAttributeDropDownContent();
            $content .= ZurmoHtml::hiddenField($hiddenInputName, ($this->rowNumber + 1), $idInputHtmlOptions);
            $content .= ZurmoHtml::tag('div', array('id' => $this->getInputsDivId(), 'class' => 'criteria-value-container'), $this->inputContent);
            $content .= '</div>';
            $content .= ZurmoHtml::link('_', '#', array('class' => 'remove-extra-dynamic-search-row-link'));
            return $content;
        }

        /**
         * Renders special scripts required for displaying the view.  Renders scripts for dropdown styling and interaction.
         */
        protected function renderScripts()
        {
            DropDownUtil::registerScripts(CClientScript::POS_END);
        }

        protected function renderAttributeDropDownContent()
        {
            $name        = $this->formModelClassName . '[' . DynamicSearchForm::DYNAMIC_NAME . '][' . $this->rowNumber . '][attributeIndexOrDerivedType]';
            $id          = $this->formModelClassName . '_' . DynamicSearchForm::DYNAMIC_NAME . '_' . $this->rowNumber . '_attributeIndexOrDerivedType';
            $htmlOptions = array('id' => $id, 'class' => 'attribute-dropdown',
                'empty' => Zurmo::t('ZurmoModule', 'Select a field')
            );
            Yii::app()->clientScript->registerScript('AttributeDropDown' . $id,
                                                     $this->renderAttributeDropDownOnChangeScript($id,
                                                     $this->getInputsDivId(),
                                                     $this->ajaxOnChangeUrl));
            $content  = ZurmoHtml::dropDownList($name,
                                           $this->selectedAttribute,
                                           $this->searchableAttributeIndicesAndDerivedTypes,
                                           $htmlOptions);
            Yii::app()->clientScript->registerScript('mappingExtraColumnRemoveLink', "
            $('.remove-extra-dynamic-search-row-link').unbind('click');
            $('.remove-extra-dynamic-search-row-link').bind('click', function()
                {
                    formId = $(this).closest('form').attr('id');
                    $(this).parent().remove();
                    rebuildDynamicSearchRowNumbersAndStructureInput(formId);
                    resolveClearLinkPrefixLabelAndVisibility(formId);
                }
            );");
            return $content;
        }

        protected function renderAttributeDropDownOnChangeScript($id, $inputDivId, $ajaxOnChangeUrl)
        {
            // Begin Not Coding Standard
            $ajaxSubmitScript  = ZurmoHtml::ajax(array(
                    'type'    => 'GET',
                    'data'    => 'js:\'suffix=' . $this->suffix .
                                 '&attributeIndexOrDerivedType=\' + $(this).val()',
                    'url'     =>  $ajaxOnChangeUrl,
                    'beforeSend' => 'js:function(){
                        $("#' . $inputDivId . '").html("<span class=\"loading z-spinner\"></span>");
                        attachLoadingSpinner("' . $inputDivId . '", true, "dark");
                        }',
                    'success' => 'js:function(data){ $("#' . $inputDivId . '").html(data); }',
            ));
            return "$('#" . $id . "').unbind('change'); $('#" . $id . "').bind('change', function()
            {
                $ajaxSubmitScript
            }
            );";
            // End Not Coding Standard
        }

        protected function getInputsDivId()
        {
            return $this->formModelClassName . '-dynamic-search-inputs-for-' . $this->rowNumber . '-' . $this->suffix;
        }
    }
?>