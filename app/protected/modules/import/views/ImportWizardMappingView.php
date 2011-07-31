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
        /**
         * The import's mapping data is massaged by adding sample column values and header values if available. This
         * property is set from the constructor and passed from the controller into this view.
         * @var array
         */
        protected $mappingDataMetadata;

        public function __construct($controllerId, $moduleId, ImportWizardForm $model, $mappingDataMetadata,
                                    $mappingDataMappingRuleFormsAndElementTypes)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_array($model->mappingData) && count($model->mappingData) > 0');
            assert('is_array($mappingDataMetadata)');
            $this->controllerId                               = $controllerId;
            $this->moduleId                                   = $moduleId;
            $this->model                                      = $model;
            $this->mappingDataMetadata                        = $mappingDataMetadata;
            $this->mappingDataMappingRuleFormsAndElementTypes = $mappingDataMappingRuleFormsAndElementTypes;
        }

        /**
         * Override to produce a form layout that does not follow the
         * standard form layout for EditView.
          */
        protected function renderFormLayout($form = null)
        {
            $content       = '';
            $headerColumns  = $this->getFormLayoutHeaderColumnsContent();
            assert('count($permissions) > 0');

            $content .= '<table>';
            $content .= '<colgroup>';
            $content .= '<col style="width:20%" />';
            $width = 80 / count($headerColumns);
            foreach ($headerColumns as $notUsed)
            {
                $content .= '<col style="width:' . $width . '%" />';
            }
            $content .= '</colgroup>';
            $content .= '<tbody>';
            $content .= '<tr>';
            $content .= '<th>&#160;</th>';
            foreach ($headerColumns as $headerColumnContent)
            {
                $content .= '<th>' . $headerColumnContent . '</th>';
            }
            $content .= '</tr>';
            foreach ($this->mappingDataMetadata as $columnName => $row)
            {
                assert('isset($row["attributeNameOrDerivedType"])');
                assert('$row["type"] == "importColumn" || $row["type"] == "extraColumn"');
                assert('isset($row["sampleValue"])');
                $content .= '<tr>';
                $content .= $this->renderAttributeDropDownElement($columnName, $row['type']);
                if($this->model->firstRowIsHeaderRow)
                {
                    assert('isset($row["headerValue"])');
                    $content .= $this->renderHeaderColumnElement($columnName, $row['headerValue']);
                }
                $content .= $this->renderImportColumnElement ($columnName, $row['sampleValue']);
                $content .= $this->renderMappingRulesElements(
                                    $columnName,
                                    $row["attributeNameOrDerivedType"],
                                    $this->model->importRulesType,
                                    $row['type'],
                                    $this->resolveMappingRuleFormsAndElementTypesByColumn($columnName));
                $content .= '</tr>';
            }
            $content .= '</tbody>';
            $content .= '</table>';
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


        protected function getFormLayoutHeaderColumnsContent()
        {
            $headerColumns = array();
            $headerColumns[] = Yii::t('Default', 'Zurmo Field');
            if($this->model->firstRowIsHeaderRow)
            {
                $headerColumns[] = Yii::t('Default', 'Import Field');
            }
            $headerColumns[] = Yii::t('Default', 'Sample Value');
            $headerColumns[] = Yii::t('Default', 'Rules');
            return $headerColumns;
        }

        protected function renderAttributeDropDownElement($columnName, $columnType)
        {
            assert('is_string($columnName)');
            assert('$columnType == "importColumn" || $columnType == "extraColumn"');
            $attributeName             = FormModelUtil::getDerivedAttributeNameFromTwoStrings(
                                         $columnName,
                                         ImportWizardForm::MAPPING_COLUMN_ATTRIBUTE);
            $element                   = new ImportMappingZurmoAttributeDropdownElement(
                                            $this->model,
                                            $attributeName,
                                            $form);
            $element->editableTemplate = '<td>{content}{error}</td>';
            $content                   = $element->render();
            $attributeName             = FormModelUtil::getDerivedAttributeNameFromTwoStrings(
                                         $columnName,
                                         ImportWizardForm::MAPPING_COLUMN_TYPE);
            $htmlOptions               = array('id' => 'ImportWizardForm' . '_' . $attributeName);
            $hiddenInputName           = 'ImportWizardForm' . '[' . $attributeName . ']';
            $content                  .= hiddenField($hiddenInputName,
                                                     $columnType,
                                                     $idInputHtmlOptions);
            return $content;
        }

        protected function renderHeaderColumnElement($columnName, $headerValue)
        {
            assert('is_string($columnName)');
            assert('is_string($headerValue)');
            $content  = '<td>';
            $contentt = $headerValue;
            $content .= '</td>';
            return $content;
        }

        protected function renderImportColumnElement($columnName, $sampleValue)
        {
            assert('is_string($columnName)');
            assert('is_string($sampleValue) || $sampleValue == null');
            $attributeName             = $columnName . '-import-data';
            $content  = '<td>';
            $contentt = '<div id="{$attributeName}">' . $sampleValue . '</div>';
            $content .= '</td>';
            return $content;
        }

        protected function renderMappingRulesElements($columnName,
                                                      $attributeNameOrDerivedType,
                                                      $importRulesType,
                                                      $columnType,
                                                      $mappingRuleFormsAndElementTypes)
        {
            assert('is_string($columnName)');
            assert('is_string($attributeNameOrDerivedType)');
            assert('is_string($importRulesType)');
            assert('$columnType == "importColumn" || $columnType == "extraColumn"');
            assert('is_array($mappingRuleFormsAndElementTypes) || $mappingRuleFormsAndElementTypes == null');
            $content = '<td>';
            if($attributeNameOrDerivedType != null)
            {
                if($mappingRuleFormsAndElementTypes == null)
                {
                    $attributeImportRules            = AttributeImportRulesFactory::
                                                       makeByImportRulesTypeAndAttributeNameOrDerivedType(
                                                           $importRulesType,
                                                           $attributeNameOrDerivedType);
                    $mappingRuleFormsAndElementTypes = MappingRuleFormAndElementTypeUtil::
                                                       makeCollectionByAttributeImportRules(
                                                           $attributeImportRules,
                                                           $attributeNameOrDerivedType);
                }
                foreach($mappingRuleFormsAndElementTypes as $notUsed => $ruleFormAndElementType)
                {
                    $mappingRuleForm       = $ruleFormAndElementType['mappingRuleForm'];
                    if($columnType == 'ExtraAttribute')
                    {
                        $mappingRuleForm->setScenario('ExtraAttribute');
                    }
                    $elementClassName      = $ruleFormAndElementType['elementType'] . 'Element';
                    $attributeName         = $mappingRuleForm::getAttributeName();
                    $modelAttributeName    = FormModelUtil::getDerivedAttributeNameFromTwoStrings(
                                             $columnName,
                                             ImportWizardForm::MAPPING_COLUMN_RULES);
                    $params                = array();
                    $params['inputPrefix'] = array(get_class($this->model),
                                                   $modelAttributeName,
                                                   get_class($mappingRuleForm));
                    $element               = new $elementClassName(
                                                  $mappingRuleForm,
                                                  $attributeName,
                                                  $form,
                                                  $htmlOptions);
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