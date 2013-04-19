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
     * Class for working with matrix report results in a grid
     */
    class MatrixReportResultsGridView extends ReportResultsGridView
    {
        /**
         * @var int
         */


        /**
         * @return string
         */
        protected function renderResultsGridContent()
        {
            if ($this->dataProvider->calculateTotalGroupingsCount() > MatrixReportDataProvider::$maximumGroupsCount)
            {
                return $this->renderMaximumGroupsContent();
            }
            return parent::renderResultsGridContent();
        }

        /**
         * @return bool
         */
        protected function isDataProviderValid()
        {
            if (!$this->dataProvider instanceof MatrixReportDataProvider)
            {
                return false;
            }
            return true;
        }

        /**
         * @return string
         */
        protected function renderMaximumGroupsContent()
        {
            $content  = '<div class="general-issue-notice"><span class="icon-notice"></span><p>';
            $content .= Zurmo::t('ReportsModule', 'Your report has too many groupings to plot. ' .
                'Please adjust the filters to reduce the number below {maximum}. ' .
                '<br />The maximum is calculated as x-axis groupings multiplied by y-axis groupings',
                array('{maximum}' => MatrixReportDataProvider::$maximumGroupsCount));
            $content .= '</p></div>';
            return $content;
        }

        /**
         * @return array
         */
        protected function getCGridViewColumns()
        {
            $columns        = array();
            $attributeKey   = 0;

            foreach ($this->dataProvider->getDisplayAttributesThatAreYAxisGroupBys() as $displayAttribute)
            {
                $columnClassName  = $this->resolveColumnClassNameForListViewColumnAdapter($displayAttribute);
                $attributeName    = MatrixReportDataProvider::resolveHeaderColumnAliasName(
                                    $displayAttribute->columnAliasName);
                $params           = $this->resolveParamsForColumnElement($displayAttribute);
                $columnAdapter    = new $columnClassName($attributeName, $this, $params);
                $column           = $columnAdapter->renderGridViewData();
                $column['header'] = $displayAttribute->label;
                $column['class']  = 'YAxisHeaderColumn';
                array_push($columns, $column);
            }

            for ($i = 0; $i < $this->dataProvider->getXAxisGroupByDataValuesCount(); $i++)
            {
                foreach ($this->dataProvider->resolveDisplayAttributes() as $displayAttribute)
                {
                    if (!$displayAttribute->queryOnly)
                    {
                        $columnClassName  = $this->resolveColumnClassNameForListViewColumnAdapter($displayAttribute);
                        $attributeName    = MatrixReportDataProvider::resolveColumnAliasName($attributeKey);
                        $params           = $this->resolveParamsForColumnElement($displayAttribute);
                        $columnAdapter    = new $columnClassName($attributeName, $this, $params);
                        $column           = $columnAdapter->renderGridViewData();
                        $column['header'] = $displayAttribute->label;
                        if (!isset($column['class']))
                        {
                            $column['class'] = 'DataColumn';
                        }
                        array_push($columns, $column);
                        $attributeKey++;
                    }
                }
            }
            return $columns;
        }

        /**
         * @return array
         */
        protected function getLeadingHeaders()
        {
            return $this->dataProvider->makeAxisCrossingColumnCountAndLeadingHeaderRowsData();
        }
    }
?>