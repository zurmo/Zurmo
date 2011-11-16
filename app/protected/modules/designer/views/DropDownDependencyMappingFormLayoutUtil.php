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
            foreach($this->dependencyCollection as $dropDownDependencyCustomFieldMapping)
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
            if($mapping->getAttributeName() != null && $mapping->getPosition() > 0)
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
            $inputName         = $this->formName . '[mappingData][' . $mapping->getPosition() . '][attributeName]';
            $inputId           = CHtml::getIdByName($inputName);

            $htmlOptions          = array();
            $htmlOptions['id']    = $inputId;

            if($mapping->allowsAttributeSelection())
            {
                $htmlOptions['empty'] = Yii::t('Default', '(None)');
                $data                 = $mapping->getAvailableCustomFieldAttributes();
            }
            else
            {
                $htmlOptions['empty'] = $mapping->getSelectHigherLevelFirstMessage();
                $data                 = array();
            }
            Yii::app()->clientScript->registerScript('DropDownDependency' . $inputId,
                                                     $this->renderAttributeDropDownOnChangeScript($inputId));
            $content = CHtml::dropDownList($inputName, $mapping->getAttributeName(), $data, $htmlOptions);
            return $content;
        }

        protected function renderAttributeDropDownOnChangeScript($id)
        {
            assert('is_string($id)');
            $ajaxOnChangeUrl   = Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/' .
                                                       $this->ajaxActionId, $_GET);
            $ajaxSubmitScript  = CHtml::ajax(array(
                    'type'    => 'POST',
                    'url'     =>  $ajaxOnChangeUrl,
                    'update'  => '#' . $this->mappingDataDivId,
            ));
            return "$('#" . $id . "').live('change', function()
            {
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
            $content .= '<tr>';
            $content .= '<td>';
            $content .= '<b>' . Yii::t('Default', 'Value') . '</b>';
            $content .= '</td>';
            $content .= '<td>';
            $content .= '<b>' . Yii::t('Default', 'Show If') . '</b>';
            $content .= '</td>';
            $content .= '</tr>';
            foreach($dataAndLabels as $value => $label)
            {
                $content .= '<tr>';
                $content .= '<td>';
                $content .= $label;
                $content .= '</td>';
                $content .= '<td>';
                $content .= $this->renderValuesToParentValuesMappingDropDownContent(
                                        $parentMapping,
                                        $mapping->getPosition(),
                                        $value,
                                        $mapping->getMappingDataSelectedParentValueByValue($value));
                $content .= '</td>';
                $content .= '</tr>';
            }
            $content .= '</table>';
            return $content;
        }

        protected function renderValuesToParentValuesMappingDropDownContent(DropDownDependencyCustomFieldMapping
                                                                            $parentMapping,
                                                                            $position,
                                                                            $value,
                                                                            $selectedParentValue)
        {
            $inputName         = $this->formName . '[mappingData][' . $position . '][valuesToParentValues][' . $value . ']';
            $inputId           = CHtml::getIdByName($inputName);

            $htmlOptions          = array();
            $htmlOptions['id']    = $inputId;
            $htmlOptions['empty'] = Yii::t('Default', '(None)');
            $dataAndLabels        = CustomFieldDataUtil::
                                    getDataIndexedByDataAndTranslatedLabelsByLanguage(
                                        $parentMapping->getCustomFieldData(),
                                        Yii::app()->language);
            $content = CHtml::dropDownList($inputName, $selectedParentValue, $dataAndLabels, $htmlOptions);
            return $content;
        }
    }
?>