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

    /**
     * A view that renders contained views in a grid.
     */
    class GridView extends View
    {
        private $rows;
        private $columns;
        private $columnWidths;
        private $containedViews;

        /**
         * Constructs a GridView specifying a its number
         * of rows and columns.
         */
        public function __construct($rows, $columns)
        {
            assert('is_int($rows)    && $rows    > 0');
            assert('is_int($columns) && $columns > 0');
            $this->rows         = $rows;
            $this->columns      = $columns;
            $this->columnWidths = array_pad(array(), $columns, 0);
            $containedViews = array_pad(array(), $rows, null);
            for ($row = 0; $row < $rows; $row++)
            {
                $this->containedViews[] = array_pad(array(), $columns, null);
            }
        }

        /**
         * Overridden from View, specifies that GridView is not unique
         * to a page.
         * @see View::isUniqueToAPage()
         */
        public function isUniqueToAPage()
        {
            return false;
        }

        /**
         * Sets the contained view that will be rendered in a
         * specify cell of the grid.
         */
        public function setView(View $view, $row, $column)
        {
            assert('is_int($row)    && $row    >= 0 && $row    < $this->rows');
            assert('is_int($column) && $column >= 0 && $column < $this->columns');
            $this->containedViews[$row][$column] = $view;
        }

        /**
         * Sets the width of the column in pixels.
         */
        public function setColumnWidth($column, $width)
        {
            assert('is_int($column) && $column >= 0 && $column < $this->columns');
            assert('is_int($width)  && $width  > 0');
            $this->columnWidths[$column] = $width;
        }

        protected function renderContent()
        {
            // The if ($this->rows > 1) and if ($this->columns > 1)
            // checks make it only generate as many divs as it needs
            // to. If there is only one row or one columns it wont
            // wrap it in another div.
            $content = null;
            for ($row = 0; $row < $this->rows; $row++)
            {
                $rowContent = null;
                if ($this->rows > 1)
                {
                    //$rowContent .= "<div>\n";
                }
                $totalColumnsWidth = null;
                for ($column = 0; $column < $this->columns; $column++)
                {
                    $columnContent = null;
                    if ($this->columns > 1)
                    {
                        $styles = array();
                        if ($column < $this->columns - 1)
                        {
                            $styles[] = 'float: left;';
                        }
                        if (isset($totalColumnsWidth))
                        {
                            $styles[] = "margin-left: {$totalColumnsWidth}px;";
                        }
                        if ($this->columnWidths[$column] > 0)
                        {
                            $styles[] = 'width: ' . $this->columnWidths[$column] . 'px;';
                            $totalColumnsWidth   += $this->columnWidths[$column];
                        }
                        if (count($styles) > 0)
                        {
                           // $style = ' style="' . join($styles, ' ') . '"';
                        }
                        else
                        {
                            $style = '';
                        }
                      //  $columnContent .= "<div$style>\n";
                    }
                    $columnContent .= $this->containedViews[$row][$column]->render();
                    if ($this->columns > 1)
                    {
                       // $columnContent .= "</div>\n";
                    }
                    $rowContent .= $columnContent;
                }
                if ($this->rows > 1)
                {
                  //  $rowContent .= "</div>\n";
                }
                $content .= $rowContent;
            }
            return $content;
        }
    }
?>
