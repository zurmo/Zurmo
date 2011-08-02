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
     *
     */
    class ImportWizardMappingExtraColumnView extends View
    {
        protected $model;

        protected $mappingDataMetadata;

        public function __construct(ImportWizardForm $model, $mappingDataMetadata, $mappableAttributeIndicesAndDerivedTypes)
        {
            assert('is_array($model->mappingData) && count($model->mappingData) > 0');
            assert('is_array($mappingDataMetadata)');
            assert('is_array($mappableAttributeIndicesAndDerivedTypes)');
            $this->model                                      = $model;
            $this->mappingDataMetadata                        = $mappingDataMetadata;
            $this->mappableAttributeIndicesAndDerivedTypes    = $mappableAttributeIndicesAndDerivedTypes;
        }

        public function render()
        {
            return $this->renderContent();
        }

        protected function renderContent()
        {
            $form                                    = new ZurmoActiveForm();
            $mappingFormLayoutUtil                   = new MappingFormLayoutUtil(get_class($this->model), $form,
                                                       $this->mappableAttributeIndicesAndDerivedTypes);
            $mappingDataMetadataWithRenderedElements = $this->resolveMappingDataMetadataWithRenderedElements(
                                                                                  $mappingFormLayoutUtil,
                                                                                  $this->mappingDataMetadata,
                                                                                  $this->model->firstRowIsHeaderRow,
                                                                                  $this->model->importRulesType,
                                                                                  $this->model->id);
            return MappingFormLayoutUtil::renderMappingDataMetadataWithRenderedElements(
                   $mappingDataMetadataWithRenderedElements);
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
                assert('$mappingDataRow["type"] == "extraColumn"');
                $row          = array();
                $row['cells'] = array();
                $row['cells'][] = $mappingFormLayoutUtil->renderAttributeAndColumnTypeContent(
                                                                       $columnName,
                                                                       $mappingDataRow['type'],
                                                                       $mappingDataRow['attributeIndexOrDerivedType'],
                                                                       $ajaxOnChangeUrl);
                if($firstRowIsHeaderRow)
                {
                    $row['cells'][] = '&#160;';
                }
                $row['cells'][] = '&#160;'; //Never any sample data for the extraColumn
                $row['cells'][] = $mappingFormLayoutUtil->renderMappingRulesElements(
                                      $columnName,
                                      $mappingDataRow['attributeIndexOrDerivedType'],
                                      $importRulesType,
                                      $mappingDataRow['type'],
                                      array());
                $metadata['rows'][] = $row;
            }
            return $metadata;
        }
    }
?>