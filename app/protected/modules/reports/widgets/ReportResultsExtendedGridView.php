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

    Yii::import('zii.widgets.grid.CGridView');

    /**
     * Extends the yii CGridView to provide additional functionality.
     * @see CGridView class
     */
    class ReportResultsExtendedGridView extends ExtendedGridView
    {
        public $expandableRows = false;

        /**
         * Used by matrix reporting or when there are headers that are for columns with values but for columns that
         * also have header labels in them.
         * @var array
         */
        public $leadingHeaders;

        public function renderTableHeader()
        {
            if (!$this->hideHeader)
            {
                echo "<thead>\n";
                if ($this->leadingHeaders != null)
                {
                    $this->renderLeadingHeaders();
                }
                echo "<tr>\n";
                foreach ($this->columns as $column)
                {
                    $column->renderHeaderCell();
                }
                echo "</tr>\n";
                echo "</thead>\n";
            }
        }

        public function renderTableBody()
        {
            $data = $this->dataProvider->getData();
            $n    = count($data);
            echo "<tbody>\n";

            if ($n > 0)
            {
                for ($row = 0; $row < $n; ++$row)
                {
                    $this->renderTableRow($row);
                    if ($this->expandableRows)
                    {
                        $this->renderExpandableRow($this->dataProvider->data[$row]->getId());
                    }
                }
            }
            else
            {
                echo '<tr><td colspan="' . count($this->columns) . '" class="empty">';
                $this->renderEmptyText();
                echo "</td></tr>\n";
            }
            echo "</tbody>\n";
        }

        /**
         * @param $id
         */
        protected function renderExpandableRow($id)
        {
            echo '<tr style="display:none;"><td class="hasDrillDownContent" colspan="' . (count($this->columns)) . '">';
            echo '<div class="drillDownContent" id="drillDownContentFor-' . $id . '"></div>';
            echo "</td></tr>\n";
        }

        protected function renderLeadingHeaders()
        {
            $previousGroupByValuesCount = 1;
            for ($i = 0; $i < count($this->leadingHeaders['rows']); $i++)
            {
                echo ZurmoHtml::openTag('tr');
                for ($j = 0; $j < $this->leadingHeaders['axisCrossingColumnCount']; $j++)
                {
                    echo ZurmoHtml::tag('th', array(), null);
                }
                for ($k = 0; $k < $previousGroupByValuesCount; $k++)
                {
                    foreach ($this->leadingHeaders['rows'][$i]['groupByValues'] as $value)
                    {
                        echo ZurmoHtml::tag('th',
                             array('colspan' => $this->leadingHeaders['rows'][$i]['colSpan']), $value);
                    }
                }
                $previousGroupByValuesCount = count($this->leadingHeaders['rows'][$i]['groupByValues']);
                echo '</tr>';
            }
        }
    }
?>
