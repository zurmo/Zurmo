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
     * View class for the filter components for the report wizard user interface
     */
    class FiltersForReportWizardView extends ComponentWithTreeForReportWizardView
    {
        /**
         * @return string
         */
        public static function getTreeType()
        {
            return FilterForReportForm::getType();
        }

        /**
         * @return string
         */
        public static function getWizardStepTitle()
        {
            return Zurmo::t('ReportsModule', 'Select Filters');
        }

        /**
         * @return string
         */
        public static function getPreviousPageLinkId()
        {
            return 'filterBysPreviousLink';
        }

        /**
         * @return string
         */
        public static function getNextPageLinkId()
        {
            return 'filterBysNextLink';
        }

        /**
         * @return string
         */
        protected function renderExtraDroppableAttributesContent()
        {
            return $this->renderStructureContent();
        }

        /**
         * @return string
         */
        protected function getAddAttributeUrl()
        {
            return  Yii::app()->createUrl('reports/default/addAttributeFromTree',
                        array_merge($_GET, array('type'                       => $this->model->type,
                                                 'treeType'                   => static::getTreeType(),
                                                 'trackableStructurePosition' => true)));
        }

        protected function registerScripts()
        {
            parent::registerScripts();
            Yii::app()->clientScript->registerScript('showStructurePanels' . $this->form->getId(), "
                $('#show-filters-structure-div-link').click( function()
                    {
                        $('#show-filters-structure-div').show();
                        $('#show-filters-structure-div-link').hide();
                        return false;
                    }
                );");
        }

        /**
         * @return string
         */
        protected function renderStructureContent()
        {
            $style1 = '';
            $style2 = 'display:none;';
            if (count($this->model->filters) > 0)
            {
                $style3 = '';
            }
            else
            {
                $style3 = 'display:none;';
            }
            $content  = ZurmoHtml::link(Zurmo::t('ReportsModule', 'Modify Structure'), '#',
                            array('id'    => 'show-filters-structure-div-link',
                                  'style' => $style1));
            $content .= ZurmoHtml::tag('div',
                            array('id'    => 'show-filters-structure-div',
                                  'class' => 'has-lang-label',
                                  'style' => $style2), $this->renderStructureInputContent());
            $content  = ZurmoHtml::tag('div', array('id'    => 'show-filters-structure-wrapper',
                                                     'style' => $style3), $content);
            return $content;
        }

        /**
         * @return string
         */
        protected function renderStructureInputContent()
        {
            $idInputHtmlOptions  = array('id'    => $this->getStructureInputId(),
                                         'name'  => $this->getStructureInputName(),
                                         'class' => 'filters-structure-input');
            $content             = $this->form->textField($this->model, 'filtersStructure', $idInputHtmlOptions);
            $content            .= ZurmoHtml::tag('span', array(), Zurmo::t('ReportsModule', 'Search Operator'));
            $content            .= $this->form->error($this->model, 'filtersStructure');
            return $content;
        }

        /**
         * @return string
         */
        protected function getStructureInputId()
        {
            return get_class($this->model) . '_filtersStructure';
        }

        /**
         * @return string
         */
        protected function getStructureInputName()
        {
            return get_class($this->model) . '[filtersStructure]';
        }

        /**
         * @return string
         */
        protected function getReportAttributeRowAddOrRemoveExtraScript()
        {
            return 'rebuildReportFiltersAttributeRowNumbersAndStructureInput("' . get_class($this) . '");';
        }

        /**
         * @return int
         */
        protected function getItemsCount()
        {
            return count($this->model->filters);
        }

        /**
         * @param int $rowCount
         * @return array|string
         */
        protected function getItemsContent(& $rowCount)
        {
            return $this->renderItems($rowCount, $this->model->filters, true);
        }

        /**
         * @return string
         */
        public static function getZeroComponentsClassName()
        {
            return 'ZeroFilters';
        }

        /**
         * @return string
         */
        protected function getZeroComponentsMessageContent()
        {
            return '<div class="large-icon"></div><h2>' . Zurmo::t('ReportsModule', 'Drag or double click your filters here') . '</h2>';
        }
    }
?>