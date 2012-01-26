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

    Yii::import('zii.widgets.jui.CJuiWidget');

    /**
     * Widget for showing layout editor user interface. Contains
     * two main parts, the layout tools which shows available attributes
     * to place in the layout and the metadata layout itself.
     */
    class DesignerLayoutEditor extends CJuiWidget
    {
        /**
         *  @var boolean, can add additional panels to layout.
         */
        public $canAddPanels;

        /**
         * @var boolean, can add additional rows to layout.
         */
        public $canAddRows;

        /**
         *  @var boolean, can merge and split cells in layout.
         */
        public $canMergeAndSplitCells;

        /**
         *  @var boolean, can modify a cell's settings in layout.
         */
        public $canModifyCellSettings;

        /**
         *  @var boolean, can modify panel settings such as panel title.
         */
        public $canModifyPanelSettings;

        /**
         *  @var boolean, can move panels to different positions in layout.
         */
        public $canMovePanels;

        /**
         *  @var boolean, can move rows.
         */
        public $canMoveRows;

        /**
         *  @var boolean, can remove panels from layout.
         */
        public $canRemovePanels;

        /**
         *  @var boolean, can remove rows from layout.
         */
        public $canRemoveRows;

        /**
         *  @var string, which css file to utilize.
         */
        public $designerCssFile = 'css/designer.css';

        /**
         * @var DesignerLayoutAttributes object.
         */
        public $designerLayoutAttributes;

        /**
         *  @var integer, max cells per row.
         */
        public $maxCellsPerRow;

        /**
         *  @var boolean, should rows and attributes be placed at the same time.
         */
        public $mergeRowAndAttributePlacement;

        /**
         *  @var boolean, should we show the required attribute span if field is required
         */
        public $showRequiredAttributeSpan;

        /**
         *  @var array, metadata to use for layout.
         */
        public $viewMetadata;

        protected function canRemoveElement()
        {
            return !$this->mergeRowAndAttributePlacement;
        }

        protected function doesCellHaveAnyWideElements($cell)
        {
            if (is_array($cell['elements']))
            {
                foreach ($cell['elements'] as $elementInformation)
                {
                    $elementParams = array_slice($elementInformation, 2);
                    if (isset($elementParams['wide']) && $elementParams['wide'])
                    {
                        return true;
                    }
                }
            }

            return false;
        }

        protected function getCellSettingsDisplay($detailViewOnly, $cellIdName)
        {
            $content = '<div class="cell-settings modal-settings" title="'. Yii::t('Default', 'Cell Settings') .'">';
            $content .= '<table>';

            $content .= '<tr>';
            $content .= '<td>' . Yii::t('Default', 'Detail View Only') . '</td>';
            $content .= '<td>' . CHtml::checkBox( 'detailViewOnly_' . $cellIdName, $detailViewOnly,
            array('class' => 'settings-form-field')
            ) . '</td>';
            $content .= '</tr>';

            $content .= '</table>';
            $content .= '</div>';
            return $content;
        }

        protected function getPanelSettingsDisplay($title, $detailViewOnly, $panelIdName)
        {
            $content = '<div class="panel-settings modal-settings" title="'. Yii::t('Default', 'Panel Settings') .'">';
            $content .= '<table>';

            $content .= '<tr>';
            $content .= '<td>' . Yii::t('Default', 'Panel Title') . '</td>';
            $content .= '<td>' . CHtml::textField( 'title_' . $panelIdName,
                $title, array('class' => 'panel-title settings-form-field')) . '</td>';
            $content .= '</tr>';
            $content .= '<tr>';
            $content .= '<td>' . Yii::t('Default', 'Detail View Only') . '</td>';
            $content .= '<td>' . CHtml::checkBox( 'panelDetailViewOnly_' . $panelIdName, $detailViewOnly,
            array('class' => 'panel-title settings-form-field')
            ) . '</td>';
            $content .= '</tr>';

            $content .= '</table>';
            $content .= '</div>';
            return $content;
        }

        /**
         *  Initialize the class
         */
        public function init()
        {
            assert('$this->designerLayoutAttributes instanceof DesignerLayoutAttributes');
            assert('is_bool($this->canAddRows)');
            assert('is_bool($this->canMoveRows)');
            assert('is_bool($this->canRemoveRows)');
            assert('is_bool($this->canAddPanels)');
            assert('is_bool($this->canModifyPanelSettings)');
            assert('is_bool($this->canRemovePanels)');
            assert('is_bool($this->canMovePanels)');
            assert('is_bool($this->canModifyCellSettings)');
            assert('is_bool($this->canMergeAndSplitCells)');
            assert('is_bool($this->mergeRowAndAttributePlacement)');
            assert('is_int($this->maxCellsPerRow)');
            assert('is_bool($this->showRequiredAttributeSpan)');
            assert('!empty($this->viewMetadata["global"]["panels"])');
            if ($this->canMoveRows == false)
            {
                assert('!$this->canAddRows');
            }
            if ($this->canMovePanels == false)
            {
                assert('!$this->canAddPanels');
            }
            $this->registerScripts();
            parent::init();
        }

        protected function registerScripts()
        {
            $baseJuiPortletsScriptUrl = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('ext.zurmoinc.framework.widgets.assets'));
            $cs = Yii::app()->getClientScript();
            $cs->registerScriptFile($baseJuiPortletsScriptUrl . '/designer/Designer.js', CClientScript::POS_END);
        }

        protected function renderLayout()
        {
            $content  = '<div id="layout-container">';
            if ($this->canMovePanels)
            {
                $content .= '<ul class="sortable-panel-list panel-list">';
            }
            else
            {
                $content .= '<ul class="panel-list">';
            }

            foreach ($this->viewMetadata['global']['panels'] as $panelNumber => $panel)
            {
                $content .= '<li id="panel_' . $panelNumber . '" class="ui-state-default sortable-panel">';
                $content .= '<span class="panel-title-display">' .
                            Yii::t('Default', ArrayUtil::getArrayValue($panel, 'title')) . '&#160;</span>';
                if ($this->canMoveRows)
                {
                    $content .= '<span class="panel-handle-icon ui-icon ui-icon-arrow-4">&#160;</span>';
                }
                if ($this->canRemovePanels)
                {
                    $content .= '<span class="panel-element-icon ui-icon ui-icon-trash">&#160;</span>';
                }
                if ($this->canModifyPanelSettings)
                {
                    $content .= '<span class="panel-modify-settings-link panel-element-icon ui-icon ui-icon-wrench">&#160;</span>';
                }
                $content .= '<div class="sortable-row-list-container">';
                if ($this->canMoveRows)
                {
                    $content .= '<ul class="sortable-row-list sortable-row-connector">';
                }
                else
                {
                    $content .= '<ul class="sortable-row-connector">';
                }
                foreach ($panel['rows'] as $row)
                {
                    $content .= '<li class="ui-state-default">';
                    if ($this->canMoveRows)
                    {
                        $content .= '<span class="row-handle-icon ui-icon ui-icon-arrow-4">&#160;</span>';
                    }
                    foreach ($row['cells'] as $cell)
                    {
                        if ($this->doesCellHaveAnyWideElements($cell) ||
                            $this->maxCellsPerRow == 1
                        )
                        {
                            $cssClassName                = 'layout-single-column';
                            $cssCellMergeIconClassName   = 'ui-icon-circle-plus';
                        }
                        else
                        {
                            $cssClassName                = 'layout-double-column';
                            $cssCellMergeIconClassName   = 'ui-icon-circle-minus';
                        }
                        $content .= '<div class="' . $cssClassName . ' droppable-cell-container ui-state-hover">';
                        if (is_array($cell['elements']))
                        {
                            assert('count($cell["elements"]) == 1');
                            $elementInformation = $cell['elements'][0];
                            if ($elementInformation['attributeName'] != null)
                            {
                                $attribute = $this->designerLayoutAttributes->getByAttributeNameAndType(
                                    $elementInformation['attributeName'],
                                    $elementInformation['type']
                                );
                                $divId = $attribute['attributeIdPrefix'] . '_Placed';
                                $content .= '<div id="' . $divId . '" ';
                                if ($this->mergeRowAndAttributePlacement)
                                {
                                    $content .= 'class="cell-element">';
                                }
                                else
                                {
                                    $content .= 'class="movable-cell-element cell-element">';
                                }
                                $content .= '<span class="cell-handle-icon ui-icon ui-icon-arrow-4">&#160;</span>';
                                $content .= $attribute['attributeLabel'];
                                if ($this->showRequiredAttributeSpan && $attribute['isRequired'])
                                {
                                    $content .= '<span class="required">*</span>';
                                }
                                if ($this->canRemoveElement())
                                {
                                    $content .= '<span class="cell-element-icon ui-icon ui-icon-trash">&#160;</span>';
                                }
                                if ($this->canModifyCellSettings)
                                {
                                    $content .= '<span class="cell-modify-settings-link cell-element-icon ui-icon ui-icon-wrench">&#160;</span>';
                                }
                                $content .= $this->getCellSettingsDisplay(
                                    ArrayUtil::getArrayValue($cell, 'detailViewOnly'),
                                    $divId
                                );
                                $content .= '</div>';
                                unset($divId);
                            }
                            unset($elementInformation);
                        }
                        $content .= '</div>';
                    }
                    $content .= '<span class="row-element-icon ui-icon ui-icon-trash">&#160;</span>';
                    if ($this->canMergeAndSplitCells)
                    {
                        $content .= '<span class="row-element-icon ui-icon ' . $cssCellMergeIconClassName .'">&#160;</span>';
                    }
                    $content .= '</li>';
                }
                $content .= '</ul>';
                $content .= '</div>';

                $content .= $this->getPanelSettingsDisplay(
                    ArrayUtil::getArrayValue($panel, 'title'),
                    ArrayUtil::getArrayValue($panel, 'detailViewOnly'),
                    $panelNumber
                );
                $content .= '</li>';
            }
            $content .= '</ul>';
            $content .= '</div>';
            return $content;
        }

        protected function renderLayoutTools()
        {
            $content  = '<div class = "sticky-anchor"></div>';
            $content .= '<div class = "sticky">';
            $content .= '<div class = "stickyD"></div>';
            $content .= '<div class = "layout-parts-container">';
            $content .= '<div class = "layout-parts">';
            if ($this->canAddRows)
            {
                $content .= '<ul>';
                $content .= '<li class = "rowToPlace ui-state-default">' . Yii::t('Default', 'Row') . '</li>';
                $content .= '</ul>';
            }
            if ($this->canAddPanels)
            {
                $content .= '<ul>';
                $content .= '<li class = "panelToPlace ui-state-default">' . Yii::t('Default', 'Panel') . '</li>';
                $content .= '</ul>';
            }
            $content .= '</div>';
            $content .= '<div class = "layout-elements">';
            $startColumnDiv = true;
            $endColumnDiv   = false;
            $columnDivCount = 0;
            foreach ($this->designerLayoutAttributes->get() as $data)
            {
                $columnDivCount++;
                if ($startColumnDiv)
                {
                    $startColumnDiv  = false;
                    $content .= '<div class = "layout-elements-column-container">';
                }
                if ($columnDivCount == 2)
                {
                    $startColumnDiv  = true;
                    $endColumnDiv    = true;
                    $columnDivCount  = 0;
                }
                if ($data['availableToSelect'])
                {
                    $cssClass = 'ui-state-default';
                }
                else
                {
                    $cssClass = 'ui-state-disabled';
                }
                $content .= '<div id = "' . $data['attributeIdPrefix'] . '_elementToPlace" class = "element-to-place ui-state-default ' . $cssClass . '">';
                $content .= $data['attributeLabel'];
                if ($this->showRequiredAttributeSpan && $data['isRequired'])
                {
                    $content .= '<span class="required">*</span>';
                }
                $content .= '</div>';
                if ($endColumnDiv)
                {
                    $content .= '</div>';
                    $endColumnDiv    = false;
                }
            }
            if ($startColumnDiv == false)
            {
                $content .= '</div>';
                unset($endColumnDiv);
            }
            $content .= '</div>';
            $content .= '</div>';
            $content .= '</div>';
            return $content;
        }

        /**
         * Run this widget.
         * This method registers necessary javascript and renders the needed HTML code.
         */
        public function run()
        {
            Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $this->id,
                "designer.init(
                    " . BooleanUtil::boolToString($this->canAddPanels) .",
                    " . BooleanUtil::boolToString($this->canModifyPanelSettings) .",
                    " . BooleanUtil::boolToString($this->canRemovePanels) .",
                    " . BooleanUtil::boolToString($this->canMovePanels) .",
                    " . BooleanUtil::boolToString($this->canAddRows) .",
                    " . BooleanUtil::boolToString($this->canMoveRows) .",
                    " . BooleanUtil::boolToString($this->canRemoveRows) .",
                    " . BooleanUtil::boolToString($this->canModifyCellSettings) .",
                    " . BooleanUtil::boolToString($this->canMergeAndSplitCells) .",
                    " . BooleanUtil::boolToString($this->mergeRowAndAttributePlacement) .",
                    " . $this->maxCellsPerRow . ",
                    '" . $this->getPanelSettingsDisplay(null, false, '{panelId}') . "',
                    '" . $this->getCellSettingsDisplay(false, '{cellId}') . "'
                );");
            echo $this->renderLayoutTools();
            echo $this->renderLayout();
        }
    }
?>
