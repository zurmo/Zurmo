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
     * Extends the ExtendedGridView to provide a 'stacked' format for viewing lists of data.
     * @see ExtendedGridView class
     */
    class StackedExtendedGridView extends ExtendedGridView
    {
        /**
        * Renders the data items for the grid view.
        */
        public function renderItems()
        {
            if ($this->dataProvider->getItemCount() > 0 || $this->showTableOnEmpty)
            {
                echo "<table class=\"{$this->itemsCssClass} stacked-list\">\n";
                ob_start();
                $this->renderTableBody();
                $body = ob_get_clean();
                $this->renderTableFooter();
                echo $body; // TFOOT must appear before TBODY according to the standard.
                echo "</table>";
            }
            else
            {
                $this->renderEmptyText();
            }
        }

        /**
         * Renders the table body.
         */
        public function renderTableBody()
        {
            $data = $this->dataProvider->getData();
            $n = count($data);
            echo "<tbody>\n";

            if ($n > 0)
            {
                for ($row = 0; $row < $n; ++$row) // Not Coding Standard
                {
                    $this->renderTableRow($row);
                }
            }
            else
            {
                echo '<tr><td>';
                $this->renderEmptyText();
                echo "</td></tr>\n";
            }
            echo "</tbody>\n";
        }

        /**
         * Renders a table body row.
         * @param integer $row the row number (zero-based).
         */
        public function renderTableRow($row)
        {
            if ($this->rowCssClassExpression !== null)
            {
                $data = $this->dataProvider->data[$row];
                echo '<tr class = "' . $this->evaluateExpression($this->rowCssClassExpression, array('row' => $row, 'data' => $data)) . '">';
            }
            elseif (is_array($this->rowCssClass) && ($n = count($this->rowCssClass)) > 0)
            {
                echo '<tr class="' . $this->rowCssClass[$row%$n] . '">';
            }
            else
            {
                echo '<tr>';
            }
            echo '<td>';
            foreach ($this->columns as $column)
            {
                if ($column instanceof RowMenuColumn)
                {
                    continue;
                }
                elseif ($column instanceof CGridColumn)
                {
                    $column->attachBehavior('stackedDataCell', new StackedGridColumnBehavior());
                    $column->renderStackedDataCell($row);
                }
                else
                {
                    $column->renderDataCell($row);
                }
            }
            echo "</td>\n";
            foreach ($this->columns as $column)
            {
                if ($column instanceof RowMenuColumn)
                {
                    $column->attachBehavior('stackedDataCell', new StackedGridColumnBehavior());
                    echo "<td>\n";
                    $column->renderStackedDataCell($row);
                    echo "</td>\n";
                }
            }
            echo "</tr>\n";
        }
    }
?>
