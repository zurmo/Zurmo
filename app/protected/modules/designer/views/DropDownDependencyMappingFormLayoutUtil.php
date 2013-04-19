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
     * Helper class that can take an array of @see DropDownDependencyCustomFieldMapping objects and render content
     * for a user interface. This user interface is where a user can decide on the mappings for a drop down dependency
     * attribute.
     */
    class DropDownDependencyMappingFormLayoutUtil
    {
        /**
         * Array of @see DropDownDependencyCustomFieldMapping objects
         * @var array
         */
        protected $dependencyCollection;

        /**
         * Name of form being used by the containing view.
         * @var string
         */
        protected $formName;

        /**
         * Controller Id to be used by any actions called.
         * @var string
         */
        protected $controllerId;

        /**
         * Module Id to be used by an actions called.
         * @var string
         */
        protected $moduleId;

        /**
         * Action Id to be used by the ajax action called in this class.
         * @var string
         */
        protected $ajaxActionId;

        /**
         * Div Id of the containing div. This is used by the ajax action to know which div content to update.
         * @var string
         */
        protected $mappingDataDivId;

        /**
         * @param array $dependencyCollection
         * @param string $formName
         * @param string $controllerId
         * @param string $moduleId
         * @param string $ajaxActionId
         * @param string $mappingDataDivId
         */
        public function __construct($dependencyCollection,
                                    $formName,
                                    $controllerId,
                                    $moduleId,
                                    $ajaxActionId,
                                    $mappingDataDivId)
        {
            assert('is_array($dependencyCollection) && count($dependencyCollection) >= 2');
            assert('is_string($formName)');
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_string($ajaxActionId)');
            assert('is_string($mappingDataDivId)');
            $this->dependencyCollection = $dependencyCollection;
            $this->formName             = $formName;
            $this->controllerId         = $controllerId;
            $this->moduleId             = $moduleId;
            $this->ajaxActionId         = $ajaxActionId;
            $this->mappingDataDivId     = $mappingDataDivId;
        }

        /**
         * Render content and return as a string.
         */
        public function render()
        {
            return $this->renderContainerContent();
        }

        protected function renderContainerContent()
        {
            $content  = '<table>';
            $content .= '<tr>';
            $content .= $this->renderCollectionContent();
            $content .= '</tr>';
            $content .= '</table>';
            return $content;
        }

        protected function renderCollectionContent()
        {
            $dropDownDependencyCustomFieldParentMapping = null;
            $content                                    = null;
            foreach ($this->dependencyCollection as $dropDownDependencyCustomFieldMapping)
            {
                assert('$dropDownDependencyCustomFieldMapping instanceof DropDownDependencyCustomFieldMapping');
                $content .= '<td>';
                $content .= $this->renderDependencyContent($dropDownDependencyCustomFieldMapping,
                                                           $dropDownDependencyCustomFieldParentMapping,
                                                           count($this->dependencyCollection));
                $content .= '</td>';
                $dropDownDependencyCustomFieldParentMapping = $dropDownDependencyCustomFieldMapping;
            }
            return $content;
        }

        protected function renderDependencyContent(DropDownDependencyCustomFieldMapping $mapping,
                                                   $parentMapping,
                                                   $totalPositions)
        {
            assert('$parentMapping instanceof DropDownDependencyCustomFieldMapping || $parentMapping == null');
            $content  = '<table width="' . round(100 / $totalPositions, 2) . '%">';
            $content .= '<tr>';
            $content .= '<td>';
            $content .= $mapping->getTitle();
            $content .= '</td>';
            $content .= '</tr>';
            $content .= '<tr>';
            $content .= '<td>';
            $content .= $this->renderAttributeNameSelectionContent($mapping);
            $content .= '</td>';
            $content .= '</tr>';
            $content .= '<tr>';
            $content .= '<td>';
            if ($mapping->getAttributeName() != null && $mapping->getPosition() > 0)
            {
                $content .= $this->renderValuesToParentValuesContent($mapping, $parentMapping);
            }
            else
            {
                $content .= '&#160;';
            }
            $content .= '</td>';
            $content .= '</tr>';
            $content .= '</table>';
            return $content;
        }

        protected function renderAttributeNameSelectionContent(DropDownDependencyCustomFieldMapping $mapping)
        {
            $inputName            = $this->formName . '[mappingData][' . $mapping->getPosition() . '][attributeName]';
            $inputId              = ZurmoHtml::getIdByName($inputName);
            $htmlOptions          = array();
            $htmlOptions['id']    = $inputId;

            if ($mapping->allowsAttributeSelection())
            {
                $htmlOptions['empty'] = Zurmo::t('Core', '(None)');
                $data                 = $mapping->getAvailableCustomFieldAttributes();
            }
            else
            {
                $htmlOptions['empty'] = $mapping->getSelectHigherLevelFirstMessage();
                $data                 = array();
            }
            Yii::app()->clientScript->registerScript('DropDownDependency' . $inputId,
                                                     $this->renderAttributeDropDownOnChangeScript($inputId));
            $content = ZurmoHtml::dropDownList($inputName, $mapping->getAttributeName(), $data, $htmlOptions);
            return $content;
        }

        protected function renderAttributeDropDownOnChangeScript($id)
        {
            assert('is_string($id)');
            $ajaxOnChangeUrl   = Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/' .
                                                       $this->ajaxActionId, $_GET);
            $ajaxSubmitScript  = ZurmoHtml::ajax(array(
                    'type'    => 'POST',
                    'url'     =>  $ajaxOnChangeUrl,
                    'update'  => '#' . $this->mappingDataDivId,
            ));
            return "$('#" . $id . "').live('change', function()
            {
                $('.AppContainer').removeAttr('style');
                $ajaxSubmitScript
            }
            );";
        }

        protected function renderValuesToParentValuesContent(DropDownDependencyCustomFieldMapping $mapping,
                                                             DropDownDependencyCustomFieldMapping $parentMapping)
        {
            assert('is_string($mapping->getAttributeName()) && $mapping->getPosition() > 0');
            $dataAndLabels    = CustomFieldDataUtil::
                                getDataIndexedByDataAndTranslatedLabelsByLanguage($mapping->getCustomFieldData(),
                                                                                  Yii::app()->language);
            $content  = '<table>';
            $valuePosition = 0;
            foreach ($dataAndLabels as $value => $label)
            {
                $content .= '<tr>';
                $content .= '<td>';
                $content .= Zurmo::t('DesignerModule', 'Display {label} when', array('{label}' => $label));
                $content .= '</td>';
                $content .= '</tr><tr>';
                $content .= '<td>';
                $content .= $this->renderValuesToParentValuesMappingDropDownContent(
                                        $parentMapping,
                                        $mapping->getPosition(),
                                        $value,
                                        $valuePosition,
                                        $mapping->getMappingDataSelectedParentValueByValue($value));
                $content .= '</td>';
                $content .= '</tr>';
                $valuePosition++;
            }
            $content .= '</table>';
            return $content;
        }

        protected function renderValuesToParentValuesMappingDropDownContent(DropDownDependencyCustomFieldMapping
                                                                            $parentMapping,
                                                                            $position,
                                                                            $value,
                                                                            $valuePosition,
                                                                            $selectedParentValue)
        {
            assert('is_int($position)');
            assert('is_string($value)');
            assert('is_int($valuePosition)');
            assert('is_string($selectedParentValue) || $selectedParentValue == null');
            $inputName           = $this->formName . '[mappingData][' . $position . '][valuesToParentValues][' . $value . ']';
            $inputId             = $this->formName . '_mappingData_' . $position . '_valuesToParentValues_' . $valuePosition;
            $htmlOptions          = array();
            $htmlOptions['id']    = $inputId;
            $htmlOptions['empty'] = Zurmo::t('Core', '(None)');
            $dataAndLabels        = CustomFieldDataUtil::
                                    getDataIndexedByDataAndTranslatedLabelsByLanguage(
                                        $parentMapping->getCustomFieldData(),
                                        Yii::app()->language);
            $content = ZurmoHtml::dropDownList($inputName, $selectedParentValue, $dataAndLabels, $htmlOptions);
            return $content;
        }
    }
?>