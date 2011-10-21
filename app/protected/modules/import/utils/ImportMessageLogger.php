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
     * Helper class for logging messages during the final sanitize and import process.
     */
    class ImportMessageLogger extends MessageLogger
    {
        private $rowCount    = 0;

        private $pagingCount = 0;

        private $messageOutputInterval = 100;

        /**
         * During import, add a count after a row has been imported.  This can be used to provide paging information
         * regarding how far an import is.
         */
        public function countAfterRowImported()
        {
            $this->rowCount ++;
            $this->pagingCount ++;
            if($this->pagingCount > $this->messageOutputInterval)
            {
                $this->messageStreamer->addIgnoringTemplate('.');
                $this->pagingCount = 0;
            }
        }

        /**
         * Once the import is complete when using getData in ImportUtil::importByDataProvider, this can be called
         * to provide the final count.
         */
        public function countDataProviderGetDataImportCompleted()
        {
            $this->messageStreamer->addIgnoringTemplate("\n");
            $this->add(array(MessageLogger::INFO, Yii::t('Default', 'Import complete.  Rows processed: {rowsProcessed}',
                       array('{rowsProcessed}' => $this->rowCount))));
        }

        public function setMessageOutputInterval($interval)
        {
            assert('is_int($interval)');
            $this->messageOutputInterval = $interval;
        }
    }
?>