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
     * Helper class for working with import results as rows are processed. As rows are processed, their individual row
     * results can be collected in this class.  Additionally methods in the class can be used to update the rows
     * status and serialize messages for each row that is attempted to be imported.
     */
    class ImportResultsUtil
    {
        /**
         * Import object.
         * @var object
         */
        protected $import;

        /**
         * Array of row results data which includes row results status  and any messages.
         * @var array
         */
        protected $rowResultsData = array();

        /**
         * @param object $import
         */
        public function __construct(Import $import)
        {
            $this->import = $import;
        }

        /**
         * Given an ImportRowDataResultsUtil, add it to the row results data collection.
         * @param object $importRowDataResultsUtil
         */
        public function addRowDataResults(ImportRowDataResultsUtil $importRowDataResultsUtil)
        {
            $this->rowResultsData[] = $importRowDataResultsUtil;
        }

        /**
         * After the rows have been imported or attempted to be imported, process the status and messages of each
         * row to the temporary table where these rows are stored. This information can then be used later as feedback
         * in the user interface on any issues that need resolving any of the rows.
         */
        public function processStatusAndMessagesForEachRow()
        {
            foreach ($this->rowResultsData as $rowResult)
            {
                assert('$rowResult instanceof ImportRowDataResultsUtil');
                $tableName = $this->import->getTempTableName();
                $status    = $rowResult->getStatus();
                assert('$status != null');
                $messages  = $rowResult->getMessages();
                if ($messages != null)
                {
                    $serializedMessagesOrNull = serialize($messages);
                }
                else
                {
                    $serializedMessagesOrNull = null;
                }
                $rowId     = $rowResult->getId();
                ImportDatabaseUtil::updateRowAfterProcessing($tableName, $rowId, $status, $serializedMessagesOrNull);
            }
        }

        public static function convertSerializedMessagesToDisplayReadyString($messages)
        {
            assert('is_string($messages)');
            $unserializedMessages = unserialize($messages);
            $content = null;
            foreach ($unserializedMessages as $message)
            {
                if ($content != null)
                {
                    $content .= '<br/>';
                }
                $content .= $message;
            }
            return $content;
        }
    }
?>