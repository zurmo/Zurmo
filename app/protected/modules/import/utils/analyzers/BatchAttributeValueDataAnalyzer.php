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
     * Base class for Batch analyzers. These type of analyzers loop over all the rows using the data provider instead
     * of doing one sql query on all the rows at once like the Sql analyzer.
     * @see SqlAttributeValueDataAnalyzer
     */
    abstract class BatchAttributeValueDataAnalyzer extends AttributeValueDataAnalyzer
    {
        /**
         * For each row's column value, perform the analysis.
         * @param mixed $value
         */
        abstract protected function analyzeByValue($value);

        /**
         * After analysis is complete, this method is used to determine the appropriate messages to create.
         */
        abstract protected function makeMessages();

        /**
         * Given a data provider and a column name, process over all the rows and analyze the column specified by
         * column name.  After the data is processed any messages required are made and added to the messages array.
         * @param object $dataProvider
         * @param string $columnName
         */
        protected function processAndMakeMessage(AnalyzerSupportedDataProvider $dataProvider, $columnName)
        {
            assert('is_string($columnName)');
            $page           = 0;
            $itemsProcessed = 0;
            $totalItemCount =  $dataProvider->getTotalItemCount(true);
            $dataProvider->getPagination()->setCurrentPage($page);
            while (null != $data = $dataProvider->getData(true))
            {
                foreach ($data as $rowData)
                {
                    $this->analyzeByValue($rowData->$columnName);
                    $itemsProcessed++;
                }
                if ($itemsProcessed < $totalItemCount)
                {
                    $page++;
                    $dataProvider->getPagination()->setCurrentPage($page);
                }
                else
                {
                    break;
                }
            }
            $this->makeMessages();
        }
    }
?>