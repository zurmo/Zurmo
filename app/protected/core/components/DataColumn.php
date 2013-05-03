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
     * Override class for CDataColumn in order to allow public access to renderDataCellContent and to allow offset
     * information to properly pass into each column
     * @see CGridView class
     */
    class DataColumn extends CDataColumn
    {
        /**
         * Renders the header cell content.
         * This method will render a link that can trigger the sorting if the column is sortable.
         */
        protected function renderHeaderCellContent()
        {
            if($this->grid->enableSorting && $this->sortable && $this->name !== null)
            {
                echo $this->grid->dataProvider->getSort()->link($this->name, $this->header, array('class' => 'sort-link'));
            }
            elseif($this->name!==null && $this->header===null)
            {
                if($this->grid->dataProvider instanceof CActiveDataProvider)
                {
                    echo CHtml::encode($this->grid->dataProvider->model->getAttributeLabel($this->name));
                }
                elseif($this->grid->dataProvider instanceof RedBeanModelDataProvider)
                {
                    $modelClassName = $this->grid->dataProvider->getModelClassName();
                    if($modelClassName::isAnAttribute($this->name))
                    {
                        echo CHtml::encode($modelClassName::getAnAttributeLabel($this->name));
                    }
                    else
                    {
                        echo CHtml::encode($this->name);
                    }
                }
                else
                {
                    echo CHtml::encode($this->name);
                }
            }
            else
            {
                echo parent::renderHeaderCellContent();
            }
        }

        /**
         * Override to add in offset information
         * (non-PHPdoc)
         * @see CDataColumn::renderDataCellContent()
         */
        protected function renderDataCellContent($row, $data)
        {
            if ($this->value !== null)
            {
                $pagination = $this->grid->dataProvider->getPagination();
                if (isset($pagination))
                {
                    $offset = $pagination->getOffset();
                }
                else
                {
                    $offset = 0;
                }
                $value = $this->evaluateExpression($this->value, array('data' => $data, 'row' => $row, 'offset' => ($offset + $row)));
            }
            elseif ($this->name !== null)
            {
                $value = ZurmoHtml::value($data, $this->name);
            }
            if ($value === null)
            {
                echo $this->grid->nullDisplay;
            }
            else
            {
                echo $this->grid->getFormatter()->format($value, $this->type);
            }
        }

        public function renderDataCellContentFromOutsideClass($row, $data)
        {
            $this->renderDataCellContent($row, $data);
        }
    }
?>
