<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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
     * View for mapping import columns to zurmo attributes. Also has user interface to fill in rules such as attribute
     * defaults and other attribute specific rules.
     */
    class ImportWizardMappingView extends ImportWizardView
    {
        protected $sampleColumnPagerContent;

        /**
         * The import's mapping data is massaged by adding sample column values and header values if available. This
         * property is set from the constructor and passed from the controller into this view.
         * @var array
         */
        protected $mappingDataMetadata;

        protected $mappableAttributeIndicesAndDerivedTypes;

        protected $requiredAttributesLabelsData;

        public function __construct($controllerId,
                                    $moduleId,
                                    ImportWizardForm $model,
                                    $sampleColumnPagerContent,
                                    $mappingDataMetadata,
                                    $mappingDataMappingRuleFormsAndElementTypes,
                                    $mappableAttributeIndicesAndDerivedTypes,
                                    $requiredAttributesLabelsData)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_array($model->mappingData) && count($model->mappingData) > 0');
            assert('is_string($sampleColumnPagerContent)');
            assert('is_array($mappingDataMetadata)');
            assert('is_array($mappableAttributeIndicesAndDerivedTypes)');
            assert('is_array($requiredAttributesLabelsData)');
            $this->controllerId                               = $controllerId;
            $this->moduleId                                   = $moduleId;
            $this->model                                      = $model;
            $this->sampleColumnPagerContent                   = $sampleColumnPagerContent;
            $this->mappingDataMetadata                        = $mappingDataMetadata;
            $this->mappingDataMappingRuleFormsAndElementTypes = $mappingDataMappingRuleFormsAndElementTypes;
            $this->mappableAttributeIndicesAndDerivedTypes    = $mappableAttributeIndicesAndDerivedTypes;
            $this->requiredAttributesLabelsData               = $requiredAttributesLabelsData;
        }

        /**
         * Override to produce a form layout that does not follow the
         * standard form layout for EditView.
          */
        protected function renderFormLayout($form = null)
        {
            assert('$form != null && $form instanceof ZurmoActiveForm');
            $mappingFormLayoutUtil                   = new MappingFormLayoutUtil(get_class($this->model), $form,
                                                       $this->mappableAttributeIndicesAndDerivedTypes);
            $mappingDataMetadataWithRenderedElements = $this->resolveMappingDataMetadataWithRenderedElements(
                                                                                  $mappingFormLayoutUtil,
                                                                                  $this->mappingDataMetadata,
                                                                                  $this->model->firstRowIsHeaderRow,
                                                                                  $this->model->importRulesType,
                                                                                  $this->model->id);
            $headerColumns  = $this->getFormLayoutHeaderColumnsContent();
            assert('count($headerColumns) > 0');

            $content  = $form->errorSummary($this->model);
            $content .= '<h3>' . Yii::t('Default', 'Please map the fields you would like to import.') . '</h3>';
            $content .= $this->renderRequiredAttributesLabelsDataContent();
            $content .= '<table>';
            $content .= '<colgroup>';
            $content .= '<col style="width:20%" />';
            $content .= '<col style="width:20%" />';
            if(count($headerColumns) == 4)
            {
                $content .= '<col style="width:20%" />';
                $content .= '<col style="width:40%" />';
            }
            else
            {
                $content .= '<col style="width:60%" />';
            }
            $content .= '</colgroup>';
            $content .= '<tbody>';
            $content .= '<tr>';
            foreach ($headerColumns as $headerColumnContent)
            {
                $content .= '<th>' . $headerColumnContent . '</th>';
            }
            $content .= '</tr>';
            $content .= MappingFormLayoutUtil::
                        renderMappingDataMetadataWithRenderedElements($mappingDataMetadataWithRenderedElements);
            $content .= '<tr>';
            $content .= '<td colspan="' . count($headerColumns) . '">';
            $content .= $this->renderAddExtraColumnContent(count($this->mappingDataMetadata));
            $content .= '</td>';
            $content .= '</tr>';
            $content .= '</tbody>';
            $content .= '</table>';
            $content .= $this->renderActionLinksContent();
            return $content;
        }

        protected function renderRequiredAttributesLabelsDataContent()
        {
            $content = null;
            if(count($this->requiredAttributesLabelsData) > 0)
            {
                $content .= '<b>' . Yii::t('Default', 'Required Fields') . '</b>' . '<br/>';
                foreach($this->requiredAttributesLabelsData as $label)
                {
                    $content .= $label. '<br/>';
                }
                $content .= '<br/>';
            }
            return $content;
        }

        protected function getFormLayoutHeaderColumnsContent()
        {
            $headerColumns = array();
            $headerColumns[] = Yii::t('Default', 'Zurmo Field');
            if($this->model->firstRowIsHeaderRow)
            {
                $headerColumns[] = Yii::t('Default', 'Header');
            }
            $headerColumns[] = '<div id="' . MappingFormLayoutUtil::getSampleColumnHeaderId() . '">' .
                               $this->sampleColumnPagerContent . '</div>';
            $headerColumns[] = Yii::t('Default', 'Rules');
            return $headerColumns;
        }

        protected function resolveMappingDataMetadataWithRenderedElements($mappingFormLayoutUtil, $mappingDataMetadata,
                                                                          $firstRowIsHeaderRow, $importRulesType, $id)
        {
            assert('$mappingFormLayoutUtil instanceof MappingFormLayoutUtil');
            assert('is_int($id)');
            $ajaxOnChangeUrl  = Yii::app()->createUrl("import/default/mappingRulesEdit", array('id' => $id));
            $metadata         = array();
            $metadata['rows'] = array();
            foreach ($mappingDataMetadata as $columnName => $mappingDataRow)
            {
                assert('$mappingDataRow["type"] == "importColumn" || $mappingDataRow["type"] == "extraColumn"');
                $row          = array();
                $row['cells'] = array();
                $row['cells'][] = $mappingFormLayoutUtil->renderAttributeAndColumnTypeContent(
                                                                       $columnName,
                                                                       $mappingDataRow['type'],
                                                                       $mappingDataRow['attributeIndexOrDerivedType'],
                                                                       $ajaxOnChangeUrl);
                if($firstRowIsHeaderRow)
                {
                    assert('$mappingDataRow["headerValue"] == null || is_string($mappingDataRow["headerValue"])');
                    $row['cells'][] = $mappingFormLayoutUtil->renderHeaderColumnContent($columnName,
                                                                                    $mappingDataRow['headerValue']);
                }
                $row['cells'][] = $mappingFormLayoutUtil->renderImportColumnContent ($columnName,
                                                                                 $mappingDataRow['sampleValue']);
                $row['cells'][] = $mappingFormLayoutUtil->renderMappingRulesElements(
                                      $columnName,
                                      $mappingDataRow['attributeIndexOrDerivedType'],
                                      $importRulesType,
                                      $mappingDataRow['type'],
                                      $this->resolveMappingRuleFormsAndElementTypesByColumn($columnName));
                $metadata['rows'][] = $row;
            }
            return $metadata;
        }

        protected function renderAddExtraColumnContent($columnCount)
        {
            assert('is_int($columnCount)');
            $idInputHtmlOptions  = array('id' => 'columnCounter');
            $hiddenInputName     = 'columnCounter';
            $ajaxOnChangeUrl     = Yii::app()->createUrl("import/default/mappingAddExtraMappingRow",
                                   array('id' => $this->model->id));
            $content             = CHtml::hiddenField($hiddenInputName, $columnCount, $idInputHtmlOptions);
            $content            .= CHtml::ajaxButton(Yii::t('Default', 'Add Field'), $ajaxOnChangeUrl,
                                    array('type' => 'GET',
                                          'data' => 'js:\'columnCount=\' + $(\'#columnCounter\').val()',
                                          'success' => 'js:function(data){
                                            $(\'#columnCounter\').val(parseInt($(\'#columnCounter\').val()) + 1)
                                            $(\'#addExtraColumnButton\').parent().parent().prev().after(data);
                                          }'),
                                    array('id' => 'addExtraColumnButton'));
            return $content;
        }

        protected function resolveMappingRuleFormsAndElementTypesByColumn($columnName)
        {
            assert('is_string($columnName)');
            if(isset($this->mappingDataMappingRuleFormsAndElementTypes[$columnName]))
            {
                return $this->mappingDataMappingRuleFormsAndElementTypes[$columnName];
            }
            return array();
        }

        protected function renderPreviousPageLinkContent()
        {
            return $this->getPreviousPageLinkContentByControllerAction('step3');
        }
    }
?>