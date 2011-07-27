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

    class ImportWizardMappingView extends ImportWizardView
    {
        protected $mappingDataMetadata;

        /**
         * Constructs a module permissions view specifying the controller as
         * well as the model that will have its details displayed.
         */
        public function __construct($controllerId, $moduleId, ImportWizardForm $model, $importId, $mappingDataMetadata)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_int($importId)');
            assert('is_array($model->mappingData) && count($model->mappingData) > 0');
            assert('is_array($mappingDataMetadata)');
            $this->controllerId        = $controllerId;
            $this->moduleId            = $moduleId;
            $this->model               = $model;
            $this->modelId             = $importId;
            $this->mappingDataMetadata = $mappingDataMetadata;
        }

        /**
         * Override to produce a form layout that does not follow the
         * standard form layout for EditView.
          */
        protected function renderFormLayout($form = null)
        {
            $content       = '';
            $mappingData   = $this->model->mappingData;
            $headerColumns  = $this->getFormLayoutHeaderColumns();
            assert('count($permissions) > 0');

            $content .= '<table>';
            $content .= '<colgroup>';
            $content .= '<col style="width:20%" />';
            $width = 80 / count($headerColumns);
            foreach ($headerColumns as $headerColumnLabel)
            {
                $content .= '<col style="width:' . $width . '%" />';
            }
            $content .= '</colgroup>';
            $content .= '<tbody>';
            $content .= '<tr>';
            $content .= '<th>&#160;</th>';
            foreach ($headerColumns as $headerColumnLabel)
            {
                $content .= '<th>' . $headerColumnLabel . '</th>';
            }
            $content .= '</tr>';
            foreach ($mappingData as $columnName => $row)
            {
                assert('isset($row["attributeNameOrDerivedType"])');
                assert('isset($row["mappingDataRules"])');
                $content .= '<tr>';
                $content .= $this->renderAttributeDropDownElement($columnName);
                if($this->model->firstRowIsHeaderRow)
                {
                    $content .= $this->renderHeaderColumnElement($columnName);
                }
                $content .= $this->renderImportColumnElement($columnName);
                $content .= $this->renderMappingRulesElements($columnName, $row['mappingDataRules']);

                $content .= '</tr>';
            }
            $content .= '</tbody>';
            $content .= '</table>';
            return $content;
        }

        protected function renderAttributeDropDownElement($columnName)
        {
            assert('is_string($columnName)');
            $attributeName             = FormModelUtil::getDerivedAttributeNameFromTwoStrings(
                                         $columnName,
                                         ImportWizardForm::MAPPING_COLUMN_ATTRIBUTE);
            $element                   = new ImportMappingZurmoAttributeDropdownElement(
                                            $this->model,
                                            $attributeName,
                                            $form);
            $element->editableTemplate = '<td>{content}{error}</td>';
            return $element->render();
        }

        protected function renderHeaderColumnElement($columnName)
        {
            assert('is_string($columnName)');
            $attributeName             = FormModelUtil::getDerivedAttributeNameFromTwoStrings(
                                         $columnName,
                                         ImportWizardForm::MAPPING_COLUMN_HEADER);
            $element                   = new ImportMappingHeaderColumnElement(
                                            $this->model,
                                            $attributeName,
                                            $form);
            $element->editableTemplate = '<td>{content}{error}</td>';
            return $element->render();
        }

        protected function renderImportColumnElement($columnName)
        {
            assert('is_string($columnName)');
            $attributeName             = FormModelUtil::getDerivedAttributeNameFromTwoStrings(
                                         $columnName,
                                         ImportWizardForm::MAPPING_COLUMN_IMPORT);
            $element                   = new ImportMappingImportColumnElement(
                                            $this->model,
                                            $attributeName,
                                            $form);
            $element->editableTemplate = '<td>{content}{error}</td>';
            return $element->render();
        }

        protected function renderMappingRulesElements($columnName, $columnMappingRulesData)
        {
            assert('is_string($columnName)');
            assert('is_array($columnMappingRulesData) || $columnMappingRulesData == null');
            $content = '<td>';
            if($columnMappingRulesData != null)
            {
                foreach($columnMappingRulesData as $mappingRulesType => $mappingRulesValue)
                {
                    $attributeName             = FormModelUtil::getDerivedAttributeNameFromTwoStrings(
                                                 $columnName,
                                                 ImportWizardForm::MAPPING_COLUMN_IMPORT);
                    $elementClassName          = $mappingRulesType . 'Element';
                    $element                   = new $elementClassName(
                                                    $this->model,
                                                    $attributeName,
                                                    $form);
                    $content .= '<table><tbody><tr>';
                    $content .= $element->render();
                    $content .= '</tr></tbody></table>';
                }
            }
            $content .= '</td>';
            return $content;
        }
    }
?>