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
     * Supports dyanmic advanced search.  This is where the user can decide the fields to filter on.
     */
    abstract class DynamicSearchView extends SearchView
    {
        const ADVANCED_SEARCH_TYPE_STATIC  = 'Static';

        const ADVANCED_SEARCH_TYPE_DYNAMIC = 'Dynamic';

        public static function getDesignerRulesType()
        {
            return 'DynamicSearchView';
        }

        /**
         * Constructs a detail view specifying the controller as
         * well as the model that will have its details displayed.
         */
        public function __construct($model, $listModelClassName, $gridIdSuffix = null, $hideAllSearchPanelsToStart = false)
        {
            assert('$model instanceof DynamicSearchForm');
            parent::__construct($model, $listModelClassName, $gridIdSuffix, $hideAllSearchPanelsToStart);
        }

        protected function getClientOptions()
        {
            return array(
                        'validateOnSubmit'  => true,
                        'validateOnChange'  => false,
                        'beforeValidate'    => 'js:beforeValidateAction',
                        'afterValidate'     => 'js:afterDynamicSearchValidateAjaxAction',
                        'afterValidateAjax' => $this->renderConfigSaveAjax($this->getSearchFormId()),
                    );
        }

        protected function getEnableAjaxValidationValue()
        {
            return true;
        }

        protected function getExtraRenderForClearSearchLinkScript()
        {
            return parent::getExtraRenderForClearSearchLinkScript() .
                    "$(this).closest('form').find('.search-view-1').find('.dynamic-search-row').each(function()
                    {
                        $(this).remove();
                    });
                    $('#" . $this->getRowCounterInputId() . "').val(0);
                    $('#" . $this->getStructureInputId() . "').val('');
                    $(this).closest('form').find('.search-view-1').hide();
                    $('.select-list-attributes-view').hide();
                    resolveClearLinkPrefixLabelAndVisibility('" . $this->getSearchFormId() . "');
                    rebuildDynamicSearchRowNumbersAndStructureInput('" . $this->getSearchFormId() . "');
            ";
        }

        protected function getFormActionUrl()
        {
            return Yii::app()->createUrl('zurmo/default/validateDynamicSearch',
                                            array('viewClassName'       => get_class($this),
                                                  'modelClassName'     => get_class($this->model->getModel()),
                                                  'formModelClassName' => get_class($this->model)));
        }

        protected function getRowCounterInputId()
        {
            return 'rowCounter-' . $this->getSearchFormId();
        }

        protected function getStructureInputId()
        {
            return get_class($this->model) . '_' . DynamicSearchForm::DYNAMIC_STRUCTURE_NAME;
        }

        protected function getStructureInputName()
        {
            return get_class($this->model) . '[' . DynamicSearchForm::DYNAMIC_STRUCTURE_NAME . ']';
        }

        protected function renderConfigSaveAjax($formName)
        {
            return     "$(this).closest('form').find('.search-view-1').hide();
                        $('.select-list-attributes-view').hide();
                        $('#" . $formName . "').find('.attachLoading:first').removeClass('loading');
                        $('#" . $formName . "').find('.attachLoading:first').removeClass('loading-ajax-submit');
                        $('#" . $this->gridId . $this->gridIdSuffix . "-selectedIds').val(null);
                        $.fn.yiiGridView.update('" . $this->gridId . $this->gridIdSuffix . "',
                        {
                            data: $('#" . $formName . "').serialize() + '&" . $this->listModelClassName . "_page=&" . // Not Coding Standard
                            $this->listModelClassName . "_sort=" .
                            $this->getExtraQueryPartForSearchFormScriptSubmitFunction() ."' // Not Coding Standard
                         }
                        );
                        $('#" . $this->getClearingSearchInputId() . "').val('');
                        ";
        }

        protected function getExtraRenderFormBottomPanelScriptPart()
        {
            return parent::getExtraRenderFormBottomPanelScriptPart() .
                    "$('#" . $this->getSearchFormId(). "').find('.anyMixedAttributes-input').unbind('input.clear propertychange.clear keyup.clear');
                     $('#" . $this->getSearchFormId(). "').find('.anyMixedAttributes-input').bind('input.clear propertychange.clear keyup.clear', function(event)
                     {
                         resolveClearLinkPrefixLabelAndVisibility('" . $this->getSearchFormId() . "');
                     });";
        }

        /**
         * Override to do nothing since the validation and ajax is controlled via @see renderConfigSaveAjax
         * (non-PHPdoc)
         * @see SearchView::renderAdvancedSearchScripts()
         */
        protected function renderAdvancedSearchScripts()
        {
        }

        protected function renderAdvancedSearchForFormLayout($panel, $maxCellsPerRow, $form = null)
        {
            if (isset($panel['advancedSearchType']) &&
               $panel['advancedSearchType'] == self::ADVANCED_SEARCH_TYPE_DYNAMIC)
            {
                return $this->renderDynamicAdvancedSearchRows($panel, $maxCellsPerRow, $form);
            }
            else
            {
                return $this->renderStaticSearchRows($panel, $maxCellsPerRow, $form);
            }
        }

        protected function renderDynamicAdvancedSearchRows($panel, $maxCellsPerRow,  $form)
        {
            assert('$form != null');
            $content            = $form->errorSummary($this->model);
            $content           .= $this->renderDynamicClausesValidationHelperContent($form);
            $rowCount           = 0;
            $suffix             = $this->getSearchFormId();
            $viewClassName      = get_class($this);
            $modelClassName     = get_class($this->model->getModel());
            $formModelClassName = get_class($this->model);
            if ($this->model->dynamicClauses!= null)
            {
                foreach ($this->model->dynamicClauses as $dynamicClause)
                {
                    $attributeIndexOrDerivedType = ArrayUtil::getArrayValue($dynamicClause, 'attributeIndexOrDerivedType');
                    if ($attributeIndexOrDerivedType != null)
                    {
                        $searchAttributes = self::resolveSearchAttributeValuesForDynamicRow($dynamicClause,
                                                                                            $attributeIndexOrDerivedType);
                        $inputContent = DynamicSearchUtil::renderDynamicSearchAttributeInput($viewClassName,
                                                                                             $modelClassName,
                                                                                             $formModelClassName,
                                                                                             (int)$rowCount,
                                                                                             $attributeIndexOrDerivedType,
                                                                                             $searchAttributes,
                                                                                             $suffix);
                        $content .= DynamicSearchUtil::renderDynamicSearchRowContent(        $viewClassName,
                                                                                             $modelClassName,
                                                                                             $formModelClassName,
                                                                                             $rowCount,
                                                                                             $attributeIndexOrDerivedType,
                                                                                             $inputContent,
                                                                                             $suffix);
                        $rowCount++;
                    }
                }
            }
            $content .= $this->renderAddExtraRowContent($rowCount);
            $content .= $this->renderAfterAddExtraRowContent($form);
            $content .= $this->renderDynamicSearchStructureContent($form);
           return $content;
        }

        protected static function resolveSearchAttributeValuesForDynamicRow($dynamicClause, $attributeIndexOrDerivedType)
        {
            $dynamicClauseOnlyWithAttributes = $dynamicClause;
            if (isset($dynamicClause['structurePosition']))
            {
                unset($dynamicClauseOnlyWithAttributes['structurePosition']);
            }
            if (isset($dynamicClause['attributeIndexOrDerivedType']))
            {
                unset($dynamicClauseOnlyWithAttributes['attributeIndexOrDerivedType']);
            }
            return $dynamicClauseOnlyWithAttributes;
        }

        protected function renderAddExtraRowContent($rowCount)
        {
            assert('is_int($rowCount)');
            $idInputHtmlOptions   = array('id' => $this->getRowCounterInputId());
            $hiddenInputName      = 'rowCounter';
            $ajaxOnChangeUrl      = Yii::app()->createUrl("zurmo/default/dynamicSearchAddExtraRow",
                                    array('viewClassName'      => get_class($this),
                                         'modelClassName'     => get_class($this->model->getModel()),
                                         'formModelClassName' => get_class($this->model),
                                         'suffix'             => $this->getSearchFormId()));
            $content              = ZurmoHtml::hiddenField($hiddenInputName, $rowCount, $idInputHtmlOptions);
            // Begin Not Coding Standard
            $addFieldLabelContent = $this->getAddFieldLabelContent();
            $aContent             = ZurmoHtml::wrapLink($addFieldLabelContent);
            $content             .= ZurmoHtml::ajaxLink($aContent, $ajaxOnChangeUrl,
                                    array('type' => 'GET',
                                          'data' => 'js:\'rowNumber=\' + $(\'#rowCounter-' . $this->getSearchFormId(). '\').val()',
                                          'beforeSend' => 'js:function(){
                                            makeOrRemoveLoadingSpinner(true, "#' . $this->getSearchFormId() . '", "dark");
                                            }',
                                          'success' => 'js:function(data){
                                            $(\'#' . $this->getRowCounterInputId(). '\').val(parseInt($(\'#' . $this->getRowCounterInputId() . '\').val()) + 1)
                                            $(\'#addExtraAdvancedSearchRowButton-' . $this->getSearchFormId() . '\').parent().before(data);
                                            rebuildDynamicSearchRowNumbersAndStructureInput("' . $this->getSearchFormId() . '");
                                            resolveClearLinkPrefixLabelAndVisibility("' . $this->getSearchFormId() . '");
                                            makeOrRemoveLoadingSpinner(false, "#' . $this->getSearchFormId() . '");
                                          }'),
                                    array('id' => 'addExtraAdvancedSearchRowButton-' . $this->getSearchFormId(), 'namespace' => 'add'));
            // End Not Coding Standard
            return ZurmoHtml::tag('div', array('class' => 'add-fields-container'), $content);
        }

        protected function renderAfterAddExtraRowContent($form)
        {
        }

        protected function getAddFieldLabelContent()
        {
            return Zurmo::t('ZurmoModule', 'Add criteria');
        }

        protected function renderAfterFormLayout($form)
        {
           parent::renderAfterFormLayout($form);
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.core.views.assets')) . '/dynamicSearchViewUtils.js');
            Yii::app()->clientScript->registerScript('showStructurePanels' . $this->getSearchFormId(), "
                $('#show-dynamic-search-structure-div-link-" . $this->getSearchFormId() . "').click( function()
                    {
                        $('#show-dynamic-search-structure-div-"      . $this->getSearchFormId() . "').show();
                        $('#show-dynamic-search-structure-div-link-" . $this->getSearchFormId() . "').hide();
                        return false;
                    }
                );");
        }

        /**
         * This is a trick to properly validate this form. Eventually refactor.  Used to support error summary correctly.
         */
        protected function renderDynamicClausesValidationHelperContent($form)
        {
            $htmlOptionsForInput = array('id'   => get_class($this->model) . '_dynamicClausesNotUsed',
                                         'name' => 'dynamicClausesValidationHelper',
                                         'value' => 'notUsed');
            $htmlOptionsForError = array('id'   => get_class($this->model) . '_dynamicClauses');
            $content  = '<div style="display:none;">';
            $content .= $form->hiddenField($this->model, 'dynamicClauses', $htmlOptionsForInput);
            $content .= $form->error($this->model, 'dynamicClauses', $htmlOptionsForError);
            $content .= '</div>';
            return $content;
        }

        protected function renderDynamicSearchStructureContent($form)
        {
            if ($this->shouldHideDynamicSearchStructureByDefault())
            {
                $style1 = '';
                $style2 = 'display:none;';
            }
            else
            {
                $style1 = 'display:none;';
                $style2 = '';
            }
            if (count($this->model->dynamicClauses) > 0)
            {
                $style3 = '';
            }
            else
            {
                $style3 = 'display:none;';
            }
            $content  = ZurmoHtml::link(Zurmo::t('ZurmoModule', 'Modify Structure'), '#',
                            array('id'    => 'show-dynamic-search-structure-div-link-' . $this->getSearchFormId() . '',
                                  'style' => $style1));
            $content .= ZurmoHtml::tag('div',
                            array('id'    => 'show-dynamic-search-structure-div-' . $this->getSearchFormId(),
                                  'class' => 'has-lang-label',
                                  'style' => $style2), $this->renderStructureInputContent($form));
            $content  = ZurmoHtml::tag('div', array('id'    => 'show-dynamic-search-structure-wrapper-' . $this->getSearchFormId(),
                                                     'style' => $style3), $content);
            return $content;
        }

        protected function renderStructureInputContent($form)
        {
            $idInputHtmlOptions  = array('id'    => $this->getStructureInputId(),
                                         'name'  => $this->getStructureInputName(),
                                         'class' => 'dynamic-search-structure-input');
            $content             = $form->textField($this->model, 'dynamicStructure', $idInputHtmlOptions);
            $content            .= ZurmoHtml::tag('span', array(), Zurmo::t('ZurmoModule', 'Search Operator'));
            $content            .= $form->error($this->model, 'dynamicStructure');
            return $content;
        }

        protected function shouldHideDynamicSearchStructureByDefault()
        {
            return true;
        }

        protected function getClearSearchLabelPrefixContent()
        {
            if (Yii::app()->userInterface->isMobile())
            {
                return parent::getClearSearchLabelPrefixContent();
            }
            $criteriaCount = count($this->model->dynamicClauses);
            if ($this->model->anyMixedAttributes != null)
            {
                $criteriaCount++;
            }
            if ($criteriaCount == 0)
            {
                $criteriaCountContent = '';
            }
            else
            {
                $criteriaCountContent = $criteriaCount . ' ';
            }
            return ZurmoHtml::tag('span',
                                  array('class' => 'clear-search-link-criteria-selected-count'),
                                  $criteriaCountContent);
        }

        protected function getClearSearchLabelContent()
        {
            if (Yii::app()->userInterface->isMobile())
            {
                return parent::getClearSearchLabelContent();
            }
            return Zurmo::t('ZurmoModule', 'Criteria Selected <span class="icon-clear">Z</span>');
        }

        protected function getClearSearchLinkStartingStyle()
        {
            if ($this->model->anyMixedAttributes == null && count($this->model->dynamicClauses) == 0)
            {
                return "display:none;";
            }
        }

        /**
         * Override and manipulate as needed. This method can be used to change the ordering that the dynamic search
         * attribute dropdown shows attributes in for example.
         * @param array $attributeIndexOrDerivedTypeAndLabels
         */
        public static function resolveAttributeIndexOrDerivedTypeAndLabelsForDynamicSearchRow(
                               & $attributeIndexOrDerivedTypeAndLabels)
        {
        }
    }
?>